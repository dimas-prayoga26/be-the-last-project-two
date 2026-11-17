<?php

namespace App\Policies;

use App\Models\MemberApplication;
use App\Models\User;

/**
 * Applications arrive through the public API; admins review and delete them.
 */
class MemberApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MemberApplication $memberApplication): bool
    {
        return $user->isAdmin();
    }

    /**
     * Applications are never written in the admin panel.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can accept or decline the application.
     */
    public function update(User $user, MemberApplication $memberApplication): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MemberApplication $memberApplication): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
