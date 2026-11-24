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
     * Vista principal de la bandeja de chats.
     */
    public function index()
    {
        $usuario = Auth::user();$esAdmin = $this->esAdministrador($usuario);

        $consultas = $this->queryConsultas($usuario)
            ->with(['mensajes' => fn($q) =>$q->latest('fecha'), 'docenteAsignado'])
            ->orderByRaw("FIELD(estado, 'pendiente', 'en_atencion', 'cerrada')")
            ->orderByDesc('fecha')
            ->get();

        // Docentes disponibles para derivación (excluye al usuario actual y a administradores)
        $docentes = Usuario::where('id_usuario', '!=',$usuario->id_usuario)
            ->whereHas('rol', function ($q) {$q->whereRaw('LOWER(nombre) NOT IN (?, ?, ?, ?, ?)', [
                    'admin', 'administrador', 'directivo', 'director', 'directora'
                ]);
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get(['id_usuario', 'nombre', 'apellido']);

        return view('panel.consultas.index', compact('consultas', 'docentes', 'esAdmin'));
    }

    /**
     * Endpoint JSON para actualizar la lista de chats en tiempo real.
     */
    public function feed()
    {
        $usuario = Auth::user();

        $consultas = $this->queryConsultas($usuario)
            ->with(['mensajes', 'docenteAsignado'])
            ->orderByRaw("FIELD(estado, 'pendiente', 'en_atencion', 'cerrada')")
            ->orderByDesc('fecha')
            ->get()
            ->map(function ($c) {
                $ultimo =$c->mensajes->last();
                return [
                    'id_consulta'        => $c->id_consulta,
                    'codigo_seguimiento' => $c->codigo_seguimiento,
                    'nombre_consultante' => $c->nombre_consultante,
                    'contacto'           => $c->contacto,
                    'asunto'             => $c->asunto,
                    'estado'             => $c->estado,
                    'docente_asignado'   => $c->docenteAsignado
                        ? "Prof. {$c->docenteAsignado->nombre} {$c->docenteAsignado->apellido}"
                        : 'Sin asignar',
                    'ultimo_mensaje'     => $ultimo ? $ultimo->contenido : '',
                    'ultima_hora'        => $ultimo ? $ultimo->fecha->format('d/m H:i') :$c->fecha->format('d/m H:i'),
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
        $this->autorizarLectura($consulta);

        $consulta->load(['mensajes.usuario', 'docenteAsignado']);
        $usuario = Auth::user();$esAdmin = $this->esAdministrador($usuario);

        return response()->json([
            'id_consulta'        => $consulta->id_consulta,
            'codigo_seguimiento' => $consulta->codigo_seguimiento,
            'nombre_consultante' => $consulta->nombre_consultante,
            'contacto'           => $consulta->contacto,
            'asunto'             => $consulta->asunto,
            'estado'             => $consulta->estado,
            'docente_asignado'   => $consulta->docenteAsignado
                ? "Prof. {$consulta->docenteAsignado->nombre} {$consulta->docenteAsignado->apellido}"
                : 'Sin asignar',
            'puede_gestionar'    => !$esAdmin && (int) $consulta->fk_id_usuario === (int)$usuario->id_usuario,
            'mensajes'           => $consulta->mensajes
                ->filter(fn($m) =>$m->contenido !== '(Iniciando consulta con docente)')
                ->values()
                ->map(fn($m) => [
                    'id'        => $m->id_mensaje_consulta,
                    'emisor'    => $m->emisor,
                    'autor'     => $m->emisor === 'docente' &&$m->usuario
                        ? "Prof. {$m->usuario->nombre} {$m->usuario->apellido}"
                        : $consulta->nombre_consultante,
                    'contenido' => $m->contenido,
                    'fecha'     => $m->fecha->format('d/m/Y H:i'),
                ]),
        ]);
    }

    /**
     * Guarda la respuesta del docente asignado en el hilo.
     */
    public function responder(Request $request, Consulta$consulta)
    {
        $this->autorizarGestion($consulta);

        $data =$request->validate([
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
    public function derivar(Request $request, Consulta$consulta)
    {
        $this->autorizarGestion($consulta);

        if ($consulta->estado === 'cerrada') {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No se puede derivar una consulta que ya está cerrada.',
            ], 422);
        }

        $data =$request->validate([
            'fk_id_usuario' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'motivo'        => ['nullable', 'string', 'max:255'],
        ]);

        if ((int) $data['fk_id_usuario'] === (int)$consulta->fk_id_usuario) {
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
        $this->autorizarGestion($consulta);

        $consulta->update(['estado' => 'cerrada']);

        return response()->json(['ok' => true]);
    }

    private function esAdministrador($usuario): bool
    {
        if (!$usuario) {
            return false;
        }

        if (method_exists($usuario, 'esAdmin') &&$usuario->esAdmin()) {
            return true;
        }

        $nombreRol = strtolower(trim($usuario->rol->nombre ?? $usuario->role ?? ''));
        return in_array($nombreRol, ['admin', 'administrador', 'directivo', 'director', 'directora']);
    }

    private function queryConsultas($usuario)
    {
        if ($this->esAdministrador($usuario)) {
            return Consulta::query();
        }

        return Consulta::where('fk_id_usuario', $usuario->id_usuario);
    }

    private function autorizarLectura(Consulta $consulta): void
    {
        $usuario = Auth::user();

        if (!$this->esAdministrador($usuario) && (int) $consulta->fk_id_usuario !== (int)$usuario->id_usuario) {
            abort(403, 'No tenés permiso para ver esta consulta.');
        }
    }

    private function autorizarGestion(Consulta $consulta): void
    {
        $usuario = Auth::user();

        if ($this->esAdministrador($usuario) || (int) $consulta->fk_id_usuario !== (int)$usuario->id_usuario) {
            abort(403, 'Solo el docente asignado a esta consulta puede responder, derivar o cerrarla.');
        }
    }
}