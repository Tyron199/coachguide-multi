<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformBranding extends Model
{
    protected $fillable = [
        'theme_name',
        'logo_path',
        'icon_path',
        'custom_css',
        'company_name',
    ];

    /**
     * Get the singleton branding instance for the current tenant
     */
    public static function current(): self
    {
        // Use tenant-aware cache key (tenant context is handled by tenancy package)
        return Cache::remember('platform_branding', 3600, function () {
            return self::firstOrCreate([]);
        });
    }

    /**
     * Clear the branding cache
     */
    public static function clearCache(): void
    {
        Cache::forget('platform_branding');
    }

    /**
     * Update branding and clear cache
     */
    public function updateBranding(array $attributes): bool
    {
        $result = $this->update($attributes);
        self::clearCache();
        return $result;
    }

    /**
     * Get the theme name, falling back to default
     */
    public function getThemeName(): string
    {
        return $this->theme_name ?? 'default';
    }

    /**
     * Check if has custom logo
     */
    public function hasCustomLogo(): bool
    {
        return !empty($this->logo_path);
    }

    /**
     * Check if has custom icon
     */
    public function hasCustomIcon(): bool
    {
        return !empty($this->icon_path);
    }
}
