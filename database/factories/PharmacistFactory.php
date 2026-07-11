<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Commune;
use App\Models\Pharmacist;
use App\Models\Province;
use App\Services\CryptographyService;
use App\Services\PharmacistService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class PharmacistFactory extends Factory
{
    protected $model = Pharmacist::class;

    public function definition(): array
    {
        $province = Province::inRandomOrder()->first() ?? Province::factory()->create();
        $city = City::inRandomOrder()->first() ?? City::factory()->create();
        $commune = Commune::inRandomOrder()->first() ?? Commune::factory()->create();

        $publicId = 'PH-RDC-2026-'.fake()->unique()->numerify('######');
        $token = 'QR.'.$publicId.'.'.Str::upper(bin2hex(random_bytes(16)));

        $attributes = [
            'public_id' => $publicId,
            'photo_path' => 'pharmacists/photos/test.webp',
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->lastName(),
            'last_name' => fake()->lastName(),
            'ordinal_number' => 'ONP-RDC-'.fake()->unique()->numberBetween(20, 26).'-'.fake()->unique()->numerify('#####'),
            'sex' => fake()->randomElement(['female', 'male']),
            'province_id' => $province->id,
            'city_id' => $city->id,
            'commune_id' => $commune->id,
            'professional_address' => fake()->streetAddress(),
            'professional_phone' => '+243858575940',
            'professional_email' => fake()->unique()->safeEmail(),
            'professional_status' => 'active',
            'registered_at' => now('UTC')->subYears(3)->toDateString(),
            'practice_started_at' => now('UTC')->subYears(3)->toDateString(),
            'license_number' => 'CNOP-RDC-2026-'.fake()->unique()->numerify('######'),
            'license_status' => 'active',
            'license_expires_at' => now('UTC')->addYear()->toDateString(),
            'pharmacy_establishment' => 'Pharmacie Centrale',
            'specialization' => 'Pharmacie communautaire',
            'qr_code_token' => $token,
        ];

        // Use the real crypto pipeline (CryptographyService/PharmacistService) so factory-made
        // pharmacists produce a verification_hash/signature that PharmacistVerificationService
        // actually validates, instead of the unrelated legacy validika_hash()/validika_signature() helpers.
        $crypto = app(CryptographyService::class);
        $verificationHash = $crypto->hashPayload(app(PharmacistService::class)->canonicalPayload($attributes));
        $signature = $crypto->sign($token.'|'.$verificationHash);

        return $attributes + [
            'verification_hash' => $verificationHash,
            'qr_code_signature' => $signature['signature'],
            'public_key' => $signature['public_key'],
            'public_key_fingerprint' => $signature['public_key_fingerprint'],
        ];
    }
}
