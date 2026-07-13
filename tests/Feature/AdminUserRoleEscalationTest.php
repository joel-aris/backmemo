<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AdminUserRoleEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrateur_cannot_grant_itself_super_admin_role(): void
    {
        // Regression test: PUT /admin/users/{user} is reachable by both
        // "Administrateur" and "Super Admin" (route middleware
        // role:Administrateur|Super Admin), but role assignment must stay
        // Super-Admin-only, otherwise an Administrateur can self-promote to
        // Super Admin through this same endpoint.
        $this->seed(RolePermissionSeeder::class);

        $administrateur = User::factory()->create(['password' => 'Password!2026']);
        $administrateur->assignRole('Administrateur');

        Sanctum::actingAs($administrateur);

        $this->putJson("/api/v1/admin/users/{$administrateur->id}", [
            'roles' => ['Super Admin'],
        ])->assertForbidden();

        self::assertFalse($administrateur->fresh()->hasRole('Super Admin'));
    }

    public function test_super_admin_can_change_roles(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $superAdmin = User::factory()->create(['password' => 'Password!2026']);
        $superAdmin->assignRole('Super Admin');

        $target = User::factory()->create();
        $target->assignRole('Visiteur');

        Sanctum::actingAs($superAdmin);

        $this->putJson("/api/v1/admin/users/{$target->id}", [
            'roles' => ['Administrateur'],
        ])->assertOk();

        self::assertTrue($target->fresh()->hasRole('Administrateur'));
    }
}
