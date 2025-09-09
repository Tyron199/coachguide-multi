<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Central\HomeController;
use App\Http\Controllers\Central\RegistrationController;
use App\Http\Controllers\Central\LoginController;
use App\Http\Controllers\Central\OathController;
use Laravel\Paddle\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Log;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->name('central.')->group(function (  ) use ($domain) {

         if (str_contains($domain, 'sharedwithexpose.com')) {
            Route::post('paddle/webhook', WebhookController::class)
                ->name('cashier.webhook');
            Route::post('paddle/test', function () {
                Log::debug('Paddle webhook received');
                return 'test';
            });
            Route::get('paddle/test', function () {
                Log::debug('Paddle webhook received');
                return response()->json(['message' => 'Paddle webhook received']);
            });
            return;
        }
    

        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
        Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
        Route::get('/register', [RegistrationController::class, 'index'])->name('register');
        Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
        Route::get('/login', [LoginController::class, 'index'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->name('login.store');
        Route::get('/login/tenants', [LoginController::class, 'showSelectTenant'])->name('login.tenants.show');
        Route::post('/login/tenants', [LoginController::class, 'selectTenant'])->name('login.tenants.select');
        Route::get('/verify', [RegistrationController::class, 'verify'])->name('registration.verify');
        Route::get('/confirm/{token}', [RegistrationController::class, 'showConfirm'])->name('registration.confirm.show');
        Route::post('/confirm/{token}', [RegistrationController::class, 'submitConfirm'])->name('registration.confirm.submit');
        
        // OAuth callback routes
        Route::get('/oauth/{provider}/callback', [OathController::class, 'callback'])
            ->where('provider', 'microsoft|google')
            ->name('oauth.callback');

    });
}




