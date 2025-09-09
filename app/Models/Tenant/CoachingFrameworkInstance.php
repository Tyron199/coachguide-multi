<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingFrameworkInstance extends Model
{
    protected $fillable = [
        'framework_id',
        'session_id',
        'coach_id',
        'client_id',
        'schema_snapshot',
        'form_data',
        'completed_at',
    ];

    protected $casts = [
        'schema_snapshot' => 'array',
        'form_data' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the framework this instance is based on
     */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(CoachingFramework::class, 'framework_id');
    }

    /**
     * Get the coaching session this instance belongs to (optional)
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CoachingSession::class, 'session_id');
    }

    /**
     * Get the coach who created this instance
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Get the client this instance is for
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Scope to get completed instances
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope to get incomplete instances
     */
    public function scopeIncomplete($query)
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Scope to filter by coach
     */
    public function scopeForCoach($query, $coachId)
    {
        return $query->where('coach_id', $coachId);
    }

    /**
     * Scope to filter by client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Check if this instance is completed
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Get progress percentage (0-100)
     */
    public function getProgressPercentageAttribute(): int
    {
        if (!$this->schema_snapshot || !isset($this->schema_snapshot['properties'])) {
            return 0;
        }

        $totalFields = count($this->schema_snapshot['properties']);
        $completedFields = $this->getCompletedFieldsAttribute();

        if ($totalFields === 0) {
            return 0;
        }

        return min(100, (int) round(($completedFields / $totalFields) * 100));
    }

    /**
     * Get the total number of fields in this framework
     */
    public function getTotalFieldsAttribute(): int
    {
        if (!$this->schema_snapshot || !isset($this->schema_snapshot['properties'])) {
            return 0;
        }

        return count($this->schema_snapshot['properties']);
    }

    /**
     * Get the number of completed fields (fields with non-empty values)
     */
    public function getCompletedFieldsAttribute(): int
    {
        if (!$this->form_data) {
            return 0;
        }

        $completedCount = 0;
        foreach ($this->form_data as $value) {
            if (!empty($value)) {
                $completedCount++;
            }
        }

        return $completedCount;
    }

    /**
     * Mark the entire framework as completed
     */
    public function markCompleted(): void
    {
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Update form data for a specific field
     */
    public function updateFormData(string $fieldName, $value): void
    {
        $formData = $this->form_data ?? [];
        $formData[$fieldName] = $value;
        $this->form_data = $formData;
        $this->save();
    }

    /**
     * Check if a specific field has been completed (has a value)
     */
    public function isFieldCompleted(string $fieldName): bool
    {
        return !empty($this->form_data[$fieldName] ?? null);
    }
}
