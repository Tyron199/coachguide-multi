<?php
use App\Http\Controllers\Tenant\Coach\ClientController;
use App\Http\Controllers\Tenant\Coach\CompanyController;
use App\Http\Controllers\Tenant\Coach\CoachingSessionController;
use App\Http\Controllers\Tenant\Coach\CoachingNoteController;
use App\Http\Controllers\Tenant\Coach\CoachingTaskController;
use App\Http\Controllers\Tenant\AttachmentController;
use App\Http\Controllers\Tenant\Coach\ContractController;



use Illuminate\Support\Facades\Route;

// Client resource routes with Route Model Binding
Route::middleware(['auth', 'verified','role:coach'])->prefix('coach')->group(function () {

    
        Route::get('clients/archived', [ClientController::class, 'archived'])->name('clients.archived');
        Route::get('clients/{client}/objectives', [ClientController::class, 'objectives'])->name('clients.objectives');
        Route::get('clients/{client}/objectives/edit', [ClientController::class, 'editObjectives'])->name('clients.objectives.edit');
        Route::put('clients/{client}/objectives', [ClientController::class, 'updateObjectives'])->name('clients.objectives.update');
        Route::get('clients/{client}/notes', [CoachingNoteController::class, 'clientNotes'])->name('clients.notes');
        Route::post('clients/archive', [ClientController::class, 'archiveBatch'])->name('clients.archive.batch');
        Route::patch('clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');
        Route::post('clients/unarchive', [ClientController::class, 'unarchiveBatch'])->name('clients.unarchive.batch');
        Route::patch('clients/{client}/unarchive', [ClientController::class, 'unarchive'])->name('clients.unarchive');
        Route::post('clients/{client}/send-invitation', [ClientController::class, 'sendInvitation'])->name('clients.send-invitation');
        Route::post('clients/delete', [ClientController::class, 'destroyBatch'])->name('clients.delete.batch');
        Route::resource('clients', ClientController::class)->parameters(['clients' => 'client']);

        
        Route::post('companies/delete', [CompanyController::class, 'destroyBatch'])->name('companies.delete.batch');
        Route::get('companies/{company}/employees', [CompanyController::class, 'employees'])->name('companies.employees');
        Route::resource('companies', CompanyController::class)->parameters(['companies' => 'company']);
  
        Route::get('coaching-sessions/past', [CoachingSessionController::class, 'past'])->name('coaching-sessions.past');
        Route::get('coaching-sessions/calendar', [CoachingSessionController::class, 'calendar'])->name('coaching-sessions.calendar');
        Route::patch('coaching-sessions/attendance', [CoachingSessionController::class, 'updateAttendance'])->name('coaching-sessions.attendance');
        Route::post('coaching-sessions/delete', [CoachingSessionController::class, 'destroyBatch'])->name('coaching-sessions.delete.batch');
        Route::resource('coaching-sessions', CoachingSessionController::class)->parameters(['coaching-sessions' => 'coachingSession']);
        
        // Coaching Notes routes
        Route::resource('coaching-notes', CoachingNoteController::class)->parameters(['coaching-notes' => 'note']);
        Route::get('coaching-sessions/{session}/notes', [CoachingNoteController::class, 'sessionNotes'])->name('coaching-sessions.notes');
        
        // Coaching Tasks routes
        Route::resource('coaching-tasks', CoachingTaskController::class)->parameters(['coaching-tasks' => 'task']);
        Route::get('coaching-sessions/{session}/tasks', [CoachingTaskController::class, 'sessionTasks'])->name('coaching-sessions.tasks');
        Route::get('clients/{client}/tasks', [CoachingTaskController::class, 'clientTasks'])->name('clients.tasks');
        
        // Attachment routes
        Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
        Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

        // Contract management routes (all nested under clients)
        Route::get('/clients/{client}/contracts', [ContractController::class, 'clientContracts'])->name('clients.contracts.index');
        Route::get('/clients/{client}/contracts/create', [ContractController::class, 'create'])->name('clients.contracts.create');
        Route::post('/clients/{client}/contracts', [ContractController::class, 'store'])->name('clients.contracts.store');
        Route::get('/clients/{client}/contracts/{contract}', [ContractController::class, 'show'])->name('clients.contracts.show');
        Route::get('/clients/{client}/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('clients.contracts.edit');
        Route::patch('/clients/{client}/contracts/{contract}', [ContractController::class, 'update'])->name('clients.contracts.update');
        Route::get('/clients/{client}/contracts/{contract}/preview', [ContractController::class, 'preview'])->name('clients.contracts.preview');
        Route::get('/clients/{client}/contracts/{contract}/pdf', [ContractController::class, 'pdf'])->name('clients.contracts.pdf');
        Route::delete('/clients/{client}/contracts/{contract}', [ContractController::class, 'destroy'])->name('clients.contracts.destroy');
        Route::patch('/clients/{client}/contracts/{contract}/send', [ContractController::class, 'send'])->name('clients.contracts.send');
     
   
   


});

// Coach contract signing routes (outside auth middleware for token access)
Route::prefix('coach')->group(function () {
    Route::get('sign-contract/{token}', [ContractController::class, 'sign'])
        ->middleware('throttle:contract-view')
        ->name('coach.contracts.sign');
    
    Route::post('sign-contract/{token}', [ContractController::class, 'storeSignature'])
        ->middleware('throttle:contract-sign')
        ->name('coach.contracts.store');
    
    Route::get('sign-contract/{token}/preview', [ContractController::class, 'signPreview'])
        ->middleware('throttle:contract-view')
        ->name('coach.contracts.sign.preview');

           Route::get('sign-contract/{token}/pdf', [ContractController::class, 'pdfToken'])
        ->middleware('throttle:contract-download')
        ->name('coach.contracts.sign.pdf');
});