<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\ScopeSessions;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    ScopeSessions::class,
])->name('tenant.')->group(function () {
  

Route::get('/', function () {
    return Inertia::render('Tenant/Welcome');
})->name('home');

Route::get('dashboard', function () {
    // Redirect to role-specific dashboard
    if (auth()->user()->hasRole('coach')) {
        return app(\App\Http\Controllers\Tenant\Coach\DashboardController::class)->index();
    }
    if (auth()->user()->hasRole('client')) {
        return app(\App\Http\Controllers\Tenant\Client\DashboardController::class)->index();
    }
    // Default fallback for admin or other roles - show coach dashboard
    if (auth()->user()->hasRole('admin')) {
        return app(\App\Http\Controllers\Tenant\Coach\DashboardController::class)->index();
    }
    // If user has no recognized role
    abort(403, 'No dashboard available for your role');
})->middleware(['auth', 'verified', 'two-factor'])->name('dashboard');

Route::get('support', [\App\Http\Controllers\Tenant\SupportController::class, 'index'])
    ->middleware(['auth', 'verified', 'two-factor'])
    ->name('support');

Route::post('support', [\App\Http\Controllers\Tenant\SupportController::class, 'store'])
    ->middleware(['auth', 'verified', 'two-factor'])
    ->name('support.store');

Route::post('impersonate/{user}', [\App\Http\Controllers\Tenant\ImpersonationController::class, 'start'])
    ->middleware(['auth', 'verified', 'two-factor'])
    ->name('impersonate.start');

Route::post('impersonate/stop', [\App\Http\Controllers\Tenant\ImpersonationController::class, 'stop'])
    ->middleware(['auth', 'verified', 'two-factor'])
    ->name('impersonate.stop');

require __DIR__.'/tenant/settings.php';
require __DIR__.'/tenant/auth.php';
require __DIR__.'/tenant/coach.php';
require __DIR__.'/tenant/client.php';
require __DIR__.'/tenant/api.php';
require __DIR__.'/tenant/oauth.php';
require __DIR__.'/tenant/admin.php';


});
