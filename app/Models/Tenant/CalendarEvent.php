<?php

namespace App\Models\Tenant;

use App\Services\OAuth\OauthProviderType;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'coaching_session_id',
        'user_id',
        'provider',
        'external_event_id',
        'external_calendar_id',
        'meeting_url',
        'meeting_id',
        'sync_status',
        'last_synced_at',
        'sync_error',
        'external_data',
    ];

    protected $casts = [
        'provider' => OauthProviderType::class,
        'last_synced_at' => 'datetime',
        'external_data' => 'array',
    ];

    /**
     * Get the coaching session this calendar event belongs to
     */
    public function coachingSession()
    {
        return $this->belongsTo(CoachingSession::class);
    }

    /**
     * Get the user whose calendar this event is in
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the sync was successful
     */
    public function isSyncSuccessful(): bool
    {
        return in_array($this->sync_status, ['created', 'updated']);
    }

    /**
     * Check if the sync failed
     */
    public function isSyncFailed(): bool
    {
        return $this->sync_status === 'failed';
    }

    /**
     * Check if the event was deleted
     */
    public function isDeleted(): bool
    {
        return $this->sync_status === 'deleted';
    }
}
