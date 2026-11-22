<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Eventos - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
</head>

<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased overflow-y-auto">

    @include('layouts.nav')

    <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">

        <!-- Encabezado y Leyenda de Colores -->
        <div class="flex flex-wrap justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Agenda y Calendario Escolar</h2>
                <div class="flex flex-wrap items-center gap-4 mt-2 text-xs font-medium">
                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                        <span class="w-3 h-3 rounded-full bg-[#0284c7]"></span> Externo (Público y personal)
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-slate-600">
                        <span class="w-3 h-3 rounded-full bg-[#d97706]"></span> Interno (Coordinación escolar)
                    </span>
                </div>
            </div>

            <button type="button" onclick="abrirModalCrear()"
                class="bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition duration-200 shadow-md cursor-pointer">
                + Nuevo Evento
            </button>
        </div>

        @if (session('status'))
        <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r-xl text-sm text-emerald-800 shadow-sm">
            {{ session('status') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r-xl text-sm text-red-700 shadow-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Contenedor del Calendario -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200/80">
            <div id="calendar"></div>
        </div>

    </main>

    <!-- Modal Crear / Editar / Ver Evento -->
    <div id="modal-evento" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 sm:p-7 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 id="modal-titulo" class="text-lg font-bold text-slate-800">Agendar Nuevo Evento</h3>
                <button type="button" onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Información del creador (solo visible para Admin al abrir un evento existente) -->
            @if(auth()->user()->esAdmin())
            <div id="info-creador-evento" class="hidden bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-600 flex items-center justify-between">
                <span>✍️ <strong>Publicado por:</strong> <span id="texto-creador-evento" class="font-semibold text-slate-800"></span></span>
                <span id="badge-tipo-evento" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"></span>
            </div>
            @endif

            <!-- Aviso solo lectura cuando un docente abre un evento interno -->
            <div id="aviso-solo-lectura" class="hidden bg-amber-50 border-l-4 border-amber-500 p-3 rounded-r-xl text-xs text-amber-800">
                Este es un <strong>evento interno</strong> gestionado por la administración. Podés consultar los detalles pero no modificarlo.
            </div>

            <form id="form-evento" action="{{ route('admin.eventos.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Título del Evento</label>
                    <input type="text" name="titulo" id="ev-titulo" required placeholder="Ej: Acto Día de la Bandera"
                        class="campo-editable w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-600">
                </div>

                @if(auth()->user()->esAdmin())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Categoría</label>
                        <select name="fk_id_cat_evento" id="ev-categoria" required
                            class="campo-editable w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl bg-white">
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id_cat_evento }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Visibilidad (Tipo)</label>
                        <select name="fk_id_tipo_evento" id="ev-tipo" required onchange="toggleDocenteEncargado()"
                            class="campo-editable w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl bg-white">
                            @foreach($tipos as $tipo)
                            <option value="{{ $tipo->id_tipo_evento }}" data-tipo="{{ $tipo->tipo }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Selector condicional de Docente Encargado (solo Admin cuando elige Interno) -->
                <div id="bloque-docente" class="hidden bg-amber-50/70 border border-amber-200 rounded-xl p-3.5">
                    <label for="ev-docente" class="block text-xs font-bold text-amber-900 mb-1 uppercase">
                        Docente / Personal Encargado *
                    </label>
                    <select name="fk_docente_encargado" id="ev-docente"
                        class="campo-editable w-full px-3.5 py-2 text-sm border border-amber-300 rounded-xl bg-white">
                        <option value="">-- Seleccionar docente encargado --</option>
                        @foreach($docentes as $doc)
                        <option value="{{ $doc->id_usuario }}">
                            {{ $doc->apellido }}, {{ $doc->nombre }} ({{ $doc->rol->nombre }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @else
                <!-- Vista para Docentes: Tipo Externo fijo por defecto -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Categoría</label>
                    <select name="fk_id_cat_evento" id="ev-categoria" required
                        class="campo-editable w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl bg-white">
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id_cat_evento }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="fk_id_tipo_evento" id="ev-tipo-hidden" value="{{ $tipoExterno->id_tipo_evento ?? '' }}">

                <!-- Info de encargado cuando un docente abre un evento interno en modo lectura -->
                <div id="info-docente-lectura" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900">
                    <strong>Docente encargado:</strong> <span id="texto-docente-lectura"></span>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Fecha</label>
                        <input type="date" name="fecha" id="ev-fecha" required
                            class="campo-editable w-full px-3 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Hora Inicio</label>
                        <input type="time" name="hora_inicio" id="ev-inicio"
                            class="campo-editable w-full px-3 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Hora Fin</label>
                        <input type="time" name="hora_fin" id="ev-fin"
                            class="campo-editable w-full px-3 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Lugar (Opcional)</label>
                    <input type="text" name="lugar" id="ev-lugar" placeholder="Ej: Patio Cubierto / Salón de Actos"
                        class="campo-editable w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Descripción / Detalles</label>
                    <textarea name="descripcion" id="ev-descripcion" rows="3" placeholder="Información adicional del evento..."
                        class="campo-editable w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl"></textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div id="contenedor-eliminar" class="hidden">
                        <button type="button" onclick="confirmarEliminar()" class="text-xs font-bold text-red-600 hover:text-red-800 px-3 py-2 rounded-lg hover:bg-red-50 transition">
                            Eliminar Evento
                        </button>
                    </div>

                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" onclick="cerrarModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Cerrar
                        </button>
                        <button type="submit" id="btn-guardar-evento" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow">
                            Guardar Evento
                        </button>
                    </div>
                </div>
            </form>

            <!-- Formulario oculto para eliminar -->
            <form id="form-eliminar-evento" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

    <script>
        const esAdmin = @json(auth()->user()->esAdmin());
        const modal = document.getElementById('modal-evento');
        const form = document.getElementById('form-evento');
        const formMethod = document.getElementById('form-method');
        const modalTitulo = document.getElementById('modal-titulo');
        const contenedorEliminar = document.getElementById('contenedor-eliminar');
        const btnGuardar = document.getElementById('btn-guardar-evento');
        const avisoSoloLectura = document.getElementById('aviso-solo-lectura');
        const formEliminar = document.getElementById('form-eliminar-evento');

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                events: "{{ route('admin.eventos.feed') }}",
                dateClick: function(info) {
                    abrirModalCrear(info.dateStr);
                },
                eventClick: function(info) {
                    abrirModalDetalle(info.event);
                }
            });
            calendar.render();

            if (window.Echo) {
                window.Echo.channel('portal-escolar')
                    .listen('.evento.actualizado', () => {
                        calendar.refetchEvents();
                    });
            }
        });

        function toggleDocenteEncargado() {
            if (!esAdmin) return;
            const selectTipo = document.getElementById('ev-tipo');
            const bloqueDocente = document.getElementById('bloque-docente');
            const selectDocente = document.getElementById('ev-docente');
            const opcionSeleccionada = selectTipo.options[selectTipo.selectedIndex];
            const esInterno = opcionSeleccionada && opcionSeleccionada.getAttribute('data-tipo') === 'Interno';

            if (esInterno) {
                bloqueDocente.classList.remove('hidden');
                selectDocente.required = true;
            } else {
                bloqueDocente.classList.add('hidden');
                selectDocente.required = false;
                selectDocente.value = '';
            }
        }

        function setModoEdicion(habilitado) {
            document.querySelectorAll('.campo-editable').forEach(el => {
                el.disabled = !habilitado;
            });
            if (habilitado) {
                btnGuardar.classList.remove('hidden');
                avisoSoloLectura.classList.add('hidden');
            } else {
                btnGuardar.classList.add('hidden');
                avisoSoloLectura.classList.remove('hidden');
            }
        }

        function abrirModalCrear(fechaSeleccionada = '') {
            modalTitulo.textContent = esAdmin ? 'Agendar Nuevo Evento' : 'Agendar Evento Externo';
            form.action = "{{ route('admin.eventos.store') }}";
            formMethod.value = 'POST';
            form.reset();
            setModoEdicion(true);

            if (fechaSeleccionada) {
                document.getElementById('ev-fecha').value = fechaSeleccionada;
            }
            if (esAdmin) {
                const infoCreador = document.getElementById('info-creador-evento');
                if (infoCreador) infoCreador.classList.add('hidden');
                toggleDocenteEncargado();
            } else {
                const infoLectura = document.getElementById('info-docente-lectura');
                if (infoLectura) infoLectura.classList.add('hidden');
            }

            contenedorEliminar.classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function abrirModalDetalle(event) {
            const props = event.extendedProps;
            const puedeEditar = props.puede_editar;

            modalTitulo.textContent = puedeEditar ? 'Editar Evento' : 'Detalle de Evento Interno';
            form.action = `/admin/eventos/${event.id}`;
            formMethod.value = 'PUT';
            formEliminar.action = `/admin/eventos/${event.id}`;

            document.getElementById('ev-titulo').value = props.titulo_original;
            document.getElementById('ev-categoria').value = props.fk_id_cat_evento;

            if (esAdmin) {
                const infoCreador = document.getElementById('info-creador-evento');
                const textoCreador = document.getElementById('texto-creador-evento');
                const badgeTipo = document.getElementById('badge-tipo-evento');

                if (infoCreador && textoCreador) {
                    textoCreador.textContent = props.creado_por;
                    badgeTipo.textContent = props.tipo;
                    badgeTipo.className = props.es_interno ?
                        'text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-amber-100 text-amber-800' :
                        'text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-sky-100 text-sky-800';
                    infoCreador.classList.remove('hidden');
                }

                document.getElementById('ev-tipo').value = props.fk_id_tipo_evento;
                toggleDocenteEncargado();
                if (props.fk_docente_encargado) {
                    document.getElementById('ev-docente').value = props.fk_docente_encargado;
                }
            } else {
                const infoLectura = document.getElementById('info-docente-lectura');
                if (props.es_interno) {
                    infoLectura.classList.remove('hidden');
                    document.getElementById('texto-docente-lectura').textContent = props.docente_nombre;
                } else {
                    infoLectura.classList.add('hidden');
                }
            }

            document.getElementById('ev-fecha').value = props.fecha_raw;
            document.getElementById('ev-inicio').value = props.hora_inicio;
            document.getElementById('ev-fin').value = props.hora_fin;
            document.getElementById('ev-lugar').value = props.lugar || '';
            document.getElementById('ev-descripcion').value = props.descripcion || '';

            setModoEdicion(puedeEditar);

            if (puedeEditar) {
                contenedorEliminar.classList.remove('hidden');
            } else {
                contenedorEliminar.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function confirmarEliminar() {
            if (confirm('¿Estás segura de eliminar este evento del calendario?')) {
                formEliminar.submit();
            }
        }
    </script>

</body>

</html>