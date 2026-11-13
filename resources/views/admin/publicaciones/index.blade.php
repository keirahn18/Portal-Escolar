<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Noticias - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

    @include('layouts.nav')

    <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        
        <div class="flex flex-wrap justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Noticias y Comunicados Oficiales</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Administración de publicaciones con bloques dinámicos de contenido e imágenes</p>
            </div>
            
            <a href="{{ route('admin.publicaciones.create') }}" 
               class="bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition duration-200 shadow-md hover:shadow-lg hover:shadow-sky-500/20 active:scale-[0.98]">
                + Nueva Noticia
            </a>
        </div>

        @if (session('status'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r-xl text-sm text-emerald-800 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($publicaciones as $pub)
                @php
                    $imagenes = $pub->bloques->where('tipo', 'imagen');
                    $primerTexto = $pub->bloques->where('tipo', 'texto')->first();
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="relative h-48 bg-slate-100">
                            @if($imagenes->isNotEmpty())
                                <img src="{{ asset($imagenes->first()->ruta_img) }}" alt="Portada" class="h-full w-full object-cover">
                                @if($imagenes->count() > 1)
                                    <span class="absolute bottom-2 right-2 bg-slate-900/75 text-white text-[11px] font-semibold px-2.5 py-1 rounded-lg">
                                        +{{ $imagenes->count() - 1 }} fotos
                                    </span>
                                @endif
                            @else
                                <div class="h-full w-full flex items-center justify-center text-slate-400 text-xs font-medium">
                                    Publicación solo texto
                                </div>
                            @endif
                            <span class="absolute top-3 left-3 bg-sky-600 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-sm">
                                {{ $pub->categoria->nombre }}
                            </span>
                        </div>

                        <div class="p-5 space-y-2.5">
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span>{{ $pub->fecha_publicacion->format('d/m/Y H:i') }} hs</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ $pub->estado === 'publicado' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $pub->estado }}
                                </span>
                            </div>

                            <h3 class="font-bold text-slate-800 text-base leading-snug line-clamp-2">{{ $pub->titulo }}</h3>
                            <p class="text-slate-600 text-xs line-clamp-3">
                                {{ $primerTexto ? $primerTexto->contenido_texto : 'Sin texto descriptivo.' }}
                            </p>
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500">Por: {{ $pub->usuario->nombre }}</span>
                        <div class="space-x-3">
                            <a href="{{ route('admin.publicaciones.show', $pub->id_publicacion) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold text-xs transition">
                                Ver
                            </a>
                            <a href="{{ route('admin.publicaciones.edit', $pub->id_publicacion) }}" class="text-sky-600 hover:text-sky-800 font-semibold text-xs transition">
                                Editar
                            </a>
                            <form action="{{ route('admin.publicaciones.destroy', $pub->id_publicacion) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar esta noticia?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-xs transition">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-12 text-center text-slate-400 shadow-sm border border-slate-200/80">
                    <p class="text-base font-semibold">Aún no hay noticias publicadas.</p>
                    <p class="text-xs mt-1">Hacé clic en "+ Nueva Noticia" para crear la primera publicación.</p>
                </div>
            @endforelse
        </div>

    </main>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

</body>
</html>