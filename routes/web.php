<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');

require __DIR__.'/portal.php';
require __DIR__.'/auth.php';
