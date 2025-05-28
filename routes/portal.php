<?php

use App\Http\Controllers\AnagraficaController;
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
    });
});
