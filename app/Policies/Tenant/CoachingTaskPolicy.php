<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CoachingTask;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CoachingTaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['coach', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CoachingTask $coachingTask): bool
    {
        // Admins can view all tasks
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coaches can view tasks they created or for clients assigned to them
        return $user->hasRole('coach') && (
            $coachingTask->coach_id === $user->id ||
            $coachingTask->client->assigned_coach_id === $user->id
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['coach', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CoachingTask $coachingTask): bool
    {
        // Admins can update all tasks
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coaches can only update tasks they created
        return $user->hasRole('coach') && $coachingTask->coach_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CoachingTask $coachingTask): bool
    {
        // Admins can delete all tasks
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coaches can only delete tasks they created
        return $user->hasRole('coach') && $coachingTask->coach_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CoachingTask $coachingTask): bool
    {
        return $this->delete($user, $coachingTask);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CoachingTask $coachingTask): bool
    {
        return $user->hasRole('admin');
    }
}
