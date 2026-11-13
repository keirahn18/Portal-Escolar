<nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- Marca Institucional y Enlaces Desktop -->
            <div class="flex items-center gap-4 sm:gap-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo E.P.E.P. N° 436" class="h-9 w-auto object-contain group-hover:scale-105 transition">
                    <div>
                        <span class="block text-xs sm:text-sm font-extrabold text-slate-800 leading-none">E.P.E.P. N° 436</span>
                        <span class="text-[10px] sm:text-[11px] text-sky-600 font-medium">Panel Administrativo</span>
                    </div>
                </a>

                <!-- Navegación Escritorio -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        Dashboard
                    </a>
                    
                    <a href="{{ route('admin.usuarios.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-medium transition flex items-center gap-1.5 {{ request()->routeIs('admin.usuarios.index') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <span>Gestión de Personal</span>
                    </a>

                    <a href="{{ route('admin.usuarios.create') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-medium transition flex items-center gap-1 {{ request()->routeIs('admin.usuarios.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <span class="text-emerald-600 font-bold">+</span>
                        <span>Nuevo Usuario</span>
                    </a>
                    <a href="{{ route('admin.publicaciones.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-medium transition flex items-center gap-1 {{ request()->routeIs('admin.noticias.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <span class="text-emerald-600 font-bold">+</span>
                        <span>Noticias</span>
                    </a>
                </div>
            </div>

            <!-- Datos de Usuario y Botón Hamburguesa Móvil -->
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden sm:flex flex-col text-right">
                    <span class="text-xs font-bold text-slate-800">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</span>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">{{ auth()->user()->rol->nombre }}</span>
                </div>

                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                <!-- Botón de Cerrar Sesión (Escritorio) -->
                <form action="{{ route('logout') }}" method="POST" class="hidden sm:inline">
                    @csrf
                    <button type="submit" title="Cerrar Sesión" 
                        class="bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 px-3 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1 border border-slate-200/80 hover:border-red-200">
                        <span>Cerrar Sesión</span>
                    </button>
                </form>

                <!-- Botón Hamburguesa (Móvil) -->
                <div class="flex items-center md:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                        class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none transition border border-slate-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Menú Desplegable Móvil -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-4 space-y-2 shadow-lg">
        <div class="pb-2 mb-2 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-slate-800">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</span>
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">{{ auth()->user()->rol->nombre }}</span>
            </div>
        </div>

        <a href="{{ route('admin.dashboard') }}" 
           class="block px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
            📌 Dashboard
        </a>
        
        <a href="{{ route('admin.usuarios.index') }}" 
           class="block px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.usuarios.index') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
            👥 Gestión de Personal
        </a>

        <a href="{{ route('admin.usuarios.create') }}" 
           class="block px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.usuarios.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
            ➕ Registrar Nuevo Personal
        </a>
        <a href="{{ route('admin.publicaciones.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-medium transition flex items-center gap-1 {{ request()->routeIs('admin.noticias.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <span class="text-emerald-600 font-bold">+</span>
                        <span>Noticias</span>
                    </a>

        <div class="pt-2 border-t border-slate-100">
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" 
                    class="w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 transition flex items-center justify-between">
                    <span>Cerrar Sesión</span>
                    <span>→</span>
                </button>
            </form>
        </div>
    </div>
</nav>