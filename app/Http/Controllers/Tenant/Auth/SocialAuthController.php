<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Models\Tenant\User;
use App\Models\Tenant\SocialAccount;
use App\Services\Tenant\MasterApiService;
use App\Services\Tenant\OAuthProviderValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Client\RequestException;
use App\Http\Controllers\Controller;

use Exception;

class SocialAuthController extends Controller
{
    protected $masterApi;

    public function __construct(MasterApiService $masterApi) 
    {
        $this->masterApi = $masterApi;
    }

    /**
     * Initiate social authentication for any provider
     */
    public function initiate(Request $request, string $provider)
    {
        try {
            // Validate provider is supported for authentication
            if (!OAuthProviderValidator::supportsAuth($provider)) {
                return redirect()->route('tenant.login')
                    ->with(['error' => 'Unsupported authentication provider.']);
            }

            $returnUrl = route('tenant.social.oauth.callback', ['provider' => $provider]);
            
            Log::info('Social authentication initiation started', [
                'provider' => $provider,
                'return_url' => $returnUrl,
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->id()
            ]);

            // For social auth, user_id is null since they might not be logged in yet
            $masterOauthUrl = $this->masterApi->getOauthInitiateUrl($provider, 'auth', null, $returnUrl);

            Log::info('Social OAuth URL generated successfully', [
                'provider' => $provider,
                'url_generated' => !empty($masterOauthUrl)
            ]);
            
            return redirect($masterOauthUrl);

        } catch (Exception $e) {
            Log::error('Social authentication initiation failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('tenant.login')
                ->with(['error' => 'Failed to initiate social login. Please try again.']);
        }
    }
    
    /**
     * Handle social authentication callback
     */
    public function callback(Request $request, string $provider)
    {
        try {
            // Validate required parameters
            $exchangeToken = $request->get('exchange_token');
            $state = $request->get('state');

            if (empty($exchangeToken)) {
                Log::warning('Social OAuth callback missing exchange token', [
                    'provider' => $provider,
                    'request_params' => $request->all()
                ]);
                return redirect()->route('tenant.login')
                    ->with(['error' => 'Invalid callback parameters. Please try signing in again.']);
            }

            // Check for OAuth error from provider
            if ($request->has('error')) {
                $error = $request->get('error');
                $errorDescription = $request->get('error_description', 'No description provided');
                
                Log::warning('Social OAuth provider returned error', [
                    'provider' => $provider,
                    'error' => $error,
                    'error_description' => $errorDescription
                ]);

                return redirect()->route('tenant.login')
                    ->with(['error' => 'Social login was cancelled or failed. Please try again.']);
            }

            Log::info('Social OAuth callback processing started', [
                'provider' => $provider,
                'has_exchange_token' => !empty($exchangeToken),
                'has_state' => !empty($state)
            ]);

            // Exchange token with master API
            $tokenData = $this->masterApi->exchangeOAuthToken($exchangeToken);

            // Validate this is an auth integration
            if ($tokenData['purpose'] !== 'auth') {
                throw new Exception("Expected auth purpose, got: {$tokenData['purpose']}");
            }

            Log::info('Social OAuth token exchange successful', [
                'provider' => $tokenData['provider'],
                'purpose' => $tokenData['purpose'],
                'user_email' => $tokenData['user_data']['email'] ?? 'not_provided',
                'user_name' => $tokenData['user_data']['name'] ?? 'not_provided'
            ]);

            // Find or create user based on social account data
            $user = $this->findOrCreateUserFromSocial($tokenData);

            // Log the user in
            Auth::login($user, true); // Remember the user

            $providerName = ucfirst($tokenData['provider']);
            
            Log::info('User successfully authenticated via social login', [
                'user_id' => $user->id,
                'provider' => $tokenData['provider'],
                'user_email' => $user->email
            ]);

            return redirect()->intended(route('tenant.dashboard'))
                ->with('success', "Successfully signed in with {$providerName}!");

        } catch (RequestException $e) {
            // Handle HTTP client exceptions
            $response = $e->response;
            $statusCode = $response ? $response->status() : 'unknown';
            $errorBody = $response ? $response->json() : [];
            
            Log::error('Social OAuth callback HTTP error', [
                'provider' => $provider,
                'status_code' => $statusCode,
                'error_body' => $errorBody,
                'exchange_token_provided' => !empty($request->get('exchange_token')),
                'full_error' => $e->getMessage()
            ]);

            return redirect()->route('tenant.login')
                ->with(['error' => 'Failed to complete social login. Please try again.']);

        } catch (Exception $e) {
            Log::error('Social OAuth callback general error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exchange_token_provided' => !empty($request->get('exchange_token'))
            ]);

            return redirect()->route('tenant.login')
                ->with(['error' => 'An unexpected error occurred during social login. Please try again.']);
        }
    }

