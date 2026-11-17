<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $publicacion->titulo }} - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased flex flex-col min-h-screen">

    @include('layouts.nav-publico')

    <main class="flex-grow max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        
        <div>
            <a href="{{ route('portal.index') }}#noticias" class="inline-flex items-center gap-2 text-sm font-bold text-sky-700 hover:text-sky-900 transition">
                <span>← Volver a Noticias</span>
            </a>
        </div>

        <article class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-6 sm:p-10 space-y-6">
            <header class="space-y-3 border-b border-slate-100 pb-6">
                <span class="inline-block bg-sky-100 text-sky-800 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-lg">
                    {{ $publicacion->categoria->nombre }}
                </span>

                <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 leading-tight">
                    {{ $publicacion->titulo }}
                </h1>

                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 pt-1">
                    <span>📅 Publicado el {{ $publicacion->fecha_publicacion->format('d/m/Y') }}</span>
                    <span>•</span>
                    <span>🏫 E.P.E.P. N° 436 "Simón Bolívar"</span>
                </div>
            </header>

            <div class="space-y-6 text-slate-700 leading-relaxed">
                @foreach ($publicacion->bloques as $bloque)
                    @if ($bloque->tipo === 'texto')
                        <p class="text-base sm:text-lg whitespace-pre-line">{{ $bloque->contenido_texto }}</p>
                    @elseif ($bloque->tipo === 'imagen')
                        <figure class="my-4">
                            <img src="{{ asset($bloque->ruta_img) }}"
                                 alt="{{ $publicacion->titulo }}"
                                 class="w-full max-h-[540px] object-contain rounded-2xl border border-slate-200/80 bg-slate-50 mx-auto shadow-xs">
                        </figure>
                    @endif
                @endforeach
            </div>
        </article>

    </main>

    <footer class="bg-slate-900 text-slate-400 text-center py-6 text-xs mt-auto">
        E.P.E.P. N° 436 "Simón Bolívar" • Formosa Capital
    </footer>

</body>
</html>