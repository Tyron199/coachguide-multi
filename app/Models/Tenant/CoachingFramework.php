<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingFramework extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'subcategory',
        'best_for',
        'schema',
        'is_system',
        'is_active',
        'created_by_coach_id',
    ];

    protected $casts = [
        'best_for' => 'array',
        'schema' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the coach who created this framework (if not a system framework)
     */
    public function createdByCoach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_coach_id');
    }

    /**
     * Get all instances of this framework
     */
    public function instances(): HasMany
    {
        return $this->hasMany(CoachingFrameworkInstance::class, 'framework_id');
    }

    /**
     * Scope to get only active frameworks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only system frameworks
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to get only user-created frameworks
     */
    public function scopeUserCreated($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by subcategory
     */
    public function scopeSubcategory($query, $subcategory)
    {
        return $query->where('subcategory', $subcategory);
    }

    /**
     * Get frameworks suitable for a specific coaching type
     */
    public function scopeBestFor($query, $coachingType)
    {
        return $query->whereJsonContains('best_for', $coachingType);
    }

    /**
     * Check if this framework is suitable for a coaching type
     */
    public function isBestFor($coachingType): bool
    {
        return in_array($coachingType, $this->best_for ?? []);
    }

    /**
     * Get the number of times this framework has been used
     */
    public function getUsageCountAttribute(): int
    {
        return $this->instances()->count();
    }

    /**
     * Get the number of completed instances
     */
    public function getCompletedCountAttribute(): int
    {
        return $this->instances()->whereNotNull('completed_at')->count();
    }
}
