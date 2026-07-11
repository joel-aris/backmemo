<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class EncryptionService
{
    private const CIPHER = 'aes-256-gcm';
    private const TAG_LENGTH = 16;
    private const IV_LENGTH = 12;

    public function encrypt(string $plaintext, ?string $associatedData = null): array
    {
        $key = $this->key();
        $iv = random_bytes(self::IV_LENGTH);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $associatedData ?? '',
            self::TAG_LENGTH,
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Chiffrement impossible.');
        }

        return [
            'ciphertext' => base64_encode($iv . $tag . $ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'algorithm' => 'AES-256-GCM',
        ];
    }

    public function decrypt(string $payload, ?string $associatedData = null): string
    {
        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            throw new RuntimeException('Payload chiffre invalide.');
        }

        $iv = substr($decoded, 0, self::IV_LENGTH);
        $tag = substr($decoded, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($decoded, self::IV_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->key(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $associatedData ?? '',
        );

        if ($plaintext === false) {
            throw new RuntimeException('Dechiffrement impossible. Integrite compromisee.');
        }

        return $plaintext;
    }

    private function key(): string
    {
        $key = (string) env('VALIDIKA_ENCRYPTION_KEY');

        if ($key === '') {
            throw new RuntimeException('Cle de chiffrement VALIDIKA_ENCRYPTION_KEY manquante.');
        }

        $decoded = base64_decode($key, true);

        if ($decoded === false || strlen($decoded) !== 32) {
            throw new RuntimeException('Cle de chiffrement invalide. 32 bytes base64 requis.');
        }

        return $decoded;
    }
}
