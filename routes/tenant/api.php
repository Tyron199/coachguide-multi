<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Coach\ContractController;

Route::middleware(['auth', 'verified'])->group(function () {
     // API routes
        Route::get('/api/templates/{templatePath}/variables', [ContractController::class, 'getTemplateVariables'])
            ->where('templatePath', '.*');
        Route::get('/api/templates', [ContractController::class, 'getAvailableTemplates']);

        // Framework Assignment Modal API
        Route::get('/api/coaching-frameworks/modal-data', [\App\Http\Controllers\Tenant\Coach\CoachingFrameworkController::class, 'getModalData']);
        Route::post('/api/coaching-frameworks/assign', [\App\Http\Controllers\Tenant\Coach\CoachingFrameworkController::class, 'apiAssign']);
        Route::get('/api/coaching-sessions/for-client/{clientId}', [\App\Http\Controllers\Tenant\Coach\CoachingSessionController::class, 'getSessionsForClient']);

        // Custom Framework API routes
        Route::prefix('api/custom-frameworks')->name('api.custom-frameworks.')->middleware(['role:coach'])->group(function () {
            Route::post('/save-draft', [\App\Http\Controllers\Tenant\Coach\CustomFrameworkController::class, 'saveDraft'])->name('save-draft');
            Route::post('/preview', [\App\Http\Controllers\Tenant\Coach\CustomFrameworkController::class, 'preview'])->name('preview');
            Route::post('/validate', [\App\Http\Controllers\Tenant\Coach\CustomFrameworkController::class, 'validateFramework'])->name('validate');
        });
});