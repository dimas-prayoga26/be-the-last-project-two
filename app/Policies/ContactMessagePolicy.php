<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

/**
 * Messages arrive through the public API; admins read and delete them.
 */
class ContactMessagePolicy
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
    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->isAdmin();
    }

    /**
     * Messages are never written in the admin panel.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can mark the message as read.
     */
    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContactMessage $contactMessage): bool
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
