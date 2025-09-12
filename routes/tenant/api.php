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
});