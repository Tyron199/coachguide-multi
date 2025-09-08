<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Central\HomeController;
use App\Http\Controllers\Central\RegistrationController;
use App\Http\Controllers\Central\LoginController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->name('central.')->group(function () {
    

        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
        Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
        Route::get('/register', [RegistrationController::class, 'index'])->name('register');
        Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
        Route::get('/login', [LoginController::class, 'index'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    });
}




