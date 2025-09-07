<?php

use App\Http\Controllers\Tenant\Settings\AvatarController;
use App\Http\Controllers\Tenant\Settings\PasswordController;
use App\Http\Controllers\Tenant\Settings\ProfileController;
use App\Http\Controllers\Tenant\Settings\CalendarIntegrationController;
use App\Http\Controllers\Tenant\Settings\ThemeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/avatar', [AvatarController::class, 'index'])->name('avatar.index');
    Route::post('settings/avatar', [AvatarController::class, 'update'])->name('avatar.update');
    Route::delete('settings/avatar', [AvatarController::class, 'destroy'])->name('avatar.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('Tenant/settings/Appearance');
    })->name('appearance');

    Route::get('settings/calendar-integrations', [CalendarIntegrationController::class, 'show'])->name('calendar-integrations');
    
 
});
