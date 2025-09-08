<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Central\RegistrationStatus;

class Registration extends Model
{

    protected $fillable = [
        'name',
        'email',
        'company_name',
        'confirmation_token',
        'token_expires_at',
        'tenant_id',
        'status',
        'ip_address',
        'user_agent',
        'referrer',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'status' => RegistrationStatus::class,
    ];

    /**
     * Generate a unique confirmation token
     */
    public static function generateConfirmationToken(): string
    {
        do {
            $token = \Illuminate\Support\Str::random(64);
        } while (self::where('confirmation_token', $token)->exists());
        
        return $token;
    }

    /**
     * Check if the token is expired
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
