<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Http\Controllers\Controller;
use App\Services\OAuth\OauthProviderType;
use App\Services\OAuth\OAuthProviderValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\OAuth\OAuthProviderManager;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

use Exception;

class CalendarIntegrationController extends Controller
{

    protected $providerManager;

    public function __construct(OAuthProviderManager $providerManager) 
    {
        $this->providerManager = $providerManager;
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
    public function initiate(Request $request, OauthProviderType $provider)
    {

          // Validate provider is supported for calendar integration
        if (!OAuthProviderValidator::supportsCalendar($provider)) {
                return redirect()->route('tenant.calendar-integrations')
                    ->with(['error' => 'Unsupported calendar provider.']);
        }

            // Check if user already has this calendar connected
            if ($request->user()->calendarIntegrations()->where('provider', $provider)->exists()) {
                return redirect()->route('tenant.calendar-integrations')
                    ->with(['error' => "{$provider->value} calendar is already connected to your account."]);
            }


        // Get the OAuth provider and generate authorization URL
        $state = Crypt::encrypt([
            'tenant_id' => tenant()->id,
            'provider' => $provider,
            'purpose' => 'calendar',
            'user_id' => $request->user()->id,
        ]);
        $oauthProvider = $this->providerManager->getProvider($provider);
        $authUrl = $oauthProvider->getAuthorizationUrl('calendar', $state);

        return Inertia::location($authUrl);
       
    }
    
    /**
     * Handle calendar integration callback - REMOVED
     * OAuth callbacks are now handled by the central app (OathController)
     * due to subdomain redirect limitations with OAuth providers
     */

    /**
     * Disconnect a calendar integration
     */
    public function disconnect(Request $request, OauthProviderType $provider)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('tenant.login');
            }

            
            $integration = $request->user()->calendarIntegrations()
                ->where('provider', $provider)
                ->first();

            if (!$integration) {
                return redirect()->route('tenant.calendar-integrations')
                    ->with(['error' => 'Calendar integration not found.']);
            }

            $integration->delete();

            $providerName = ucfirst($provider->value);
            Log::info('Calendar integration disconnected', [
                'user_id' => auth()->id(),
                'provider' => $provider->value
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
    protected function userHasCalendarProvider(OauthProviderType $provider): bool
    {
        return auth()->user()->calendarIntegrations()->where('provider', $provider)->exists();
    }


}