    /**
     * Link social account to existing authenticated user
     */
    public function link(Request $request, string $provider)
    {
        try {
            // User must be authenticated to link accounts
            if (!auth()->check()) {
                return redirect()->route('tenant.login')
                    ->with(['error' => 'You must be logged in to link social accounts.']);
            }

            // Check if user already has this social account linked
            if (auth()->user()->hasSocialAccount($provider)) {
                $providerName = ucfirst($provider);
                return redirect()->route('tenant.dashboard')
                    ->with(['info' => "{$providerName} account is already linked to your profile."]);
            }

            // Validate provider is supported
            if (!OAuthProviderValidator::supportsAuth($provider)) {
                return redirect()->route('tenant.dashboard')
                    ->with(['error' => 'Unsupported social provider.']);
            }

            $returnUrl = route('tenant.social.oauth.link.callback', ['provider' => $provider]);
            $userId = auth()->user()->id;
            
            Log::info('Social account linking initiated', [
                'provider' => $provider,
                'user_id' => $userId,
                'return_url' => $returnUrl
            ]);

            $masterOauthUrl = $this->masterApi->getOauthInitiateUrl($provider, 'auth', $userId, $returnUrl);
            
            return redirect($masterOauthUrl);

        } catch (Exception $e) {
            Log::error('Social account linking initiation failed', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('tenant.dashboard')
                ->with(['error' => 'Failed to initiate account linking. Please try again.']);
        }
    }

    /**
     * Handle social account linking callback
     */
    public function linkCallback(Request $request, string $provider)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('tenant.login');
            }

            $exchangeToken = $request->get('exchange_token');
            
            if (empty($exchangeToken)) {
                return redirect()->route('tenant.dashboard')
                    ->with(['error' => 'Invalid linking callback. Please try again.']);
            }

            $tokenData = $this->masterApi->exchangeOAuthToken($exchangeToken);

            // Create or update the social account link
            $this->createSocialAccountLink(auth()->user(), $tokenData);

            $providerName = ucfirst($provider);
            return redirect()->route('tenant.dashboard')
                ->with('success', "{$providerName} account linked successfully!");

        } catch (Exception $e) {
            Log::error('Social account linking callback failed', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('tenant.dashboard')
                ->with(['error' => 'Failed to link social account. Please try again.']);
        }
    }

    /**
     * Find or create user from social authentication data
     */
    protected function findOrCreateUserFromSocial(array $tokenData): User
    {
        $userData = $tokenData['user_data'];
        $provider = $tokenData['provider'];
        
        // First, try to find existing social account
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $userData['provider_id'])
            ->first();

        if ($socialAccount) {
            // Update social account tokens
            $socialAccount->update([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => $tokenData['expires_at'] ? now()->parse($tokenData['expires_at']) : null,
            ]);

            Log::info('Existing social account found', [
                'user_id' => $socialAccount->user_id,
                'provider' => $provider
            ]);

            return $socialAccount->user;
        }

        // Try to find user by email
        $user = User::where('email', $userData['email'])->first();

        if ($user) {
            // Link this social account to existing user
            $this->createSocialAccountLink($user, $tokenData);
            
            Log::info('Social account linked to existing user', [
                'user_id' => $user->id,
                'provider' => $provider,
                'email' => $userData['email']
            ]);

            return $user;
        }

        // Create new user
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make(Str::random(32)), // Random password since they'll use social login
            'email_verified_at' => now(), // Trust social provider verification
        ]);

        // Create social account link
        $this->createSocialAccountLink($user, $tokenData);

        Log::info('New user created via social authentication', [
            'user_id' => $user->id,
            'provider' => $provider,
            'email' => $userData['email']
        ]);

        return $user;
    }

    /**
     * Create social account link for a user
     */
    protected function createSocialAccountLink(User $user, array $tokenData): SocialAccount
    {
        $userData = $tokenData['user_data'];
        
        return $user->socialAccounts()->updateOrCreate(
            [
                'provider' => $tokenData['provider'],
            ],
            [
                'provider_id' => $userData['provider_id'],
                'provider_email' => $userData['email'],
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'],
                'expires_at' => $tokenData['expires_at'] ? now()->parse($tokenData['expires_at']) : null,
            ]
        );
    }


}
