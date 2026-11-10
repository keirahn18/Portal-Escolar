<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

   @include('layouts.nav')

    <main class="flex-grow flex items-center justify-center p-4 sm:p-6 md:p-8">
        <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl border border-slate-200/80 p-6 sm:p-8 space-y-6 my-auto">
            
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Modificar Datos de Usuario</h2>
                <p class="text-sm text-slate-500 mt-1">Usuario: {{ $usuario->nombre }} {{ $usuario->apellido }}</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r-xl text-sm text-red-700 shadow-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.usuarios.update', $usuario->id_usuario) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $usuario->nombre) }}" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                    </div>

                    <div>
                        <label for="apellido" class="block text-sm font-semibold text-slate-700 mb-1">Apellido</label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $usuario->apellido) }}" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Correo (esc-DNI@gmail.com)</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" id="password" placeholder="Dejar en blanco para mantener la actual"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label for="fk_id_rol" class="block text-sm font-semibold text-slate-700 mb-1">Rol</label>
                    <select name="fk_id_rol" id="fk_id_rol" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none cursor-pointer">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ old('fk_id_rol', $usuario->fk_id_rol) == $rol->id_rol ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                        class="w-full bg-sky-600 hover:bg-sky-700 active:scale-[0.99] text-white font-semibold py-3 rounded-xl transition duration-200 shadow-md">
                        Guardar Cambios
                    </button>
                </div>
            </form>

        </div>
    </main>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

</body>
</html>