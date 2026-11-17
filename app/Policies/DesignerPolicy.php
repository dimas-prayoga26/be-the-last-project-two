<?php

namespace App\Policies;

use App\Models\Designer;
use App\Models\User;

/**
 * Admins manage every designer; a designer account edits only its own profile.
 */
class DesignerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDesigner();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Designer $designer): bool
    {
        return $this->update($user, $designer);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Designer $designer): bool
    {
        return $user->isAdmin() || ($user->isDesigner() && $user->designer_id === $designer->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Designer $designer): bool
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

    /**
     * Determine whether the user can change the manual display order.
     */
    public function reorder(User $user): bool
    {
        return $user->isAdmin();
    }
}
