<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

    <!-- Navbar Reutilizable -->
    @include('layouts.nav')

    <!-- Contenido Principal -->
    <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        
        <!-- Cabecera de Sección con Botón de Acción Principal -->
        <div class="flex flex-wrap justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Personal Registrado</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Administración de cuentas para directivos y docentes institucionales</p>
            </div>
            
            <a href="{{ route('admin.usuarios.create') }}" 
               class="bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition duration-200 shadow-md hover:shadow-lg hover:shadow-sky-500/20 active:scale-[0.98] flex items-center gap-2">
                <span>+ Registrar Nuevo Personal</span>
            </a>
        </div>

        <!-- Alerta de Estado -->
        @if (session('status'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r-xl text-sm text-emerald-800 shadow-sm flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r-xl text-sm text-red-700 shadow-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Tabla de Usuarios de Alto Rendimiento -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 border-collapse">
                    <thead class="bg-slate-50/80 text-slate-700 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-4 px-6">Usuario / Personal</th>
                            <th class="py-4 px-6">Correo Institucional (esc-DNI@gmail.com)</th>
                            <th class="py-4 px-6">Rol Asignado</th>
                            <th class="py-4 px-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80">
                        @forelse ($usuarios as $user)
                            <tr class="hover:bg-sky-50/40 transition duration-150">
                                <!-- Nombre y Avatar Generado -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-sky-700 text-xs">
                                            {{ strtoupper(substr($user->nombre, 0, 1)) }}{{ strtoupper(substr($user->apellido, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block font-semibold text-slate-900">{{ $user->nombre }} {{ $user->apellido }}</span>
                                            <span class="text-[11px] text-slate-400">ID: #{{ $user->id_usuario }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Correo -->
                                <td class="py-4 px-6 font-mono text-xs text-slate-600">
                                    {{ $user->email }}
                                </td>

                                <!-- Badge de Rol -->
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold shadow-xs {{ $user->rol->nombre === 'Administrador' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-sky-100 text-sky-800 border border-sky-200' }}">
                                        {{ $user->rol->nombre }}
                                    </span>
                                </td>

                                <!-- Botones de Acción -->
                                <td class="py-4 px-6 text-right space-x-2">
                                    <a href="{{ route('admin.usuarios.edit', $user->id_usuario) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-sky-600 hover:text-white text-slate-700 text-xs font-semibold rounded-lg transition duration-150 border border-slate-200">
                                        Editar
                                    </a>

                                    @if(auth()->id() !== $user->id_usuario)
                                        <form action="{{ route('admin.usuarios.destroy', $user->id_usuario) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Confirmás la eliminación del usuario {{ $user->nombre }} {{ $user->apellido }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-600 hover:text-white text-red-600 text-xs font-semibold rounded-lg transition duration-150 border border-red-200">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-slate-400">
                                    <p class="text-base font-semibold">No se encontraron usuarios registrados</p>
                                    <p class="text-xs mt-1">Hacé clic en "+ Registrar Nuevo Personal" para dar de alta cuentas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

</body>
</html>