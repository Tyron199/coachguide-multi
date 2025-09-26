<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserProfessionalCredential extends Model
{
    protected $fillable = [
        'user_id',
        'professional_credential_provider_id',
        'credential_name',
        'expiry_date',
        'file_path',
        'original_filename',
        'file_size',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'file_size' => 'integer',
    ];

    protected $appends = [
        'is_expired',
        'is_expiring_soon',
        'days_until_expiry',
        'formatted_file_size',
    ];

    /**
     * Get the user that owns this credential
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the credential provider
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ProfessionalCredentialProvider::class, 'professional_credential_provider_id');
    }

    /**
     * Check if the credential is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if the credential is expiring within 90 days
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isBetween(now(), now()->addDays(90));
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get human readable file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the download URL for the certificate
     */
    public function getDownloadUrl(): string
    {
        return route('tenant.coach.professional-credentials.download', $this->id);
    }

    /**
     * Delete the file from storage when model is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($credential) {
            if ($credential->file_path && Storage::exists($credential->file_path)) {
                Storage::delete($credential->file_path);
            }
        });
    }

    /**
     * Scope to get expired credentials
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    /**
     * Scope to get credentials expiring soon (within 90 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(90)]);
    }
}
