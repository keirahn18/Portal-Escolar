<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consultas de Familias - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased">

    @include('layouts.nav')

    @php
    $usuarioActual = auth()->user();
    $nombreRol = strtolower(trim($usuarioActual->rol->nombre ?? $usuarioActual->role ?? ''));
    $esAdmin = ($esAdmin ?? false)
    || (method_exists($usuarioActual, 'esAdmin') && $usuarioActual->esAdmin())
    || in_array($nombreRol, ['admin', 'administrador', 'directivo', 'director', 'directora']);
    @endphp

    <main class="flex-grow p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-4">

        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Consultas Derivadas del Portal</h1>
                <p class="text-xs text-slate-500">
                    {{ $esAdmin
                        ? 'Modo supervisión institucional (solo lectura): las acciones de respuesta, derivación y cierre están deshabilitadas para administradores.'
                        : 'Bandeja de atención en tiempo real: respondé a las familias, derivá a otro docente o cerrá consultas resueltas.' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <select id="filtro-estado" onchange="filtrarLista()"
                    class="text-xs border border-slate-300 rounded-xl px-3 py-2 bg-white shadow-2xs">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="en_atencion">En atención</option>
                    <option value="cerrada">Cerradas</option>
                </select>
            </div>
        </div>

        <!-- Contenedor principal: Lista de chats + Conversación activa -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden min-h-[560px]">

            <!-- Columna izquierda: Lista de consultas -->
            <aside class="lg:col-span-4 border-b lg:border-b-0 lg:border-r border-slate-200 flex flex-col">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Consultas</span>
                    <span id="total-consultas" class="text-[11px] font-bold text-slate-500">{{ $consultas->count() }}</span>
                </div>

                <div id="lista-consultas" class="divide-y divide-slate-100 overflow-y-auto max-h-[520px] flex-grow">
                    @forelse($consultas as $c)
                    @php $ultimo = $c->mensajes->first(); @endphp
                    <button type="button"
                        data-id="{{ $c->id_consulta }}"
                        data-estado="{{ $c->estado }}"
                        onclick="seleccionarConsulta({{ $c->id_consulta }})"
                        class="item-consulta w-full text-left p-4 hover:bg-slate-50 transition space-y-1.5 cursor-pointer">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-extrabold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md">
                                {{ $c->codigo_seguimiento }}
                            </span>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full
                                    {{ $c->estado === 'pendiente' ? 'bg-amber-100 text-amber-800' : ($c->estado === 'en_atencion' ? 'bg-sky-100 text-sky-800' : 'bg-slate-200 text-slate-700') }}">
                                {{ str_replace('_', ' ', $c->estado) }}
                            </span>
                        </div>

                        <p class="text-xs font-bold text-slate-800 truncate">{{ $c->nombre_consultante }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ $c->asunto }}</p>

                        @if($esAdmin)
                        <p class="text-[10px] text-indigo-700 font-semibold">
                            Asignado a: {{ $c->docenteAsignado ? 'Prof. ' . $c->docenteAsignado->nombre . ' ' . $c->docenteAsignado->apellido : 'Sin asignar' }}
                        </p>
                        @endif

                        <div class="flex items-center justify-between text-[10px] text-slate-400 pt-0.5">
                            <span class="truncate max-w-[70%]">{{ $ultimo ? $ultimo->contenido : '' }}</span>
                            <span>{{ $ultimo ? $ultimo->fecha->format('d/m H:i') : $c->fecha->format('d/m H:i') }}</span>
                        </div>
                    </button>
                    @empty
                    <div id="sin-consultas-msg" class="p-8 text-center text-xs text-slate-400">
                        No hay consultas registradas por el momento.
                    </div>
                    @endforelse
                </div>
            </aside>

            <!-- Columna derecha: Hilo de conversación -->
            <section class="lg:col-span-8 flex flex-col justify-between bg-slate-50/40">

                <!-- Estado vacío inicial -->
                <div id="panel-vacio" class="flex-grow flex flex-col items-center justify-center p-8 text-center text-slate-400 space-y-2">
                    <span class="text-3xl">💬</span>
                    <p class="text-sm font-semibold text-slate-600">Seleccioná una consulta de la lista</p>
                    <p class="text-xs max-w-sm">
                        {{ $esAdmin
                            ? 'Podrás visualizar en tiempo real toda la conversación entre la familia y el docente asignado.'
                            : 'Podrás ver el historial de mensajes, responder en vivo al tutor o derivar el caso a otro docente.' }}
                    </p>
                </div>

                <!-- Chat activo (oculto hasta seleccionar consulta) -->
                <div id="panel-chat" class="hidden flex-col h-full justify-between">

                    <!-- Cabecera del chat seleccionado -->
                    <div class="p-4 bg-white border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span id="chat-codigo" class="text-xs font-extrabold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-lg"></span>
                                <h2 id="chat-consultante" class="text-sm font-extrabold text-slate-800"></h2>
                                <span id="chat-estado" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Contacto: <strong id="chat-contacto"></strong> •
                                Asunto: <span id="chat-asunto"></span> •
                                Encargado: <strong id="chat-docente"></strong>
                            </p>
                        </div>

                        <!-- Botones de acción: visibles pero disabled si es Admin o si la consulta está cerrada -->
                        <div id="acciones-docente" class="flex items-center gap-2">
                            <button type="button"
                                id="btn-derivar"
                                onclick="abrirModalDerivar()"
                                @if($esAdmin) disabled @endif
                                title="{{ $esAdmin ? 'Solo el docente asignado puede derivar' : 'Derivar consulta a otro docente' }}"
                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-xl border border-slate-300 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
                                🔄 Derivar
                            </button>
                            <button type="button"
                                id="btn-cerrar"
                                onclick="cerrarConsulta()"
                                @if($esAdmin) disabled @endif
                                title="{{ $esAdmin ? 'Solo el docente asignado puede cerrar' : 'Marcar consulta como cerrada' }}"
                                class="bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded-xl border border-red-200 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
                                ✓ Cerrar consulta
                            </button>
                        </div>
                    </div>

                    <!-- Mensajes del hilo -->
                    <div id="chat-mensajes" class="p-4 space-y-3 overflow-y-auto max-h-[360px] flex-grow"></div>

                    <!-- Pie del chat: Formulario completamente bloqueado (disabled + readonly) para Administrador -->
                    <form id="form-responder" onsubmit="enviarRespuesta(event)"
                        class="p-3 bg-white border-t border-slate-200 flex items-center gap-2">
                        <input type="text"
                            id="input-respuesta"
                            required
                            maxlength="2000"
                            @if($esAdmin) disabled readonly tabindex="-1" @endif
                            placeholder="{{ $esAdmin ? 'Modo solo lectura (Administrador): solo el docente asignado puede escribir' : 'Escribí una respuesta para la familia...' }}"
                            class="flex-grow text-xs border border-slate-300 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-sky-600 disabled:bg-slate-200/70 disabled:text-slate-400 disabled:border-slate-200 disabled:cursor-not-allowed disabled:pointer-events-none disabled:select-none">
                        <button type="submit"
                            id="btn-enviar"
                            @if($esAdmin) disabled @endif
                            class="bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition cursor-pointer disabled:bg-slate-300 disabled:text-slate-500 disabled:cursor-not-allowed disabled:pointer-events-none">
                            Enviar
                        </button>
                    </form>

                </div>
            </section>
        </div>
    </main>

    <!-- Modal para derivar a otro docente -->
    <div id="modal-derivar" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-sm font-extrabold text-slate-800">Derivar consulta a otro docente</h3>
                <button type="button" onclick="cerrarModalDerivar()" class="text-slate-400 hover:text-slate-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <form onsubmit="confirmarDerivacion(event)" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Docente destinatario</label>
                    <select id="select-docente-derivar"
                        required
                        @if($esAdmin) disabled @endif
                        class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-white disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        <option value="">Seleccionar docente...</option>
                        @foreach($docentes as $doc)
                        <option value="{{ $doc->id_usuario }}">{{ $doc->apellido }}, {{ $doc->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Motivo de derivación (opcional)</label>
                    <input type="text"
                        id="input-motivo-derivar"
                        maxlength="255"
                        @if($esAdmin) disabled readonly tabindex="-1" @endif
                        placeholder="Ej: Consulta sobre turno tarde / documentación..."
                        class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed disabled:pointer-events-none">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="cerrarModalDerivar()"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        @if($esAdmin) disabled @endif
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white cursor-pointer disabled:bg-slate-300 disabled:text-slate-500 disabled:cursor-not-allowed">
                        Confirmar derivación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

    <script>
        const esAdmin = @json($esAdmin);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let consultaActivaId = null;

        function badgeEstadoClase(estado) {
            if (estado === 'pendiente') return 'bg-amber-100 text-amber-800';
            if (estado === 'en_atencion') return 'bg-sky-100 text-sky-800';
            return 'bg-slate-200 text-slate-700';
        }

        function actualizarEstadoControles(bloquear, esCerrada = false) {
            const forzarBloqueo = esAdmin || bloquear;

            const inputResp = document.getElementById('input-respuesta');
            const btnEnviar = document.getElementById('btn-enviar');
            const btnDerivar = document.getElementById('btn-derivar');
            const btnCerrar = document.getElementById('btn-cerrar');
            const selectDoc = document.getElementById('select-docente-derivar');
            const inputMotivo = document.getElementById('input-motivo-derivar');

            inputResp.disabled = forzarBloqueo;
            inputResp.readOnly = forzarBloqueo;
            btnEnviar.disabled = forzarBloqueo;
            btnDerivar.disabled = forzarBloqueo;
            btnCerrar.disabled = forzarBloqueo;

            if (forzarBloqueo) {
                inputResp.value = '';
                inputResp.setAttribute('disabled', 'disabled');
                inputResp.setAttribute('readonly', 'readonly');
                inputResp.setAttribute('tabindex', '-1');
            } else {
                inputResp.removeAttribute('disabled');
                inputResp.removeAttribute('readonly');
                inputResp.removeAttribute('tabindex');
            }

            if (selectDoc) selectDoc.disabled = forzarBloqueo;
            if (inputMotivo) {
                inputMotivo.disabled = forzarBloqueo;
                inputMotivo.readOnly = forzarBloqueo;
            }

            if (esAdmin) {
                inputResp.placeholder = 'Modo solo lectura (Administrador): solo el docente asignado puede escribir';
            } else if (esCerrada) {
                inputResp.placeholder = 'Esta consulta se encuentra cerrada. No se pueden enviar más mensajes.';
            } else {
                inputResp.placeholder = 'Escribí una respuesta para la familia...';
            }
        }

        async function seleccionarConsulta(id) {
            consultaActivaId = id;

            document.querySelectorAll('.item-consulta').forEach(btn => {
                btn.classList.toggle('bg-sky-50/70', Number(btn.dataset.id) === Number(id));
            });

            await cargarMensajes(true);
        }

        async function cargarMensajes(scrollAbajo = false) {
            if (!consultaActivaId) return;

            try {
                const res = await fetch(`/panel/consultas/${consultaActivaId}/mensajes`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();

                document.getElementById('panel-vacio').classList.add('hidden');
                const panelChat = document.getElementById('panel-chat');
                panelChat.classList.remove('hidden');
                panelChat.classList.add('flex');

                document.getElementById('chat-codigo').textContent = data.codigo_seguimiento;
                document.getElementById('chat-consultante').textContent = data.nombre_consultante;
                document.getElementById('chat-contacto').textContent = data.contacto;
                document.getElementById('chat-asunto').textContent = data.asunto;
                document.getElementById('chat-docente').textContent = data.docente_asignado || 'Sin asignar';

                const badge = document.getElementById('chat-estado');
                badge.textContent = data.estado.replace('_', ' ');
                badge.className = `text-[10px] font-bold uppercase px-2 py-0.5 rounded-full ${badgeEstadoClase(data.estado)}`;

                const esCerrada = data.estado === 'cerrada';
                const bloquear = esAdmin || esCerrada || !data.puede_gestionar;
                actualizarEstadoControles(bloquear, esCerrada);

                const contenedor = document.getElementById('chat-mensajes');
                contenedor.innerHTML = (data.mensajes || []).map(m => {
                    const esDocente = m.emisor === 'docente';
                    return `
                    <div class="flex flex-col ${esDocente ? 'items-end' : 'items-start'}">
                        <div class="max-w-[80%] rounded-2xl px-3.5 py-2.5 text-xs shadow-2xs ${
                            esDocente
                                ? 'bg-sky-700 text-white rounded-br-sm'
                                : 'bg-white border border-slate-200 text-slate-800 rounded-bl-sm'
                        }">
                            <p class="text-[10px] font-bold mb-0.5 ${esDocente ? 'text-sky-200' : 'text-slate-500'}">
                                ${m.autor} • ${m.fecha}
                            </p>
                            <p class="whitespace-pre-line leading-relaxed">${escapeHtml(m.contenido)}</p>
                        </div>
                    </div>
                `;
                }).join('');

                if (scrollAbajo) {
                    contenedor.scrollTop = contenedor.scrollHeight;
                }
            } catch (e) {}
        }

        async function enviarRespuesta(e) {
            e.preventDefault();
            if (esAdmin || !consultaActivaId) return;

            const input = document.getElementById('input-respuesta');
            const contenido = input.value.trim();
            if (!contenido) return;

            const btn = document.getElementById('btn-enviar');
            btn.disabled = true;

            try {
                const res = await fetch(`/panel/consultas/${consultaActivaId}/responder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        contenido
                    })
                });

                if (res.ok) {
                    input.value = '';
                    await cargarMensajes(true);
                    await actualizarListaLateral();
                }
            } finally {
                btn.disabled = false;
            }
        }

        function abrirModalDerivar() {
            if (esAdmin) return;
            document.getElementById('modal-derivar').classList.remove('hidden');
        }

        function cerrarModalDerivar() {
            document.getElementById('modal-derivar').classList.add('hidden');
        }

        async function confirmarDerivacion(e) {
            e.preventDefault();
            if (esAdmin || !consultaActivaId) return;

            const fk_id_usuario = document.getElementById('select-docente-derivar').value;
            const motivo = document.getElementById('input-motivo-derivar').value;

            const res = await fetch(`/panel/consultas/${consultaActivaId}/derivar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    fk_id_usuario,
                    motivo
                })
            });

            if (res.ok) {
                cerrarModalDerivar();
                document.getElementById('input-motivo-derivar').value = '';
                consultaActivaId = null;
                document.getElementById('panel-chat').classList.add('hidden');
                document.getElementById('panel-vacio').classList.remove('hidden');
                await actualizarListaLateral();
            }
        }

        async function cerrarConsulta() {
            if (esAdmin || !consultaActivaId) return;
            if (!confirm('¿Confirmás marcar esta consulta como cerrada?')) return;

            const res = await fetch(`/panel/consultas/${consultaActivaId}/cerrar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                await cargarMensajes(false);
                await actualizarListaLateral();
            }
        }

        async function actualizarListaLateral() {
            try {
                const res = await fetch("{{ route('panel.consultas.feed') }}", {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                const lista = document.getElementById('lista-consultas');
                const consultas = data.consultas || [];

                document.getElementById('total-consultas').textContent = consultas.length;

                if (consultas.length === 0) {
                    lista.innerHTML = `<div class="p-8 text-center text-xs text-slate-400">No hay consultas registradas por el momento.</div>`;
                    return;
                }

                lista.innerHTML = consultas.map(c => `
                <button type="button"
                        data-id="${c.id_consulta}"
                        data-estado="${c.estado}"
                        onclick="seleccionarConsulta(${c.id_consulta})"
                        class="item-consulta w-full text-left p-4 hover:bg-slate-50 transition space-y-1.5 cursor-pointer ${Number(c.id_consulta) === Number(consultaActivaId) ? 'bg-sky-50/70' : ''}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-extrabold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md">
                            ${escapeHtml(c.codigo_seguimiento)}
                        </span>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full ${badgeEstadoClase(c.estado)}">
                            ${c.estado.replace('_', ' ')}
                        </span>
                    </div>
                    <p class="text-xs font-bold text-slate-800 truncate">${escapeHtml(c.nombre_consultante)}</p>
                    <p class="text-[11px] text-slate-500 truncate">${escapeHtml(c.asunto)}</p>
                    ${esAdmin ? `<p class="text-[10px] text-indigo-700 font-semibold">Asignado a: ${escapeHtml(c.docente_asignado)}</p>` : ''}
                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-0.5">
                        <span class="truncate max-w-[70%]">${escapeHtml(c.ultimo_mensaje || '')}</span>
                        <span>${escapeHtml(c.ultima_hora || '')}</span>
                    </div>
                </button>
            `).join('');

                filtrarLista();
            } catch (e) {}
        }

        function filtrarLista() {
            const estado = document.getElementById('filtro-estado').value;
            document.querySelectorAll('.item-consulta').forEach(btn => {
                btn.classList.toggle('hidden', estado !== '' && btn.dataset.estado !== estado);
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        // Polling en tiempo real cada 3 segundos para mensajes y lista lateral
        setInterval(() => {
            actualizarListaLateral();
            if (consultaActivaId) cargarMensajes(false);
        }, 3000);
    </script>
</body>

</html>