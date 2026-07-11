<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class CryptographyService
{
    private const HASH_ALGORITHM = 'sha256';
    private const SIGNATURE_ALGORITHM = OPENSSL_ALGO_SHA256;

    public function hashFile(string $absolutePath): string
    {
        $hash = hash_file(self::HASH_ALGORITHM, $absolutePath);

        if ($hash === false) {
            throw new RuntimeException('Impossible de calculer l empreinte cryptographique du fichier.');
        }

        return $hash;
    }

    public function hashPayload(array|string $payload): string
    {
        $canonical = is_array($payload)
            ? json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $payload;

        return hash(self::HASH_ALGORITHM, $canonical);
    }

    public function sign(string $payload): array
    {
        $privateKey = openssl_pkey_get_private($this->privateKeyPem());

        if ($privateKey === false) {
            throw new RuntimeException('Cle privee cryptographique invalide.');
        }

        $signature = '';
        $signed = openssl_sign($payload, $signature, $privateKey, self::SIGNATURE_ALGORITHM);

        if (! $signed) {
            throw new RuntimeException('Signature numerique impossible.');
        }

        $publicKey = $this->publicKeyPem();

        return [
            'signature' => base64_encode($signature),
            'signature_algorithm' => 'RSA-4096-SHA256',
            'public_key' => $publicKey,
            'public_key_fingerprint' => $this->publicKeyFingerprint($publicKey),
        ];
    }

    public function verifySignature(string $payload, string $signature, ?string $publicKey = null): bool
    {
        $decoded = base64_decode($signature, true);

        if ($decoded === false) {
            return false;
        }

        $result = openssl_verify($payload, $decoded, $publicKey ?: $this->publicKeyPem(), self::SIGNATURE_ALGORITHM);

        return $result === 1;
    }

    public function publicKeyFingerprint(?string $publicKey = null): string
    {
        return hash(self::HASH_ALGORITHM, (string) ($publicKey ?: $this->publicKeyPem()));
    }

    public function documentPayload(array $data): string
    {
        ksort($data);

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function privateKeyPem(): string
    {
        $configured = (string) config('app.validika_private_key', env('VALIDIKA_PRIVATE_KEY', ''));

        if ($configured !== '') {
            return str_contains($configured, 'BEGIN')
                ? str_replace('\\n', "\n", $configured)
                : (string) base64_decode($configured, true);
        }

        $path = storage_path('app/private/validika_rsa4096_private.pem');

        if (! file_exists($path)) {
            $this->generateKeyPair($path);
        }

        return (string) file_get_contents($path);
    }

    private function publicKeyPem(): string
    {
        $configured = (string) config('app.validika_public_key', env('VALIDIKA_PUBLIC_KEY', ''));

        if ($configured !== '') {
            return str_contains($configured, 'BEGIN')
                ? str_replace('\\n', "\n", $configured)
                : (string) base64_decode($configured, true);
        }

        $privateKey = openssl_pkey_get_private($this->privateKeyPem());
        $details = $privateKey !== false ? openssl_pkey_get_details($privateKey) : false;

        if (! is_array($details) || ! isset($details['key'])) {
            throw new RuntimeException('Cle publique cryptographique introuvable.');
        }

        return (string) $details['key'];
    }

    private function generateKeyPair(string $privateKeyPath): void
    {
        Storage::disk('private')->makeDirectory('.');

        $key = openssl_pkey_new([
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($key === false || ! openssl_pkey_export($key, $privatePem)) {
            throw new RuntimeException('Generation RSA-4096 impossible.');
        }

        file_put_contents($privateKeyPath, $privatePem);
        chmod($privateKeyPath, 0600);
    }
}
