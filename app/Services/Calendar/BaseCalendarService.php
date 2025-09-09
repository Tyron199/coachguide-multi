<?php

namespace App\Services\Calendar;

use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CalendarIntegration;
use App\Models\Tenant\CalendarEvent;
use App\Services\OAuth\OAuthProviderManager;
use App\Services\OAuth\OauthProviderType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseCalendarService implements CalendarServiceInterface
{
    protected OauthProviderType $provider;
    protected OAuthProviderManager $oauthManager;

    public function __construct(OauthProviderType $provider, OAuthProviderManager $oauthManager)
    {
        $this->provider = $provider;
        $this->oauthManager = $oauthManager;
    }

    public function getProviderName(): string
    {
        return $this->provider->value;
    }

    public function hasValidTokens(CalendarIntegration $integration): bool
    {
        if (!$integration->access_token) {
            return false;
        }

        // If token has expired, try to refresh it
        if ($integration->expires_at && $integration->expires_at->isPast()) {
            try {
                $this->refreshTokens($integration);
                return true;
            } catch (\Exception $e) {
                Log::warning("Failed to refresh calendar tokens for integration {$integration->id}", [
                    'error' => $e->getMessage(),
                    'provider' => $this->provider->value
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Refresh tokens using the OAuth provider
     */
    protected function refreshTokens(CalendarIntegration $integration): CalendarIntegration
    {
        if (!$integration->refresh_token) {
            throw new \Exception('No refresh token available');
        }

        $oauthProvider = $this->oauthManager->getProvider($this->provider);
        $tokenData = $oauthProvider->refreshToken($integration->refresh_token);

        $integration->update([
            'access_token' => $tokenData['access_token'],
            'expires_at' => now()->addSeconds($tokenData['expires_in']),
            'refresh_token' => $tokenData['refresh_token'] ?? $integration->refresh_token,
        ]);

        return $integration->fresh();
    }

    /**
     * Make an authenticated HTTP request
     */
    protected function makeAuthenticatedRequest(CalendarIntegration $integration, string $method, string $url, array $data = []): \Illuminate\Http\Client\Response
    {
        if (!$this->hasValidTokens($integration)) {
            throw new \Exception('Invalid or expired tokens');
        }

        $request = Http::withToken($integration->access_token)
            ->withHeaders($this->getDefaultHeaders());

        return match (strtolower($method)) {
            'get' => $request->get($url, $data),
            'post' => $request->post($url, $data),
            'put' => $request->put($url, $data),
            'patch' => $request->patch($url, $data),
            'delete' => $request->delete($url, $data),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
        };
    }

    /**
     * Build event payload for the specific provider
     */
    abstract protected function buildEventPayload(CoachingSession $session): array;

    /**
     * Extract event data from provider response
     */
    abstract protected function extractEventData(array $eventData): CalendarEventData;

    /**
     * Get the base API URL for this provider
     */
    abstract protected function getApiBaseUrl(): string;

    /**
     * Get default headers for API requests
     */
    abstract protected function getDefaultHeaders(): array;

    /**
     * Get attendees array from coaching session
     */
    protected function getAttendeesFromSession(CoachingSession $session): array
    {
        $attendees = [];
        
        if ($session->client && $session->client->email) {
            $attendees[] = [
                'email' => $session->client->email,
                'name' => $session->client->name,
                'role' => 'client'
            ];
        }
        
        //We should only send it to the client, since we are in  the coaches calendar
        // if ($session->coach && $session->coach->email) {
        //     $attendees[] = [
        //         'email' => $session->coach->email,
        //         'name' => $session->coach->name,
        //         'role' => 'coach'
        //     ];
        // }
        
        return $attendees;
    }

    /**
     * Generate event title from coaching session
     */
    protected function generateEventTitle(CoachingSession $session): string
    {
        $sessionNumber = $session->session_number;
        $clientName = $session->client?->name ?? 'Client';
        
        return "Coaching Session #{$sessionNumber} - {$clientName}";
    }

    /**
     * Generate event description from coaching session
     */
    protected function generateEventDescription(CoachingSession $session): string
    {
        $description = "Coaching session between {$session->coach?->name} and {$session->client?->name}.\n\n";
        
        if ($session->duration) {
            $description .= "Duration: {$session->formatted_duration}\n";
        }
        
        if ($session->session_type) {
            $description .= "Session Type: {$session->session_type->value}\n";
        }
        
        $description .= "\nGenerated by CoachGuide";
        
        return $description;
    }
}
