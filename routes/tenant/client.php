<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Client\ContractController;
use App\Http\Controllers\Tenant\Client\SessionController;
use App\Http\Controllers\Tenant\Client\TaskController;
use App\Http\Controllers\Tenant\Client\TaskActionController;
use Inertia\Inertia;


//Public route to sign contract.

Route::prefix('client')->name('client.')->group(function () {
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


Route::middleware(['auth', 'verified', 'two-factor', 'role:client'])->prefix('client')->name('client.')->group(function () {
    // Sessions
    Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::get('sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
    
    // Tasks
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('tasks/{task}/submit', [TaskActionController::class, 'store'])->name('tasks.submit');
    Route::delete('tasks/{task}/actions/{action}', [TaskActionController::class, 'destroy'])->name('tasks.actions.destroy');
});
