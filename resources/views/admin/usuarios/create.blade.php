<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Personal - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

    <!-- Navbar -->
   @include('layouts.nav')

    <!-- Formulario Principal -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6 md:p-8">
        <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl border border-slate-200/80 p-6 sm:p-8 space-y-6 my-auto">

            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Alta de Docentes y Directivos</h2>
                <p class="text-sm text-slate-500 mt-1">Ingresá los datos asignados por la dirección para crear la cuenta</p>
            </div>

            <!-- Mostrar Errores de Validación (como usuario repetido o correo no válido) -->
            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r-xl text-sm text-red-700 shadow-sm">
                <p class="font-semibold mb-1">Por favor corregí los siguientes problemas:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.usuarios.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Nombre y Apellido -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required autofocus
                            placeholder="Ej: María"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition text-slate-800 bg-slate-50/50 focus:bg-white">
                    </div>

                    <div>
                        <label for="apellido" class="block text-sm font-semibold text-slate-700 mb-1">Apellido</label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required
                            placeholder="Ej: González"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition text-slate-800 bg-slate-50/50 focus:bg-white">
                    </div>
                </div>

                <!-- Correo Institucional Asignado -->
                <!-- Correo Institucional Asignado -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">
                        Correo Institucional (esc-DNI@gmail.com)
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        placeholder="esc-40123456@gmail.com"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition text-slate-800 bg-slate-50/50 focus:bg-white">
                    <p class="text-xs text-slate-400 mt-1">Formato obligatorio otorgado por dirección: <code>esc-[Número de DNI]@gmail.com</code></p>
                </div>

                <!-- Contraseña Inicial -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Contraseña Inicial</label>
                    <input type="password" name="password" id="password" required placeholder="Mínimo 8 caracteres"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition text-slate-800 bg-slate-50/50 focus:bg-white">
                </div>

                <!-- Selección de Rol -->
                <div>
                    <label for="fk_id_rol" class="block text-sm font-semibold text-slate-700 mb-1">Rol a Asignar</label>
                    <select name="fk_id_rol" id="fk_id_rol" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition text-slate-800 bg-slate-50/50 focus:bg-white cursor-pointer">
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}" {{ old('fk_id_rol') == $rol->id_rol ? 'selected' : '' }}>
                            {{ $rol->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botón de Registro -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-sky-600 hover:bg-sky-700 active:scale-[0.99] text-white font-semibold py-3 rounded-xl transition duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                        Guardar y Registrar Usuario
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