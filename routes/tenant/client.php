<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Client\ContractController;
use Inertia\Inertia;


//Public route to sign contract.

Route::prefix('client')->group(function () {
    // Contract signing routes with different throttling for different actions
    Route::get('sign-contract/{token}', [ContractController::class, 'sign'])
        ->middleware('throttle:contract-view')
        ->name('contracts.sign');
    
    Route::get('sign-contract/{token}/preview', [ContractController::class, 'preview'])
        ->middleware('throttle:contract-view')
        ->name('contracts.preview');
    
    Route::post('sign-contract/{token}', [ContractController::class, 'store'])
        ->middleware('throttle:contract-sign')
        ->name('contracts.store');
    
    Route::get('sign-contract/{token}/pdf', [ContractController::class, 'pdf'])
        ->middleware('throttle:contract-download')
        ->name('contracts.pdf');
});


Route::middleware('auth','verified','role:client')->prefix('client')->group(function () {
  
});
