<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingNote extends Model
{
    use HasFactory;
    protected $fillable = [
        'session_id',
        'coach_id', 
        'client_id',
        'title',
        'content',
    ];

    /**
     * Get the coaching session this note belongs to
     */
    public function session()
    {
        return $this->belongsTo(CoachingSession::class);
    }

    /**
     * Get the coach who wrote this note
     */
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Get the client this note is about
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

        /**
     * Get attachments for this note
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }


    
    /**
     * Get notes for a specific coach-client pair
     */
    public function scopeForCoachClient($query, $coachId, $clientId)
    {
        return $query->where('coach_id', $coachId)->where('client_id', $clientId);
    }

    /**
     * Get notes by a specific coach
     */
    public function scopeByCoach($query, $coachId)
    {
        return $query->where('coach_id', $coachId);
    }

    /**
     * Get notes for a specific session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
