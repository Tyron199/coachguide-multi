<?php


use App\Http\Controllers\Tenant\Admin\ThemeController;
use App\Http\Controllers\Tenant\Admin\LogoController;
use App\Http\Controllers\Tenant\Admin\SubscriptionController;


use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified','role:admin'])->prefix('admin')->name('admin.')->group(function () {


    Route::get('platform-settings/theme', [ThemeController::class, 'index'])->name('platform-settings.theme');
    Route::post('platform-settings/theme', [ThemeController::class, 'update'])->name('platform-settings.theme.update');
    Route::post('platform-settings/theme/upload', [ThemeController::class, 'uploadCustom'])->name('platform-settings.theme.upload');
    Route::post('platform-settings/theme/reset', [ThemeController::class, 'resetToDefault'])->name('platform-settings.theme.reset');

    // Logo management routes
    Route::get('platform-settings/logo', [LogoController::class, 'index'])->name('platform-settings.logo');
    Route::post('platform-settings/logo/upload', [LogoController::class, 'upload'])->name('platform-settings.logo.upload');
    Route::post('platform-settings/logo/reset', [LogoController::class, 'reset'])->name('platform-settings.logo.reset');


    // Subscription management routes
    Route::get('subscription/manage', [SubscriptionController::class, 'manage'])->name('subscription.manage');
    Route::get('subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('subscription/receipt', [SubscriptionController::class, 'receipt'])->name('subscription.receipt');

});