<?php

namespace App\Models\Tenant ;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Tenant\TaskReminderStatus;

class CoachingTaskReminder extends Model
{
    protected $fillable = [
        'coaching_task_id', 
        'user_id', 
        'remind_at', 
        'status', 
        'label'
    ];
    
    protected $casts = [
        'remind_at' => 'datetime',
        'status' => TaskReminderStatus::class,
    ];

    /**
     * Get the coaching task this reminder belongs to
     */
    public function coachingTask()
    {
        return $this->belongsTo(CoachingTask::class);
    }

    /**
     * Get the user this reminder is for
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
