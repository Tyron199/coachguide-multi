<?php

namespace App\Services\OAuth;

use App\Services\OAuth\OauthProviderType;

class OAuthProviderValidator
{
    /**
     * Providers that support calendar integration
     */
    const CALENDAR_PROVIDERS = [OauthProviderType::MICROSOFT, OauthProviderType::GOOGLE];

    /**
     * Providers that support social authentication
     */
    const AUTH_PROVIDERS = [OauthProviderType::MICROSOFT, OauthProviderType::GOOGLE];

    /**
     * Check if provider supports calendar integration
     */
    public static function supportsCalendar(OauthProviderType $provider): bool
    {
        return in_array($provider, self::CALENDAR_PROVIDERS);
    }

    /**
     * Check if provider supports social authentication
     */
    public static function supportsAuth(OauthProviderType $provider): bool
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
    public static function getProviderDisplayName(OauthProviderType $provider): string
    {
        return match($provider) {
            OauthProviderType::MICROSOFT => 'Microsoft',
            OauthProviderType::GOOGLE => 'Google',
            default => ucfirst($provider->value)
        };
    }

    /**
     * Validate provider for a specific purpose
     */
    public static function validateProviderForPurpose(OauthProviderType $provider, string $purpose): bool
    {
        return match($purpose) {
            'calendar' => self::supportsCalendar($provider),
            'auth' => self::supportsAuth($provider),
            default => false
        };
    }
}
