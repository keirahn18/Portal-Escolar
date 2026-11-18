<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

    <!-- Navbar Reutilizable -->
    @include('layouts.nav')

    <!-- Contenido Principal del Dashboard -->
    <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        
        @if (session('status'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r-xl text-sm text-emerald-800 shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Banner de Bienvenida -->
        <div class="bg-gradient-to-r from-sky-600 via-sky-700 to-blue-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 space-y-2">
                <span class="inline-block bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold tracking-wide">
                    Panel Principal
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    ¡Bienvenida, {{ auth()->user()->nombre }}!
                </h2>
                <p class="text-sky-100 text-sm max-w-xl leading-relaxed">
                    Sistema Integrado de Gestión Escolar y Administración para la comunidad de la E.P.E.P. N° 436 "Simón Bolívar".
                </p>
            </div>
        </div>

        <!-- Módulos Rápidos de Administración -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Tarjeta 1: Gestión de Personal -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="h-10 w-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-lg">
                        👤
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Gestión de Personal</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Alta, edición, roles y administración general de directivos y docentes.
                    </p>
                </div>
                
                <a href="{{ route('admin.usuarios.index') }}" 
                   class="inline-flex items-center justify-center w-full bg-slate-50 hover:bg-sky-50 text-sky-700 font-semibold py-2.5 rounded-xl border border-slate-200 hover:border-sky-200 text-xs transition">
                    Ir a Gestión de Usuarios →
                </a>
            </div>

            <!-- Tarjeta 2: Alta Rápida de Docente -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
                        ➕
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Nuevo Usuario</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Registrar una nueva cuenta docente bajo el formato obligatorio <code>esc-DNI@gmail.com</code>.
                    </p>
                </div>
                
                <a href="{{ route('admin.usuarios.create') }}" 
                   class="inline-flex items-center justify-center w-full bg-slate-50 hover:bg-emerald-50 text-emerald-700 font-semibold py-2.5 rounded-xl border border-slate-200 hover:border-emerald-200 text-xs transition">
                    + Registrar Personal →
                </a>
            </div>

            <!-- Tarjeta 3: Consultas de Familias -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="h-10 w-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
                        💬
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Consultas de Familias</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Atender chats en tiempo real con código de seguimiento y derivación entre docentes.
                    </p>
                </div>
                
                <a href="{{ route('panel.consultas.index') }}" 
                   class="inline-flex items-center justify-center w-full bg-slate-50 hover:bg-amber-50 text-amber-700 font-semibold py-2.5 rounded-xl border border-slate-200 hover:border-amber-200 text-xs transition">
                    Ir a Consultas →
                </a>
            </div>

        </div>

    </main>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

</body>
</html>