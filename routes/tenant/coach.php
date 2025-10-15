<?php
use App\Http\Controllers\Tenant\Coach\ClientController;
use App\Http\Controllers\Tenant\Coach\CompanyController;
use App\Http\Controllers\Tenant\Coach\CoachingSessionController;
use App\Http\Controllers\Tenant\Coach\CoachingNoteController;
use App\Http\Controllers\Tenant\Coach\CoachingTaskController;
use App\Http\Controllers\Tenant\Coach\CoachingFrameworkController;
use App\Http\Controllers\Tenant\Coach\CoachingFrameworkInstanceController;
use App\Http\Controllers\Tenant\Coach\CustomFrameworkController;
use App\Http\Controllers\Tenant\AttachmentController;
use App\Http\Controllers\Tenant\Coach\ContractController;
use App\Http\Controllers\Tenant\Coach\CoachingLogController;
use App\Http\Controllers\Tenant\Coach\TrainingLogController;
use App\Http\Controllers\Tenant\Coach\SupervisionLogController;
use App\Http\Controllers\Tenant\Coach\SupervisionController;
use App\Http\Controllers\Tenant\Coach\ResourceLibraryController;
use App\Http\Controllers\Tenant\Coach\ProfessionalCredentialController;
use App\Http\Controllers\Tenant\Coach\ProfessionalDevelopmentController;



use Illuminate\Support\Facades\Route;

