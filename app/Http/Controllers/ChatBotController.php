<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Consulta;
use App\Models\Evento;
use App\Models\Institucion;
use App\Models\MensajeConsulta;
use App\Models\PreguntaFrecuente;
use App\Models\Publicacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ChatBotController extends Controller
{
    public function preguntar(Request $request)
    {
        $data = $request->validate([
            'mensaje'   => 'required|string|max:1000',
            'historial' => 'nullable|array|max:15',
        ]);

        try {
            $contexto = $this->construirContextoInstitucional();
            $mensajeUsuario = trim($data['mensaje']);

            $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
            $modelo = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.8-flash');

            if (!empty($apiKey)) {
                $respuestaBruta = $this->consultarGemini($apiKey, $modelo, $contexto, $mensajeUsuario, $data['historial'] ?? []);

                if ($respuestaBruta) {
                    $ofrecerAsistente = str_contains($respuestaBruta, '[DERIVAR]');
                    $textoLimpio = trim(str_replace('[DERIVAR]', '', $respuestaBruta));

                    return response()->json([
                        'respuesta'         => nl2br(e($textoLimpio)),
                        'ofrecer_asistente' => $ofrecerAsistente,
                    ]);
                }
            }

            // Fallback contextual local si no hay API key o falla la conexión
            return response()->json([
                'respuesta'         => nl2br(e($this->responderDesdeContextoLocal($mensajeUsuario) . "\n\n¿Pude resolver tu duda o preferís comunicarte con personal de la escuela?")),
                'ofrecer_asistente' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en ChatBotController@preguntar: ' . $e->getMessage());

            return response()->json([
                'respuesta'         => '⚠️ Error interno: ' . e($e->getMessage()),
                'ofrecer_asistente' => true,
            ]);
        }
    }

    private function construirContextoInstitucional(): string
    {
        $datosEscuela = "Escuela Provincial de Educación Primaria N° 436 \"Simón Bolívar\", Formosa Capital, Provincia de Formosa, Argentina.";
        try {
            $inst = Institucion::first();
            if ($inst) {
                $partes = array_filter([
                    $inst->nombre ?? null,
                    !empty($inst->direccion) ? "Dirección: {$inst->direccion}" : null,
                    !empty($inst->telefono) ? "Teléfono: {$inst->telefono}" : null,
                    !empty($inst->email) ? "Email: {$inst->email}" : null,
                    !empty($inst->descripcion) ? $inst->descripcion : null,
                ]);
                if (!empty($partes)) {
                    $datosEscuela = implode(' | ', $partes);
                }
            }
        } catch (\Throwable $e) {
        }

        $faqs = '';
        try {
            if (class_exists(PreguntaFrecuente::class)) {
                $faqs = PreguntaFrecuente::where('activo', true)->get()
                    ->map(fn($f) => "- {$f->pregunta}: {$f->respuesta}")
                    ->implode("\n");
            }
        } catch (\Throwable $e) {
        }

        $eventosPasados = '';
        $eventosProximos = '';

        try {
            if (class_exists(Evento::class)) {
                $tablaEv = (new Evento)->getTable();
                $tieneTipo = Schema::hasColumn($tablaEv, 'tipo');
                $hoy = now()->toDateString();

                $formatearEvento = function ($e) {
                    $fecha = Carbon::parse($e->fecha)->format('d/m/Y');
                    $hora = !empty($e->hora_inicio) ? " ({$e->hora_inicio} hs)" : '';
                    $lugar = !empty($e->lugar) ? " | Lugar: {$e->lugar}" : '';
                    $desc = !empty($e->descripcion) ? " | Descripción: " . trim(strip_tags($e->descripcion)) : '';
                    return "- Fecha: {$fecha}{$hora} — Título: \"{$e->titulo}\"{$lugar}{$desc}";
                };

                $qPasados = Evento::whereDate('fecha', '<', $hoy);
                if ($tieneTipo) {
                    $qPasados->whereIn('tipo', ['externo', 'publico']);
                }
                $eventosPasados = $qPasados->orderByDesc('fecha')->take(10)->get()->map($formatearEvento)->implode("\n");

                $qProximos = Evento::whereDate('fecha', '>=', $hoy);
                if ($tieneTipo) {
                    $qProximos->whereIn('tipo', ['externo', 'publico']);
                }
                $eventosProximos = $qProximos->orderBy('fecha', 'asc')->take(15)->get()->map($formatearEvento)->implode("\n");
            }
        } catch (\Throwable $e) {
        }

        $noticias = '';
        try {
            if (class_exists(Publicacion::class)) {
                $tablaPub = (new Publicacion)->getTable();
                $queryPub = Publicacion::query();

                if (Schema::hasColumn($tablaPub, 'estado')) {
                    $queryPub->where('estado', 'publicado');
                }
                if (Schema::hasColumn($tablaPub, 'audiencia')) {
                    $queryPub->whereIn('audiencia', ['publico', 'todos', 'ambos']);
                }

                $ordenCol = Schema::hasColumn($tablaPub, 'fecha_publicacion') ? 'fecha_publicacion' : 'created_at';

                $noticias = $queryPub
                    ->orderByDesc($ordenCol)
                    ->take(15)
                    ->get()
                    ->map(function ($p, $idx) use ($ordenCol) {
                        $fechaRaw = $p->{$ordenCol} ?? null;
                        $fecha = $fechaRaw ? Carbon::parse($fechaRaw)->format('d/m/Y') : 'Sin fecha';
                        $cuerpoRaw = $p->contenido ?? $p->cuerpo ?? $p->descripcion ?? '';
                        $cuerpo = trim(preg_replace('/\s+/', ' ', strip_tags($cuerpoRaw)));
                        $etiqueta = $idx === 0 ? ' [MÁS RECIENTE]' : '';
                        return "- Fecha de publicación: {$fecha}{$etiqueta} — Título: \"{$p->titulo}\" | Contenido: " . ($cuerpo ?: 'Sin descripción adicional.');
                    })
                    ->implode("\n");
            }
        } catch (\Throwable $e) {
        }

        $hoyTexto = now()->format('d/m/Y');

        return <<<TXT
Sos el asistente virtual oficial de la {$datosEscuela}.
Hoy es {$hoyTexto}.
Respondé en español rioplatense cordial, directo y bien específico.

REGLAS ESTRICTAS DE PRECISIÓN:
1. Respondé exactamente lo que pregunta el usuario, dando todos los detalles disponibles (título, fecha, hora, lugar y contenido/descripción).
2. Si te preguntan por una FECHA específica (ej: "19/11/2026", "19 de noviembre", "qué hay el viernes", "noticias del 15/11"), buscá en EVENTOS PRÓXIMOS, EVENTOS YA REALIZADOS y NOTICIAS PUBLICADAS todo lo que coincida con esa fecha y detallalo con título, horario, lugar y descripción/contenido. Si no hay nada registrado para esa fecha, indicalo con claridad.
3. Si te preguntan por "la última noticia", mencioná específicamente la marcada como [MÁS RECIENTE] con su fecha y explicá de qué trata según su contenido.
4. Si te preguntan por "el último evento que se hizo" o eventos pasados, fijate en EVENTOS YA REALIZADOS y respondé el más reciente con su fecha y detalle. Si esa lista está vacía, aclaralo y comentá cuál es el próximo evento programado.
5. Nunca inventes información que no esté en los datos de abajo.
6. ÚNICAMENTE cuando NO tengas la respuesta en los datos de abajo (por ejemplo: notas de un alumno, vacantes exactas, hablar con una maestra) o si el usuario pide contacto humano, preguntá amablemente si pudo resolver sus dudas o si prefiere comunicarse con personal de la escuela y agregá [DERIVAR] al final. Si pudiste responder con los datos de la escuela, NO agregues [DERIVAR].

DATOS DE LA INSTITUCIÓN:
{$datosEscuela}

PREGUNTAS FRECUENTES:
{$faqs}

EVENTOS YA REALIZADOS (DEL MÁS RECIENTE AL MÁS ANTIGUO):
{$eventosPasados}

EVENTOS PRÓXIMOS EN EL CALENDARIO ESCOLAR:
{$eventosProximos}

NOTICIAS PUBLICADAS (ORDENADAS DE LA MÁS RECIENTE A LA MÁS ANTIGUA):
{$noticias}
TXT;
    }

    private function responderDesdeContextoLocal(string $mensaje): string
    {
        $texto = mb_strtolower($mensaje);

        try {
            // 1. Si pregunta por una fecha puntual (ej: 19/11/2026, 19-11, 19 de noviembre)
            $fechaBuscada = $this->extraerFechaDeTexto($texto);
            if ($fechaBuscada) {
                $fechaFormateada = $fechaBuscada->format('d/m/Y');
                $fechaSql = $fechaBuscada->toDateString();

                $eventosFecha = Evento::whereDate('fecha', $fechaSql)->get();
                $noticiasFecha = Publicacion::where('estado', 'publicado')
                    ->whereDate('fecha_publicacion', $fechaSql)
                    ->get();

                $partes = [];

                if ($eventosFecha->isNotEmpty()) {
                    $listaEv = $eventosFecha->map(function ($e) {
                        $hora = !empty($e->hora_inicio) ? " ({$e->hora_inicio} hs)" : '';
                        $lugar = !empty($e->lugar) ? " en {$e->lugar}" : '';
                        $desc = !empty($e->descripcion) ? ": " . trim(strip_tags($e->descripcion)) : '';
                        return "• Evento: \"{$e->titulo}\"{$hora}{$lugar}{$desc}";
                    })->implode("\n");
                    $partes[] = $listaEv;
                }

                if ($noticiasFecha->isNotEmpty()) {
                    $listaNot = $noticiasFecha->map(function ($n) {
                        $cuerpo = trim(strip_tags($n->contenido ?? $n->descripcion ?? ''));
                        return "• Noticia: \"{$n->titulo}\"" . ($cuerpo ? ": {$cuerpo}" : '');
                    })->implode("\n");
                    $partes[] = $listaNot;
                }

                if (!empty($partes)) {
                    return "Para el {$fechaFormateada} encontramos lo siguiente:\n" . implode("\n", $partes);
                }

                return "No hay eventos ni noticias registradas para el {$fechaFormateada}.";
            }

            // 2. Si pregunta por el último evento realizado
            if ((str_contains($texto, 'ultimo') || str_contains($texto, 'último') || str_contains($texto, 'hizo') || str_contains($texto, 'pasado'))
                && str_contains($texto, 'evento')
            ) {
                $ultimoPasado = Evento::whereDate('fecha', '<', now()->toDateString())
                    ->orderByDesc('fecha')
                    ->first();

                if ($ultimoPasado) {
                    $fecha = Carbon::parse($ultimoPasado->fecha)->format('d/m/Y');
                    $desc = !empty($ultimoPasado->descripcion) ? "\nDetalle: " . strip_tags($ultimoPasado->descripcion) : '';
                    return "El último evento realizado fue \"{$ultimoPasado->titulo}\" el {$fecha}.{$desc}";
                }

                $proximo = Evento::whereDate('fecha', '>=', now()->toDateString())->orderBy('fecha')->first();
                return $proximo
                    ? "Todavía no hay eventos pasados registrados; el próximo evento programado es \"{$proximo->titulo}\" el " . Carbon::parse($proximo->fecha)->format('d/m/Y') . "."
                    : "No hay eventos registrados en el calendario en este momento.";
            }

            // 3. Si pregunta por la última noticia
            if ((str_contains($texto, 'ultima') || str_contains($texto, 'última')) && str_contains($texto, 'noticia')) {
                $ultima = Publicacion::where('estado', 'publicado')->latest('fecha_publicacion')->first();
                if ($ultima) {
                    $fecha = $ultima->fecha_publicacion ? ' (' . Carbon::parse($ultima->fecha_publicacion)->format('d/m/Y') . ')' : '';
                    $cuerpo = trim(strip_tags($ultima->contenido ?? $ultima->descripcion ?? ''));
                    return "La última noticia publicada{$fecha} es \"{$ultima->titulo}\"" . ($cuerpo ? ":\n{$cuerpo}" : '.');
                }
            }

            // 4. Preguntas frecuentes por palabras clave
            $faqs = PreguntaFrecuente::where('activo', true)->get();
            foreach ($faqs as $faq) {
                $claves = array_filter(array_map('trim', explode(',', mb_strtolower($faq->palabras_clave ?? ''))));
                foreach ($claves as $palabra) {
                    if ($palabra !== '' && str_contains($texto, $palabra)) {
                        return $faq->respuesta;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return 'Soy el asistente de la E.P.E.P. N° 436 "Simón Bolívar" (Formosa Capital). Podés consultarme por horarios, fechas específicas, noticias recientes o eventos del calendario escolar.';
    }

    private function extraerFechaDeTexto(string $texto): ?Carbon
    {
        if (str_contains($texto, 'hoy')) {
            return now();
        }
        if (str_contains($texto, 'mañana') && !str_contains($texto, 'turno mañana')) {
            return now()->addDay();
        }

        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?\b/', $texto, $m)) {
            $dia = (int) $m[1];
            $mes = (int) $m[2];
            $anio = !empty($m[3]) ? (strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3]) : now()->year;
            if (checkdate($mes, $dia, $anio)) {
                return Carbon::create($anio, $mes, $dia);
            }
        }

        $meses = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'setiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
        ];

        if (preg_match('/\b(\d{1,2})\s+de\s+([a-záéíóú]+)(?:\s+de[l]?\s+(\d{4}))?\b/u', $texto, $m)) {
            $dia = (int) $m[1];
            $mesNombre = $m[2];
            $anio = !empty($m[3]) ? (int) $m[3] : now()->year;
            if (isset($meses[$mesNombre]) && checkdate($meses[$mesNombre], $dia, $anio)) {
                return Carbon::create($anio, $meses[$mesNombre], $dia);
            }
        }

        return null;
    }

    private function consultarGemini(string $apiKey, string $modelo, string $contexto, string $mensaje, array $historial): ?string
    {
        try {
            $contents = [];
            foreach ($historial as $item) {
                if (!empty($item['texto'])) {
                    $contents[] = [
                        'role'  => ($item['de'] ?? 'bot') === 'usuario' ? 'user' : 'model',
                        'parts' => [['text' => strip_tags($item['texto'])]],
                    ];
                }
            }
            $contents[] = [
                'role'  => 'user',
                'parts' => [['text' => $mensaje]],
            ];

            $response = Http::withoutVerifying()->timeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}",
                [
                    'systemInstruction' => [
                        'parts' => [['text' => $contexto]],
                    ],
                    'contents' => $contents,
                ]
            );

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error('Error Gemini API: ' . $response->status() . ' - ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('Excepción Gemini API: ' . $e->getMessage());
        }

        return null;
    }

    public function derivar(Request $request)
    {
        $data = $request->validate([
            'nombre_consultante' => 'required|string|max:120',
            'contacto'           => 'required|string|max:150',
            'asunto'             => 'nullable|string|max:200',
            'contenido'          => 'required|string|max:2000',
        ]);

        try {
            $institucion = Institucion::first() ?? Institucion::create([
                'nombre'    => 'E.P.E.P. N° 436 "Simón Bolívar"',
                'direccion' => 'Formosa Capital, Formosa',
            ]);

            $docenteAsignado = Usuario::whereHas('rol', function ($query) {
                $query->whereRaw('LOWER(nombre) = ?', ['docente']);
            })
                ->withCount(['consultasAsignadas' => function ($query) {
                    $query->whereIn('estado', ['pendiente', 'en_atencion']);
                }])
                ->orderBy('consultas_asignadas_count', 'asc')
                ->inRandomOrder()
                ->first();

            do {
                $codigo = 'ESI-' . random_int(1000, 9999);
            } while (Consulta::where('codigo_seguimiento', $codigo)->exists());

            $consulta = Consulta::create([
                'fk_id_institucion'  => $institucion->id_institucion,
                'fk_id_usuario'      => $docenteAsignado?->id_usuario,
                'nombre_consultante' => trim($data['nombre_consultante']),
                'contacto'           => trim($data['contacto']),
                'asunto'             => trim($data['asunto'] ?? 'Consulta desde el portal'),
                'estado'             => 'pendiente',
                'codigo_seguimiento' => $codigo,
                'fecha'              => now(),
            ]);

            MensajeConsulta::create([
                'fk_id_consulta' => $consulta->id_consulta,
                'fk_id_usuario'  => null,
                'emisor'         => 'familia',
                'contenido'      => trim($data['contenido']),
                'fecha'          => now(),
            ]);

            if ($docenteAsignado && class_exists(Auditoria::class)) {
                Auditoria::registrar(
                    $docenteAsignado->id_usuario,
                    'asignacion_automatica_consulta',
                    'consulta',
                    $consulta->id_consulta,
                    "Consulta {$codigo} delegada automáticamente a Prof. {$docenteAsignado->nombre} {$docenteAsignado->apellido}."
                );
            }

            return response()->json([
                'ok'         => true,
                'codigo'     => $consulta->codigo_seguimiento,
                'asignado_a' => $docenteAsignado
                    ? "Prof. {$docenteAsignado->nombre} {$docenteAsignado->apellido}"
                    : 'Equipo Docente',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en ChatBotController@derivar: ' . $e->getMessage());

            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function seguimiento(string $codigo)
    {
        $consulta = Consulta::with(['docenteAsignado', 'mensajes.usuario'])
            ->where('codigo_seguimiento', strtoupper(trim($codigo)))
            ->first();

        if (!$consulta) {
            return response()->json(['encontrado' => false], 404);
        }

        return response()->json([
            'encontrado'  => true,
            'codigo'      => $consulta->codigo_seguimiento,
            'asunto'      => $consulta->asunto,
            'estado'      => $consulta->estado,
            'consultante' => $consulta->nombre_consultante,
            'responsable' => $consulta->docenteAsignado
                ? "Prof. {$consulta->docenteAsignado->nombre} {$consulta->docenteAsignado->apellido}"
                : 'Equipo Docente',
            'mensajes'    => $consulta->mensajes
                ->filter(fn($m) => $m->contenido !== '(Iniciando consulta con docente)')
                ->values()
                ->map(fn($m) => [
                    'id'        => $m->id_mensaje_consulta,
                    'emisor'    => $m->emisor,
                    'autor'     => $m->emisor === 'docente' && $m->usuario
                        ? "Prof. {$m->usuario->nombre} {$m->usuario->apellido}"
                        : $consulta->nombre_consultante,
                    'contenido' => $m->contenido,
                    'fecha'     => $m->fecha->format('d/m/Y H:i'),
                ]),
        ]);
    }

    public function responderFamilia(Request $request, string $codigo)
    {
        $data = $request->validate([
            'contenido' => 'required|string|max:2000',
        ]);

        try {
            $consulta = Consulta::where('codigo_seguimiento', strtoupper(trim($codigo)))->first();

            if (!$consulta) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No se encontró la consulta con ese código de seguimiento.',
                ], 404);
            }

            if ($consulta->estado === 'cerrada') {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'Esta consulta ya fue cerrada. Si necesitás hacer otra pregunta, iniciá una nueva conversación.',
                ], 422);
            }

            $mensaje = MensajeConsulta::create([
                'fk_id_consulta' => $consulta->id_consulta,
                'fk_id_usuario'  => null,
                'emisor'         => 'familia',
                'contenido'      => trim($data['contenido']),
                'fecha'          => now(),
            ]);

            $consulta->update(['estado' => 'pendiente']);

            return response()->json([
                'ok'      => true,
                'mensaje' => [
                    'id'        => $mensaje->id_mensaje_consulta,
                    'emisor'    => 'familia',
                    'autor'     => $consulta->nombre_consultante,
                    'contenido' => $mensaje->contenido,
                    'fecha'     => $mensaje->fecha->format('d/m/Y H:i'),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en ChatBotController@responderFamilia: ' . $e->getMessage());

            return response()->json([
                'ok'      => false,
                'mensaje' => 'Error interno al guardar el mensaje: ' . $e->getMessage(),
            ], 500);
        }
    }
}