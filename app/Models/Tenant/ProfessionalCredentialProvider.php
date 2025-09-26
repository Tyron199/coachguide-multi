<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionalCredentialProvider extends Model
{
    protected $fillable = [
        'name',
        'full_name',
        'website_url',
        'logo_url',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get all user credentials for this provider
     */
    public function userCredentials(): HasMany
    {
        return $this->hasMany(UserProfessionalCredential::class, 'professional_credential_provider_id');
    }

    /**
     * Get credentials for a specific user
     */
    public function userCredential($userId)
    {
        return $this->userCredentials()->where('user_id', $userId)->first();
    }

    /**
     * Check if a user has uploaded a credential for this provider
     */
    public function hasCredentialForUser($userId): bool
    {
        return $this->userCredentials()->where('user_id', $userId)->exists();
    }

    /**
     * Scope to get only active providers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
