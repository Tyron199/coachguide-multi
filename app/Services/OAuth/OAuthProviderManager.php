<?php

namespace App\Services\OAuth;

use App\Services\OAuth\Providers\MicrosoftOAuthProvider;
use App\Services\OAuth\Providers\GoogleOAuthProvider;
use App\Services\OAuth\OauthProviderType;
use InvalidArgumentException;

class OAuthProviderManager
{
    protected array $providers = [];

    public function __construct()
    {
        $this->registerProviders();
    }

    /**
     * Register all available OAuth providers
     */
    protected function registerProviders(): void
    {
        $this->providers = [
            OauthProviderType::MICROSOFT->value => MicrosoftOAuthProvider::class,
            OauthProviderType::GOOGLE->value => GoogleOAuthProvider::class,
        ];
    }

    /**
     * Get a provider instance
     */
    public function getProvider(OauthProviderType $provider): OAuthProviderInterface
    {
        if (!$this->isProviderSupported($provider)) {
            throw new InvalidArgumentException("Unsupported OAuth provider: {$provider->value}");
        }

        $providerClass = $this->providers[$provider->value];
        
        return new $providerClass($provider);
    }

    /**
     * Check if a provider is supported
     */
    public function isProviderSupported(OauthProviderType $provider): bool
    {
        return isset($this->providers[$provider->value]);
    }

    /**
     * Get all supported providers
     */
    public function getSupportedProviders(): array
    {
        return array_map(
            fn($key) => OauthProviderType::from($key),
            array_keys($this->providers)
        );
    }

    /**
     * Check if a provider supports a specific purpose
     */
    public function providerSupportsPurpose(OauthProviderType $provider, string $purpose): bool
    {
        if (!$this->isProviderSupported($provider)) {
            return false;
        }

        $config = config("services.{$provider->value}");
        
        return isset($config['scopes'][$purpose]);
    }

    /**
     * Get all providers that support a specific purpose
     */
    public function getProvidersForPurpose(string $purpose): array
    {
        return array_filter($this->getSupportedProviders(), function ($provider) use ($purpose) {
            return $this->providerSupportsPurpose($provider, $purpose);
        });
    }
}
