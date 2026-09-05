<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN, Role::SUPPORT);
    }

    public function view(User $user, User $target): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN, Role::SUPPORT) || $user->id === $target->id;
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN, Role::SUPPORT) || $user->id === $target->id;
    }

    public function delete(User $user, User $target): bool
    {
        // Only Super Admin can delete users, and never themselves.
        return $user->hasRole(Role::SUPER_ADMIN) && $user->id !== $target->id;
    }

    public function assignRole(User $user, User $target): bool
    {
        return $user->hasRole(Role::SUPER_ADMIN);
    }
}
