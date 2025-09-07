<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Exception;

class MasterApiService
{
    protected $baseUrl;
    protected $apiToken;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('app.master_app_url');
        $this->apiToken = config('app.master_api_token');
        $this->timeout = config('app.master_api_timeout', 30);
        
        if (!$this->baseUrl) {
            throw new Exception('Master app URL not configured');
        }
        
        if (!$this->apiToken) {
            throw new Exception('Master API token not configured');
        }
    }

    /**
     * Get configured HTTP client with authentication
     */
    protected function getClient()
    {
        return Http::withToken($this->apiToken)
            ->baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry(3, 1000) // Retry 3 times with 1 second delay
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name'),
            ]);
    }

    public function getOauthInitiateUrl(string $provider, string $purpose, ?int $userId, string $returnUrl): string
    {
        $state = encrypt([
            'tenant_domain' => request()->getHost(),
            'user_id' => $userId, // Can be null for social auth during registration
            'provider' => $provider,
            'purpose' => $purpose,
            'nonce' => str()->random(32),
            'timestamp' => now()->timestamp,
        ]);
        
        return $this->baseUrl . "/oauth/{$provider}/initiate/{$purpose}?state={$state}&redirect_url={$returnUrl}";
    }

    /**
     * Exchange temporary OAuth token for actual tokens
     */
    public function exchangeOAuthToken(string $exchangeToken): array
    {
        try {
            $response = $this->getClient()
                ->post('/api/oauth/exchange', [
                    'exchange_token' => $exchangeToken
                ]);

            if (!$response->successful()) {  
                throw new Exception(
                    $response->json('error') ?? 'Failed to exchange OAuth token'
                );
            }

            $data = $response->json();
            
            Log::info('OAuth token exchange successful', [
                'microsoft_email' => $data['microsoft_email'] ?? null
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('OAuth token exchange exception', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

}