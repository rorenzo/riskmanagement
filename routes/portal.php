<?php

use App\Http\Controllers\AnagraficaController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SafetyCourseController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\PPEController;
use App\Http\Controllers\HealthSurveillanceController;


use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

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
});

Route::middleware(['auth'])->resource('offices', OfficeController::class);
Route::middleware(['auth'])->resource('sections', SectionController::class);
 
Route::middleware(['auth'])->resource('ppes', PPEController::class);
Route::middleware(['auth'])->resource('safety_courses', SafetyCourseController::class);
Route::middleware(['auth'])->resource('activities', ActivityController::class);
Route::middleware(['auth'])->resource('health_surveillances', HealthSurveillanceController::class);
Route::get('health-surveillances/data', [HealthSurveillanceController::class, 'data'])->name('health_surveillances.data');

Route::get('/sections/{section}/profiles', [SectionController::class, 'showProfiles']) // <-- NUOVA ROTTA
     ->name('sections.showProfiles'); 
Route::get('/offices/{office}/profiles', [OfficeController::class, 'showProfiles']) // <-- NUOVA ROTTA
     ->name('offices.showProfiles'); 
Route::get('/ppes/{ppe}/profiles', [PPEController::class, 'showProfiles'])->name('ppes.showProfiles');
Route::get('/actyvities/{activity}/profiles', [ActivityController::class, 'showProfiles'])->name('activity.showProfiles');
