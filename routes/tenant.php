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
    return Inertia::render('Tenant/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/tenant/settings.php';
require __DIR__.'/tenant/auth.php';
require __DIR__.'/tenant/coach.php';
require __DIR__.'/tenant/client.php';
require __DIR__.'/tenant/api.php';
require __DIR__.'/tenant/oauth.php';
require __DIR__.'/tenant/admin.php';


});
