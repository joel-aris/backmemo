<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pharmacist;

final class PharmacistVerificationService
{
    public function __construct(
        private readonly CryptographyService $crypto,
        private readonly MerkleRegistryService $merkleRegistry,
        private readonly PharmacistService $pharmacists,
    ) {}

    /**
     * Full cryptographic proof for a pharmacist, consumed identically by the public
     * QR verification endpoint and by the pharmacist detail page (cahier des charges 5.3/5.4).
     */
    public function verify(Pharmacist $pharmacist): array
    {
        $expectedHash = $this->crypto->hashPayload($this->pharmacists->canonicalPayloadFromModel($pharmacist));
        $hashValid = hash_equals($expectedHash, (string) $pharmacist->verification_hash);

        $signatureValid = $this->crypto->verifySignature(
            $pharmacist->qr_code_token.'|'.$pharmacist->verification_hash,
            (string) $pharmacist->qr_code_signature,
            $pharmacist->public_key,
        );

        $merkle = $this->merkleRegistry->proofFor($pharmacist);

        return [
            'valid' => $hashValid && $signatureValid && $merkle['merkle_valid'],
            'hash_valid' => $hashValid,
            'signature_valid' => $signatureValid,
            'merkle_valid' => $merkle['merkle_valid'],
            'merkle_root' => $merkle['merkle_root'],
            'merkle_proof_nodes' => $merkle['merkle_proof_nodes'],
            'proof_version' => $merkle['proof_version'],
            'signature_algorithm' => 'RSA-4096-SHA256',
            'public_key_fingerprint' => $pharmacist->public_key_fingerprint,
            'verified_at' => now('UTC')->toIso8601String(),
        ];
    }
}
