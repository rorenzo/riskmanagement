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
use App\Http\Controllers\ProfileController as BreezeProfileController;
use App\Http\Controllers\ProfileSafetyCourseController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\SafetyCourseController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;
use App\Models\Profile;
use App\Models\SafetyCourse;
use App\Models\HealthSurveillance; // Assicurati sia importato
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Portal Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // --- LOGICA PER I CONTEGGI DELLA DASHBOARD ---
    $activeProfiles = Profile::whereHas('employmentPeriods', fn ($q) => $q->whereNull('data_fine_periodo'))
        ->with([
            'activities.safetyCourses', 
            'safetyCourses.pivot', 
            'activities.healthSurveillances',
            'healthCheckRecords.healthSurveillance'
        ])
        ->get();

    $now = Carbon::now();
    $sixtyDaysFromNow = $now->copy()->addDays(60);

    // Conteggio Corsi con Criticità (Mancanti, Scaduti, o In Scadenza 60gg)
    $profilesWithCourseIssuesIds = []; // Per tenere traccia degli ID unici dei profili

    foreach ($activeProfiles as $profile) {
        $requiredCourseIds = $profile->activities->flatMap->safetyCourses->pluck('id')->unique()->all();
        if (empty($requiredCourseIds)) continue;

        foreach ($requiredCourseIds as $reqCourseId) {
            $courseModel = SafetyCourse::find($reqCourseId); // Sarebbe meglio precaricare i modelli dei corsi una volta sola se possibile
            if (!$courseModel) continue;

            $latestAttendance = $profile->safetyCourses->where('id', $reqCourseId)->sortByDesc('pivot.attended_date')->first();
            
            $issueFoundForThisCourse = false;
            if (!$latestAttendance || !$latestAttendance->pivot->attended_date) {
                $issueFoundForThisCourse = true; // Mancante
            } else {
                if ($courseModel->duration_years && $courseModel->duration_years > 0) {
                    $expirationDate = Carbon::parse($latestAttendance->pivot->attended_date)->addYears($courseModel->duration_years);
                    if ($expirationDate->isPast()) {
                        $issueFoundForThisCourse = true; // Scaduto
                    } elseif ($expirationDate->isBetween($now, $sixtyDaysFromNow)) {
                        $issueFoundForThisCourse = true; // In scadenza
                    }
                }
            }
            if ($issueFoundForThisCourse) {
                $profilesWithCourseIssuesIds[$profile->id] = true; // Segna il profilo
                break; // Basta una criticità per corso per questo profilo per includerlo nel conteggio
            }
        }
    }
    $profilesWithCourseIssuesCount = count($profilesWithCourseIssuesIds);


    // Conteggio Visite Mediche con Criticità (Mancanti, Scadute, o In Scadenza 60gg)
    $profilesWithHealthRecordIssuesIds = []; // Per ID unici

    foreach ($activeProfiles as $profile) {
        $requiredHealthSurveillanceIds = $profile->activities->flatMap->healthSurveillances->pluck('id')->unique()->all();
        if (empty($requiredHealthSurveillanceIds)) continue;

        foreach ($requiredHealthSurveillanceIds as $reqHsId) {
            // Non serve recuperare il modello HealthSurveillance qui se l'expiration_date è già nel record
            $latestCheckUp = $profile->healthCheckRecords->where('health_surveillance_id', $reqHsId)->sortByDesc('check_up_date')->first();
            
            $issueFoundForThisVisit = false;
            if (!$latestCheckUp) {
                $issueFoundForThisVisit = true; // Mancante
            } else if ($latestCheckUp->expiration_date) { // Assumiamo che expiration_date sia sempre calcolata e presente se la visita è stata fatta
                $expirationDate = Carbon::parse($latestCheckUp->expiration_date);
                if ($expirationDate->isPast()) {
                    $issueFoundForThisVisit = true; // Scaduta
                } elseif ($expirationDate->isBetween($now, $sixtyDaysFromNow)) {
                    $issueFoundForThisVisit = true; // In scadenza
                }
            }
            // Se $latestCheckUp esiste ma non ha expiration_date (improbabile con la logica attuale),
            // e la sorveglianza ha una durata, potresti volerla considerare "mancante di info scadenza" o calcolarla qui.
            // Per ora, ci basiamo su expiration_date presente nel record.

            if ($issueFoundForThisVisit) {
                $profilesWithHealthRecordIssuesIds[$profile->id] = true; // Segna il profilo
                break; // Basta una criticità per visita per questo profilo
            }
        }
    }
    $profilesWithHealthRecordIssuesCount = count($profilesWithHealthRecordIssuesIds);

    // --- FINE LOGICA CONTEGGI ---

    return view('dashboard', compact(
        'profilesWithCourseIssuesCount',
        'profilesWithHealthRecordIssuesCount'
        // Rimuovi 'profilesWithExpiringCoursesCount' e 'profilesWithExpiringHealthRecordsCount' se non più usati con questi nomi specifici
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

// ... (resto del file routes/portal.php, inclusa la definizione delle rotte per le viste filtrate)
// Assicurati che le rotte linkate dalle card della dashboard (es. 'profiles.expiring.courses')
// puntino a metodi del controller che implementano la logica di recupero dei profili basata su queste nuove definizioni di "criticità".
// Potrebbe essere necessario rinominare quelle rotte/metodi per maggiore chiarezza, es. 'profiles.attention.courses_issues'.

// Rotte per la gestione del profilo utente autenticato (Breeze)
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
        Route::get('/create', [AnagraficaController::class, 'create'])->name('create');
        Route::post('/', [AnagraficaController::class, 'store'])->name('store');
        Route::get('/{profile}', [AnagraficaController::class, 'show'])->name('show');
        Route::get('/{profile}/edit', [AnagraficaController::class, 'edit'])->name('edit');
        Route::put('/{profile}', [AnagraficaController::class, 'update'])->name('update');
        Route::delete('/{profile}', [AnagraficaController::class, 'destroy'])->name('destroy');
        Route::get('/{profile}/export-pdf', [AnagraficaController::class, 'exportPdf'])->name('export.pdf');


        Route::get('/{profile}/employment/create', [AnagraficaController::class, 'createEmploymentPeriodForm'])->name('employment.create.form');
        Route::post('/{profile}/employment', [AnagraficaController::class, 'storeEmploymentPeriod'])->name('employment.store');
        
        Route::get('/{profile}/section-assignment/edit', [AnagraficaController::class, 'editSectionAssignmentForm'])->name('section_assignment.edit.form');
        Route::put('/{profile}/section-assignment', [AnagraficaController::class, 'updateSectionAssignment'])->name('section_assignment.update');

        Route::get('/{profile}/transfer-out/create', [AnagraficaController::class, 'createTransferOutForm'])->name('transfer_out.create.form');
        Route::post('/{profile}/transfer-out', [AnagraficaController::class, 'storeTransferOut'])->name('transfer_out.store');

        Route::get('/{profile}/edit-ppes', [AnagraficaController::class, 'editPpes'])->name('editPpes');
        Route::put('/{profile}/update-ppes', [AnagraficaController::class, 'updatePpes'])->name('updatePpes');
        
        Route::get('/{profile}/activities/edit', [AnagraficaController::class, 'editActivities'])->name('activities.edit');
        Route::put('/{profile}/activities', [AnagraficaController::class, 'updateActivities'])->name('activities.update');
        
        Route::get('/{profile}/health-check-records/create', [HealthCheckRecordController::class, 'create'])->name('health-check-records.create');
        Route::post('/{profile}/health-check-records', [HealthCheckRecordController::class, 'store'])->name('health-check-records.store');

        Route::get('/{profile}/course-attendances/create', [ProfileSafetyCourseController::class, 'create'])->name('course_attendances.create');
        Route::post('/{profile}/course-attendances', [ProfileSafetyCourseController::class, 'store'])->name('course_attendances.store');

        // ROTTE PER VISTE FILTRATE DALLA DASHBOARD (Criticità)
        // Potresti rinominarle per chiarezza, es. profiles.issues.courses
        Route::get('/expiring/courses', [AnagraficaController::class, 'indexWithCourseIssues'])->name('expiring.courses'); // Mantenuto nome per ora
        Route::get('/expiring/health-surveillances', [AnagraficaController::class, 'indexWithHealthRecordIssues'])->name('expiring.health_surveillances'); // Mantenuto nome per ora
    });

    Route::resource('health-check-records', HealthCheckRecordController::class)
        ->except(['index', 'create', 'store', 'show'])
        ->parameters(['health-check-records' => 'record']);

    Route::resource('course-attendances', ProfileSafetyCourseController::class)
        ->except(['index', 'create', 'store', 'show'])
        ->parameters(['course-attendances' => 'attendance']);

    Route::resource('offices', OfficeController::class);
    Route::get('/offices/{office}/profiles', [OfficeController::class, 'showProfiles'])->name('offices.showProfiles');

    Route::resource('sections', SectionController::class);
    Route::get('/sections/{section}/profiles', [SectionController::class, 'showProfiles'])->name('sections.showProfiles');

    Route::resource('ppes', PPEController::class);
    Route::get('/ppes/{ppe}/profiles', [PPEController::class, 'showProfiles'])->name('ppes.showProfiles');
    Route::get('/ppes/{ppe}/profiles-attention', [PPEController::class, 'showProfilesWithAttention'])->name('ppes.showProfilesWithAttention');

    Route::resource('safety_courses', SafetyCourseController::class);
    Route::get('/safety_courses/{safety_course}/profiles', [SafetyCourseController::class, 'showProfiles'])->name('safety_courses.showProfiles');
    Route::get('/safety_courses/{safety_course}/profiles-attention', [SafetyCourseController::class, 'showProfilesWithAttention'])->name('safety_courses.showProfilesWithAttention');

    Route::resource('activities', ActivityController::class);
    Route::get('/activities/{activity}/profiles', [ActivityController::class, 'showProfiles'])->name('activities.showProfiles');

    Route::resource('health_surveillances', HealthSurveillanceController::class);
    Route::get('health-surveillances/data', [HealthSurveillanceController::class, 'data'])->name('health_surveillances.data');
    Route::get('/health_surveillances/{health_surveillance}/profiles', [HealthSurveillanceController::class, 'showProfiles'])->name('health_surveillances.showProfiles');
    Route::get('/health_surveillances/{health_surveillance}/profiles-attention', [HealthSurveillanceController::class, 'showProfilesWithAttention'])->name('health_surveillances.showProfilesWithAttention');

    Route::resource('risks', RiskController::class);
    Route::get('/risks/{risk}/profiles', [RiskController::class, 'showProfiles'])->name('risks.showProfiles');

    Route::name('admin.profiles.')->prefix('admin/profiles')->group(function () {
        Route::get('/archived', [AnagraficaController::class, 'archivedIndex'])->name('archived_index');
        Route::get('/archived/data', [AnagraficaController::class, 'archivedData'])->name('archived_data');
        Route::post('/{id}/restore', [AnagraficaController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [AnagraficaController::class, 'forceDelete'])->name('forceDelete');
    });
});

Route::middleware(['auth', 'role:Amministratore'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/edit-permissions', [RoleController::class, 'editPermissions'])->name('roles.editPermissions');
    Route::put('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.syncPermissions');
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']);
});
