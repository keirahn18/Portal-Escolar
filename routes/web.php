<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

// Redirección raíz a login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación (sin middleware guest para cortar cualquier bucle)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

// Rutas protegidas
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // CRUD Completo de Usuarios (Listar, Crear, Guardar, Editar, Actualizar, Eliminar)
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('admin.usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
    Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('admin.usuarios.edit');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('admin.usuarios.update');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('admin.usuarios.destroy');
});