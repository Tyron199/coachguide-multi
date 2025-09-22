<?php


use App\Http\Controllers\Tenant\Admin\ThemeController;
use App\Http\Controllers\Tenant\Admin\LogoController;
use App\Http\Controllers\Tenant\Admin\SubscriptionController;
use App\Http\Controllers\Tenant\Admin\CoachController;
use App\Http\Controllers\Tenant\Admin\AdminController;


use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified', 'two-factor', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Coach Management Routes
    Route::prefix('coaches')->name('coaches.')->group(function () {
        Route::get('/', [CoachController::class, 'index'])->name('index');
        Route::get('/archived', [CoachController::class, 'archived'])->name('archived');
        Route::get('/create', [CoachController::class, 'create'])->name('create');
        Route::post('/', [CoachController::class, 'store'])->name('store');
        Route::get('/{coach}', [CoachController::class, 'show'])->name('show');
        Route::get('/{coach}/edit', [CoachController::class, 'edit'])->name('edit');
        Route::put('/{coach}', [CoachController::class, 'update'])->name('update');
        Route::patch('/{coach}/archive', [CoachController::class, 'archive'])->name('archive');
        Route::patch('/{coach}/unarchive', [CoachController::class, 'unarchive'])->name('unarchive');
        Route::delete('/{coach}', [CoachController::class, 'destroy'])->name('destroy');
        Route::post('/{coach}/invite', [CoachController::class, 'sendInvitation'])->name('invite');
        
        // Batch operations
        Route::post('/batch/archive', [CoachController::class, 'archiveBatch'])->name('batch.archive');
        Route::post('/batch/unarchive', [CoachController::class, 'unarchiveBatch'])->name('batch.unarchive');
        Route::delete('/batch/destroy', [CoachController::class, 'destroyBatch'])->name('batch.destroy');
        
        // Restore operations (for soft-deleted coaches)
        Route::post('/{id}/restore', [CoachController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [CoachController::class, 'forceDelete'])->name('force-delete');
    });
    
    // Administrator Management Routes  
    Route::prefix('administrators')->name('administrators.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/archived', [AdminController::class, 'archived'])->name('archived');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::get('/{admin}', [AdminController::class, 'show'])->name('show');
        Route::get('/{admin}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [AdminController::class, 'update'])->name('update');
        Route::patch('/{admin}/archive', [AdminController::class, 'archive'])->name('archive');
        Route::patch('/{admin}/unarchive', [AdminController::class, 'unarchive'])->name('unarchive');
        Route::delete('/{admin}', [AdminController::class, 'destroy'])->name('destroy');
        Route::post('/{admin}/invite', [AdminController::class, 'sendInvitation'])->name('invite');
        
        // Batch operations
        Route::post('/batch/archive', [AdminController::class, 'archiveBatch'])->name('batch.archive');
        Route::post('/batch/unarchive', [AdminController::class, 'unarchiveBatch'])->name('batch.unarchive');
        Route::delete('/batch/destroy', [AdminController::class, 'destroyBatch'])->name('batch.destroy');
        
        // Restore operations (for soft-deleted administrators)
        Route::post('/{id}/restore', [AdminController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [AdminController::class, 'forceDelete'])->name('force-delete');
    });

    // Platform Settings Routes
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