<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $publicacion->titulo }} - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

    @include('layouts.nav')

    <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-4xl w-full mx-auto space-y-6">
        
        <!-- Barra superior de acciones -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.publicaciones.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-sky-700 transition">
                <span>← Volver a Noticias</span>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.publicaciones.edit', $publicacion->id_publicacion) }}" 
                   class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-sm transition">
                    Editar Noticia
                </a>
            </div>
        </div>

        <!-- Contenedor del artículo tal como lo verá el público -->
        <article class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden p-6 sm:p-10 space-y-6">
            
            <!-- Encabezado de la publicación -->
            <header class="space-y-3 border-b border-slate-100 pb-6">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="bg-sky-100 text-sky-800 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-lg">
                        {{ $publicacion->categoria->nombre }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold uppercase {{ $publicacion->estado === 'publicado' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $publicacion->estado }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $publicacion->titulo }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-1">
                    <span>📅 Publicado el {{ $publicacion->fecha_publicacion->format('d/m/Y') }} a las {{ $publicacion->fecha_publicacion->format('H:i') }} hs</span>
                    <span>•</span>
                    <span>✍️ Por {{ $publicacion->usuario->nombre }}</span>
                </div>
            </header>

            <!-- Cuerpo dinámico según el orden de bloques elegido -->
            <div class="space-y-6 text-slate-700 leading-relaxed">
                @forelse ($publicacion->bloques as $bloque)
                    @if ($bloque->tipo === 'texto')
                        <p class="text-base whitespace-pre-line">{{ $bloque->contenido_texto }}</p>
                    @elseif ($bloque->tipo === 'imagen')
                        <figure class="my-4">
                            <img src="{{ asset($bloque->ruta_img) }}" 
                                 alt="Imagen de {{ $publicacion->titulo }}" 
                                 class="w-full max-h-[520px] object-contain rounded-xl border border-slate-200/80 bg-slate-50 mx-auto shadow-sm">
                        </figure>
                    @endif
                @empty
                    <p class="text-sm text-slate-400 italic">Esta publicación aún no tiene bloques de contenido.</p>
                @endforelse
            </div>

        </article>

    </main>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

</body>
</html>