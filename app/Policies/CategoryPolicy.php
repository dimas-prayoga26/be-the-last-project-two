<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * The header sub-menus are managed by admins only.
 */
class CategoryPolicy
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
    public function view(User $user, Category $category): bool
    {
        return $user->isAdmin();
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
    public function update(User $user, Category $category): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model. Categories that still hold works stay.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->isAdmin() && ! $category->projects()->exists();
    }

    /**
     * Determine whether the user can change the menu order.
     */
    public function reorder(User $user): bool
    {
        return $user->isAdmin();
    }
}
