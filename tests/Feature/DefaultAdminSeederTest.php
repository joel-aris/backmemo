<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DefaultAdminSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class DefaultAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_has_super_admin_role_and_can_access_admin_routes(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DefaultAdminSeeder::class);

        $admin = User::query()->where('email', 'admin@validika.cd')->firstOrFail();

        self::assertTrue($admin->hasRole('Super Admin'));

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/admin/stats')->assertOk();
    }
}
