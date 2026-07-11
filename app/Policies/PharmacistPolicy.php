<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pharmacist;
use App\Models\User;

final class PharmacistPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view pharmacists');
    }

    public function view(User $user, Pharmacist $pharmacist): bool
    {
        return $user->can('view pharmacists');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Administrateur']);
    }

    public function update(User $user, Pharmacist $pharmacist): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Administrateur']);
    }

    public function delete(User $user, Pharmacist $pharmacist): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Administrateur']);
    }
}
