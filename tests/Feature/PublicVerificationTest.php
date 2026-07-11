<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Pharmacist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_qr_verification_returns_pharmacist(): void
    {
        $pharmacist = Pharmacist::factory()->create();

        $this->getJson('/api/v1/verify/' . $pharmacist->qr_code_token)
            ->assertOk()
            ->assertJsonPath('type', 'pharmacist')
            ->assertJsonPath('valid', true);
    }
}
