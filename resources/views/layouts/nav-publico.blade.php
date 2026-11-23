<nav class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-18 items-center">
            
            <!-- Logo e Identidad Institucional -->
            <a href="{{ route('portal.index') }}" class="flex items-center gap-3.5 group">
                <img src="{{ asset('images/logo.png') }}" alt="Escudo E.P.E.P. N° 436" class="h-11 w-auto object-contain group-hover:scale-105 transition duration-200">
                <div>
                    <span class="block text-sm sm:text-base font-extrabold text-slate-900 tracking-tight leading-tight">
                        E.P.E.P. N° 436 "Simón Bolívar"
                    </span>
                    <span class="block text-[11px] sm:text-xs text-sky-700 font-semibold">
                        Portal Institucional • Formosa Capital
                    </span>
                </div>
            </a>

            <!-- Enlaces Desktop -->
            <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                <a href="{{ route('portal.index') }}#inicio" class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-600 hover:text-sky-700 hover:bg-sky-50/70 transition">
                    Inicio
                </a>
                <a href="{{ route('portal.index') }}#institucional" class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-600 hover:text-sky-700 hover:bg-sky-50/70 transition">
                    Nuestra Escuela
                </a>
                <a href="{{ route('portal.index') }}#noticias" class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-600 hover:text-sky-700 hover:bg-sky-50/70 transition">
                    Noticias
                </a>
                <a href="{{ route('portal.index') }}#calendario" class="px-3.5 py-2 rounded-xl text-sm font-medium text-slate-600 hover:text-sky-700 hover:bg-sky-50/70 transition">
                    Calendario Escolar
                </a>

                <div class="h-6 w-px bg-slate-200 mx-2"></div>

                @auth
                    <a href="{{ route('admin.dashboard') }}" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                        Ir al Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-slate-900 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition">
                        Acceso Personal
                    </a>
                @endauth
            </div>

            <!-- Botón Menú Móvil -->
            <div class="flex items-center md:hidden">
                <button type="button" onclick="document.getElementById('public-mobile-menu').classList.toggle('hidden')"
                    class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 border border-slate-200">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú Móvil -->
    <div id="public-mobile-menu" class="hidden md:hidden bg-white border-t border-slate-200 px-4 pt-3 pb-5 space-y-2 shadow-lg">
        <a href="{{ route('portal.index') }}#inicio" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-sky-50">
            Inicio
        </a>
        <a href="{{ route('portal.index') }}#institucional" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-sky-50">
            Nuestra Escuela
        </a>
        <a href="{{ route('portal.index') }}#noticias" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-sky-50">
            Noticias y Comunicados
        </a>
        <a href="{{ route('portal.index') }}#calendario" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-sky-50">
            Calendario de Eventos
        </a>
        <div class="pt-2 border-t border-slate-100">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="block text-center bg-sky-600 text-white text-sm font-bold py-2.5 rounded-xl">
                    Ir al Panel Administrativo
                </a>
            @else
                <a href="{{ route('login') }}" class="block text-center bg-slate-900 text-white text-sm font-semibold py-2.5 rounded-xl">
                    Acceso Docentes / Directivos
                </a>
            @endauth
        </div>
    </div>
</nav>