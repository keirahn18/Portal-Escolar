<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Institucional - E.P.E.P. N° 436 Simón Bolívar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Animaciones personalizadas mediante Keyframes -->
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-12px) scale(1.03); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.25; }
            50% { opacity: 0.4; }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-float-delayed {
            animation: float 8s ease-in-out 2s infinite;
        }
        .animate-glow {
            animation: pulseGlow 4s ease-in-out infinite;
        }
        .animate-fade-in {
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-900 min-h-full flex flex-col justify-between font-sans antialiased relative overflow-y-auto p-4 sm:p-6 md:p-8">

    <!-- Esferas de luz animadas en el fondo -->
    <div class="fixed top-1/4 -left-20 w-80 h-80 bg-sky-500 rounded-full blur-3xl opacity-30 animate-float animate-glow pointer-events-none"></div>
    <div class="fixed bottom-1/4 -right-20 w-96 h-96 bg-emerald-500 rounded-full blur-3xl opacity-20 animate-float-delayed animate-glow pointer-events-none"></div>
    <div class="fixed top-10 right-1/3 w-64 h-64 bg-amber-600 rounded-full blur-3xl opacity-15 animate-float pointer-events-none"></div>

    <!-- Contenedor Centrado Responsivo -->
    <main class="flex-grow flex items-center justify-center relative z-10 my-auto py-4">
        <div class="max-w-4xl w-full bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 border border-slate-200/50 animate-fade-in transition-all duration-300">
            
            <!-- Lateral Izquierdo: Identidad Institucional y Escudo -->
            <div class="bg-gradient-to-br from-sky-600 via-sky-700 to-blue-900 p-6 sm:p-8 text-white flex flex-col justify-between items-center text-center relative overflow-hidden min-h-[280px] sm:min-h-[360px]">
                <!-- Destellos decorativos internos -->
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-white/10 via-transparent to-transparent"></div>

                <div class="relative z-10 my-auto space-y-4">
                    
                    <!-- Tarjeta del Logo con animación Hover -->
                    <div class="bg-white/95 backdrop-blur p-4 sm:p-5 rounded-3xl shadow-xl inline-block transform hover:scale-105 transition-all duration-300 border border-sky-100 group">
                        <img src="{{ asset('images/logo.jpeg') }}" 
                             alt="Escudo E.P.E.P. N° 436 Simón Bolívar" 
                             class="h-28 sm:h-36 mx-auto object-contain transition-transform duration-500 group-hover:rotate-1">
                    </div>

                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">E.P.E.P. N° 436</h2>
                        <p class="text-sky-200 font-medium text-base sm:text-lg mt-0.5">"Simón Bolívar"</p>
                    </div>

                    <div class="w-16 h-1 bg-amber-500 mx-auto rounded-full shadow-sm"></div>

                    <p class="text-xs text-sky-100 max-w-xs mx-auto leading-relaxed">
                        Sistema Integrado de Gestión Escolar y Comunicación Institucional.
                    </p>
                </div>

                <div class="relative z-10 text-xs text-sky-200 font-medium tracking-wide pt-2">
                    Formosa, Argentina
                </div>
            </div>

            <!-- Lateral Derecho: Formulario de Inicio de Sesión -->
            <div class="p-6 sm:p-10 md:p-12 flex flex-col justify-center space-y-6 bg-white">
                
                <div>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-800">Acceso al Sistema</h3>
                    <p class="text-sm text-slate-500 mt-1">Ingresá tus credenciales institucionales</p>
                </div>

                <!-- Alertas de Errores -->
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r-xl text-sm text-red-700 animate-fade-in shadow-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST" class="space-y-4 sm:space-y-5">
                    @csrf

                    <!-- Campo Correo -->
                    <div class="space-y-1 group">
                        <label for="email" class="block text-sm font-semibold text-slate-700 group-focus-within:text-sky-600 transition-colors duration-200">
                            Correo Electrónico
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            placeholder="usuario@epep436.edu.ar"
                            class="w-full px-4 py-2.5 sm:py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 text-slate-800 placeholder-slate-400 bg-slate-50/50 focus:bg-white shadow-sm">
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="space-y-1 group">
                        <label for="password" class="block text-sm font-semibold text-slate-700 group-focus-within:text-sky-600 transition-colors duration-200">
                            Contraseña
                        </label>
                        <input type="password" name="password" id="password" required placeholder="••••••••"
                            class="w-full px-4 py-2.5 sm:py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all duration-200 text-slate-800 placeholder-slate-400 bg-slate-50/50 focus:bg-white shadow-sm">
                    </div>

                    <!-- Checkbox Recordar Sesión -->
                    <div class="flex items-center justify-between text-sm pt-1">
                        <label class="flex items-center text-slate-600 cursor-pointer group">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 transition duration-150">
                            <span class="ml-2 group-hover:text-slate-800 transition-colors">Recordar mi sesión</span>
                        </label>
                    </div>

                    <!-- Botón de Ingreso Animado -->
                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 active:scale-[0.98] text-white font-semibold py-3 sm:py-3.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:shadow-sky-500/25 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                        Iniciar Sesión
                    </button>
                </form>

                <div class="text-center pt-2">
                    <span class="text-xs text-slate-400 font-medium">Exclusivo para Directivos y Docentes</span>
                </div>

            </div>

        </div>
    </main>

    <footer class="relative z-10 text-center py-2 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

</body>
</html>