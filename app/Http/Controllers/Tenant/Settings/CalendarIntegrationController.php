<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Http\Controllers\Controller;
use App\Services\Tenant\MasterApiService;
use App\Services\Tenant\OAuthProviderValidator;
use App\Enums\Tenant\CalendarIntegrationProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Inertia\Inertia;

use Exception;

class CalendarIntegrationController extends Controller
{
    protected $masterApi;

    public function __construct(MasterApiService $masterApi) 
    {
        $this->masterApi = $masterApi;
    }

    public function show(Request $request)
    {
        return Inertia::render('Tenant/settings/CalendarIntegrations', [
            'hasMicrosoftCalendar' => $request->user()->hasMicrosoftCalendar(),
            'hasGoogleCalendar' => $request->user()->hasGoogleCalendar(),
        ]);
    }

    /**
     * Initiate calendar integration for any provider
     */
    public function initiate(Request $request, string $provider)
    {
        try {
            // Validate user is authenticated
            if (!auth()->check()) {
                Log::warning('Unauthenticated user attempted calendar integration');
                return redirect()->route('tenant.login')
                    ->with(['error' => 'You must be logged in to connect your calendar.']);
            }

            // Validate provider is supported for calendar integration
            if (!OAuthProviderValidator::supportsCalendar($provider)) {
                return redirect()->route('tenant.dashboard')
                    ->with(['error' => 'Unsupported calendar provider.']);
            }

            // Check if user already has this calendar connected
            if ($this->userHasCalendarProvider($provider)) {
                $providerName = ucfirst($provider);
                return redirect()->route('tenant.dashboard')
                    ->with(['info' => "{$providerName} calendar is already connected to your account."]);
            }

            $returnUrl = route('tenant.calendar.oauth.callback', ['provider' => $provider]);
            $userId = auth()->user()->id;
            
            Log::info('Calendar integration initiation started', [
                'provider' => $provider, 
                'user_id' => $userId,
                'return_url' => $returnUrl
            ]);

            $masterOauthUrl = $this->masterApi->getOauthInitiateUrl($provider, 'calendar', $userId, $returnUrl);

            Log::info('Calendar OAuth URL generated successfully', [
                'user_id' => $userId,
                'provider' => $provider,
                'url_generated' => !empty($masterOauthUrl)
            ]);
            
            return Inertia::location($masterOauthUrl);

        } catch (Exception $e) {
            Log::error('Calendar integration initiation failed', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('tenant.dashboard')
                ->with(['error' => 'Failed to initiate calendar connection. Please try again.']);
        }
    }
    
    /**
     * Handle calendar integration callback
     */
    public function callback(Request $request, string $provider)
    {
        try {
            // Validate user is authenticated
            if (!auth()->check()) {
                Log::warning('Unauthenticated user attempted calendar callback');
                return redirect()->route('tenant.login')
                    ->with(['error' => 'Authentication session expired. Please try again.']);
            }

            // Validate required parameters
            $exchangeToken = $request->get('exchange_token');
            $state = $request->get('state');

            if (empty($exchangeToken)) {
                Log::warning('Calendar OAuth callback missing exchange token', [
                    'user_id' => auth()->id(),
                    'provider' => $provider,
                    'request_params' => $request->all()
                ]);
                return redirect()->route('tenant.calendar-integrations')
                    ->with(['error' => 'Invalid callback parameters. Please try connecting your calendar again.']);
            }

            // Check for OAuth error from provider
            if ($request->has('error')) {
                $error = $request->get('error');
                $errorDescription = $request->get('error_description', 'No description provided');
                
                Log::warning('Calendar OAuth provider returned error', [
                    'user_id' => auth()->id(),
                    'provider' => $provider,
                    'error' => $error,
                    'error_description' => $errorDescription
                ]);

                return redirect()->route('tenant.calendar-integrations')
                    ->with(['error' => 'Calendar connection was cancelled or failed. Please try again.']);
            }

            Log::info('Calendar OAuth callback processing started', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'has_exchange_token' => !empty($exchangeToken),
                'has_state' => !empty($state)
            ]);

            // Exchange token with master API
            $tokenData = $this->masterApi->exchangeOAuthToken($exchangeToken);

            // Validate this is a calendar integration
            if ($tokenData['purpose'] !== 'calendar') {
                throw new Exception("Expected calendar purpose, got: {$tokenData['purpose']}");
            }

            Log::info('Calendar OAuth token exchange successful', [
                'user_id' => auth()->id(),
                'provider' => $tokenData['provider'],
                'purpose' => $tokenData['purpose'],
                'user_email' => $tokenData['user_data']['email'] ?? 'not_provided',
                'has_access_token' => isset($tokenData['access_token']),
                'has_refresh_token' => isset($tokenData['refresh_token'])
            ]);

            // Convert provider string to enum
            $providerEnum = $this->getProviderEnum($tokenData['provider']);

            // Store calendar integration data in database
            $request->user()->calendarIntegrations()->create([
                'provider' => $providerEnum,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => $tokenData['expires_at'] ? now()->parse($tokenData['expires_at']) : null
            ]);

            $providerName = ucfirst($tokenData['provider']);
            return redirect()->route('tenant.calendar-integrations')
                ->with('success', "{$providerName} calendar connected successfully!");

        } catch (RequestException $e) {
            // Handle HTTP client exceptions
            $response = $e->response;
            $statusCode = $response ? $response->status() : 'unknown';
            $errorBody = $response ? $response->json() : [];
            
            Log::error('Calendar OAuth callback HTTP error', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'status_code' => $statusCode,
                'error_body' => $errorBody,
                'exchange_token_provided' => !empty($request->get('exchange_token')),
                'full_error' => $e->getMessage()
            ]);

            // Handle specific error cases
            if ($statusCode == 400 && isset($errorBody['error'])) {
                $errorMessage = $errorBody['error'];
                
                if (str_contains($errorMessage, 'Invalid or expired exchange token')) {
                    return redirect()->route('tenant.calendar-integrations')
                        ->with(['error' => 'The calendar connection session has expired. Please try again.']);
                }
            }

            return redirect()->route('tenant.calendar-integrations')
                ->with(['error' => 'Failed to connect calendar. Please try again.']);

        } catch (Exception $e) {
            Log::error('Calendar OAuth callback general error', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exchange_token_provided' => !empty($request->get('exchange_token'))
            ]);