Route::prefix('coach')->name('coach.')->middleware(['role:coach|admin'])->group(function () {

    //Auth grouped
    Route::middleware(['auth', 'verified', 'two-factor'])->group(function () {

        Route::get('clients/archived', [ClientController::class, 'archived'])->name('clients.archived');
        Route::get('clients/{client}/objectives', [ClientController::class, 'objectives'])->name('clients.objectives');
        Route::get('clients/{client}/objectives/edit', [ClientController::class, 'editObjectives'])->name('clients.objectives.edit');
        Route::put('clients/{client}/objectives', [ClientController::class, 'updateObjectives'])->name('clients.objectives.update');
        Route::get('clients/{client}/notes', [CoachingNoteController::class, 'clientNotes'])->name('clients.notes');
        Route::get('clients/{client}/sessions', [CoachingSessionController::class, 'clientSessions'])->name('clients.sessions');
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

        // Coaching Framework routes
        Route::get('coaching-frameworks/assign', [CoachingFrameworkController::class, 'showAssignment'])->name('coaching-frameworks.assign');
        Route::get('coaching-frameworks/{framework}/assign', [CoachingFrameworkController::class, 'showAssignment'])->name('coaching-frameworks.assign-specific');
        Route::post('coaching-frameworks/assign', [CoachingFrameworkController::class, 'storeAssignment'])->name('coaching-frameworks.store-assignment');

        Route::get('coaching-frameworks', [CoachingFrameworkController::class, 'index'])->name('coaching-frameworks.index');
        Route::get('coaching-frameworks/models', [CoachingFrameworkController::class, 'models'])->name('coaching-frameworks.models');
        Route::get('coaching-frameworks/tools', [CoachingFrameworkController::class, 'tools'])->name('coaching-frameworks.tools');
        Route::get('coaching-frameworks/profiling', [CoachingFrameworkController::class, 'profiling'])->name('coaching-frameworks.profiling');
        Route::get('coaching-frameworks/{framework}', [CoachingFrameworkController::class, 'show'])->name('coaching-frameworks.show');

        // Coaching Framework Instance routes
        Route::get('coaching-sessions/{session}/frameworks', [CoachingFrameworkInstanceController::class, 'sessionInstances'])->name('coaching-sessions.frameworks');
        Route::get('coaching-framework-instances/{instance}', [CoachingFrameworkInstanceController::class, 'show'])->name('coaching-framework-instances.show');
        Route::patch('coaching-framework-instances/{instance}', [CoachingFrameworkInstanceController::class, 'update'])->name('coaching-framework-instances.update');
        Route::delete('coaching-framework-instances/{instance}', [CoachingFrameworkInstanceController::class, 'destroy'])->name('coaching-framework-instances.destroy');

        // Custom Framework routes
        Route::prefix('custom-frameworks')->name('custom-frameworks.')->group(function () {
            Route::get('/', [CustomFrameworkController::class, 'index'])->name('index');
            Route::get('/create', [CustomFrameworkController::class, 'create'])->name('create');
            Route::post('/', [CustomFrameworkController::class, 'store'])->name('store');
            Route::get('/{framework}', [CustomFrameworkController::class, 'show'])->name('show');
            Route::get('/{framework}/edit', [CustomFrameworkController::class, 'edit'])->name('edit');
            Route::patch('/{framework}', [CustomFrameworkController::class, 'update'])->name('update');
            Route::patch('/{framework}/publish', [CustomFrameworkController::class, 'publish'])->name('publish');
            Route::delete('/{framework}', [CustomFrameworkController::class, 'destroy'])->name('destroy');
            Route::patch('/{framework}/toggle-active', [CustomFrameworkController::class, 'toggleActive'])->name('toggle-active');
            Route::patch('/{framework}/duplicate', [CustomFrameworkController::class, 'duplicate'])->name('duplicate');
        })->where('framework', '[0-9]+');
        
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


        //Coaching log
        Route::get('coaching-log', [CoachingLogController::class, 'index'])->name('coaching-log.index');

        //Training log
        Route::get('training-log', [TrainingLogController::class, 'index'])->name('training-log.index');

    

        //Resource Library
        Route::get('resource-library', [ResourceLibraryController::class, 'all'])->name('resource-library.all');
        Route::get('resource-library/books', [ResourceLibraryController::class, 'books'])->name('resource-library.books');
        Route::get('resource-library/podcasts', [ResourceLibraryController::class, 'podcasts'])->name('resource-library.podcasts');
        Route::get('resource-library/videos', [ResourceLibraryController::class, 'videos'])->name('resource-library.videos');
        Route::get('resource-library/courses', [ResourceLibraryController::class, 'courses'])->name('resource-library.courses');
        Route::get('resource-library/articles', [ResourceLibraryController::class, 'articles'])->name('resource-library.articles');

        //Professional credentials
        Route::prefix('professional-credentials')->name('professional-credentials.')->group(function () {
            Route::get('/', [ProfessionalCredentialController::class, 'index'])->name('index');
            Route::post('/', [ProfessionalCredentialController::class, 'store'])->name('store');
            Route::patch('/{credential}', [ProfessionalCredentialController::class, 'update'])->name('update');
            Route::post('/{credential}/replace', [ProfessionalCredentialController::class, 'replace'])->name('replace');
            Route::delete('/{credential}', [ProfessionalCredentialController::class, 'destroy'])->name('destroy');
            Route::get('/{credential}/download', [ProfessionalCredentialController::class, 'download'])->name('download');
        });

        //Training & Development (Growth Tracker)
        Route::prefix('growth-tracker/training-development')->name('growth-tracker.training-development.')->group(function () {
            Route::get('/', [ProfessionalDevelopmentController::class, 'index'])->name('index');
            Route::get('/create', [ProfessionalDevelopmentController::class, 'create'])->name('create');
            Route::post('/', [ProfessionalDevelopmentController::class, 'store'])->name('store');
            Route::post('/delete', [ProfessionalDevelopmentController::class, 'destroyBatch'])->name('delete.batch');
            Route::get('/{professionalDevelopment}', [ProfessionalDevelopmentController::class, 'show'])->name('show');
            Route::get('/{professionalDevelopment}/edit', [ProfessionalDevelopmentController::class, 'edit'])->name('edit');
            Route::patch('/{professionalDevelopment}', [ProfessionalDevelopmentController::class, 'update'])->name('update');
            Route::delete('/{professionalDevelopment}', [ProfessionalDevelopmentController::class, 'destroy'])->name('destroy');
        });

        //Supervision Log (Growth Tracker)
        Route::prefix('growth-tracker/supervision-log')->name('growth-tracker.supervision-log.')->group(function () {
            Route::get('/', [SupervisionController::class, 'index'])->name('index');
            Route::get('/create', [SupervisionController::class, 'create'])->name('create');
            Route::post('/', [SupervisionController::class, 'store'])->name('store');
            Route::post('/delete', [SupervisionController::class, 'destroyBatch'])->name('delete.batch');
            Route::get('/{supervision}', [SupervisionController::class, 'show'])->name('show');
            Route::get('/{supervision}/edit', [SupervisionController::class, 'edit'])->name('edit');
            Route::patch('/{supervision}', [SupervisionController::class, 'update'])->name('update');
            Route::delete('/{supervision}', [SupervisionController::class, 'destroy'])->name('destroy');
        });

    });


    // Coach contract signing routes (outside auth middleware for token access)
    Route::group([], function () {
        Route::get('sign-contract/{token}', [ContractController::class, 'sign'])
            ->middleware('throttle:contract-view')
            ->name('contracts.sign');
        
        Route::post('sign-contract/{token}', [ContractController::class, 'storeSignature'])
            ->middleware('throttle:contract-sign')
            ->name('contracts.store');
        
        Route::get('sign-contract/{token}/preview', [ContractController::class, 'signPreview'])
            ->middleware('throttle:contract-view')
            ->name('contracts.sign.preview');

        Route::get('sign-contract/{token}/pdf', [ContractController::class, 'pdfToken'])
            ->middleware('throttle:contract-download')
            ->name('contracts.sign.pdf');
    });



});


