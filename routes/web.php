<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PublicacionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EventoController;

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

    // CRUD Completo de Noticias y Galería de Imágenes
    Route::get('/noticias', [PublicacionController::class, 'index'])->name('admin.publicaciones.index');
    Route::get('/noticias/crear', [PublicacionController::class, 'create'])->name('admin.publicaciones.create');
    Route::get('/noticias/{id}', [PublicacionController::class, 'show'])->name('admin.publicaciones.show');
    Route::post('/noticias', [PublicacionController::class, 'store'])->name('admin.publicaciones.store');
    Route::get('/noticias/{id}/editar', [PublicacionController::class, 'edit'])->name('admin.publicaciones.edit');
    Route::put('/noticias/{id}', [PublicacionController::class, 'update'])->name('admin.publicaciones.update');
    Route::delete('/noticias/{id}', [PublicacionController::class, 'destroy'])->name('admin.publicaciones.destroy');
    Route::delete('/noticias/imagen/{idImagen}', [PublicacionController::class, 'destroyImagen'])->name('admin.publicaciones.imagen.destroy');
    // Calendario y CRUD de Eventos
    Route::get('/eventos', [EventoController::class, 'index'])->name('admin.eventos.index');
    Route::get('/eventos/feed', [EventoController::class, 'feed'])->name('admin.eventos.feed');
    Route::post('/eventos', [EventoController::class, 'store'])->name('admin.eventos.store');
    Route::put('/eventos/{id}', [EventoController::class, 'update'])->name('admin.eventos.update');
    Route::delete('/eventos/{id}', [EventoController::class, 'destroy'])->name('admin.eventos.destroy');
});
