<?php

namespace App\Notifications\Concerns;

use App\Models\Tenant\PlatformBranding;

trait HasTenantBranding
{
    /**
     * Get the platform branding for the current tenant
     */
    protected function getBranding(): PlatformBranding
    {
        return PlatformBranding::current();
    }

    /**
     * Get the company name for the current tenant
     */
    protected function getCompanyName(): string
    {
        return $this->getBranding()->company_name ?? config('app.name');
    }

    /**
     * Get the logo URL for the current tenant
     */
    protected function getLogoUrl(): ?string
    {
        $branding = $this->getBranding();
        
        if ($branding->hasCustomLogo()) {
            return asset('storage/' . $branding->logo_path);
        }
        
        return null;
    }

    /**
     * Get the icon URL for the current tenant
     */
    protected function getIconUrl(): ?string
    {
        $branding = $this->getBranding();
        
        if ($branding->hasCustomIcon()) {
            return asset('storage/' . $branding->icon_path);
        }
        
        return null;
    }
}

