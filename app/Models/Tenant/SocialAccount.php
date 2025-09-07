<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_email',
        'access_token',
        'refresh_token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the access token is expired
     */
    public function isTokenExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if this account has a valid access token
     */
    public function hasValidToken(): bool
    {
        return $this->access_token && !$this->isTokenExpired();
    }
}
