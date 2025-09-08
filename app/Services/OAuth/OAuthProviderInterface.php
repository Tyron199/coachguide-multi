<?php

namespace App\Services\OAuth;

use Illuminate\Http\Request;

interface OAuthProviderInterface
{
    /**
     * Get the authorization URL for the OAuth flow
     */
    public function getAuthorizationUrl(string $purpose, string $state): string;
    
    /**
     * Handle the OAuth callback and return user data
     */
    public function handleCallback(Request $request, string $purpose): OAuthUserData;
    
    /**
     * Get the scopes for a specific purpose
     */
    public function getScopes(string $purpose): array;
    
    /**
     * Refresh an access token
     */
    public function refreshToken(string $refreshToken): array;
    
    /**
     * Get the provider name
     */
    public function getProviderName(): string;
}
