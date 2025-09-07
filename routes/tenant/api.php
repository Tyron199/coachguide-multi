<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Coach\ContractController;

Route::middleware(['auth', 'verified'])->group(function () {
     // API routes
        Route::get('/api/templates/{templatePath}/variables', [ContractController::class, 'getTemplateVariables'])
            ->where('templatePath', '.*');
        Route::get('/api/templates', [ContractController::class, 'getAvailableTemplates']);

});