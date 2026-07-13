<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Pharmacist;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class PharmacistUpdateProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_pharmacist_keeps_its_cryptographic_proof_valid(): void
    {
        // Regression test: PharmacistService::update() used to just fill()+save()
        // without recomputing verification_hash/signature. Since the proof commits
        // to the pharmacist's data (name, license_status, etc.), any legitimate edit
        // silently made the pharmacist's own cryptographic proof report itself as
        // invalid, until someone remembered to run `pharmacists:recalculate-proofs`.
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $pharmacist = Pharmacist::factory()->create(['license_status' => 'active']);

        Sanctum::actingAs($admin);

        $this->putJson("/api/v1/pharmacists/{$pharmacist->id}", [
            'license_status' => 'suspended',
        ])->assertOk();

        $response = $this->getJson("/api/v1/pharmacists/{$pharmacist->id}")->assertOk();

        $response->assertJsonPath('cryptographic_proof.hash_valid', true);
        $response->assertJsonPath('cryptographic_proof.valid', true);
        $response->assertJsonPath('data.license_status', 'suspended');
    }
}
