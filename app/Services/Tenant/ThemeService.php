<?php

namespace App\Services\Tenant;

use App\Models\Tenant\PlatformBranding;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ThemeService
{
    protected array $availableThemes = [
        'default' => [
            'name' => 'Default',
            'preview_color' => 'oklch(0.427 0.071 195.8)',
        ],
        'blue' => [
            'name' => 'Blue',
            'preview_color' => 'oklch(0.623 0.214 259.815)',
        ],
        'blue-gray' => [
            'name' => 'Blue Gray',
            'preview_color' => '#607D8B',
        ],
        'green' => [
            'name' => 'Green',
            'preview_color' => '#4CAF50',
        ],
        'orange' => [
            'name' => 'Orange',
            'preview_color' => 'oklch(0.705 0.213 47.604)',
        ],
        'pink' => [
            'name' => 'Pink',
            'preview_color' => 'oklch(0.645 0.246 16.439)',
        ],
        'red' => [
            'name' => 'Red',
            'preview_color' => 'oklch(0.637 0.237 25.331)',
        ],
        'violet' => [
            'name' => 'Violet',
            'preview_color' => 'oklch(0.606 0.25 292.717)',
        ],
        'yellow' => [
            'name' => 'Yellow',
            'preview_color' => 'oklch(0.795 0.184 86.047)',
        ],
        'brown' => [
            'name' => 'Brown',
            'preview_color' => '#795548',
        ],
        'gray'=>[
            'name' => 'Gray',
            'preview_color' => '#757575',
        ],
    ];

    /**
     * Get the currently configured theme name
     */
    public function getCurrentTheme(): string
    {
        // If not in tenant context, return default theme
        if (!tenant()) {
            return 'default';
        }
        
        return PlatformBranding::current()->getThemeName();
    }

    /**
     * Get the CSS file path for the current theme
     */
    public function getCurrentThemePath(): string
    {
        $themeName = $this->getCurrentTheme();
        return global_asset("/css/themes/{$themeName}.css");
    }

    /**
     * Get all available predefined themes
     */
    public function getAvailableThemes(): array
    {
        return $this->availableThemes;
    }

    /**
     * Get theme names only (for backwards compatibility)
     */
    public function getThemeNames(): array
    {
        return array_map(fn($theme) => $theme['name'], $this->availableThemes);
    }

    /**
     * Get preview color for a specific theme
     */
    public function getThemePreviewColor(string $themeName): ?string
    {
        return $this->availableThemes[$themeName]['preview_color'] ?? null;
    }

    /**
     * Check if a theme file exists
     */
    public function themeExists(string $themeName): bool
    {
        return File::exists(public_path("/css/themes/{$themeName}.css"));
    }


    /**
     * Get theme file paths for all available themes
     */
    public function getThemePaths(): array
    {
        $paths = [];
        
        foreach ($this->availableThemes as $key => $theme) {
            $paths[$key] = "/css/themes/{$key}.css";
        }
        
        return $paths;
    }

    /**
     * Update the current theme
     */
    public function updateTheme(string $themeName): void
    {
        // Only allow theme updates in tenant context
        if (!tenant()) {
            throw new \InvalidArgumentException('Theme updates are only available in tenant context');
        }
        
        PlatformBranding::current()->updateBranding([
            'theme_name' => $themeName
        ]);
    }

    /**
     * Reset theme to default
     */
    public function resetTheme(): void
    {
        // Only allow theme resets in tenant context
        if (!tenant()) {
            throw new \InvalidArgumentException('Theme resets are only available in tenant context');
        }
        
        PlatformBranding::current()->updateBranding([
            'theme_name' => 'default'
        ]);
    }


}
