<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $appends = [
        'url',
        'formatted_size',
        'is_image',
    ];

    /**
     * Get the parent attachable model (User or CoachingSession).
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * Get the download URL for the file (since it's private storage)
     */
    public function getUrlAttribute(): string
    {
        return route('tenant.attachments.download', $this->id);
    }

    /**
     * Get human readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Delete the file from storage when model is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($attachment) {
            Storage::delete($attachment->file_path);
        });
    }
}
