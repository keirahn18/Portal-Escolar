@php
    $noLeidas = auth()->user()->unreadNotifications;
    $todasNotificaciones = auth()->user()->notifications()->take(12)->get();
@endphp

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
                    
                    @if(auth()->user()->esAdmin())
                        <a href="{{ route('admin.usuarios.index') }}" 
                           class="px-3 py-2 rounded-xl text-sm font-medium transition flex items-center gap-1.5 {{ request()->routeIs('admin.usuarios.index') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <span>Gestión de Personal</span>
                        </a>

                        <a href="{{ route('admin.usuarios.create') }}" 
                           class="px-3 py-2 rounded-xl text-sm font-medium transition flex items-center gap-1 {{ request()->routeIs('admin.usuarios.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            <span class="text-emerald-600 font-bold">+</span>
                            <span>Nuevo Usuario</span>
                        </a>
                    @endif

                    <a href="{{ route('admin.publicaciones.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.publicaciones.*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        Noticias
                    </a>

                    <a href="{{ route('admin.eventos.index') }}" 
                       class="px-3 py-2 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.eventos.*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        Eventos
                    </a>
                </div>
            </div>

            <!-- Campanita, Datos de Usuario y Botón Hamburguesa Móvil -->
            <div class="flex items-center gap-2 sm:gap-3">

                <!-- Campanita de Bandeja de Notificaciones -->
                <div class="relative" id="dropdown-notificaciones">
                    <button type="button"
                            onclick="document.getElementById('menu-notificaciones').classList.toggle('hidden')"
                            title="Bandeja de notificaciones"
                            class="relative p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span id="badge-notif-count"
                              class="{{ $noLeidas->count() > 0 ? '' : 'hidden' }} absolute -top-1.5 -right-1.5 bg-red-600 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full leading-none shadow-xs">
                            {{ $noLeidas->count() }}
                        </span>
                    </button>

                    <!-- Desplegable Bandeja de Entrada -->
                    <div id="menu-notificaciones"
                         class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden">
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Bandeja de Entrada</span>
                            @if($noLeidas->count() > 0)
                                <form action="{{ route('admin.notificaciones.leerTodas') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-semibold text-sky-700 hover:underline cursor-pointer">
                                        Marcar todas leídas
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div id="lista-notificaciones" class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                            @forelse($todasNotificaciones as $notif)
                                <a href="{{ $notif->data['url'] ?? '#' }}"
                                   onclick="marcarNotificacionLeida('{{ $notif->id }}')"
                                   class="block p-3.5 hover:bg-slate-50 transition {{ is_null($notif->read_at) ? 'bg-sky-50/70' : '' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-xs text-slate-800 leading-relaxed {{ is_null($notif->read_at) ? 'font-bold' : 'font-normal' }}">
                                            {{ $notif->data['mensaje'] ?? 'Nueva notificación institucional' }}
                                        </p>
                                        @if(is_null($notif->read_at))
                                            <span class="w-2 h-2 rounded-full bg-sky-600 shrink-0 mt-1"></span>
                                        @endif
                                    </div>
                                    @if(!empty($notif->data['lugar']) || !empty($notif->data['hora_inicio']))
                                        <p class="text-[11px] text-slate-500 mt-1">
                                            @if(!empty($notif->data['hora_inicio'])) 🕒 {{ $notif->data['hora_inicio'] }} hs @endif
                                            @if(!empty($notif->data['lugar'])) • 📍 {{ $notif->data['lugar'] }} @endif
                                        </p>
                                    @endif
                                    <span class="block text-[10px] text-slate-400 mt-1">
                                        {{ $notif->created_at->diffForHumans() }}
                                    </span>
                                </a>
                            @empty
                                <p id="notif-vacio" class="p-6 text-center text-xs text-slate-400">
                                    No tenés notificaciones en tu bandeja.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="hidden sm:flex flex-col text-right">
                    <span class="text-xs font-bold text-slate-800">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</span>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">{{ auth()->user()->rol->nombre }}</span>
                </div>

                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                <!-- Botón de Cerrar Sesión (Escritorio) -->
                <form action="{{ route('logout') }}" method="POST" class="hidden sm:inline">
                    @csrf
                    <button type="submit" title="Cerrar Sesión" 
                        class="bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 px-3 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1 border border-slate-200/80 hover:border-red-200 cursor-pointer">
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
        
        @if(auth()->user()->esAdmin())
            <a href="{{ route('admin.usuarios.index') }}" 
               class="block px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.usuarios.index') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                👥 Gestión de Personal
            </a>

            <a href="{{ route('admin.usuarios.create') }}" 
               class="block px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.usuarios.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                ➕ Registrar Nuevo Personal
            </a>
        @endif

        <a href="{{ route('admin.publicaciones.index') }}" 
           class="block px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.publicaciones.*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
            📰 Noticias y Cartelera
        </a>

        <a href="{{ route('admin.eventos.index') }}" 
           class="block px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.eventos.*') ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
            📅 Agenda de Eventos
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

