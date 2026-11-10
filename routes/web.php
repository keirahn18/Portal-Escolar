<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Redirección principal al Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas Públicas (Solo para usuarios no autenticados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

// Rutas Protegidas (Panel Administrativo)
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});