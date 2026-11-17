<div id="widget-chat-escolar" class="fixed bottom-5 right-5 z-50 flex flex-col items-end font-sans">

    <!-- VENTANA DEL CHAT (Oculta por defecto con class="hidden") -->
    <div id="ventana-chat-escolar"
        class="hidden mb-3 w-[92vw] sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex-col"
        style="height: 500px; max-height: 82vh;">

        <!-- Cabecera -->
        <div class="bg-gradient-to-r from-sky-900 to-blue-900 text-white px-4 py-3.5 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Escudo" class="h-8 w-8 bg-white rounded-lg p-1 object-contain">
                <div>
                    <h4 class="text-xs font-extrabold">Asistente E.P.E.P. N° 436</h4>
                    <p class="text-[11px] text-sky-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> En línea
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btn-reiniciar-chat" title="Volver a empezar"
                    class="text-[11px] bg-white/15 hover:bg-white/25 text-white px-2.5 py-1 rounded-lg cursor-pointer">
                    Inicio
                </button>
                <button type="button" id="btn-cerrar-chat" title="Cerrar chat"
                    class="text-slate-200 hover:text-white text-2xl font-bold leading-none px-1 cursor-pointer">
                    &times;
                </button>
            </div>
        </div>

        <!-- Área de Mensajes -->
        <div id="chat-historial" class="p-4 space-y-3 overflow-y-auto flex-1 bg-slate-50 text-xs"></div>

        <!-- Barra Inferior de Entrada -->
        <form id="form-chat-input" class="p-2.5 bg-white border-t border-slate-200 flex gap-2 shrink-0">
            <input type="text" id="input-chat-mensaje" autocomplete="off"
                placeholder="Elegí una opción arriba..."
                disabled
                class="flex-1 text-xs px-3 py-2.5 bg-slate-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/30 disabled:opacity-60">
            <button type="submit" id="btn-enviar-chat" disabled
                class="bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl disabled:opacity-50 cursor-pointer">
                Enviar
            </button>
        </form>
    </div>

    <!-- ÍCONO FLOTANTE CIRCULAR (Lo único visible al cargar la página) -->
    <button type="button" id="btn-toggle-chat" title="Abrir asistente escolar"
        class="h-14 w-14 rounded-full bg-sky-700 hover:bg-sky-800 text-white shadow-xl flex items-center justify-center transition transform hover:scale-105 cursor-pointer">
        <span id="icono-chat-abierto" class="text-2xl">💬</span>
        <span id="icono-chat-cerrado" class="hidden text-2xl font-bold">&times;</span>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ventana = document.getElementById('ventana-chat-escolar');
        const btnToggle = document.getElementById('btn-toggle-chat');
        const btnCerrar = document.getElementById('btn-cerrar-chat');
        const btnReiniciar = document.getElementById('btn-reiniciar-chat');
        const iconoAbrir = document.getElementById('icono-chat-abierto');
        const iconoCerrar = document.getElementById('icono-chat-cerrado');
        const historial = document.getElementById('chat-historial');
        const form = document.getElementById('form-chat-input');
        const input = document.getElementById('input-chat-mensaje');
        const btnEnviar = document.getElementById('btn-enviar-chat');

        const CSRF = '{{ csrf_token() }}';
        const URL_PREGUNTAR = "{{ route('portal.chat.preguntar') }}";
        const URL_DERIVAR = "{{ route('portal.chat.derivar') }}";

        // Estados posibles:
        // 'menu_inicial' | 'esperando_codigo' | 'chat_ia' | 'pedir_nombre' | 'pedir_contacto' | 'pedir_pregunta_docente' | 'hilo_docente'
        let estado = 'menu_inicial';
        let historialIa = [];
        let datosDerivacion = {
            nombre: '',
            contacto: '',
            asunto: 'Consulta desde el portal'
        };
        let codigoSeguimiento = '';

        function abrirChat() {
            ventana.classList.remove('hidden');
            ventana.classList.add('flex');
            iconoAbrir.classList.add('hidden');
            iconoCerrar.classList.remove('hidden');
            if (historial.innerHTML.trim() === '') {
                iniciarMenu();
            }
        }

        function cerrarChat() {
            ventana.classList.add('hidden');
            ventana.classList.remove('flex');
            iconoAbrir.classList.remove('hidden');
            iconoCerrar.classList.add('hidden');
        }

        btnToggle.addEventListener('click', () => {
            if (ventana.classList.contains('hidden')) abrirChat();
            else cerrarChat();
        });

        btnCerrar.addEventListener('click', cerrarChat);
        btnReiniciar.addEventListener('click', iniciarMenu);

        function habilitarInput(placeholder) {
            input.disabled = false;
            btnEnviar.disabled = false;
            input.placeholder = placeholder;
            input.focus();
        }

        function deshabilitarInput(placeholder) {
            input.disabled = true;
            btnEnviar.disabled = true;
            input.value = '';
            input.placeholder = placeholder;
        }

        function scrollAbajo() {
            historial.scrollTop = historial.scrollHeight;
        }

        function agregarMensaje(emisor, htmlTexto, autor = '') {
            const wrapper = document.createElement('div');
            wrapper.className = emisor === 'usuario' ? 'flex justify-end' : 'flex justify-start';

            const burbuja = document.createElement('div');
            burbuja.className = emisor === 'usuario' ?
                'bg-sky-700 text-white rounded-2xl rounded-br-xs px-3.5 py-2.5 max-w-[85%] leading-relaxed whitespace-pre-line' :
                'bg-white text-slate-700 border border-slate-200 rounded-2xl rounded-bl-xs shadow-2xs px-3.5 py-2.5 max-w-[85%] leading-relaxed whitespace-pre-line';

            if (autor) {
                const pAutor = document.createElement('p');
                pAutor.className = 'text-[10px] font-bold opacity-75 mb-0.5';
                pAutor.textContent = autor;
                burbuja.appendChild(pAutor);
            }

            const contenido = document.createElement('div');
            contenido.innerHTML = htmlTexto;
            burbuja.appendChild(contenido);

            wrapper.appendChild(burbuja);
            historial.appendChild(wrapper);
            scrollAbajo();
            return burbuja;
        }

        function agregarBotonesOpciones(opciones) {
            const cont = document.createElement('div');
            cont.className = 'flex flex-col gap-2 pt-1 opciones-chat';

            opciones.forEach(op => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = op.primario ?
                    'w-full bg-sky-700 hover:bg-sky-800 text-white font-bold text-xs py-2.5 px-3.5 rounded-xl transition cursor-pointer text-center' :
                    'w-full bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 font-bold text-xs py-2.5 px-3.5 rounded-xl transition cursor-pointer text-center';
                btn.textContent = op.texto;
                btn.addEventListener('click', () => {
                    cont.remove();
                    op.accion();
                });
                cont.appendChild(btn);
            });

            historial.appendChild(cont);
            scrollAbajo();
        }

        function iniciarMenu() {
            historial.innerHTML = '';
            historialIa = [];
            codigoSeguimiento = '';
            datosDerivacion = {
                nombre: '',
                contacto: '',
                asunto: 'Consulta desde el portal'
            };
            estado = 'menu_inicial';
            deshabilitarInput('Elegí una opción arriba...');

            agregarMensaje('bot', '¡Hola! Soy el asistente de la <strong>E.P.E.P. N° 436 "Simón Bolívar"</strong>.\n¿Cómo querés continuar?');

            agregarBotonesOpciones([{
                    texto: '💬 Quiero iniciar un chat',
                    primario: true,
                    accion: () => {
                        agregarMensaje('usuario', 'Quiero iniciar un chat');
                        estado = 'chat_ia';
                        agregarMensaje('bot', '¡Perfecto! Escribí tu consulta y te ayudo.');
                        habilitarInput('Escribí tu pregunta...');
                    }
                },
                {
                    texto: '🔎 Tengo un código de seguimiento',
                    primario: false,
                    accion: () => {
                        agregarMensaje('usuario', 'Tengo un código de seguimiento');
                        estado = 'esperando_codigo';
                        agregarMensaje('bot', 'Ingresá tu código de seguimiento (ej: <strong>ESI-4821</strong>):');
                        habilitarInput('Ingresá tu código (ej: ESI-4821)...');
                    }
                }
            ]);
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const texto = input.value.trim();
            if (!texto) return;
            input.value = '';

            // Quitar botones previos si quedaban visibles
            document.querySelectorAll('.opciones-chat').forEach(el => el.remove());

            agregarMensaje('usuario', texto);

            // 1. BUSCAR CÓDIGO DE SEGUIMIENTO
            if (estado === 'esperando_codigo') {
                await consultarCodigo(texto.toUpperCase());
                return;
            }

            // 2. CONVERSACIÓN CON LA IA
            if (estado === 'chat_ia') {
                datosDerivacion.asunto = texto.substring(0, 80);
                historialIa.push({
                    de: 'usuario',
                    texto
                });
                await preguntarIa(texto);
                return;
            }

            // 3. PASO A DERIVACIÓN: NOMBRE Y APELLIDO
            if (estado === 'pedir_nombre') {
                datosDerivacion.nombre = texto;
                estado = 'pedir_contacto';
                agregarMensaje('bot', `Gracias, <strong>${texto}</strong>. Ahora ingresá tu email o teléfono de contacto:`);
                habilitarInput('Ej: nombre@gmail.com o 3704...');
                return;
            }

            // 4. PASO B DERIVACIÓN: CONTACTO -> GENERA CÓDIGO Y PIDE LA PREGUNTA
            if (estado === 'pedir_contacto') {
                datosDerivacion.contacto = texto;
                await generarTicketYPedirPregunta();
                return;
            }

            // 5. PASO C DERIVACIÓN: ENVÍA LA PREGUNTA AL DOCENTE YA ASIGNADO
            if (estado === 'pedir_pregunta_docente') {
                await enviarMensajeAlHilo(texto, true);
                return;
            }

            // 6. HILO ACTIVO CON EL DOCENTE
            if (estado === 'hilo_docente') {
                await enviarMensajeAlHilo(texto, false);
            }
        });

        async function preguntarIa(texto) {
            deshabilitarInput('Pensando respuesta...');
            const burbujaCargando = agregarMensaje('bot', '<span class="italic text-slate-400">Escribiendo...</span>');

            try {
                const res = await fetch(URL_PREGUNTAR, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mensaje: texto,
                        historial: historialIa.slice(-12)
                    })
                });
                const data = await res.json();
                burbujaCargando.parentElement.remove();

                const respuesta = data.respuesta || 'No pude procesar tu consulta en este momento.';
                historialIa.push({
                    de: 'bot',
                    texto: respuesta
                });
                agregarMensaje('bot', respuesta);

                // Solo muestra los botones cuando la propia IA detecta que no pudo resolverlo
                if (data.ofrecer_asistente) {
                    agregarBotonesOpciones([{
                            texto: '✅ Sí, pude resolver mis dudas',
                            primario: false,
                            accion: () => {
                                agregarMensaje('usuario', 'Sí, pude resolver mis dudas');
                                agregarMensaje('bot', '¡Excelente! Si necesitás algo más, escribime por acá.');
                                habilitarInput('Escribí otra consulta si necesitás...');
                            }
                        },
                        {
                            texto: '👨‍🏫 Comunicarme con personal de la escuela',
                            primario: true,
                            accion: () => {
                                agregarMensaje('usuario', 'Quiero comunicarme con personal de la escuela');
                                estado = 'pedir_nombre';
                                agregarMensaje('bot', 'Con gusto te comunico con un docente. Por favor, ingresá tu <strong>nombre y apellido</strong>:');
                                habilitarInput('Tu nombre y apellido...');
                            }
                        }
                    ]);
                }
            } catch (err) {
                burbujaCargando.parentElement.remove();
                agregarMensaje('bot', 'Tuve un inconveniente momentáneo. ¿Querés comunicarte directamente con un docente de la escuela?');
                agregarBotonesOpciones([{
                    texto: '👨‍🏫 Comunicarme con personal de la escuela',
                    primario: true,
                    accion: () => {
                        estado = 'pedir_nombre';
                        agregarMensaje('bot', 'Ingresá tu <strong>nombre y apellido</strong>:');
                        habilitarInput('Tu nombre y apellido...');
                    }
                }]);
            }

            habilitarInput('Escribí tu mensaje...');
        }

        async function generarTicketYPedirPregunta() {
        deshabilitarInput('Generando código de seguimiento...');
        try {
            const res = await fetch(URL_DERIVAR, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nombre_consultante: datosDerivacion.nombre,
                    contacto: datosDerivacion.contacto,
                    asunto: datosDerivacion.asunto || 'Consulta desde el portal',
                    contenido: '(Iniciando consulta con docente)'
                })
            });
            const data = await res.json();

            if (!res.ok || !data.ok || !data.codigo) {
                const detalle = data.error || data.message || 'Error al registrar la consulta.';
                agregarMensaje('bot', `⚠️ No se pudo generar el código: ${detalle}`);
                habilitarInput('Escribí nuevamente tu contacto para reintentar...');
                return;
            }

            codigoSeguimiento = data.codigo;
            estado = 'pedir_pregunta_docente';

            agregarMensaje(
                'bot',
                `✅ Tu código de seguimiento es: <strong class="text-sky-700 text-sm">${codigoSeguimiento}</strong> (asignado a <strong>${data.asignado_a}</strong>).\n\nAhora sí, <strong>escribí tu pregunta</strong> para el asistente:`
            );
            habilitarInput('Escribí tu pregunta para el docente...');
        } catch (err) {
            agregarMensaje('bot', '⚠️ No se pudo conectar con el servidor. Intentá nuevamente.');
            habilitarInput('Reintentar...');
        }
    }

        async function enviarMensajeAlHilo(texto, esPrimeraPregunta) {
            deshabilitarInput('Enviando mensaje...');
            try {
                const res = await fetch(`/portal/chat/seguimiento/${encodeURIComponent(codigoSeguimiento)}/responder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        contenido: texto
                    })
                });
                const data = await res.json();

                if (!data.ok) {
                    agregarMensaje('bot', data.mensaje || 'No se pudo enviar el mensaje.');
                    deshabilitarInput('Consulta cerrada');
                    return;
                }

                if (esPrimeraPregunta) {
                    estado = 'hilo_docente';
                    agregarMensaje(
                        'bot',
                        `📩 ¡Listo! Tu consulta fue enviada. El asistente te estará respondiendo a la brevedad.\n\nSi cerrás la página y querés ver la respuesta más tarde, volvé al chat e ingresá tu código de seguimiento: <strong class="text-sky-700">${codigoSeguimiento}</strong>.`
                    );
                }
                habilitarInput('Escribí otro mensaje para el docente...');
            } catch (err) {
                agregarMensaje('bot', '⚠️ Error al enviar el mensaje.');
                habilitarInput('Escribí tu mensaje...');
            }
        }

        async function consultarCodigo(codigo) {
            deshabilitarInput('Buscando código...');
            try {
                const res = await fetch(`/portal/chat/seguimiento/${encodeURIComponent(codigo)}`);
                if (!res.ok) {
                    agregarMensaje('bot', `❌ No encontré ninguna consulta con el código <strong>${codigo}</strong>. Verificalo e ingresalo de nuevo:`);
                    habilitarInput('Ingresá tu código de seguimiento...');
                    return;
                }

                const data = await res.json();
                codigoSeguimiento = data.codigo;
                estado = 'hilo_docente';

                agregarMensaje(
                    'bot',
                    `📋 <strong>Consulta ${data.codigo}</strong>\nDocente asignado: <strong>${data.responsable}</strong> | Estado: <strong class="uppercase">${data.estado}</strong>`
                );

                data.mensajes.forEach(m => {
                    if (m.contenido !== '(Iniciando consulta con docente)') {
                        agregarMensaje(m.emisor === 'familia' ? 'usuario' : 'bot', m.contenido, `${m.autor} • ${m.fecha}`);
                    }
                });

                if (data.estado === 'cerrada') {
                    deshabilitarInput('Esta consulta fue cerrada');
                } else {
                    habilitarInput('Escribí un mensaje para el docente...');
                }
            } catch (err) {
                agregarMensaje('bot', '⚠️ Error al buscar el código.');
                habilitarInput('Ingresá tu código...');
            }
        }
    });
</script>