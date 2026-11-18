<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas de Familias - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-full flex flex-col justify-between font-sans text-slate-800 antialiased">

    <!-- Navbar Reutilizable -->
    @include('layouts.nav')

    <!-- Contenido Principal -->
    <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Consultas de Familias</h1>
                <p class="text-xs text-slate-500">Bandeja en tiempo real con código de seguimiento institucional</p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sincronización activa
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" style="height: 75vh;">

            <!-- COLUMNA IZQUIERDA: LISTA DE CHATS CON CÓDIGO DE SEGUIMIENTO -->
            <div class="md:col-span-4 border-r border-slate-200 flex flex-col bg-slate-50 overflow-hidden">
                <div class="p-3 bg-white border-b border-slate-200">
                    <input type="text" id="buscador-chats" placeholder="Buscar por código (ESI-...) o nombre..."
                        class="w-full text-xs px-3 py-2 bg-slate-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                </div>

                <div id="lista-consultas" class="flex-1 overflow-y-auto divide-y divide-slate-200">
                    @forelse($consultas as $c)
                        <button type="button"
                            data-id="{{ $c->id_consulta }}"
                            data-filtro="{{ strtolower($c->codigo_seguimiento . ' ' . $c->nombre_consultante . ' ' . $c->asunto) }}"
                            onclick="seleccionarChat({{ $c->id_consulta }})"
                            class="item-chat w-full text-left p-3.5 hover:bg-sky-50/70 transition flex flex-col gap-1 cursor-pointer {{ $c->estado === 'pendiente' ? 'bg-amber-50/50' : 'bg-white' }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-extrabold text-sky-800 bg-sky-100 px-2 py-0.5 rounded-md">
                                    {{ $c->codigo_seguimiento }}
                                </span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full
                                    {{ $c->estado === 'pendiente' ? 'bg-amber-100 text-amber-800' : ($c->estado === 'en_atencion' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600') }}">
                                    {{ str_replace('_', ' ', $c->estado) }}
                                </span>
                            </div>
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $c->nombre_consultante }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ $c->asunto }}</p>
                        </button>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            Aún no tenés consultas asignadas.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- COLUMNA DERECHA: CONVERSACIÓN EN TIEMPO REAL -->
            <div class="md:col-span-8 flex flex-col bg-white overflow-hidden">
                <!-- Estado vacío inicial -->
                <div id="panel-vacio" class="flex-1 flex flex-col items-center justify-center text-center p-6 text-slate-400">
                    <span class="text-4xl mb-2">💬</span>
                    <p class="text-sm font-bold text-slate-600">Seleccioná una consulta de la lista</p>
                    <p class="text-xs mt-1">Vas a ver el código de seguimiento, los datos de contacto y el chat actualizado al instante.</p>
                </div>

                <!-- Chat activo -->
                <div id="panel-chat-activo" class="hidden flex-1 flex-col overflow-hidden">
                    <!-- Cabecera del chat seleccionado -->
                    <div class="px-5 py-3.5 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-3 shrink-0">
                        <div>
                            <div class="flex items-center gap-2">
                                <span id="chat-codigo" class="text-xs font-extrabold bg-sky-500/30 border border-sky-400/40 text-sky-200 px-2.5 py-0.5 rounded-md"></span>
                                <h2 id="chat-consultante" class="text-sm font-bold"></h2>
                            </div>
                            <p class="text-[11px] text-slate-300 mt-0.5">
                                Contacto: <strong id="chat-contacto"></strong> • Asunto: <span id="chat-asunto"></span>
                            </p>
                        </div>

                        <div id="acciones-chat-activo" class="flex flex-wrap items-center gap-2">
                            <!-- Selector para derivar a otro docente -->
                            <div id="box-derivar-docente" class="flex items-center gap-1.5 bg-slate-800 border border-slate-700 rounded-xl p-1">
                                <select id="select-derivar-docente"
                                    class="bg-slate-800 text-slate-200 text-xs px-2.5 py-1 rounded-lg focus:outline-none cursor-pointer">
                                    <option value="">Derivar a docente...</option>
                                    @foreach($docentes as $doc)
                                        <option value="{{ $doc->id_usuario }}">
                                            {{ $doc->apellido }}, {{ $doc->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="abrirModalDerivar()"
                                    class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-extrabold px-2.5 py-1 rounded-lg transition cursor-pointer">
                                    Derivar
                                </button>
                            </div>

                            <button type="button" id="btn-cerrar-consulta" onclick="abrirModalCerrar()"
                                class="text-xs bg-rose-600 hover:bg-rose-700 text-white font-bold px-3 py-1.5 rounded-xl transition cursor-pointer">
                                Cerrar consulta
                            </button>
                        </div>
                    </div>

                    <!-- Mensajes del hilo -->
                    <div id="contenedor-mensajes-docente" class="flex-1 p-5 space-y-3 overflow-y-auto bg-slate-50 text-xs"></div>

                    <!-- Caja de respuesta -->
                    <form id="form-respuesta-docente" class="p-3 bg-white border-t border-slate-200 flex gap-2 shrink-0">
                        <input type="text" id="input-respuesta-docente" autocomplete="off"
                            placeholder="Escribí tu respuesta para la familia..."
                            class="flex-1 text-xs px-3.5 py-2.5 bg-slate-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                        <button type="submit" id="btn-enviar-docente"
                            class="bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition cursor-pointer">
                            Responder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center py-4 text-xs text-slate-400">
        E.P.E.P. N° 436 "Simón Bolívar" - Formosa
    </footer>

    <!-- MODAL PERSONALIZADO: DERIVAR CONSULTA -->
    <div id="modal-derivar" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800">🔄 Derivar Consulta</h3>
                <button type="button" onclick="cerrarModales()" class="text-slate-400 hover:text-slate-700 text-xl leading-none cursor-pointer">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <p class="text-slate-600 leading-relaxed">
                    Vas a transferir esta conversación a <strong id="modal-derivar-nombre" class="text-slate-900"></strong>. El historial completo quedará visible para el docente asignado.
                </p>
                <div>
                    <label for="input-motivo-derivacion" class="block font-bold text-slate-700 mb-1">Motivo de derivación (opcional)</label>
                    <input type="text" id="input-motivo-derivacion" maxlength="255"
                        placeholder="Ej: Consulta sobre inscripciones de 3° grado..."
                        class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/40">
                </div>
            </div>
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="cerrarModales()"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-derivar" onclick="confirmarDerivarConsulta()"
                    class="px-4 py-2 rounded-xl text-xs font-extrabold bg-amber-500 hover:bg-amber-400 text-slate-950 transition cursor-pointer">
                    Confirmar derivación
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL PERSONALIZADO: CERRAR CONSULTA -->
    <div id="modal-cerrar" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800">🔒 Finalizar Consulta</h3>
                <button type="button" onclick="cerrarModales()" class="text-slate-400 hover:text-slate-700 text-xl leading-none cursor-pointer">&times;</button>
            </div>
            <div class="p-5 text-xs text-slate-600 leading-relaxed">
                ¿Confirmás que esta consulta ya fue resuelta? La familia ya no podrá enviar nuevos mensajes en este ticket.
            </div>
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="cerrarModales()"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-cerrar" onclick="confirmarCerrarConsulta()"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition cursor-pointer">
                    Sí, cerrar consulta
                </button>
            </div>
        </div>
    </div>

    <!-- TOAST FLOTANTE PARA AVISOS SIN ALERT -->
    <div id="toast-panel-consultas" class="hidden fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl shadow-xl text-xs font-semibold text-white transition"></div>

    <script>
    const CSRF_DOCENTE = '{{ csrf_token() }}';
    const ES_ADMIN = @json(auth()->user()->esAdmin());
    let consultaActivaId = null;
    let ultimoConteoMensajes = 0;
    let toastTimer = null;

    function mostrarAviso(mensaje, tipo = 'info') {
        const toast = document.getElementById('toast-panel-consultas');
        const colores = {
            ok: 'bg-emerald-700 border border-emerald-500',
            error: 'bg-rose-700 border border-rose-500',
            aviso: 'bg-amber-600 border border-amber-400',
            info: 'bg-slate-900 border border-slate-700'
        };
        toast.className = `fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl shadow-xl text-xs font-semibold text-white ${colores[tipo] || colores.info}`;
        toast.textContent = mensaje;
        toast.classList.remove('hidden');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.add('hidden'), 3500);
    }

    function cerrarModales() {
        ['modal-derivar', 'modal-cerrar'].forEach(id => {
            const m = document.getElementById(id);
            m.classList.add('hidden');
            m.classList.remove('flex');
        });
    }

    // 1. Filtro rápido en la barra izquierda
    document.getElementById('buscador-chats').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.item-chat').forEach(btn => {
            const texto = btn.dataset.filtro || '';
            btn.style.display = texto.includes(q) ? '' : 'none';
        });
    });

    // 2. Seleccionar un chat de la lista
    async function seleccionarChat(idConsulta) {
        consultaActivaId = idConsulta;
        ultimoConteoMensajes = 0;

        document.querySelectorAll('.item-chat').forEach(btn => {
            btn.classList.toggle('ring-2', Number(btn.dataset.id) === idConsulta);
            btn.classList.toggle('ring-sky-600', Number(btn.dataset.id) === idConsulta);
        });

        document.getElementById('panel-vacio').classList.add('hidden');
        const panelActivo = document.getElementById('panel-chat-activo');
        panelActivo.classList.remove('hidden');
        panelActivo.classList.add('flex');

        await cargarMensajesActivos(true);
    }

    // 3. Cargar mensajes del chat activo
    async function cargarMensajesActivos(forzarScroll = false) {
        if (!consultaActivaId) return;

        try {
            const res = await fetch(`/admin/consultas/${consultaActivaId}/mensajes`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            document.getElementById('chat-codigo').textContent = data.codigo_seguimiento;
            document.getElementById('chat-consultante').textContent = data.nombre_consultante;
            document.getElementById('chat-contacto').textContent = data.contacto;
            document.getElementById('chat-asunto').textContent = data.asunto;

            const input = document.getElementById('input-respuesta-docente');
            const btnEnviar = document.getElementById('btn-enviar-docente');
            const btnCerrar = document.getElementById('btn-cerrar-consulta');
            const boxDerivar = document.getElementById('box-derivar-docente');

            if (data.estado === 'cerrada') {
                input.disabled = true;
                btnEnviar.disabled = true;
                input.placeholder = 'Consulta cerrada';
                btnCerrar.classList.add('hidden');
                if (boxDerivar) boxDerivar.classList.add('hidden');
            } else {
                input.disabled = false;
                btnEnviar.disabled = false;
                input.placeholder = 'Escribí tu respuesta para la familia...';
                btnCerrar.classList.remove('hidden');
                if (boxDerivar) boxDerivar.classList.remove('hidden');
            }

            if (data.mensajes.length !== ultimoConteoMensajes || forzarScroll) {
                ultimoConteoMensajes = data.mensajes.length;
                const contenedor = document.getElementById('contenedor-mensajes-docente');
                contenedor.innerHTML = '';

                data.mensajes.forEach(m => {
                    const esDocente = m.emisor === 'docente';
                    const row = document.createElement('div');
                    row.className = esDocente ? 'flex justify-end' : 'flex justify-start';

                    row.innerHTML = `
                        <div class="${esDocente
                            ? 'bg-sky-700 text-white rounded-2xl rounded-br-xs'
                            : 'bg-white text-slate-800 border border-slate-200 rounded-2xl rounded-bl-xs shadow-2xs'} px-4 py-2.5 max-w-[78%] leading-relaxed">
                            <p class="text-[10px] font-bold opacity-75 mb-0.5">${escapeHtml(m.autor)} • ${escapeHtml(m.fecha)}</p>
                            <div class="whitespace-pre-line">${escapeHtml(m.contenido)}</div>
                        </div>
                    `;
                    contenedor.appendChild(row);
                });

                contenedor.scrollTop = contenedor.scrollHeight;
            }
        } catch (e) {
            console.error('Error sincronizando mensajes:', e);
        }
    }

    // 4. Enviar respuesta del docente
    document.getElementById('form-respuesta-docente').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!consultaActivaId) return;

        const input = document.getElementById('input-respuesta-docente');
        const texto = input.value.trim();
        if (!texto) return;
        input.value = '';

        try {
            const res = await fetch(`/admin/consultas/${consultaActivaId}/responder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_DOCENTE,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ contenido: texto })
            });
            if (res.ok) {
                await cargarMensajesActivos(true);
                await actualizarListaConsultas();
            } else {
                mostrarAviso('No se pudo enviar la respuesta.', 'error');
            }
        } catch (e) {
            mostrarAviso('Error de conexión al enviar la respuesta.', 'error');
        }
    });

    // 5. Derivar consulta mediante modal personalizado (sin prompt ni alert)
    function abrirModalDerivar() {
        if (!consultaActivaId) return;

        const select = document.getElementById('select-derivar-docente');
        if (!select.value) {
            select.focus();
            mostrarAviso('Seleccioná primero un docente de la lista.', 'aviso');
            return;
        }

        document.getElementById('modal-derivar-nombre').textContent = select.options[select.selectedIndex].text.trim();
        document.getElementById('input-motivo-derivacion').value = '';

        const modal = document.getElementById('modal-derivar');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('input-motivo-derivacion').focus();
    }

    async function confirmarDerivarConsulta() {
        if (!consultaActivaId) return;

        const select = document.getElementById('select-derivar-docente');
        const nuevoDocenteId = select.value;
        const motivo = document.getElementById('input-motivo-derivacion').value.trim();
        const btn = document.getElementById('btn-confirmar-derivar');

        btn.disabled = true;
        try {
            const res = await fetch(`/admin/consultas/${consultaActivaId}/derivar`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_DOCENTE,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    fk_id_usuario: Number(nuevoDocenteId),
                    motivo: motivo
                })
            });

            const data = await res.json();
            cerrarModales();

            if (!res.ok || !data.ok) {
                mostrarAviso(data.mensaje || 'No se pudo derivar la consulta.', 'error');
                return;
            }

            select.value = '';
            mostrarAviso(`Consulta derivada a ${data.asignado_a}.`, 'ok');
            await actualizarListaConsultas();

            if (!ES_ADMIN) {
                consultaActivaId = null;
                document.getElementById('panel-chat-activo').classList.add('hidden');
                document.getElementById('panel-chat-activo').classList.remove('flex');
                document.getElementById('panel-vacio').classList.remove('hidden');
            } else {
                await cargarMensajesActivos(true);
            }
        } catch (e) {
            cerrarModales();
            mostrarAviso('Error de conexión al derivar la consulta.', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    // 6. Cerrar consulta mediante modal personalizado (sin confirm ni alert)
    function abrirModalCerrar() {
        if (!consultaActivaId) return;
        const modal = document.getElementById('modal-cerrar');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    async function confirmarCerrarConsulta() {
        if (!consultaActivaId) return;
        const btn = document.getElementById('btn-confirmar-cerrar');
        btn.disabled = true;

        try {
            const res = await fetch(`/admin/consultas/${consultaActivaId}/cerrar`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CSRF_DOCENTE,
                    'Accept': 'application/json'
                }
            });

            cerrarModales();

            if (res.ok) {
                mostrarAviso('Consulta marcada como cerrada.', 'ok');
                await cargarMensajesActivos(true);
                await actualizarListaConsultas();
            } else {
                mostrarAviso('No se pudo cerrar la consulta.', 'error');
            }
        } catch (e) {
            cerrarModales();
            mostrarAviso('Error de conexión al cerrar la consulta.', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    // 7. Actualizar la lista izquierda en tiempo real
    async function actualizarListaConsultas() {
        try {
            const res = await fetch("{{ route('panel.consultas.feed') }}", {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            const filtroActual = document.getElementById('buscador-chats').value.toLowerCase().trim();
            const lista = document.getElementById('lista-consultas');

            if (data.consultas.length === 0) {
                lista.innerHTML = '<div class="p-6 text-center text-xs text-slate-400">Aún no tenés consultas asignadas.</div>';
                return;
            }

            lista.innerHTML = data.consultas.map(c => {
                const activo = Number(c.id_consulta) === Number(consultaActivaId);
                const filtro = `${c.codigo_seguimiento} ${c.nombre_consultante} ${c.asunto}`.toLowerCase();
                const oculto = filtroActual && !filtro.includes(filtroActual) ? 'style="display:none;"' : '';
                const badgeClase = c.estado === 'pendiente'
                    ? 'bg-amber-100 text-amber-800'
                    : (c.estado === 'en_atencion' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600');

                return `
                    <button type="button"
                        data-id="${c.id_consulta}"
                        data-filtro="${escapeHtml(filtro)}"
                        ${oculto}
                        onclick="seleccionarChat(${c.id_consulta})"
                        class="item-chat w-full text-left p-3.5 hover:bg-sky-50/70 transition flex flex-col gap-1 cursor-pointer ${activo ? 'ring-2 ring-sky-600 bg-sky-50' : (c.sin_responder ? 'bg-amber-50/60' : 'bg-white')}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-extrabold text-sky-800 bg-sky-100 px-2 py-0.5 rounded-md">
                                ${escapeHtml(c.codigo_seguimiento)}
                            </span>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full ${badgeClase}">
                                ${escapeHtml(c.estado.replace('_', ' '))}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold text-slate-800 truncate">${escapeHtml(c.nombre_consultante)}</p>
                            <span class="text-[10px] text-slate-400 shrink-0">${escapeHtml(c.ultima_hora)}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 truncate">${escapeHtml(c.ultimo_mensaje || c.asunto)}</p>
                    </button>
                `;
            }).join('');
        } catch (e) {
            console.error('Error actualizando lista:', e);
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // Polling automático cada 3 segundos
    setInterval(() => {
        actualizarListaConsultas();
        if (consultaActivaId) {
            cargarMensajesActivos(false);
        }
    }, 3000);
    </script>
</body>
</html>