<?php

use App\Http\Controllers\Tenant\Settings\CalendarIntegrationController;
use App\Http\Controllers\Tenant\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

// Calendar Integration Routes (requires authentication)
Route::middleware('auth')->prefix('calendar/oauth')->name('calendar.oauth.')->group(function () {
    Route::get('{provider}/initiate', [CalendarIntegrationController::class, 'initiate'])
        ->where('provider', 'microsoft|google')
        ->name('initiate');
    Route::get('{provider}/callback', [CalendarIntegrationController::class, 'callback'])
        ->where('provider', 'microsoft|google')
        ->name('callback');
    Route::delete('{provider}/disconnect', [CalendarIntegrationController::class, 'disconnect'])
        ->where('provider', 'microsoft|google')
        ->name('disconnect');
});

// Social Authentication Routes (no auth required for login/register)
Route::prefix('social/oauth')->name('social.oauth.')->group(function () {
    // Social login/register routes
    Route::get('{provider}/initiate', [SocialAuthController::class, 'initiate'])
        ->where('provider', 'microsoft|google')
        ->name('initiate');
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback'])
        ->where('provider', 'microsoft|google')
        ->name('callback');
    
    // Social account linking routes (requires authentication)
    Route::middleware('auth')->group(function () {
        Route::get('{provider}/link', [SocialAuthController::class, 'link'])
            ->where('provider', 'microsoft|google')
            ->name('link');
        Route::get('{provider}/link/callback', [SocialAuthController::class, 'linkCallback'])
            ->where('provider', 'microsoft|google')
            ->name('link.callback');
    });
});
