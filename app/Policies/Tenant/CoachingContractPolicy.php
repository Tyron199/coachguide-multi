<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\CoachingContract;
use App\Models\Tenant\User;

class CoachingContractPolicy
{
    /**
     * Determine whether the user can view any contracts.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['coach', 'admin']);
    }

    /**
     * Determine whether the user can view the contract.
     */
    public function view(User $user, CoachingContract $contract): bool
    {
        return $user->hasRole(['coach', 'admin']) && 
               ($user->id === $contract->coach_id || $user->id === $contract->client_id);
    }

    /**
     * Determine whether the user can create contracts.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['coach', 'admin']);
    }

    /**
     * Determine whether the user can update the contract.
     */
    public function update(User $user, CoachingContract $contract): bool
    {
        return $user->hasRole(['coach', 'admin']) && 
               ($user->id === $contract->coach_id || $user->id === $contract->client_id);
    }

    /**
     * Determine whether the user can delete the contract.
     */
    public function delete(User $user, CoachingContract $contract): bool
    {
        return $user->hasRole(['coach', 'admin']) && $user->id === $contract->coach_id;
    }
}
