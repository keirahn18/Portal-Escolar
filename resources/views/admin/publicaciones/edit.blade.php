<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Noticia - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

    @include('layouts.nav')

    <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-800">Editar Noticia</h2>
                <p class="text-xs text-slate-500 mt-1">Arrastrá los bloques para cambiar el orden, agregá nuevos párrafos o imágenes</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 bg-red-50 border-l-4 border-red-600 p-4 rounded-r-xl text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.publicaciones.update', $publicacion->id_publicacion) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="titulo" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wide">Título de la Noticia</label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $publicacion->titulo) }}" required
                        class="w-full px-4 py-3 text-base font-semibold border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition bg-slate-50/50 focus:bg-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="fk_id_cat_publicacion" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Categoría</label>
                        <select id="fk_id_cat_publicacion" name="fk_id_cat_publicacion" required
                            class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition bg-slate-50/50 focus:bg-white">
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id_cat_publicacion }}" {{ $publicacion->fk_id_cat_publicacion == $cat->id_cat_publicacion ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estado" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wide">Estado</label>
                        <select id="estado" name="estado" required
                            class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600 transition bg-slate-50/50 focus:bg-white">
                            <option value="publicado" {{ $publicacion->estado === 'publicado' ? 'selected' : '' }}>Publicado</option>
                            <option value="borrador" {{ $publicacion->estado === 'borrador' ? 'selected' : '' }}>Borrador</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Bloques de la Noticia (Arrastrá para ordenar)</label>

                    <div id="bloques-container" class="space-y-4">
                        @foreach($publicacion->bloques as $index => $bloque)
                            @if($bloque->tipo === 'texto')
                                <div class="bloque-item bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm" draggable="true">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2 cursor-move text-slate-500 select-none">
                                            <span class="text-base font-bold">⠿</span>
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Bloque de Contenido</span>
                                        </div>
                                        <button type="button" onclick="eliminarBloque(this)" class="text-xs text-red-500 hover:text-red-700 font-semibold">✕ Quitar</button>
                                    </div>
                                    <input type="hidden" data-name="id_bloque" value="{{ $bloque->id_bloque }}">
                                    <input type="hidden" data-name="tipo" value="texto">
                                    <textarea data-name="texto" rows="4" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white">{{ $bloque->contenido_texto }}</textarea>
                                </div>
                            @else
                                <div class="bloque-item bg-sky-50/40 border border-sky-200 rounded-xl p-4 shadow-sm" draggable="true">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2 cursor-move text-sky-600 select-none">
                                            <span class="text-base font-bold">⠿</span>
                                            <span class="text-xs font-bold uppercase tracking-wider text-sky-700">Bloque de Imagen</span>
                                        </div>
                                        <button type="button" onclick="eliminarBloque(this)" class="text-xs text-red-500 hover:text-red-700 font-semibold">✕ Quitar</button>
                                    </div>
                                    <input type="hidden" data-name="id_bloque" value="{{ $bloque->id_bloque }}">
                                    <input type="hidden" data-name="tipo" value="imagen">
                                    <div class="mb-3">
                                        <img src="{{ asset($bloque->ruta_img) }}" alt="Imagen actual" class="max-h-48 rounded-lg border border-slate-200 object-cover mx-auto">
                                    </div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Reemplazar imagen (opcional):</label>
                                    <input type="file" data-name="imagen" accept="image/*" onchange="previsualizarImagen(this)"
                                        class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white border border-slate-300 rounded-xl p-1 bg-white">
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="button" onclick="agregarBloqueTexto()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-300 transition cursor-pointer">
                            📝 + Agregar Contenido
                        </button>
                        <button type="button" onclick="agregarBloqueImagen()" class="px-4 py-2.5 bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold rounded-xl border border-sky-200 transition cursor-pointer">
                            🖼️ + Agregar Imagen
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                    <a href="{{ route('admin.publicaciones.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancelar</a>
                    <button type="submit" class="bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition shadow-md">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

    <script>
        const container = document.getElementById('bloques-container');
        let draggedItem = null;

        window.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.bloque-item').forEach(configurarDragAndDrop);
            reindexarBloques();
        });

        function agregarBloqueTexto() {
            const div = document.createElement('div');
            div.className = 'bloque-item bg-slate-50 border border-slate-200 rounded-xl p-4 shadow-sm';
            div.draggable = true;
            div.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 cursor-move text-slate-500 select-none">
                        <span class="text-base font-bold">⠿</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Bloque de Contenido</span>
                    </div>
                    <button type="button" onclick="eliminarBloque(this)" class="text-xs text-red-500 hover:text-red-700 font-semibold">✕ Quitar</button>
                </div>
                <input type="hidden" data-name="tipo" value="texto">
                <textarea data-name="texto" rows="4" required placeholder="Escribí un párrafo aquí..." class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white"></textarea>
            `;
            configurarDragAndDrop(div);
            container.appendChild(div);
            reindexarBloques();
        }

        function agregarBloqueImagen() {
            const div = document.createElement('div');
            div.className = 'bloque-item bg-sky-50/40 border border-sky-200 rounded-xl p-4 shadow-sm';
            div.draggable = true;
            div.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 cursor-move text-sky-600 select-none">
                        <span class="text-base font-bold">⠿</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-700">Bloque de Imagen</span>
                    </div>
                    <button type="button" onclick="eliminarBloque(this)" class="text-xs text-red-500 hover:text-red-700 font-semibold">✕ Quitar</button>
                </div>
                <input type="hidden" data-name="tipo" value="imagen">
                <input type="file" data-name="imagen" accept="image/*" required onchange="previsualizarImagen(this)"
                    class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-600 file:text-white border border-slate-300 rounded-xl p-1 bg-white">
                <div class="preview-container mt-3 hidden">
                    <img src="" alt="Vista previa" class="max-h-48 rounded-lg border border-slate-200 object-cover mx-auto">
                </div>
            `;
            configurarDragAndDrop(div);
            container.appendChild(div);
            reindexarBloques();
        }

        function previsualizarImagen(input) {
            const previewContainer = input.nextElementSibling;
            if (!previewContainer) return;
            const img = previewContainer.querySelector('img');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; previewContainer.classList.remove('hidden'); };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function eliminarBloque(btn) {
            btn.closest('.bloque-item').remove();
            reindexarBloques();
        }

        function configurarDragAndDrop(item) {
            item.addEventListener('dragstart', () => { draggedItem = item; setTimeout(() => item.classList.add('opacity-50'), 0); });
            item.addEventListener('dragend', () => { setTimeout(() => { item.classList.remove('opacity-50'); draggedItem = null; reindexarBloques(); }, 0); });
            item.addEventListener('dragover', e => {
                e.preventDefault();
                const afterElement = getDragAfterElement(container, e.clientY);
                if (afterElement == null) container.appendChild(draggedItem);
                else container.insertBefore(draggedItem, afterElement);
            });
        }

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.bloque-item:not(.opacity-50)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) return { offset, element: child };
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function reindexarBloques() {
            container.querySelectorAll('.bloque-item').forEach((item, index) => {
                item.querySelectorAll('[data-name]').forEach(input => {
                    input.name = `bloques[${index}][${input.getAttribute('data-name')}]`;
                });
            });
        }
    </script>

</body>
</html>