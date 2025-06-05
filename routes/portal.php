<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\AnagraficaController;
use App\Http\Controllers\HealthCheckRecordController;
use App\Http\Controllers\HealthSurveillanceController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PPEController;
use App\Http\Controllers\ProfileController as BreezeProfileController; // Alias per il controller di Breeze
use App\Http\Controllers\ProfileSafetyCourseController;
use App\Http\Controllers\RiskController; // Aggiunto il RiskController
use App\Http\Controllers\SafetyCourseController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Routes
|--------------------------------------------------------------------------
|
| Qui vengono registrate le rotte web specifiche del portale applicativo.
| Queste rotte sono caricate dal RouteServiceProvider e sono
| assegnate al gruppo middleware "web".
|
*/

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rotte per la gestione del profilo utente autenticato (Breeze)
// Queste sono separate dalle rotte "anagrafiche" dell'applicazione.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [BreezeProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [BreezeProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [BreezeProfileController::class, 'destroy'])->name('profile.destroy');
});

// Gruppo principale di rotte dell'applicazione gestionale
Route::middleware(['auth'])->group(function () {

    // --- ANAGRAFICHE (Profili Applicativi) ---
    Route::name('profiles.')->prefix('profiles')->group(function () {
        Route::get('/', [AnagraficaController::class, 'index'])->name('index');
        Route::get('/data', [AnagraficaController::class, 'data'])->name('data');
        Route::get('/create', [AnagraficaController::class, 'create'])->name('create'); // Form per dati anagrafici base [cite: 2782]
        Route::post('/', [AnagraficaController::class, 'store'])->name('store'); // Salva dati anagrafici base [cite: 2782]
        Route::get('/{profile}', [AnagraficaController::class, 'show'])->name('show');
        Route::get('/{profile}/edit', [AnagraficaController::class, 'edit'])->name('edit'); // Form per modifica dati anagrafici base [cite: 2782]
        Route::put('/{profile}', [AnagraficaController::class, 'update'])->name('update'); // Aggiorna dati anagrafici base [cite: 2782]
        Route::delete('/{profile}', [AnagraficaController::class, 'destroy'])->name('destroy'); // Soft delete del profilo [cite: 2783]

        // Gestione Periodi di Impiego (Nuovo Impiego / Rientro)
        Route::get('/{profile}/employment/create', [AnagraficaController::class, 'createEmploymentPeriodForm'])->name('employment.create.form');
        Route::post('/{profile}/employment', [AnagraficaController::class, 'storeEmploymentPeriod'])->name('employment.store');
        // TODO: Considerare rotte per edit/update di un employment period esistente se necessario (oltre a terminarlo)

        // Gestione Assegnazione Sezione (per impiego attivo)
        Route::get('/{profile}/section-assignment/edit', [AnagraficaController::class, 'editSectionAssignmentForm'])->name('section_assignment.edit.form');
        Route::put('/{profile}/section-assignment', [AnagraficaController::class, 'updateSectionAssignment'])->name('section_assignment.update');

        // Gestione Uscita / Trasferimento (Termina impiego attivo)
        Route::get('/{profile}/transfer-out/create', [AnagraficaController::class, 'createTransferOutForm'])->name('transfer_out.create.form');
        Route::post('/{profile}/transfer-out', [AnagraficaController::class, 'storeTransferOut'])->name('transfer_out.store');

        // Gestione DPI assegnati al profilo
        Route::get('/{profile}/edit-ppes', [AnagraficaController::class, 'editPpes'])->name('editPpes');
        Route::put('/{profile}/update-ppes', [AnagraficaController::class, 'updatePpes'])->name('updatePpes');
        
        // NUOVE ROTTE PER GESTIONE ATTIVITÀ DEL PROFILO
        Route::get('/{profile}/activities/edit', [AnagraficaController::class, 'editActivities'])->name('activities.edit');
        Route::put('/{profile}/activities', [AnagraficaController::class, 'updateActivities'])->name('activities.update');
        
        // Gestione Registrazioni Controlli Sanitari per un profilo
        Route::get('/{profile}/health-check-records/create', [HealthCheckRecordController::class, 'create'])->name('health-check-records.create');
        Route::post('/{profile}/health-check-records', [HealthCheckRecordController::class, 'store'])->name('health-check-records.store');

        // Gestione Frequenze Corsi di Sicurezza per un profilo
        Route::get('/{profile}/course-attendances/create', [ProfileSafetyCourseController::class, 'create'])->name('course_attendances.create');
        Route::post('/{profile}/course-attendances', [ProfileSafetyCourseController::class, 'store'])->name('course_attendances.store');
                Route::get('/{profile}/export-pdf', [AnagraficaController::class, 'exportPdf'])->name('export.pdf'); // NUOVA ROTTA

    });

    // Rotte singole per HealthCheckRecords (edit, update, destroy)
    // Nota: 'record' è il nome del parametro atteso dal controller
    Route::resource('health-check-records', HealthCheckRecordController::class)
        ->except(['index', 'create', 'store', 'show'])
        ->parameters(['health-check-records' => 'record']);

    // Rotte singole per ProfileSafetyCourse (Frequenze Corsi) (edit, update, destroy)
    // Nota: 'attendance' è il nome del parametro atteso dal controller
    Route::resource('course-attendances', ProfileSafetyCourseController::class)
        ->except(['index', 'create', 'store', 'show'])
        ->parameters(['course-attendances' => 'attendance']);


    // --- RISORSE STANDARD (Uffici, Sezioni, DPI, Corsi, Attività, Tipi Sorveglianza, Rischi) ---
    Route::resource('offices', OfficeController::class);
    Route::get('/offices/{office}/profiles', [OfficeController::class, 'showProfiles'])->name('offices.showProfiles');

    Route::resource('sections', SectionController::class);
    Route::get('/sections/{section}/profiles', [SectionController::class, 'showProfiles'])->name('sections.showProfiles');

    Route::resource('ppes', PPEController::class);
    Route::get('/ppes/{ppe}/profiles', [PPEController::class, 'showProfiles'])->name('ppes.showProfiles');
    Route::get('/ppes/{ppe}/profiles-attention', [PPEController::class, 'showProfilesWithAttention'])->name('ppes.showProfilesWithAttention'); // NUOVA ROTTA

    Route::resource('safety_courses', SafetyCourseController::class);
    Route::get('/safety_courses/{safety_course}/profiles', [SafetyCourseController::class, 'showProfiles'])->name('safety_courses.showProfiles');
    Route::get('/safety_courses/{safety_course}/profiles-attention', [SafetyCourseController::class, 'showProfilesWithAttention'])->name('safety_courses.showProfilesWithAttention'); // NUOVA ROTTA

    Route::resource('activities', ActivityController::class);
    Route::get('/activities/{activity}/profiles', [ActivityController::class, 'showProfiles'])->name('activities.showProfiles');

    Route::resource('health_surveillances', HealthSurveillanceController::class);
    Route::get('health-surveillances/data', [HealthSurveillanceController::class, 'data'])->name('health_surveillances.data');
    Route::get('/health_surveillances/{health_surveillance}/profiles', [HealthSurveillanceController::class, 'showProfiles'])->name('health_surveillances.showProfiles');
    Route::get('/health_surveillances/{health_surveillance}/profiles-attention', [HealthSurveillanceController::class, 'showProfilesWithAttention'])->name('health_surveillances.showProfilesWithAttention'); // NUOVA ROTTA

    Route::resource('risks', RiskController::class);
    Route::get('/risks/{risk}/profiles', [RiskController::class, 'showProfiles'])->name('risks.showProfiles');


    // --- ARCHIVIO PROFILI (e azioni correlate come restore, forceDelete) ---
    Route::name('admin.profiles.')->prefix('admin/profiles')->group(function () {
        Route::get('/archived', [AnagraficaController::class, 'archivedIndex'])->name('archived_index');
        Route::get('/archived/data', [AnagraficaController::class, 'archivedData'])->name('archived_data');

        // Laravel di default si aspetta {profile} se il parametro nel controller è type-hinted Profile.
        // Se usi {id} o {profile_id} nel path, assicurati che il controller method accetti $id.
        Route::post('/{id}/restore', [AnagraficaController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [AnagraficaController::class, 'forceDelete'])->name('forceDelete');
    });
});


// --- SEZIONE AMMINISTRAZIONE (Gestione Utenti Portale, Ruoli, Permessi) ---
Route::middleware(['auth', 'role:Amministratore'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/edit-permissions', [RoleController::class, 'editPermissions'])->name('roles.editPermissions');
    Route::put('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.syncPermissions');
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']);
});