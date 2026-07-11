<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class DocumentService
{
    public function __construct(private readonly CryptographyService $crypto)
    {
    }

    public function store(array $data, int|string $ownerId): Document
    {
        /** @var UploadedFile $file */
        $file = $data['file'];
        $path = $file->store('documents', 'private');
        $hash = $this->crypto->hashFile($file->getRealPath());
        $token = 'DOC-' . Str::upper(Str::random(32));
        $timestamp = now('UTC');
        $payload = $this->crypto->documentPayload([
            'document_token' => $token,
            'hash_algorithm' => 'SHA-256',
            'issued_at' => (string) $data['issued_at'],
            'owner_id' => (string) $ownerId,
            'pharmacist_id' => $data['pharmacist_id'] ?? null,
            'sha256_hash' => $hash,
            'timestamp' => $timestamp->toIso8601String(),
            'title' => $data['title'],
            'type' => $data['type'],
        ]);
        $signature = $this->crypto->sign($payload);

        return DB::transaction(fn () => Document::query()->create([
            'pharmacist_id' => $data['pharmacist_id'] ?? null,
            'owner_id' => $ownerId,
            'title' => $data['title'],
            'type' => $data['type'],
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha256_hash' => $hash,
            'current_sha256_hash' => $hash,
            'hash_algorithm' => 'SHA-256',
            'issued_at' => $data['issued_at'],
            'qr_code_token' => $token,
            'signature_payload' => $payload,
            'signature' => $signature['signature'],
            'signature_algorithm' => $signature['signature_algorithm'],
            'public_key' => $signature['public_key'],
            'public_key_fingerprint' => $signature['public_key_fingerprint'],
            'trusted_timestamp' => $timestamp,
            'integrity_verified_at' => $timestamp,
            'integrity_status' => 'valid',
            'proof_metadata' => [
                'non_repudiation' => true,
                'timestamp_authority' => 'local-trusted-clock',
                'signature_scope' => 'document_token|sha256_hash|owner|issued_at|timestamp',
            ],
            'status' => 'uploaded',
        ]));
    }

    public function sign(Document $document): Document
    {
        $payload = $this->signedPayload($document);
        $signature = $this->crypto->sign($payload);

        $document->forceFill([
            'status' => 'signed',
            'signature_payload' => $payload,
            'signature' => $signature['signature'],
            'signature_algorithm' => $signature['signature_algorithm'],
            'public_key' => $signature['public_key'],
            'public_key_fingerprint' => $signature['public_key_fingerprint'],
            'trusted_timestamp' => now('UTC'),
        ])->save();

        return $document->refresh();
    }

    public function verify(Document $document): array
    {
        $absolutePath = Storage::disk('private')->path($document->path);
        $currentHash = file_exists($absolutePath) ? $this->crypto->hashFile($absolutePath) : null;
        $integrityValid = $currentHash !== null && hash_equals((string) $document->sha256_hash, $currentHash);
        $signatureValid = $this->crypto->verifySignature(
            (string) $document->signature_payload,
            (string) $document->signature,
            $document->public_key,
        );

        $document->forceFill([
            'current_sha256_hash' => $currentHash,
            'integrity_verified_at' => now('UTC'),
            'integrity_status' => $integrityValid && $signatureValid ? 'valid' : 'invalid',
        ])->save();

        return [
            'valid' => $integrityValid && $signatureValid,
            'integrity_valid' => $integrityValid,
            'signature_valid' => $signatureValid,
            'stored_sha256_hash' => $document->sha256_hash,
            'current_sha256_hash' => $currentHash,
            'signature_algorithm' => $document->signature_algorithm,
            'public_key_fingerprint' => $document->public_key_fingerprint,
            'trusted_timestamp' => $document->trusted_timestamp?->toIso8601String(),
        ];
    }

    private function signedPayload(Document $document): string
    {
        return $this->crypto->documentPayload([
            'document_id' => (string) $document->id,
            'document_token' => $document->qr_code_token,
            'hash_algorithm' => $document->hash_algorithm ?? 'SHA-256',
            'owner_id' => (string) $document->owner_id,
            'sha256_hash' => $document->sha256_hash,
            'signed_at' => now('UTC')->toIso8601String(),
            'status' => 'signed',
            'title' => $document->title,
            'type' => $document->type,
        ]);
    }
}
