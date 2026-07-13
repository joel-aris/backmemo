<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionSeeder extends Seeder
{
    // Every request authenticated through auth:sanctum makes Laravel's Authenticate
    // middleware switch the runtime default guard to 'sanctum' for the rest of that
    // request (AuthManager::shouldUse() rewrites config('auth.defaults.guard')). Since
    // this is an API-only app, that means any assignRole()/syncRoles()/permission-by-name
    // call made from inside an authenticated endpoint (e.g. AdminController::updateUser)
    // resolves roles under guard 'sanctum', not 'web'. Roles/permissions must exist under
    // both guards, otherwise those calls throw "There is no role named ... for guard
    // sanctum" even for a legitimate Super Admin.
    private const GUARDS = ['web', 'sanctum'];

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

        $roles = [
            'Super Admin' => $permissions,
            'Administrateur' => ['view pharmacists', 'manage pharmacists', 'view documents', 'manage documents', 'sign documents', 'view audit logs', 'manage users', 'reset user 2fa'],
            'Président' => ['view pharmacists', 'view documents', 'sign documents', 'view audit logs'],
            'Secrétaire' => ['view pharmacists', 'manage pharmacists', 'view documents', 'manage documents'],
            'Pharmacien' => ['view pharmacists', 'view documents'],
            'Auditeur' => ['view pharmacists', 'view documents', 'view audit logs'],
            'Visiteur' => ['view pharmacists'],
        ];

        foreach (self::GUARDS as $guard) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, $guard);
            }

            foreach ($roles as $name => $rolePermissions) {
                Role::findOrCreate($name, $guard)->syncPermissions($rolePermissions);
            }
        }
    }
}
