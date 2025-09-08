<?php

namespace App\Services\OAuth;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use InvalidArgumentException;

abstract class BaseOAuthProvider implements OAuthProviderInterface
{
    protected OauthProviderType $provider;
    protected array $config;

    public function __construct(OauthProviderType $provider)
    {
        $this->provider = $provider;
        $this->config = config("services.{$provider->value}");
        
        if (empty($this->config)) {
            throw new InvalidArgumentException("Provider {$provider->value} not configured");
        }
    }

    public function getAuthorizationUrl(string $purpose, string $state): string
    {
        $scopes = $this->getScopes($purpose);
        
        // Override the redirect URL to use the generic callback route
       // $redirectUrl = config('app.url') . "/oauth/{$this->provider}/callback";
        
        return Socialite::driver($this->provider->value)
            ->scopes($scopes)
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();
    }

    public function handleCallback(Request $request, string $purpose): OAuthUserData
    {
        $socialiteUser = Socialite::driver($this->provider->value)->stateless()->user();
        
        return $this->extractUserData($socialiteUser, $purpose);
    }

    public function getScopes(string $purpose): array
    {
        $scopes = $this->config['scopes'][$purpose] ?? null;
        
        if ($scopes === null) {
            throw new InvalidArgumentException("Purpose '{$purpose}' not supported for provider '{$this->provider->value}'");
        }
        
        return $scopes;
    }

    public function getProviderName(): string
    {
        return $this->provider->value;
    }

    /**
     * Extract standardized user data from the Socialite user
     */
    abstract protected function extractUserData($socialiteUser, string $purpose): OAuthUserData;
}
