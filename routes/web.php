<?php

use App\Http\Controllers\PublicStorageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Works on shared hosting without symlinks (Hostinger-safe).
Route::get('/storage/{path}', PublicStorageController::class)
    ->where('path', '.*')
    ->name('storage.public');
