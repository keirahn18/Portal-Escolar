<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E.P.E.P. N° 436 "Simón Bolívar" - Portal Escolar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased flex flex-col min-h-screen">

    @include('layouts.nav-publico')

    <!-- 1. HERO INSTITUCIONAL -->
    <section id="inicio" class="relative bg-gradient-to-br from-sky-900 via-blue-900 to-slate-900 text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-8 space-y-5">
                    <span class="inline-flex items-center gap-2 bg-sky-500/20 border border-sky-400/30 text-sky-200 text-xs font-bold uppercase tracking-wider px-3.5 py-1.5 rounded-full">
                        Escuela Primaria Provincial • Formosa
                    </span>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                        Bienvenidos al Portal Oficial de la <span class="text-sky-300">E.P.E.P. N° 436 "Simón Bolívar"</span>
                    </h1>
                    <p class="text-slate-200 text-base sm:text-lg max-w-2xl leading-relaxed">
                        Un espacio institucional pensado para fortalecer el vínculo entre la escuela y las familias. Consultá comunicados oficiales, novedades pedagógicas y la agenda de actividades escolares.
                    </p>
                    <div class="flex flex-wrap gap-3.5 pt-2">
                        <a href="#noticias" class="bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold text-sm px-6 py-3 rounded-xl shadow-lg transition">
                            Ver Últimas Noticias
                        </a>
                        <a href="#calendario" class="bg-white/10 hover:bg-white/20 border border-white/25 text-white font-semibold text-sm px-6 py-3 rounded-xl transition">
                            Consultar Calendario
                        </a>
                    </div>
                </div>

                <!-- Tarjeta Lateral con Escudo Institucional -->
                <div class="lg:col-span-4 flex justify-center">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-8 text-center max-w-xs w-full shadow-2xl">
                        <img src="{{ asset('images/logo.png') }}" alt="Escudo Institucional" class="h-32 w-auto mx-auto object-contain drop-shadow-md mb-4">
                        <h2 class="font-bold text-lg text-white">E.P.E.P. N° 436</h2>
                        <p class="text-sky-200 text-xs font-medium mt-0.5">"Simón Bolívar"</p>
                        <div class="mt-4 pt-4 border-t border-white/15 text-xs text-slate-300 space-y-1">
                            <p>Formosa Capital, Argentina</p>
                            <p class="text-sky-300 font-semibold">Educación Pública de Calidad</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. INTRODUCCIÓN INSTITUCIONAL (NUESTRA ESCUELA) -->
    <section id="institucional" class="py-14 sm:py-16 bg-white border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="max-w-3xl mx-auto text-center space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-sky-700">Identidad Educativa</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Nuestra Institución</h2>
                <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                    La Escuela Provincial de Educación Primaria N° 436 "Simón Bolívar" trabaja diariamente en la formación integral de nuestros estudiantes, promoviendo valores de respeto, inclusión, participación ciudadana y acompañamiento continuo a cada trayectoria escolar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/70 space-y-2.5">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-lg">
                        🎓
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Compromiso Pedagógico</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Acompañamos el aprendizaje en cada ciclo con proyectos áulicos, ferias de ciencias, actividades culturales y espacios de lectura compartida.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/70 space-y-2.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                        🤝
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Escuela y Familia</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Mantenemos canales claros y accesibles para que madres, padres y tutores estén informados sobre actos, reuniones y comunicados importantes.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/70 space-y-2.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
                        🏫
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Comunidad Activa</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Fomentamos un entorno seguro, participativo y abierto a la comunidad de Formosa Capital, celebrando nuestras fechas patrias y logros institucionales.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. CARTELERA DE NOTICIAS PUBLICADAS (CON BUSCADOR, PAGINACIÓN Y TIEMPO REAL) -->
    <section id="noticias" class="py-14 sm:py-16 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-sky-700">Actualidad Escolar</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Noticias y Comunicados</h2>
                <p class="text-slate-500 text-xs sm:text-sm mt-1">
                    Mostrando {{ $publicaciones->firstItem() ?? 0 }} - {{ $publicaciones->lastItem() ?? 0 }} de {{ $publicaciones->total() }} publicaciones
                </p>
            </div>

            <!-- Buscador de Noticias -->
            <form action="{{ route('portal.index') }}#noticias" method="GET" class="w-full lg:w-96">
                @if(request('categoria'))
                <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                @endif
                <div class="relative flex items-center">
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar por título o palabra clave..."
                        class="w-full pl-4 pr-24 py-2.5 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 shadow-2xs">

                    @if(request('buscar'))
                    <a href="{{ route('portal.index', request()->only('categoria')) }}#noticias"
                        title="Limpiar búsqueda"
                        class="absolute right-16 text-xs text-slate-400 hover:text-red-600 font-bold px-1.5">
                        ✕
                    </a>
                    @endif

                    <button type="submit"
                        class="absolute right-1.5 bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition cursor-pointer">
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        <!-- Filtros por Categoría -->
        <div class="flex flex-wrap items-center gap-2 pb-1">
            <a href="{{ route('portal.index', request()->only('buscar')) }}#noticias"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ !request('categoria') ? 'bg-sky-700 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                Todas
            </a>
            @foreach($categorias as $cat)
            <a href="{{ route('portal.index', array_merge(request()->only('buscar'), ['categoria' => $cat->id_cat_publicacion])) }}#noticias"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ request('categoria') == $cat->id_cat_publicacion ? 'bg-sky-700 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                {{ $cat->nombre }}
            </a>
            @endforeach
        </div>

        <!-- Grilla de Noticias -->
        <div id="grid-noticias" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
            @forelse ($publicaciones as $pub)
            @php
            $imagenes = $pub->bloques->where('tipo', 'imagen');
            $primerTexto = $pub->bloques->where('tipo', 'texto')->first();
            @endphp
            <article data-noticia-id="{{ $pub->id_publicacion }}" class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col justify-between hover:shadow-md transition group">
                <div>
                    <a href="{{ route('portal.noticia', $pub->id_publicacion) }}" class="block relative h-52 bg-slate-100 overflow-hidden">
                        @if($imagenes->isNotEmpty())
                        <img src="{{ asset($imagenes->first()->ruta_img) }}" alt="{{ $pub->titulo }}"
                            class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                        @if($imagenes->count() > 1)
                        <span class="absolute bottom-2.5 right-2.5 bg-slate-900/75 text-white text-[11px] font-semibold px-2.5 py-1 rounded-lg">
                            +{{ $imagenes->count() - 1 }} fotos
                        </span>
                        @endif
                        @else
                        <div class="h-full w-full bg-gradient-to-br from-sky-50 to-slate-100 flex flex-col items-center justify-center text-sky-800/60 p-4 text-center">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-14 w-auto opacity-40 mb-2">
                            <span class="text-xs font-semibold">Comunicado Institucional</span>
                        </div>
                        @endif
                        <span class="absolute top-3 left-3 bg-sky-700 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-xs">
                            {{ $pub->categoria->nombre }}
                        </span>
                    </a>

                    <div class="p-6 space-y-3">
                        <div class="text-xs text-slate-400 font-medium">
                            📅 {{ $pub->fecha_publicacion->format('d/m/Y') }}
                        </div>

                        <h3 class="font-bold text-slate-900 text-lg leading-snug line-clamp-2 group-hover:text-sky-700 transition">
                            <a href="{{ route('portal.noticia', $pub->id_publicacion) }}">
                                {{ $pub->titulo }}
                            </a>
                        </h3>

                        <p class="text-slate-600 text-sm line-clamp-3 leading-relaxed">
                            {{ $primerTexto ? $primerTexto->contenido_texto : 'Hacé clic para ver el detalle completo de esta publicación.' }}
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('portal.noticia', $pub->id_publicacion) }}"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900 transition">
                        <span>Leer publicación completa</span>
                        <span>→</span>
                    </a>
                </div>
            </article>
            @empty
            <div id="estado-vacio-noticias" class="col-span-full bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200/80 space-y-2">
                <p class="text-base font-semibold text-slate-600">No se encontraron publicaciones con esos criterios.</p>
                @if(request('buscar') || request('categoria'))
                <a href="{{ route('portal.index') }}#noticias" class="inline-block text-xs font-bold text-sky-700 hover:underline">
                    Restablecer filtros y ver todas las noticias
                </a>
                @endif
            </div>
            @endforelse
        </div>

        <!-- Navegación de Páginas (Paginación) -->
        @if($publicaciones->hasPages())
        <div class="pt-4">
            {{ $publicaciones->links() }}
        </div>
        @endif
    </section>

    <!-- 4. CALENDARIO ESCOLAR PÚBLICO (SOLO EVENTOS EXTERNOS) -->
    <section id="calendario" class="py-14 sm:py-16 bg-white border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-sky-700">Fechas Importantes</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Calendario de Actividades Escolares</h2>
                    <p class="text-slate-600 text-sm mt-1">Actos escolares, reuniones de tutores, ferias y feriados abiertos a la comunidad.</p>
                </div>

                <div class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 bg-sky-50 border border-sky-200 px-3.5 py-2 rounded-xl">
                    <span class="w-3 h-3 rounded-full bg-[#0284c7]"></span>
                    <span>Hacé clic sobre un evento para ver horario y lugar</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Calendario FullCalendar -->
                <div class="lg:col-span-8 bg-slate-50/60 p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div id="public-calendar"></div>
                </div>

                <!-- Próximos Eventos Destacados -->
                <div class="lg:col-span-4 bg-slate-50 rounded-2xl p-6 border border-slate-200/80 space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-3">
                        Próximas Fechas
                    </h3>

                    @forelse($proximosEventos as $ev)
                    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-sky-700 uppercase">{{ $ev->categoria->nombre }}</span>
                            <span class="font-semibold text-slate-500">{{ $ev->fecha->format('d/m/Y') }}</span>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm">{{ $ev->titulo }}</h4>
                        @if($ev->hora_inicio || $ev->lugar)
                        <p class="text-xs text-slate-500">
                            @if($ev->hora_inicio) 🕒 {{ substr($ev->hora_inicio, 0, 5) }} hs @endif
                            @if($ev->lugar) 📍 {{ $ev->lugar }} @endif
                        </p>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No hay eventos próximos programados en los siguientes días.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Público de Detalle de Evento -->
    <div id="modal-evento-publico" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                <div>
                    <span id="pub-ev-categoria" class="inline-block bg-sky-100 text-sky-800 text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-md mb-1"></span>
                    <h3 id="pub-ev-titulo" class="text-lg font-extrabold text-slate-900"></h3>
                </div>
                <button type="button" onclick="cerrarModalPublico()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-2.5 text-sm text-slate-700">
                <p><strong>📅 Fecha:</strong> <span id="pub-ev-fecha"></span></p>
                <p id="pub-ev-horario-wrap" class="hidden"><strong>🕒 Horario:</strong> <span id="pub-ev-horario"></span></p>
                <p id="pub-ev-lugar-wrap" class="hidden"><strong>📍 Lugar:</strong> <span id="pub-ev-lugar"></span></p>
                <div id="pub-ev-desc-wrap" class="hidden pt-2 border-t border-slate-100">
                    <p class="text-xs font-bold uppercase text-slate-500 mb-1">Detalles:</p>
                    <p id="pub-ev-desc" class="text-sm text-slate-600 whitespace-pre-line"></p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 text-right">
                <button type="button" onclick="cerrarModalPublico()" class="bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold px-5 py-2.5 rounded-xl">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    <!-- FOOTER INSTITUCIONAL -->
    <footer class="bg-slate-900 text-slate-300 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/logo.png') }}" alt="Escudo" class="h-12 w-auto bg-white p-1.5 rounded-xl">
                <div>
                    <span class="block font-bold text-white text-sm">E.P.E.P. N° 436 "Simón Bolívar"</span>
                    <span class="block text-xs text-slate-400">Formosa Capital, Provincia de Formosa</span>
                </div>
            </div>

            <div class="text-xs text-slate-400 md:text-center">
                <p>Portal Oficial de Comunicación Institucional</p>
                <p class="mt-1">Información actualizada para estudiantes, familias y docentes.</p>
            </div>

            <div class="md:text-right">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300 transition">
                    Acceso al Panel Administrativo →
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('public-calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                events: "{{ route('portal.eventos.feed') }}",
                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    document.getElementById('pub-ev-titulo').textContent = info.event.title;
                    document.getElementById('pub-ev-categoria').textContent = props.categoria;
                    document.getElementById('pub-ev-fecha').textContent = props.fecha_formateada;

                    const horarioWrap = document.getElementById('pub-ev-horario-wrap');
                    if (props.hora_inicio) {
                        let textoHora = props.hora_inicio + ' hs';
                        if (props.hora_fin) textoHora += ' a ' + props.hora_fin + ' hs';
                        document.getElementById('pub-ev-horario').textContent = textoHora;
                        horarioWrap.classList.remove('hidden');
                    } else {
                        horarioWrap.classList.add('hidden');
                    }

                    const lugarWrap = document.getElementById('pub-ev-lugar-wrap');
                    if (props.lugar) {
                        document.getElementById('pub-ev-lugar').textContent = props.lugar;
                        lugarWrap.classList.remove('hidden');
                    } else {
                        lugarWrap.classList.add('hidden');
                    }

                    const descWrap = document.getElementById('pub-ev-desc-wrap');
                    if (props.descripcion) {
                        document.getElementById('pub-ev-desc').textContent = props.descripcion;
                        descWrap.classList.remove('hidden');
                    } else {
                        descWrap.classList.add('hidden');
                    }

                    const modal = document.getElementById('modal-evento-publico');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });
            calendar.render();

            // Escuchar actualizaciones en tiempo real por WebSocket (Laravel Reverb)
            if (window.Echo) {
                window.Echo.channel('portal-escolar')
                    .listen('.evento.actualizado', () => {
                        calendar.refetchEvents();
                    })
                    .listen('.noticia.actualizada', (e) => {
                        actualizarCarteleraEnVivo(e.noticia, e.accion);
                    });
            }
        });

        function cerrarModalPublico() {
            const modal = document.getElementById('modal-evento-publico');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function actualizarCarteleraEnVivo(noticia, accion) {
            const grid = document.getElementById('grid-noticias');
            if (!grid || !noticia) return;

            const tarjetaExistente = grid.querySelector(`[data-noticia-id="${noticia.id}"]`);

            // Si se eliminó o pasó a borrador, quitarla de la vista pública
            if (accion === 'eliminada' || noticia.estado !== 'publicado') {
                if (tarjetaExistente) tarjetaExistente.remove();
                return;
            }

            // Si hay filtro por categoría activo y no coincide, quitar si estaba o ignorar
            const categoriaActiva = new URLSearchParams(window.location.search).get('categoria');
            if (categoriaActiva && String(categoriaActiva) !== String(noticia.categoria_id)) {
                if (tarjetaExistente) tarjetaExistente.remove();
                return;
            }

            const estadoVacio = document.getElementById('estado-vacio-noticias');
            if (estadoVacio) estadoVacio.remove();

            const etiquetaEstado = accion === 'actualizada' ? 'Actualizado' : 'Recién publicado';

            const bloqueImagen = noticia.imagen_portada
                ? `<img src="${noticia.imagen_portada}" alt="${noticia.titulo}" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                   ${noticia.fotos_extra > 0 ? `<span class="absolute bottom-2.5 right-2.5 bg-slate-900/75 text-white text-[11px] font-semibold px-2.5 py-1 rounded-lg">+${noticia.fotos_extra} fotos</span>` : ''}`
                : `<div class="h-full w-full bg-gradient-to-br from-sky-50 to-slate-100 flex flex-col items-center justify-center text-sky-800/60 p-4 text-center">
                       <img src="${noticia.logo_fallback}" alt="Logo" class="h-14 w-auto opacity-40 mb-2">
                       <span class="text-xs font-semibold">Comunicado Institucional</span>
                   </div>`;

            const htmlTarjeta = `
                <div>
                    <a href="${noticia.url}" class="block relative h-52 bg-slate-100 overflow-hidden">
                        ${bloqueImagen}
                        <span class="absolute top-3 left-3 bg-sky-700 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-xs">
                            ${noticia.categoria}
                        </span>
                    </a>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-400 font-medium">
                            <span>📅 ${noticia.fecha}</span>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">${etiquetaEstado}</span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg leading-snug line-clamp-2 group-hover:text-sky-700 transition">
                            <a href="${noticia.url}">${noticia.titulo}</a>
                        </h3>
                        <p class="text-slate-600 text-sm line-clamp-3 leading-relaxed">${noticia.extracto}</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between">
                    <a href="${noticia.url}" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900 transition">
                        <span>Leer publicación completa</span>
                        <span>→</span>
                    </a>
                </div>
            `;

            if (tarjetaExistente) {
                tarjetaExistente.innerHTML = htmlTarjeta;
            } else {
                const nuevoArticulo = document.createElement('article');
                nuevoArticulo.setAttribute('data-noticia-id', noticia.id);
                nuevoArticulo.className = 'bg-white rounded-2xl shadow-xs border-2 border-sky-400 overflow-hidden flex flex-col justify-between hover:shadow-md transition group';
                nuevoArticulo.innerHTML = htmlTarjeta;
                grid.prepend(nuevoArticulo);
            }
        }
    </script>

</body>

</html>