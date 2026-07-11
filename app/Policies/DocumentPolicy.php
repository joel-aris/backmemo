<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

final class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view documents');
    }

    public function view(User $user, Document $document): bool
    {
        return $user->can('view documents');
    }

    public function create(User $user): bool
    {
        return $user->can('manage documents');
    }

    public function update(User $user, Document $document): bool
    {
        return $user->can('manage documents');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('manage documents');
    }
}
