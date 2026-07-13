<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_visitor_account(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.org',
            'password' => 'Password!2026',
            'password_confirmation' => 'Password!2026',
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'jane@example.org']);
    }

    public function test_visitor_can_log_in_without_2fa_setup(): void
    {
        // Regression test: outside the 'local' env (i.e. in real 'testing'/
        // 'production'), login used to require 2FA setup for every account,
        // including freshly self-registered public "Visiteur" users. Since
        // the 2FA setup endpoints themselves require an authenticated token
        // that login never issued in that state, this permanently locked
        // every new user out with no way to proceed.
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.org',
            'password' => 'Password!2026',
            'password_confirmation' => 'Password!2026',
        ])->assertCreated();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.org',
            'password' => 'Password!2026',
        ])
            ->assertOk()
            ->assertJsonPath('requires_2fa_setup', false)
            ->assertJsonStructure(['access_token']);
    }

    public function test_staff_role_can_log_in_while_2fa_enforcement_is_disabled(): void
    {
        // 2FA enforcement is temporarily disabled for every role (see the
        // comment in AuthController::login): there's no working self-service
        // setup screen on web/mobile yet, so enforcing it locked every
        // staff/admin account out with no way to proceed.
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $user = \App\Models\User::factory()->create(['password' => 'Password!2026']);
        $user->assignRole('Administrateur');

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password!2026',
        ])
            ->assertOk()
            ->assertJsonPath('requires_2fa_setup', false)
            ->assertJsonStructure(['access_token']);
    }
}
