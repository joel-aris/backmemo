<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pharmacist;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class PharmacistService
{
    public function __construct(private readonly CryptographyService $crypto) {}

    public function create(array $data): Pharmacist
    {
        return DB::transaction(function () use ($data): Pharmacist {
            if (($data['photo'] ?? null) instanceof UploadedFile) {
                $data['photo_path'] = $data['photo']->store('pharmacists/photos', 'public');
                unset($data['photo']);
            }

            $sequence = str_pad((string) (Pharmacist::withTrashed()->count() + 1), 6, '0', STR_PAD_LEFT);
            $data['public_id'] = sprintf('PH-RDC-%s-%s', Carbon::now('UTC')->year, $sequence);
            $data['qr_code_token'] = sprintf(
                'QR.%s.%s',
                $data['public_id'],
                Str::upper(bin2hex(random_bytes(16))),
            );
            $data['verification_hash'] = $this->crypto->hashPayload($this->canonicalPayload($data));
            $signature = $this->crypto->sign($data['qr_code_token'].'|'.$data['verification_hash']);
            $data['qr_code_signature'] = $signature['signature'];
            $data['public_key'] = $signature['public_key'];
            $data['public_key_fingerprint'] = $signature['public_key_fingerprint'];

            return Pharmacist::query()->create($data);
        });
    }

    public function update(Pharmacist $pharmacist, array $data): Pharmacist
    {
        if (($data['photo'] ?? null) instanceof UploadedFile) {
            if ($pharmacist->photo_path) {
                Storage::disk('public')->delete($pharmacist->photo_path);
            }

            $data['photo_path'] = $data['photo']->store('pharmacists/photos', 'public');
            unset($data['photo']);
        }

        $pharmacist->fill($data)->save();

        // Any field in the canonical payload (name, license status/expiry, etc.)
        // is covered by the verification_hash/signature. Without recomputing them
        // here, every legitimate edit (e.g. suspending a license) would make the
        // pharmacist's own cryptographic proof report itself as invalid until
        // someone remembered to run `pharmacists:recalculate-proofs` by hand.
        return $this->recalculateProof($pharmacist);
    }

    /**
     * Recompute the verification hash and re-sign the QR proof from the pharmacist's
     * current data. Used for the "recalcul global des preuves crypto" admin operation
     * and to backfill records after the canonical payload shape changes.
     */
    public function recalculateProof(Pharmacist $pharmacist): Pharmacist
    {
        $verificationHash = $this->crypto->hashPayload($this->canonicalPayloadFromModel($pharmacist));
        $signature = $this->crypto->sign($pharmacist->qr_code_token.'|'.$verificationHash);

        $pharmacist->forceFill([
            'verification_hash' => $verificationHash,
            'qr_code_signature' => $signature['signature'],
            'public_key' => $signature['public_key'],
            'public_key_fingerprint' => $signature['public_key_fingerprint'],
        ])->save();

        return $pharmacist->refresh();
    }

    /**
     * Canonical payload signed/hashed for a pharmacist, per the field list required by
     * the VALIDIKA specification (cahier des charges 5.1). Keys are sorted for a stable
     * JSON encoding by CryptographyService::hashPayload().
     */
    public function canonicalPayload(array $data): array
    {
        return [
            'public_id' => $data['public_id'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'ordinal_number' => $data['ordinal_number'],
            'license_number' => $data['license_number'],
            'license_status' => $data['license_status'],
            'license_expires_at' => $data['license_expires_at'] !== null && $data['license_expires_at'] !== ''
                ? (string) $data['license_expires_at']
                : null,
            'province_id' => (string) $data['province_id'],
            'city_id' => (string) $data['city_id'],
            'commune_id' => (string) $data['commune_id'],
            'professional_status' => $data['professional_status'],
            'registered_at' => (string) $data['registered_at'],
            'practice_started_at' => (string) $data['practice_started_at'],
            'pharmacy_establishment' => $data['pharmacy_establishment'],
            'qr_code_token' => $data['qr_code_token'],
        ];
    }

    public function canonicalPayloadFromModel(Pharmacist $pharmacist): array
    {
        return $this->canonicalPayload([
            'public_id' => $pharmacist->public_id,
            'first_name' => $pharmacist->first_name,
            'middle_name' => $pharmacist->middle_name,
            'last_name' => $pharmacist->last_name,
            'ordinal_number' => $pharmacist->ordinal_number,
            'license_number' => $pharmacist->license_number,
            'license_status' => $pharmacist->license_status,
            'license_expires_at' => $pharmacist->license_expires_at?->toDateString(),
            'province_id' => $pharmacist->province_id,
            'city_id' => $pharmacist->city_id,
            'commune_id' => $pharmacist->commune_id,
            'professional_status' => $pharmacist->professional_status,
            'registered_at' => $pharmacist->registered_at->toDateString(),
            'practice_started_at' => $pharmacist->practice_started_at->toDateString(),
            'pharmacy_establishment' => $pharmacist->pharmacy_establishment,
            'qr_code_token' => $pharmacist->qr_code_token,
        ]);
    }
}
