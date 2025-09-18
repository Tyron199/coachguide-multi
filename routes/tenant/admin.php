<?php


use App\Http\Controllers\Tenant\Admin\ThemeController;
use App\Http\Controllers\Tenant\Admin\LogoController;
use App\Http\Controllers\Tenant\Admin\SubscriptionController;


use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified', 'two-factor', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {


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
    Route::get('subscription/update-payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->name('subscription.update-payment-method');
    Route::get('subscription/billing-portal', [SubscriptionController::class, 'billingPortal'])->name('subscription.billing-portal');
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('subscription/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');
    Route::get('subscription/invoice/{transaction}', [SubscriptionController::class, 'downloadInvoice'])->name('subscription.invoice');

});