<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController;       // Namespace corretto
use App\Http\Controllers\Admin\PermissionController;  // Namespace corretto
use App\Http\Controllers\AnagraficaController;
use App\Http\Controllers\HealthCheckRecordController;
use App\Http\Controllers\HealthSurveillanceController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PPEController;
use App\Http\Controllers\ProfileController; // Controller di Breeze per /profile
use App\Http\Controllers\ProfileSafetyCourseController;
use App\Http\Controllers\SafetyCourseController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rotte per la gestione del profilo utente autenticato (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Gruppo principale di rotte dell'applicazione
Route::middleware(['auth'])->group(function () {
    // Gruppo per Anagrafiche (Profili applicativi)
    Route::name('profiles.')->prefix('profiles')->group(function () {
        Route::get('/', [AnagraficaController::class, 'index'])->name('index');
        Route::get('/data', [AnagraficaController::class, 'data'])->name('data');
        Route::get('/create', [AnagraficaController::class, 'create'])->name('create');
        Route::post('/', [AnagraficaController::class, 'store'])->name('store');
        Route::get('/{profile}', [AnagraficaController::class, 'show'])->name('show');
        Route::get('/{profile}/edit', [AnagraficaController::class, 'edit'])->name('edit');
        Route::put('/{profile}', [AnagraficaController::class, 'update'])->name('update');
        Route::delete('/{profile}', [AnagraficaController::class, 'destroy'])->name('destroy');
        Route::get('/{profile}/edit-ppes', [AnagraficaController::class, 'editPpes'])->name('editPpes');
        Route::put('/{profile}/update-ppes', [AnagraficaController::class, 'updatePpes'])->name('updatePpes');
    });

    // Rotte per HealthCheckRecords nidificate sotto Profiles (per la creazione)
    Route::resource('profiles.health-check-records', HealthCheckRecordController::class)
        ->only(['create', 'store'])
        ->parameters(['health-check-records' => 'record']);

    // Rotte per HealthCheckRecords (per edit, update, destroy)
    Route::resource('health-check-records', HealthCheckRecordController::class)
        ->except(['index', 'create', 'store', 'show'])
        ->parameters(['health-check-records' => 'record']);

    // Rotte per la gestione delle frequenze dei corsi di sicurezza per un profilo
    Route::get('/profiles/{profile}/course-attendances/create', [ProfileSafetyCourseController::class, 'create'])
         ->name('profiles.course_attendances.create');
    Route::post('/profiles/{profile}/course-attendances', [ProfileSafetyCourseController::class, 'store'])
         ->name('profiles.course_attendances.store');

    // Rotte per la modifica/eliminazione di una specifica frequenza corso
    Route::get('/course-attendances/{attendance}/edit', [ProfileSafetyCourseController::class, 'edit'])
         ->name('course_attendances.edit');
    Route::put('/course-attendances/{attendance}', [ProfileSafetyCourseController::class, 'update'])
         ->name('course_attendances.update');
    Route::delete('/course-attendances/{attendance}', [ProfileSafetyCourseController::class, 'destroy'])
         ->name('course_attendances.destroy');

    // Risorse Standard
    Route::resource('offices', OfficeController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('ppes', PPEController::class);
    Route::resource('safety_courses', SafetyCourseController::class);
    Route::resource('activities', ActivityController::class);
    Route::resource('health_surveillances', HealthSurveillanceController::class);
    Route::get('health-surveillances/data', [HealthSurveillanceController::class, 'data'])->name('health_surveillances.data');

    // Rotte per visualizzare profili correlati
    Route::get('/sections/{section}/profiles', [SectionController::class, 'showProfiles'])->name('sections.showProfiles');
    Route::get('/offices/{office}/profiles', [OfficeController::class, 'showProfiles'])->name('offices.showProfiles');
    Route::get('/ppes/{ppe}/profiles', [PPEController::class, 'showProfiles'])->name('ppes.showProfiles');
    Route::get('/activities/{activity}/profiles', [ActivityController::class, 'showProfiles'])->name('activities.showProfiles'); // Corretto
    Route::get('/safety_courses/{safety_course}/profiles', [SafetyCourseController::class, 'showProfiles'])->name('safety_courses.showProfiles');
    Route::get('/health_surveillances/{health_surveillance}/profiles', [HealthSurveillanceController::class, 'showProfiles'])->name('health_surveillances.showProfiles');

    
    Route::resource('risks', App\Http\Controllers\RiskController::class);
    Route::get('/risks/{risk}/profiles', [App\Http\Controllers\RiskController::class, 'showProfiles'])->name('risks.showProfiles');
    
    
    });

// Gruppo per Amministrazione (Utenti, Ruoli, Permessi)
Route::middleware(['auth', 'role:Amministratore'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/edit-permissions', [RoleController::class, 'editPermissions'])->name('roles.editPermissions');
    Route::put('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.syncPermissions');
    Route::resource('permissions', PermissionController::class)->only(['index', 'show']);
});
