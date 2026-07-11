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
}
