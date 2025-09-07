<?php

namespace App\Services\Tenant;

use App\Models\Tenant\PlatformBranding;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoService
{
    /**
     * Default logo paths in public directory (brand assets)
     */
    private const DEFAULT_LOGO_PATH = '/images/logo.png';
    private const DEFAULT_ICON_PATH = '/images/icon.png';
    
    /**
     * Custom logo filenames in tenant storage
     */
    private const CUSTOM_LOGO_FILENAME = 'custom-logo.png';
    private const CUSTOM_ICON_FILENAME = 'custom-icon.png';

    /**
     * Get the URL for the logo (checks database first, falls back to default)
     */
    public function getLogoUrl(string $type = 'logo'): string
    {
        $branding = PlatformBranding::current();
        
        // Check if tenant has a custom logo/icon in database
        if ($type === 'icon' && $branding->hasCustomIcon()) {
            return tenant_asset("logos/{$branding->icon_path}");
        } elseif ($type === 'logo' && $branding->hasCustomLogo()) {
            return tenant_asset("logos/{$branding->logo_path}");
        }
        
        // Fall back to default brand logos (use global_asset to avoid tenant scoping)
        $defaultPath = $type === 'icon' ? self::DEFAULT_ICON_PATH : self::DEFAULT_LOGO_PATH;
        return global_asset($defaultPath);
    }

    /**
     * Get the main logo URL
     */
    public function getMainLogoUrl(): string
    {
        return $this->getLogoUrl('logo');
    }

    /**
     * Get the icon URL
     */
    public function getIconUrl(): string
    {
        return $this->getLogoUrl('icon');
    }

    /**
     * Get the favicon URL (uses icon)
     */
    public function getFaviconUrl(): string
    {
        return $this->getIconUrl();
    }

    /**
     * Get the Apple touch icon URL (uses icon)
     */
    public function getAppleTouchIconUrl(): string
    {
        return $this->getIconUrl();
    }

    /**
     * Check if tenant has a custom logo (checks database)
     */
    public function hasCustomLogo(string $type = 'logo'): bool
    {
        $branding = PlatformBranding::current();
        return $type === 'icon' ? $branding->hasCustomIcon() : $branding->hasCustomLogo();
    }

    /**
     * Get all logo URLs for sharing with frontend
     */
    public function getAllLogoUrls(): array
    {
        return [
            'logoUrl' => $this->getMainLogoUrl(),
            'iconUrl' => $this->getIconUrl(),
            'faviconUrl' => $this->getFaviconUrl(),
            'appleTouchIconUrl' => $this->getAppleTouchIconUrl(),
        ];
    }

    /**
     * Get logo as data URI for PDF compatibility
     */
    public function getLogoAsDataUri(string $type = 'logo'): ?string
    {
        $branding = PlatformBranding::current();
        $filePath = null;
        
        // Check if tenant has a custom logo/icon in storage
        if ($type === 'icon' && $branding->hasCustomIcon()) {
            $filePath = storage_path("app/public/logos/{$branding->icon_path}");
        } elseif ($type === 'logo' && $branding->hasCustomLogo()) {
            $filePath = storage_path("app/public/logos/{$branding->logo_path}");
        } else {
            // Use default logo from public directory
            $defaultPath = $type === 'icon' ? self::DEFAULT_ICON_PATH : self::DEFAULT_LOGO_PATH;
            $filePath = public_path($defaultPath);
        }
        
        // Check if file exists and convert to data URI
        if ($filePath && file_exists($filePath)) {
            $imageData = file_get_contents($filePath);
            $mimeType = mime_content_type($filePath);
            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }
        
        return null;
    }

    /**
     * Get contract logo data for templates
     */
    public function getContractLogoData(): array
    {
        return [
            'url' => $this->getMainLogoUrl(),
            'hasCustomLogo' => $this->hasCustomLogo(),
            'dataUri' => $this->getLogoAsDataUri(),
        ];
    }

    /**
     * Delete custom logo file from tenant storage and database
     */
    public function deleteCustomLogo(string $type): void
    {
        $branding = PlatformBranding::current();
        
        // Get the current file path from database
        $currentPath = $type === 'icon' ? $branding->icon_path : $branding->logo_path;
        
        // Delete the file if it exists
        if ($currentPath && Storage::disk('public')->exists("logos/{$currentPath}")) {
            Storage::disk('public')->delete("logos/{$currentPath}");
        }
        
        // Clear the path from database
        $field = $type === 'icon' ? 'icon_path' : 'logo_path';
        $branding->updateBranding([$field => null]);
    }

    /**
     * Store a custom logo file in tenant storage and database
     */
    public function storeCustomLogo(string $type, $file): string
    {
        // Delete existing custom logo if it exists
        $this->deleteCustomLogo($type);
        
        // Generate unique filename to avoid browser caching issues
        $extension = $file->getClientOriginalExtension();
        $filename = $type . '-' . Str::uuid() . '.' . $extension;
        
        // Store the file with unique filename
        $path = $file->storeAs('logos', $filename, 'public');
        
        // Update database with new path
        $field = $type === 'icon' ? 'icon_path' : 'logo_path';
        PlatformBranding::current()->updateBranding([$field => $filename]);
        
        return $path;
    }
}
