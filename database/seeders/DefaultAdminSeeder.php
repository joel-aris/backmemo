<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('email', 'admin@validika.cd')->exists()) {
            return;
        }

        $user = User::query()->create([
            'name' => 'Admin VALIDIKA',
            'email' => 'admin@validika.cd',
            'password' => Hash::make('password'),
            'email_verified_at' => now('UTC'),
        ]);

        $user->assignRole('Super Admin');
    }
}
