<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\CalendarIntegration;
use App\Models\Tenant\User;
use App\Services\OAuth\OAuthProviderManager;
use App\Services\OAuth\OauthProviderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/*
OAuth providers can't redirect back to wildcard subdomains, so we need to handle all oauth redirects in our central app and redirect to the correct tenant app.
*/
class OathController extends Controller
{
    protected $providerManager;

    public function __construct(OAuthProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     * Handle calendar integration callback
     */
    public function callback(Request $request, OauthProviderType $provider)
    {
        try {
            Log::info('OAuth callback received', [
                'provider' => $provider,
                'has_code' => $request->has('code'),
                'has_state' => $request->has('state'),
                'has_error' => $request->has('error')
            ]);

            // Check for OAuth errors
            if ($request->has('error')) {
                Log::error('OAuth provider returned error', [
                    'provider' => $provider,
                    'error' => $request->get('error'),
                    'error_description' => $request->get('error_description')
                ]);

                return $this->handleError('OAuth authorization was denied or failed.');
            }

            if (!$request->has('code') || !$request->has('state')) {
                Log::error('OAuth callback missing required parameters', [
                    'provider' => $provider,
                    'has_code' => $request->has('code'),
                    'has_state' => $request->has('state')
                ]);

                return $this->handleError('Invalid OAuth callback - missing required parameters.');
            }

            // Decrypt and validate state
            $state = Crypt::decrypt($request->get('state'));

            Log::info('Decrypted state', [
                'state' => $state
            ]);

            if (!isset($state['tenant_id'], $state['user_id'], $state['purpose'])) {
                Log::error('Invalid state data', ['state' => $state]);
                return $this->handleError('Invalid OAuth state.');
            }

            // Find tenant and initialize tenancy
            $tenant = Tenant::find($state['tenant_id']);
            if (!$tenant) {
                Log::error('Tenant not found', ['tenant_id' => $state['tenant_id']]);
                return $this->handleError('Tenant not found.');
            }

            // Initialize tenant context
            tenancy()->initialize($tenant);

            // Find user in tenant context
            $user = User::find($state['user_id']);
            if (!$user) {
                Log::error('User not found in tenant context', [
                    'tenant_id' => $state['tenant_id'],
                    'user_id' => $state['user_id']
                ]);
                return $this->handleError('User not found.');
            }

            // Handle the OAuth callback and get user data
            $oauthProvider = $this->providerManager->getProvider($provider);
            $userData = $oauthProvider->handleCallback($request, $state['purpose']);

            // Save calendar integration
            if ($state['purpose'] === 'calendar') {
                $this->saveCalendarIntegration($user, $provider, $userData);
                
                Log::info('Calendar integration saved successfully', [
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'tenant_id' => $tenant->id
                ]);

                // Redirect to tenant calendar integrations page with success message
                $tenantUrl = tenant_route($tenant->domains[0]->domain, 'tenant.calendar-integrations', [
                    'success' => ucfirst($provider->value) . ' calendar connected successfully!'
                ]);

                return redirect($tenantUrl);
            }

            // Handle other purposes (social auth, etc.) here if needed
            return $this->handleError('Unsupported OAuth purpose.');

        } catch (\Exception $e) {
            Log::error('OAuth callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->handleError('OAuth integration failed. Please try again.');
        }
    }

    /**
     * Save calendar integration for user
     */
    protected function saveCalendarIntegration(User $user, OauthProviderType $provider, $userData)
    {
        // Check if integration already exists
        $existingIntegration = $user->calendarIntegrations()
            ->where('provider', $provider)
            ->first();

        $integrationData = [
            'user_id' => $user->id,
            'provider' => $provider,
            'access_token' => $userData->accessToken,
            'refresh_token' => $userData->refreshToken,
            'expires_at' => $userData->expiresIn ? Carbon::now()->addSeconds($userData->expiresIn) : null,
        ];

        if ($existingIntegration) {
            // Update existing integration
            $existingIntegration->update($integrationData);
        } else {
            // Create new integration
            CalendarIntegration::create($integrationData);
        }
    }

    /**
     * Handle OAuth errors by redirecting to a generic error page or tenant
     */
    protected function handleError(string $message)
    {
        // You might want to redirect to a central error page or back to the referring domain
        // For now, we'll redirect to the central app with an error
        return redirect()->route('central.home')->with('error', $message);
    }
}
