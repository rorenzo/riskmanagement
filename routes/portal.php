<?php

use App\Http\Controllers\AnagraficaController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SafetyCourseController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\PPEController;
use App\Http\Controllers\HealthSurveillanceController;
use App\Http\Controllers\HealthCheckRecordController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\ProfileSafetyCourseController;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Route::middleware('auth')->group(function () {
//    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//});

// Gruppo di rotte per la gestione dei Profili (Anagrafiche)
Route::middleware(['auth'])->group(function () {
    Route::name('profiles.')->prefix('profiles')->group(function () {
        // Usa il controller rinominato per le anagrafiche
        Route::get('/', [AnagraficaController::class, 'index'])->name('index');
        Route::get('/data', [AnagraficaController::class, 'data'])->name('data'); // <-- NUOVA ROTTA PER DATATABLES
        Route::get('/create', [AnagraficaController::class, 'create'])->name('create');
        Route::post('/', [AnagraficaController::class, 'store'])->name('store');
        Route::get('/{profile}', [AnagraficaController::class, 'show'])->name('show'); // {profile} qui è il nome del parametro, non del modello
        Route::get('/{profile}/edit', [AnagraficaController::class, 'edit'])->name('edit');
        Route::put('/{profile}', [AnagraficaController::class, 'update'])->name('update');
        Route::delete('/{profile}', [AnagraficaController::class, 'destroy'])->name('destroy');
        Route::get('/{profile}/edit-ppes', [AnagraficaController::class, 'editPpes'])->name('editPpes');
        Route::put('/{profile}/update-ppes', [AnagraficaController::class, 'updatePpes'])->name('updatePpes');
    });

    // Rotte per HealthCheckRecords nidificate sotto Profiles
    Route::resource('profiles.health-check-records', HealthCheckRecordController::class)
            ->only(['create', 'store']) // Per ora implementiamo create e store
            ->parameters(['health-check-records' => 'record']); // Cambia il nome del parametro se necessario
    // Rotte singole per edit, update, destroy di HealthCheckRecord (non nidificate se si accede tramite ID del record)
    Route::resource('health-check-records', HealthCheckRecordController::class)
            ->except(['index', 'create', 'store', 'show']) // 'show' potrebbe essere utile
            ->parameters(['health-check-records' => 'record']);

    // Se vuoi una pagina show per il singolo record, la puoi aggiungere:
    // Route::get('health-check-records/{record}', [HealthCheckRecordController::class, 'show'])->name('health-check-records.show');



    Route::resource('offices', OfficeController::class);
    Route::resource('sections', SectionController::class);

    Route::resource('ppes', PPEController::class);
    Route::resource('safety_courses', SafetyCourseController::class);
    Route::resource('activities', ActivityController::class);
    Route::resource('health_surveillances', HealthSurveillanceController::class);
    Route::get('health-surveillances/data', [HealthSurveillanceController::class, 'data'])->name('health_surveillances.data');

    Route::get('/sections/{section}/profiles', [SectionController::class, 'showProfiles']) // <-- NUOVA ROTTA
            ->name('sections.showProfiles');
    Route::get('/offices/{office}/profiles', [OfficeController::class, 'showProfiles']) // <-- NUOVA ROTTA
            ->name('offices.showProfiles');
    Route::get('/ppes/{ppe}/profiles', [PPEController::class, 'showProfiles'])->name('ppes.showProfiles');
    Route::get('/actyvities/{activity}/profiles', [ActivityController::class, 'showProfiles'])->name('activity.showProfiles');
    Route::get('/safety_courses/{safety_course}/profiles', [SafetyCourseController::class, 'showProfiles'])->name('safety_courses.showProfiles');
    Route::get('/health_surveillances/{health_surveillance}/profiles', [HealthSurveillanceController::class, 'showProfiles'])->name('health_surveillances.showProfiles');
    
    
    // Rotte per la gestione delle frequenze dei corsi di sicurezza per un profilo
    Route::get('/profiles/{profile}/course-attendances/create', [ProfileSafetyCourseController::class, 'create'])
         ->name('profiles.course_attendances.create');
    Route::post('/profiles/{profile}/course-attendances', [ProfileSafetyCourseController::class, 'store'])
         ->name('profiles.course_attendances.store');

    // Usiamo il nome del parametro 'attendance' per il record della tabella pivot ProfileSafetyCourse
    Route::get('/course-attendances/{attendance}/edit', [ProfileSafetyCourseController::class, 'edit'])
         ->name('course_attendances.edit');
    Route::put('/course-attendances/{attendance}', [ProfileSafetyCourseController::class, 'update'])
         ->name('course_attendances.update');
    Route::delete('/course-attendances/{attendance}', [ProfileSafetyCourseController::class, 'destroy'])
         ->name('course_attendances.destroy');
});

