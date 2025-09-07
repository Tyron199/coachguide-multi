<?php

namespace App\Services\Tenant;

class OAuthProviderValidator
{
    /**
     * Providers that support calendar integration
     */
    const CALENDAR_PROVIDERS = ['microsoft', 'google'];

    /**
     * Providers that support social authentication
     */
    const AUTH_PROVIDERS = ['microsoft', 'google'];

    /**
     * Check if provider supports calendar integration
     */
    public static function supportsCalendar(string $provider): bool
    {
        return in_array($provider, self::CALENDAR_PROVIDERS);
    }

    /**
     * Check if provider supports social authentication
     */
    public static function supportsAuth(string $provider): bool
    {
        return in_array($provider, self::AUTH_PROVIDERS);
    }

    /**
     * Get all providers that support calendar integration
     */
    public static function getCalendarProviders(): array
    {
        return self::CALENDAR_PROVIDERS;
    }

    /**
     * Get all providers that support social authentication
     */
    public static function getAuthProviders(): array
    {
        return self::AUTH_PROVIDERS;
    }

    /**
     * Get human-readable provider name
     */
    public static function getProviderDisplayName(string $provider): string
    {
        return match($provider) {
            'microsoft' => 'Microsoft',
            'google' => 'Google',
            default => ucfirst($provider)
        };
    }

    /**
     * Validate provider for a specific purpose
     */
    public static function validateProviderForPurpose(string $provider, string $purpose): bool
    {
        return match($purpose) {
            'calendar' => self::supportsCalendar($provider),
            'auth' => self::supportsAuth($provider),
            default => false
        };
    }
}
