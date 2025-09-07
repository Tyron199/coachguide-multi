<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CoachingTask extends Model
{
    protected $fillable = [
        'session_id',
        'coach_id',
        'client_id',
        'title',
        'description',
        'deadline',
        'status',
        'send_reminders',
        'evidence_required',
        'completed_at',
        'reviewed_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'send_reminders' => 'boolean',
        'evidence_required' => 'boolean',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the coaching session this task belongs to (if any)
     */
    public function session()
    {
        return $this->belongsTo(CoachingSession::class, 'session_id');
    }

    /**
     * Get the coach who created this task
     */
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id')->withTrashed();
    }

    /**
     * Get the client assigned to this task
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id')->withTrashed();
    }

    /**
     * Get all task actions for this task
     */
    public function actions()
    {
        return $this->hasMany(CoachingTaskAction::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get all reminders for this task
     */
    public function reminders()
    {
        return $this->hasMany(CoachingTaskReminder::class);
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->deadline && 
               $this->deadline->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if task is due soon (within 24 hours)
     */
    public function isDueSoon(): bool
    {
        return $this->deadline && 
               $this->deadline->isFuture() && 
               $this->deadline->diffInHours(now()) <= 24 &&
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Scope for pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for tasks in review
     */
    public function scopeInReview($query)
    {
        return $query->where('status', 'review');
    }

    /**
     * Scope for completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope for tasks by coach
     */
    public function scopeByCoach($query, $coachId)
    {
        return $query->where('coach_id', $coachId);
    }

    /**
     * Scope for tasks for a specific coach-client pair
     */
    public function scopeForCoachClient($query, $coachId, $clientId)
    {
        return $query->where('coach_id', $coachId)->where('client_id', $clientId);
    }

    /**
     * Scope for tasks in a specific session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Delete task actions and reminders when task is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($task) {
            // Delete all task actions (this will trigger the CoachingTaskAction model's deleting event
            // which removes any attachments)
            $task->actions()->each(function ($action) {
                $action->delete();
            });
            
            // Delete all reminders
            $task->reminders()->delete();
        });
    }
}
