<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Candidacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CandidacyMineTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_only_sees_their_own_candidacies(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Candidacy::query()->create([
            'user_id' => $owner->id,
            'first_name' => 'Jean',
            'last_name' => 'Kabamba',
            'email' => 'jean@example.cd',
            'status' => 'pending',
        ]);

        Candidacy::query()->create([
            'user_id' => $other->id,
            'first_name' => 'Alice',
            'last_name' => 'Mbeki',
            'email' => 'alice@example.cd',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($owner);
        $response = $this->getJson('/api/v1/auth/candidacies');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.email', 'jean@example.cd');
    }

    public function test_guest_cannot_access_their_candidacies(): void
    {
        $this->getJson('/api/v1/auth/candidacies')->assertUnauthorized();
    }
}
