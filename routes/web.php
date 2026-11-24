<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\PublicacionController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\DocenteConsultaController;
use Illuminate\Support\Facades\Route;

// ================= RUTAS PÚBLICAS (FAMILIAS Y COMUNIDAD) =================
Route::get('/', [PortalController::class, 'index'])->name('portal.index');
Route::get('/noticia/{id}', [PortalController::class, 'showNoticia'])->name('portal.noticia');
Route::get('/api/eventos-publicos', [PortalController::class, 'eventosFeed'])->name('portal.eventos.feed');

Route::prefix('portal/chat')->name('portal.chat.')->group(function () {
    Route::post('/preguntar', [ChatBotController::class, 'preguntar'])->name('preguntar');
    Route::post('/derivar', [ChatBotController::class, 'derivar'])->name('derivar');
    Route::get('/seguimiento/{codigo}', [ChatBotController::class, 'seguimiento'])->name('seguimiento');
    Route::post('/seguimiento/{codigo}/responder', [ChatBotController::class, 'responderFamilia'])->name('responder');
});

// ================= AUTENTICACIÓN =================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

// ================= PANEL ADMINISTRATIVO Y DOCENTE =================
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // CRUD Completo de Usuarios
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

    // Bandeja de Consultas en Tiempo Real (Docente / Admin)
    Route::prefix('consultas')->name('panel.consultas.')->group(function () {
        Route::get('/', [DocenteConsultaController::class, 'index'])->name('index');
        Route::get('/feed', [DocenteConsultaController::class, 'feed'])->name('feed');
        Route::get('/{consulta}/mensajes', [DocenteConsultaController::class, 'mensajes'])->name('mensajes');
        Route::post('/{consulta}/responder', [DocenteConsultaController::class, 'responder'])->name('responder');
        Route::patch('/{consulta}/derivar', [DocenteConsultaController::class, 'derivar'])->name('derivar');
        Route::patch('/{consulta}/cerrar', [DocenteConsultaController::class, 'cerrar'])->name('cerrar');
    });

    // Notificaciones y eventos en tiempo real
    Route::post('/notificaciones/{id}/leer', function ($id) {
        $notificacion = auth()->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();
        return response()->json(['ok' => true]);
    })->name('admin.notificaciones.leer');

    Route::post('/notificaciones/leer-todas', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('admin.notificaciones.leerTodas');
});