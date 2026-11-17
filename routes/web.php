<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Frontend actual React/Inertia
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard del starter
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas adicionales de la aplicación
require __DIR__.'/store.php';
require __DIR__.'/account.php';
require __DIR__.'/admin.php';

// Rutas originales del starter
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';