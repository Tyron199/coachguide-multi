<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Tenant\CoachingSessionType;

class CoachingSession extends Model
{
    protected $fillable = [
        'client_id',
        'coach_id',
        'scheduled_at',
        'start_at',
        'end_at',
        'session_type',
        'duration',
        'client_attended',
        'sync_to_calendar',
    ];

    protected $appends = [
        'session_number',
        'formatted_duration',
        'is_active',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'duration' => 'integer',
        'client_attended' => 'boolean',
        'sync_to_calendar' => 'boolean',
        'session_type' => CoachingSessionType::class,
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }


    public function coachingNotes()
    {
        return $this->hasMany(CoachingNote::class, 'session_id');
    }

    /**
     * Get coaching tasks assigned to this session
     */
    public function coachingTasks()
    {
        return $this->hasMany(CoachingTask::class, 'session_id');
    }

    /**
     * Get coaching framework instances used in this session
     */
    public function frameworkInstances()
    {
        return $this->hasMany(CoachingFrameworkInstance::class, 'session_id');
    }

    /**
     * Get calendar events for this session
     */
    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /**
     * Get the planned end time based on scheduled_at + duration
     */
    public function getPlannedEndTimeAttribute()
    {
        if ($this->scheduled_at && $this->duration) {
            return $this->scheduled_at->addMinutes($this->duration);
        }
        return null;
    }

    /**
     * Get actual duration if session has started and ended
     */
    public function getActualDurationAttribute()
    {
        if ($this->start_at && $this->end_at) {
            return $this->start_at->diffInMinutes($this->end_at);
        }
        return null;
    }

    /**
     * Format duration for display
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration)
            return null;

        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Get the session number for this client-coach pair
     * Sessions are numbered sequentially by scheduled date
     */
    public function getSessionNumberAttribute()
    {
        return self::where('client_id', $this->client_id)
            ->where('scheduled_at', '<=', $this->scheduled_at)
            ->orderBy('scheduled_at', 'asc')
            ->count();
    }

    /**
     * Check if the session is currently active (happening right now)
     */
    public function getIsActiveAttribute()
    {
        $now = now();
        
        // Session is active if current time is between start_at and end_at
        return $this->start_at && $this->end_at && 
               $now->between($this->start_at, $this->end_at);
    }

    /**
     * Check if this session should be synced to calendars
     */
    public function shouldSyncToCalendar(): bool
    {
        return $this->sync_to_calendar && 
               ($this->client->calendarIntegrations()->exists() || 
                $this->coach->calendarIntegrations()->exists());
    }
}
