<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            TerritorySeeder::class,
        ]);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@validika.cd'],
            [
                'name' => 'VALIDIKA Super Admin',
                'password' => Hash::make('ChangeMe!2026-validika'),
                'email_verified_at' => now('UTC'),
            ],
        );
        $admin->assignRole('Super Admin');

        $jairo = User::query()->firstOrCreate(
            ['email' => 'jairo404@validika.cd'],
            [
                'name' => 'Jairo Admin',
                'password' => Hash::make('UVQUG777'),
                'email_verified_at' => now('UTC'),
                'two_factor_confirmed_at' => now('UTC'),
            ],
        );
        $jairo->assignRole('Administrateur');
    }
}
