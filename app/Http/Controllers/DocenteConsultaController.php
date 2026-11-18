<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Consulta;
use App\Models\MensajeConsulta;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocenteConsultaController extends Controller
{
    /**
     * Vista principal de la bandeja de chats asignados al docente.
     */
    public function index()
    {
        $usuario = Auth::user();

        $consultas = $this->queryConsultas($usuario)
            ->with(['mensajes' => fn($q) => $q->latest('fecha')])
            ->orderByRaw("FIELD(estado, 'pendiente', 'en_atencion', 'cerrada')")
            ->orderByDesc('fecha')
            ->get();

        // Todos los usuarios/docentes disponibles para derivar (sin exigir filtro de activo)
        $docentes = Usuario::where('id_usuario', '!=', $usuario->id_usuario)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get(['id_usuario', 'nombre', 'apellido']);

        return view('panel.consultas.index', compact('consultas', 'docentes'));
    }

    /**
     * Endpoint JSON para actualizar la lista de chats en tiempo real.
     */
    public function feed()
    {
        $usuario = Auth::user();

        $consultas = $this->queryConsultas($usuario)
            ->with(['mensajes'])
            ->orderByRaw("FIELD(estado, 'pendiente', 'en_atencion', 'cerrada')")
            ->orderByDesc('fecha')
            ->get()
            ->map(function ($c) {
                $ultimo = $c->mensajes->last();
                return [
                    'id_consulta'        => $c->id_consulta,
                    'codigo_seguimiento' => $c->codigo_seguimiento,
                    'nombre_consultante' => $c->nombre_consultante,
                    'contacto'           => $c->contacto,
                    'asunto'             => $c->asunto,
                    'estado'             => $c->estado,
                    'ultimo_mensaje'     => $ultimo ? $ultimo->contenido : '',
                    'ultima_hora'        => $ultimo ? $ultimo->fecha->format('d/m H:i') : $c->fecha->format('d/m H:i'),
                    'sin_responder'      => $c->estado === 'pendiente',
                ];
            });

        return response()->json(['consultas' => $consultas]);
    }

    /**
     * Devuelve el hilo completo de mensajes de una consulta específica (polling en tiempo real).
     */
    public function mensajes(Consulta $consulta)
    {
        $this->autorizarAcceso($consulta);

        $consulta->load(['mensajes.usuario', 'docenteAsignado']);

        return response()->json([
            'id_consulta'        => $consulta->id_consulta,
            'codigo_seguimiento' => $consulta->codigo_seguimiento,
            'nombre_consultante' => $consulta->nombre_consultante,
            'contacto'           => $consulta->contacto,
            'asunto'             => $consulta->asunto,
            'estado'             => $consulta->estado,
            'mensajes'           => $consulta->mensajes
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

    /**
     * Guarda la respuesta del docente en el hilo.
     */
    public function responder(Request $request, Consulta $consulta)
    {
        $this->autorizarAcceso($consulta);

        $data = $request->validate([
            'contenido' => 'required|string|max:2000',
        ]);

        if ($consulta->estado === 'cerrada') {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Esta consulta ya está cerrada.',
            ], 422);
        }

        $docente = Auth::user();

        $mensaje = MensajeConsulta::create([
            'fk_id_consulta' => $consulta->id_consulta,
            'fk_id_usuario'  => $docente->id_usuario,
            'emisor'         => 'docente',
            'contenido'      => trim($data['contenido']),
            'fecha'          => now(),
        ]);

        $consulta->update(['estado' => 'en_atencion']);

        if (class_exists(Auditoria::class)) {
            Auditoria::registrar(
                $docente->id_usuario,
                'respuesta_consulta',
                'consulta',
                $consulta->id_consulta,
                "Respuesta enviada en consulta {$consulta->codigo_seguimiento}."
            );
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => [
                'id'        => $mensaje->id_mensaje_consulta,
                'emisor'    => 'docente',
                'autor'     => "Prof. {$docente->nombre} {$docente->apellido}",
                'contenido' => $mensaje->contenido,
                'fecha'     => $mensaje->fecha->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Deriva la consulta a otro docente registrado en el sistema.
     */
    public function derivar(Request $request, Consulta $consulta)
    {
        $this->autorizarAcceso($consulta);

        if ($consulta->estado === 'cerrada') {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No se puede derivar una consulta que ya está cerrada.',
            ], 422);
        }

        $data = $request->validate([
            'fk_id_usuario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'motivo'        => ['nullable', 'string', 'max:255'],
        ]);

        if ((int) $data['fk_id_usuario'] === (int) $consulta->fk_id_usuario) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'La consulta ya está asignada a ese docente.',
            ], 422);
        }

        $docenteActual = Auth::user();
        $nuevoDocente  = Usuario::findOrFail($data['fk_id_usuario']);

        $nombreActual = trim("{$docenteActual->nombre} {$docenteActual->apellido}");
        $nombreNuevo  = trim("{$nuevoDocente->nombre} {$nuevoDocente->apellido}");

        $consulta->update([
            'fk_id_usuario' => $nuevoDocente->id_usuario,
            'estado'        => 'pendiente',
        ]);

        $detalleMotivo = !empty($data['motivo']) ? " Motivo: " . trim($data['motivo']) : '';

        MensajeConsulta::create([
            'fk_id_consulta' => $consulta->id_consulta,
            'fk_id_usuario'  => $docenteActual->id_usuario,
            'emisor'         => 'docente',
            'contenido'      => "🔄 Consulta derivada por Prof. {$nombreActual} a Prof. {$nombreNuevo}.{$detalleMotivo}",
            'fecha'          => now(),
        ]);

        if (class_exists(Auditoria::class)) {
            Auditoria::registrar(
                $docenteActual->id_usuario,
                'derivacion_consulta',
                'consulta',
                $consulta->id_consulta,
                "Consulta {$consulta->codigo_seguimiento} derivada a Prof. {$nombreNuevo}."
            );
        }

        return response()->json([
            'ok'          => true,
            'asignado_a'  => "Prof. {$nombreNuevo}",
            'id_asignado' => $nuevoDocente->id_usuario,
        ]);
    }

    /**
     * Marca la consulta como cerrada.
     */
    public function cerrar(Consulta $consulta)
    {
        $this->autorizarAcceso($consulta);

        $consulta->update(['estado' => 'cerrada']);

        return response()->json(['ok' => true]);
    }

    private function queryConsultas($usuario)
    {
        // Si es admin/directivo puede ver todas; si es docente ve las asignadas a él
        if (method_exists($usuario, 'esAdmin') && $usuario->esAdmin()) {
            return Consulta::query();
        }

        return Consulta::where('fk_id_usuario', $usuario->id_usuario);
    }

    private function autorizarAcceso(Consulta $consulta): void
    {
        $usuario = Auth::user();
        $esAdmin = method_exists($usuario, 'esAdmin') && $usuario->esAdmin();

        if (!$esAdmin && (int) $consulta->fk_id_usuario !== (int) $usuario->id_usuario) {
            abort(403, 'No tenés permiso para acceder a esta consulta.');
        }
    }
}