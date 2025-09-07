<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only coaches can view clients (admins who coach will also have coach role)
        return $user->hasRole('coach');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admins can view any client
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can only view their assigned clients
        if ($user->hasRole('coach')) {
            return $model->assigned_coach_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only coaches can create clients (admins who coach will also have coach role)
        return $user->hasRole('coach');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admins can update any client
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can only update their assigned clients
        if ($user->hasRole('coach')) {
            return $model->assigned_coach_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only allow deletion of archived clients
        if (!$model->archived) {
            return false;
        }
        
        // Admins can delete any archived client
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can only delete their assigned archived clients
        if ($user->hasRole('coach')) {
            return $model->assigned_coach_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
