<?php

namespace App\Policies\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only coaches can view companies (admins who coach will also have coach role)
        return $user->hasRole('coach');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        // Admins can view any company
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can only view companies that have clients assigned to them
        if ($user->hasRole('coach')) {
            return $company->users()->where('assigned_coach_id', $user->id)->exists();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create companies
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        // Only admins can update companies
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view employees of the company.
     */
    public function viewEmployees(User $user, Company $company): bool
    {
        // Admins can view any company's employees
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Coaches can only view employees of companies where they have assigned clients
        if ($user->hasRole('coach')) {
            return $company->users()->where('assigned_coach_id', $user->id)->exists();
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {
        // Only admins can delete companies
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return false;
    }
}
