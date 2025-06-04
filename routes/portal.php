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
    Route::get('/profile', [BreezeProfileController::class, 'edit'])->name('profile.edit'); // [cite: 2774]
    Route::patch('/profile', [BreezeProfileController::class, 'update'])->name('profile.update'); // [cite: 2774]
    Route::delete('/profile', [BreezeProfileController::class, 'destroy'])->name('profile.destroy'); // [cite: 2774]
});

// Gruppo principale di rotte dell'applicazione gestionale
Route::middleware(['auth'])->group(function () {

    // --- ANAGRAFICHE (Profili Applicativi) ---
    Route::name('profiles.')->prefix('profiles')->group(function () {
        Route::get('/', [AnagraficaController::class, 'index'])->name('index'); // [cite: 2782]
        Route::get('/data', [AnagraficaController::class, 'data'])->name('data'); // [cite: 2782]
        Route::get('/create', [AnagraficaController::class, 'create'])->name('create'); // Form per dati anagrafici base [cite: 2782]
        Route::post('/', [AnagraficaController::class, 'store'])->name('store'); // Salva dati anagrafici base [cite: 2782]
        Route::get('/{profile}', [AnagraficaController::class, 'show'])->name('show'); // [cite: 2782]
        Route::get('/{profile}/edit', [AnagraficaController::class, 'edit'])->name('edit'); // Form per modifica dati anagrafici base [cite: 2782]
        Route::put('/{profile}', [AnagraficaController::class, 'update'])->name('update'); // Aggiorna dati anagrafici base [cite: 2782]
        Route::delete('/{profile}', [AnagraficaController::class, 'destroy'])->name('destroy'); // Soft delete del profilo [cite: 2783]

        // Gestione Incarico e Mansione S.P.P.
        Route::get('/{profile}/roles-responsibilities/edit', [AnagraficaController::class, 'editRolesAndResponsibilitiesForm'])->name('roles_responsibilities.edit.form');
        Route::put('/{profile}/roles-responsibilities', [AnagraficaController::class, 'updateRolesAndResponsibilities'])->name('roles_responsibilities.update');

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
        Route::get('/{profile}/edit-ppes', [AnagraficaController::class, 'editPpes'])->name('editPpes'); // [cite: 2783]
        Route::put('/{profile}/update-ppes', [AnagraficaController::class, 'updatePpes'])->name('updatePpes'); // [cite: 2783]

        // Gestione Registrazioni Controlli Sanitari per un profilo
        Route::get('/{profile}/health-check-records/create', [HealthCheckRecordController::class, 'create'])->name('health-check-records.create'); // [cite: 2784]
        Route::post('/{profile}/health-check-records', [HealthCheckRecordController::class, 'store'])->name('health-check-records.store'); // [cite: 2784]

        // Gestione Frequenze Corsi di Sicurezza per un profilo
        Route::get('/{profile}/course-attendances/create', [ProfileSafetyCourseController::class, 'create'])->name('course_attendances.create'); // [cite: 2786]
        Route::post('/{profile}/course-attendances', [ProfileSafetyCourseController::class, 'store'])->name('course_attendances.store'); // [cite: 2787]
    });

    // Rotte singole per HealthCheckRecords (edit, update, destroy)
    // Nota: 'record' è il nome del parametro atteso dal controller
    Route::resource('health-check-records', HealthCheckRecordController::class)
        ->except(['index', 'create', 'store', 'show'])
        ->parameters(['health-check-records' => 'record']); // [cite: 2785]

    // Rotte singole per ProfileSafetyCourse (Frequenze Corsi) (edit, update, destroy)
    // Nota: 'attendance' è il nome del parametro atteso dal controller
    Route::resource('course-attendances', ProfileSafetyCourseController::class)
        ->except(['index', 'create', 'store', 'show'])
        ->parameters(['course-attendances' => 'attendance']);


    // --- RISORSE STANDARD (Uffici, Sezioni, DPI, Corsi, Attività, Tipi Sorveglianza, Rischi) ---
    Route::resource('offices', OfficeController::class); // [cite: 2790]
    Route::get('/offices/{office}/profiles', [OfficeController::class, 'showProfiles'])->name('offices.showProfiles'); // [cite: 2792]

    Route::resource('sections', SectionController::class); // [cite: 2791]
    Route::get('/sections/{section}/profiles', [SectionController::class, 'showProfiles'])->name('sections.showProfiles'); // [cite: 2792]

    Route::resource('ppes', PPEController::class); // [cite: 2791]
    Route::get('/ppes/{ppe}/profiles', [PPEController::class, 'showProfiles'])->name('ppes.showProfiles'); // [cite: 2792]

    Route::resource('safety_courses', SafetyCourseController::class); // [cite: 2791]
    Route::get('/safety_courses/{safety_course}/profiles', [SafetyCourseController::class, 'showProfiles'])->name('safety_courses.showProfiles'); // [cite: 2793]

    Route::resource('activities', ActivityController::class); // [cite: 2791]
    Route::get('/activities/{activity}/profiles', [ActivityController::class, 'showProfiles'])->name('activities.showProfiles'); // [cite: 2793]

    Route::resource('health_surveillances', HealthSurveillanceController::class); // [cite: 2791]
    Route::get('health-surveillances/data', [HealthSurveillanceController::class, 'data'])->name('health_surveillances.data'); // [cite: 2791]
    Route::get('/health_surveillances/{health_surveillance}/profiles', [HealthSurveillanceController::class, 'showProfiles'])->name('health_surveillances.showProfiles'); // [cite: 2793]

    Route::resource('risks', RiskController::class); // [cite: 2793]
    Route::get('/risks/{risk}/profiles', [RiskController::class, 'showProfiles'])->name('risks.showProfiles'); // [cite: 2793]


    // --- ARCHIVIO PROFILI (e azioni correlate come restore, forceDelete) ---
    Route::name('admin.profiles.')->prefix('admin/profiles')->group(function () { // [cite: 2795]
        Route::get('/archived', [AnagraficaController::class, 'archivedIndex'])->name('archived_index'); // [cite: 2795]
        Route::get('/archived/data', [AnagraficaController::class, 'archivedData'])->name('archived_data'); // [cite: 2795]

        // Laravel di default si aspetta {profile} se il parametro nel controller è type-hinted Profile.
        // Se usi {id} o {profile_id} nel path, assicurati che il controller method accetti $id.
        Route::post('/{id}/restore', [AnagraficaController::class, 'restore'])->name('restore'); // [cite: 2795]
        Route::delete('/{id}/force-delete', [AnagraficaController::class, 'forceDelete'])->name('forceDelete'); // [cite: 2796]
    });
});


// --- SEZIONE AMMINISTRAZIONE (Gestione Utenti Portale, Ruoli, Permessi) ---
Route::middleware(['auth', 'role:Amministratore'])->prefix('admin')->name('admin.')->group(function () { // [cite: 2798]
    Route::resource('users', AdminUserController::class); // [cite: 2798]
    Route::resource('roles', RoleController::class); // [cite: 2798]
    Route::get('roles/{role}/edit-permissions', [RoleController::class, 'editPermissions'])->name('roles.editPermissions'); // [cite: 2798]
    Route::put('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.syncPermissions'); // [cite: 2798]
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']); // [cite: 2798]
});