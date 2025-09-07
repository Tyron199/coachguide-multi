<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Central\TenantController;


foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->name('central.')->group(function () {
    

        Route::get('/', [TenantController::class, 'index'])->name('home');


    });
}




