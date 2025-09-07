<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CoachingNote;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CoachingNotePolicy
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
    public function view(User $user, CoachingNote $coachingNote): bool
    {
        // Admins can view all notes
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coaches can view notes they created or for clients assigned to them
        return $user->hasRole('coach') && (
            $coachingNote->coach_id === $user->id ||
            $coachingNote->client->assigned_coach_id === $user->id
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
    public function update(User $user, CoachingNote $coachingNote): bool
    {
        // Admins can update all notes
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coaches can only update notes they created
        return $user->hasRole('coach') && $coachingNote->coach_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CoachingNote $coachingNote): bool
    {
        // Admins can delete all notes
        if ($user->hasRole('admin')) {
            return true;
        }

        // Coaches can only delete notes they created
        return $user->hasRole('coach') && $coachingNote->coach_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CoachingNote $coachingNote): bool
    {
        return $this->delete($user, $coachingNote);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CoachingNote $coachingNote): bool
    {
        return $user->hasRole('admin');
    }
}