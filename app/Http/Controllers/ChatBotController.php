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
            $modelo = config('services.gemini.model', 'gemini-2.5-flash');

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
        $faqs = '';
        try {
            if (class_exists(PreguntaFrecuente::class)) {
                $faqs = PreguntaFrecuente::where('activo', true)->get()
                    ->map(fn($f) => "- {$f->pregunta}: {$f->respuesta}")
                    ->implode("\n");
            }
        } catch (\Throwable $e) {
            $faqs = '';
        }

        $eventos = '';
        try {
            if (class_exists(Evento::class)) {
                $eventos = Evento::whereDate('fecha', '>=', now()->toDateString())
                    ->orderBy('fecha')
                    ->take(6)
                    ->get()
                    ->map(fn($e) => "- " . Carbon::parse($e->fecha)->format('d/m/Y') . ": {$e->titulo}")
                    ->implode("\n");
            }
        } catch (\Throwable $e) {
            $eventos = '';
        }

        $noticias = '';
        try {
            if (class_exists(Publicacion::class)) {
                $noticias = Publicacion::latest()->take(5)->get()
                    ->map(fn($p) => "- {$p->titulo}")
                    ->implode("\n");
            }
        } catch (\Throwable $e) {
            $noticias = '';
        }

        return <<<TXT
Sos el asistente virtual oficial de la Escuela Provincial de Educación Primaria N° 436 "Simón Bolívar", ubicada en Formosa Capital, Provincia de Formosa, Argentina.
Mantené una conversación natural, cálida, empática y variable en español rioplatense. No uses frases robóticas ni repitas siempre lo mismo.

REGLAS DE CONVERSACIÓN Y DERIVACIÓN:
1. Respondé basándote únicamente en la información institucional que tenés abajo y los datos básicos de la escuela (ubicada en Formosa Capital, Provincia de Formosa, nivel primario, turnos mañana y tarde). No inventes nombres de docentes, notas ni vacantes puntuales.
2. Si la persona saluda o hace una consulta general que SÍ podés responder con esta información, respondé de forma natural y conversacional sin cortar la charla.
3. Si detectás que NO podés resolver la consulta (por ejemplo: preguntan por un alumno en particular, notas, faltas, vacantes exactas, entrevistas con un docente, o cualquier dato que no tenés), aclaralo con amabilidad, preguntale si pudiste resolver sus dudas o si prefiere comunicarse con personal docente de la escuela, y agregá exactamente la etiqueta [DERIVAR] al final de tu mensaje.
4. Nunca muestres la etiqueta [DERIVAR] en medio de una oración; ponela siempre al final cuando corresponda ofrecer contacto humano.

INFORMACIÓN INSTITUCIONAL Y PREGUNTAS FRECUENTES:
{$faqs}

PRÓXIMOS EVENTOS DEL CALENDARIO ESCOLAR:
{$eventos}

ÚLTIMAS NOTICIAS PUBLICADAS:
{$noticias}
TXT;
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

    private function responderDesdeContextoLocal(string $mensaje): string
    {
        try {
            $texto = mb_strtolower($mensaje);
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
            // Ignora fallo de tabla local y devuelve respuesta base
        }

        return 'Soy el asistente de la E.P.E.P. N° 436 "Simón Bolívar", ubicada en Formosa Capital. Puedo ayudarte con información sobre horarios, inscripciones, constancias y eventos escolares.';
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
            'mensajes'    => $consulta->mensajes->map(fn($m) => [
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

        $consulta = Consulta::where('codigo_seguimiento', strtoupper(trim($codigo)))->firstOrFail();

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
    }
}