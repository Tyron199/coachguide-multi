<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalDevelopment extends Model
{
    protected $fillable = [
        'user_id',
        'date_from',
        'date_to',
        'training_type',
        'training_provider',
        'course_title',
        'accredited',
        'total_hours_theory',
        'total_hours_practical',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'accredited' => 'boolean',
        'total_hours_theory' => 'decimal:2',
        'total_hours_practical' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
