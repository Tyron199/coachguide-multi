<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Supervision extends Model
{
    protected $fillable = [
        'user_id',
        'supervision_date',
        'duration_minutes',
        'supervisor_name',
        'supervisor_contact',
        'supervisor_accreditation',
        'supervision_type',
        'session_format',
        'themes_discussed',
        'reflections',
        'action_points',
        'ethical_considerations',
        'impact_on_practice',
    ];

    protected $casts = [
        'supervision_date' => 'date',
        'duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
