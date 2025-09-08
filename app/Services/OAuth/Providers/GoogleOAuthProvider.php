<?php

namespace App\Services\OAuth\Providers;

use App\Services\OAuth\BaseOAuthProvider;
use App\Services\OAuth\OAuthUserData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleOAuthProvider extends BaseOAuthProvider
{
    protected function extractUserData($socialiteUser, string $purpose): OAuthUserData
    {
        return new OAuthUserData(
            providerId: $socialiteUser->getId(),
            email: $socialiteUser->getEmail(),
            name: $socialiteUser->getName() ?? '',
            avatar: $socialiteUser->getAvatar(),
            accessToken: $socialiteUser->token,
            refreshToken: $socialiteUser->refreshToken,
            expiresIn: $socialiteUser->expiresIn,
            rawData: [
                'google_user_id' => $socialiteUser->getId(),
                'google_email' => $socialiteUser->getEmail(),
                'purpose' => $purpose
            ]
        );
    }

    public function refreshToken(string $refreshToken): array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            Log::error('Google token refresh failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to refresh Google token');
        }

        return $response->json();
    }
}