<!-- Toast Flotante para Notificación en Tiempo Real (Docente Encargado) -->
<div id="toast-notificacion" class="hidden fixed bottom-5 right-5 max-w-sm bg-slate-900 text-white p-4 rounded-2xl shadow-2xl border border-slate-700 z-50 space-y-2.5">
    <div class="flex items-start justify-between gap-2">
        <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">📌 Nueva Asignación de Evento</span>
        <button type="button" onclick="document.getElementById('toast-notificacion').classList.add('hidden')" class="text-slate-400 hover:text-white text-base leading-none">&times;</button>
    </div>
    <p id="toast-notificacion-texto" class="text-xs text-slate-200 leading-relaxed"></p>
    <div class="text-right">
        <a href="{{ route('admin.eventos.index') }}" class="inline-block bg-sky-600 hover:bg-sky-500 text-white text-[11px] font-bold px-3.5 py-1.5 rounded-lg transition">
            Ver en Calendario
        </a>
    </div>
</div>

<script>
    const userIdActual = @json(auth()->user()->id_usuario);

    document.addEventListener('DOMContentLoaded', function () {
        // Cerrar bandeja al hacer clic fuera
        document.addEventListener('click', function (e) {
            const contenedor = document.getElementById('dropdown-notificaciones');
            const menu = document.getElementById('menu-notificaciones');
            if (contenedor && menu && !contenedor.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // Escuchar canal privado de notificaciones en tiempo real (Laravel Reverb)
        if (window.Echo && userIdActual) {
            window.Echo.private(`docente.${userIdActual}`)
                .notification((notification) => {
                    // 1. Mostrar Toast flotante en pantalla
                    const toast = document.getElementById('toast-notificacion');
                    document.getElementById('toast-notificacion-texto').textContent = notification.mensaje;
                    toast.classList.remove('hidden');

                    // 2. Actualizar contador rojo de la campanita
                    const badge = document.getElementById('badge-notif-count');
                    const actual = parseInt(badge.textContent || '0', 10) + 1;
                    badge.textContent = actual;
                    badge.classList.remove('hidden');

                    // 3. Insertar arriba de la bandeja de entrada
                    const vacio = document.getElementById('notif-vacio');
                    if (vacio) vacio.remove();

                    const lista = document.getElementById('lista-notificaciones');
                    const detalles = [
                        notification.hora_inicio ? `🕒 ${notification.hora_inicio} hs` : '',
                        notification.lugar ? `📍 ${notification.lugar}` : ''
                    ].filter(Boolean).join(' • ');

                    const item = document.createElement('a');
                    item.href = notification.url || '#';
                    item.onclick = () => marcarNotificacionLeida(notification.id);
                    item.className = 'block p-3.5 bg-sky-50/80 hover:bg-slate-50 transition';
                    item.innerHTML = `
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xs text-slate-800 leading-relaxed font-bold">${notification.mensaje}</p>
                            <span class="w-2 h-2 rounded-full bg-sky-600 shrink-0 mt-1"></span>
                        </div>
                        ${detalles ? `<p class="text-[11px] text-slate-500 mt-1">${detalles}</p>` : ''}
                        <span class="block text-[10px] text-sky-600 font-semibold mt-1">Ahora mismo</span>
                    `;
                    lista.prepend(item);
                });
        }
    });

    function marcarNotificacionLeida(id) {
        if (!id) return;
        fetch(`/admin/notificaciones/${id}/leer`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
    }
</script>