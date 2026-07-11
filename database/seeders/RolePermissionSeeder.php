<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view pharmacists',
            'manage pharmacists',
            'view documents',
            'manage documents',
            'sign documents',
            'view audit logs',
            'manage users',
            'reset user 2fa',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'Super Admin' => $permissions,
            'Administrateur' => ['view pharmacists', 'manage pharmacists', 'view documents', 'manage documents', 'sign documents', 'view audit logs', 'manage users', 'reset user 2fa'],
            'Président' => ['view pharmacists', 'view documents', 'sign documents', 'view audit logs'],
            'Secrétaire' => ['view pharmacists', 'manage pharmacists', 'view documents', 'manage documents'],
            'Pharmacien' => ['view pharmacists', 'view documents'],
            'Auditeur' => ['view pharmacists', 'view documents', 'view audit logs'],
            'Visiteur' => ['view pharmacists'],
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::findOrCreate($name, 'web')->syncPermissions($rolePermissions);
        }
    }
}
