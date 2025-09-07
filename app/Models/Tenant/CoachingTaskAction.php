<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CoachingTaskAction extends Model
{
    protected $fillable = [
        'coaching_task_id',
        'user_id',
        'content',
    ];

    /**
     * Get the coaching task this action belongs to
     */
    public function coachingTask()
    {
        return $this->belongsTo(CoachingTask::class);
    }

    /**
     * Get the user who created this task action
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get attachments for this task action
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Check if task action is from coach
     */
    public function isFromCoach(): bool
    {
        return $this->user->hasRole('coach');
    }

    /**
     * Check if task action is from client
     */
    public function isFromClient(): bool
    {
        return $this->user->hasRole('client');
    }

    /**
     * Delete attachments when task action is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($taskAction) {
            // Delete all attachments (this will trigger the Attachment model's deleting event
            // which removes the actual files from storage)
            $taskAction->attachments()->each(function ($attachment) {
                $attachment->delete();
            });
        });
    }
}
