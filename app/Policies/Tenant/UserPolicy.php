<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * This handles clients, coaches, and admins based on the authenticated user's role.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all user types
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can view clients (for existing ClientController functionality)
        if ($user->hasRole('coach')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admins can view any user (client, coach, or admin)
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can only view their assigned clients
        if ($user->hasRole('coach') && $model->hasRole('client')) {
            return $model->assigned_coach_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admins can create any user type (clients, coaches, other admins)
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can create clients (existing functionality)
        if ($user->hasRole('coach')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admins can update any user (client, coach, or admin)
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can only update their assigned clients
        if ($user->hasRole('coach') && $model->hasRole('client')) {
            return $model->assigned_coach_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Admins can delete any user type
        if ($user->hasRole('admin')) {
            // Prevent admins from deleting themselves
            if ($user->id === $model->id) {
                return false;
            }
            
            // Only allow deletion of archived users (clients, coaches, or admins)
            if (!$model->archived) {
                return false;
            }
            
            return true;
        }
        
        // Coaches can only delete their assigned archived clients
        if ($user->hasRole('coach') && $model->hasRole('client')) {
            return $model->assigned_coach_id === $user->id && $model->archived;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // Only admins can restore coaches and other admins
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only admins can permanently delete coaches and other admins
        if (!$user->hasRole('admin')) {
            return false;
        }

        // Prevent admins from permanently deleting themselves
        if ($user->id === $model->id) {
            return false;
        }

        return true;
    }
}