            return redirect()->route('tenant.calendar-integrations')
                ->with(['error' => 'An unexpected error occurred. Please try connecting your calendar again.']);
        }
    }

    /**
     * Disconnect a calendar integration
     */
    public function disconnect(Request $request, string $provider)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('tenant.login');
            }

            $providerEnum = $this->getProviderEnum($provider);
            
            $integration = $request->user()->calendarIntegrations()
                ->where('provider', $providerEnum)
                ->first();

            if (!$integration) {
                return redirect()->route('tenant.calendar-integrations')
                    ->with(['error' => 'Calendar integration not found.']);
            }

            $integration->delete();

            $providerName = ucfirst($provider);
            Log::info('Calendar integration disconnected', [
                'user_id' => auth()->id(),
                'provider' => $provider
            ]);

            return redirect()->route('tenant.calendar-integrations')
                ->with('success', "{$providerName} calendar disconnected successfully.");

        } catch (Exception $e) {
            Log::error('Calendar disconnection failed', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('tenant.calendar-integrations')
                ->with(['error' => 'Failed to disconnect calendar. Please try again.']);
        }
    }



    /**
     * Check if user already has this calendar provider connected
     */
    protected function userHasCalendarProvider(string $provider): bool
    {
        $providerEnum = $this->getProviderEnum($provider);
        return auth()->user()->calendarIntegrations()->where('provider', $providerEnum)->exists();
    }

    /**
     * Convert provider string to enum
     */
    protected function getProviderEnum(string $provider): CalendarIntegrationProvider
    {
        return match($provider) {
            'microsoft' => CalendarIntegrationProvider::MICROSOFT,
            'google' => CalendarIntegrationProvider::GOOGLE,
            default => throw new \Exception("Unsupported calendar provider: {$provider}")
        };
    }
}
