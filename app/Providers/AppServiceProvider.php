<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use App\Models\Tenant\CoachingTask;
use App\Policies\Tenant\UserPolicy;
use App\Policies\Tenant\CompanyPolicy;
use App\Policies\Tenant\CoachingTaskPolicy;
use App\Services\Calendar\CalendarServiceManager;
use App\Services\OAuth\OAuthProviderManager;
use App\Models\Tenant\CoachingSession;
use App\Observers\CoachingSessionObserver;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Calendar Service Manager
        $this->app->singleton(CalendarServiceManager::class, function ($app) {
            return new CalendarServiceManager($app->make(OAuthProviderManager::class));
        });

        // Register OAuth Provider Manager
        $this->app->singleton(OAuthProviderManager::class, function ($app) {
            return new OAuthProviderManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Register Socialite providers
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', \SocialiteProviders\Microsoft\Provider::class);
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });


        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(CoachingTask::class, CoachingTaskPolicy::class);

        // Register observers
        CoachingSession::observe(CoachingSessionObserver::class);

        // Configure rate limiting for contract endpoints
        $this->configureRateLimiting();

        
    }


      /**
     * Configure rate limiting for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Contract viewing - more lenient for legitimate users
        RateLimiter::for('contract-view', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Contract signing - stricter to prevent abuse
        RateLimiter::for('contract-sign', function (Request $request) {
            return [
                // Per IP limit - prevents brute force from single IP
                Limit::perMinute(3)->by($request->ip())->response(function () {
                    return response()->json([
                        'message' => 'Too many signing attempts. Please wait before trying again.'
                    ], 429);
                }),
                // Per token limit - prevents multiple rapid submissions for same contract
               // Limit::perHour(5)->by($request->route('tenant.token')),
            ];
        });

        // PDF downloads - moderate limit
        RateLimiter::for('contract-download', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
