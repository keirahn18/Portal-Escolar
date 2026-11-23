<?php

namespace App\Http\Controllers\Admin;

use App\Events\EventoActualizado;
use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\CatEvento;
use App\Models\TipoEvento;
use App\Models\Usuario;
use App\Models\Institucion;
use App\Notifications\EventoAsignadoDocente;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        $categorias = CatEvento::all();
        $tipos = TipoEvento::all();
        $tipoExterno = TipoEvento::where('tipo', 'Externo')->first();
        $docentes = Usuario::with('rol')->orderBy('apellido')->get();

        return view('admin.eventos.index', compact('categorias', 'tipos', 'tipoExterno', 'docentes'));
    }

    public function feed()
    {
        $esAdmin = auth()->user()->esAdmin();

        $eventos = Evento::with(['categoria', 'tipo', 'docenteEncargado', 'usuario'])
            ->where('estado', '!=', 'cancelado')
            ->get()
            ->map(function ($evento) use ($esAdmin) {
                $start = $evento->fecha->format('Y-m-d');
                if ($evento->hora_inicio) {
                    $start .= 'T' . $evento->hora_inicio;
                }

                $end = null;
                if ($evento->hora_fin) {
                    $end = $evento->fecha->format('Y-m-d') . 'T' . $evento->hora_fin;
                }

                $esInterno = $evento->tipo->tipo === 'Interno';
                $tituloCalendario = $evento->titulo;
                if ($esInterno && $evento->docenteEncargado) {
                    $tituloCalendario .= ' (Enc: ' . $evento->docenteEncargado->apellido . ')';
                }

                $creadorNombre = $evento->usuario
                    ? $evento->usuario->apellido . ', ' . $evento->usuario->nombre
                    : 'Desconocido';

                return [
                    'id' => $evento->id_evento,
                    'title' => $tituloCalendario,
                    'start' => $start,
                    'end' => $end,
                    'backgroundColor' => $evento->tipo->color_hex,
                    'borderColor' => $evento->tipo->color_hex,
                    'extendedProps' => [
                        'titulo_original' => $evento->titulo,
                        'descripcion' => $evento->descripcion,
                        'lugar' => $evento->lugar,
                        'categoria' => $evento->categoria->nombre,
                        'tipo' => $evento->tipo->tipo,
                        'es_interno' => $esInterno,
                        'puede_editar' => $esAdmin || !$esInterno,
                        'fk_id_cat_evento' => $evento->fk_id_cat_evento,
                        'fk_id_tipo_evento' => $evento->fk_id_tipo_evento,
                        'fk_docente_encargado' => $evento->fk_docente_encargado,
                        'docente_nombre' => $evento->docenteEncargado
                            ? $evento->docenteEncargado->apellido . ', ' . $evento->docenteEncargado->nombre
                            : 'No asignado',
                        'creado_por' => $creadorNombre,
                        'fecha_raw' => $evento->fecha->format('Y-m-d'),
                        'hora_inicio' => $evento->hora_inicio ? substr($evento->hora_inicio, 0, 5) : '',
                        'hora_fin' => $evento->hora_fin ? substr($evento->hora_fin, 0, 5) : '',
                    ],
                ];
            });

        return response()->json($eventos);
    }

    public function store(Request $request)
    {
        $esAdmin = auth()->user()->esAdmin();
        $tipoInterno = TipoEvento::where('tipo', 'Interno')->first();
        $tipoExterno = TipoEvento::where('tipo', 'Externo')->first();

        // Si no es admin, forzamos tipo Externo por seguridad
        $idTipoFinal = $esAdmin ? (int) $request->fk_id_tipo_evento : $tipoExterno->id_tipo_evento;
        $esInterno = $tipoInterno && $idTipoFinal === $tipoInterno->id_tipo_evento;

        $request->validate([
            'titulo' => 'required|string|max:255',
            'fk_id_cat_evento' => 'required|exists:cat_eventos,id_cat_evento',
            'fk_docente_encargado' => $esInterno ? 'required|exists:usuarios,id_usuario' : 'nullable',
            'fecha' => 'required|date',
            'hora_inicio' => 'nullable',
            'hora_fin' => 'nullable',
            'lugar' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ], [
            'fk_docente_encargado.required' => 'Para eventos internos debés seleccionar el docente encargado.',
        ]);

        $institucion = Institucion::first();

        $evento = Evento::create([
            'fk_usuario' => auth()->id(),
            'fk_id_cat_evento' => $request->fk_id_cat_evento,
            'fk_id_tipo_evento' => $idTipoFinal,
            'fk_docente_encargado' => $esInterno ? $request->fk_docente_encargado : null,
            'fk_id_institucion' => $institucion->id_institucion,
            'titulo' => trim($request->titulo),
            'descripcion' => trim($request->descripcion),
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'lugar' => trim($request->lugar),
            'estado' => 'activo',
        ]);

        // Refrescar calendarios en tiempo real (público y panel)
        event(new EventoActualizado('creado'));

        // Notificar al docente encargado (bandeja de entrada + tiempo real)
        if ($esInterno && $evento->fk_docente_encargado) {
            $docente = Usuario::find($evento->fk_docente_encargado);
            if ($docente) {
                $docente->notify(new EventoAsignadoDocente($evento));
            }
        }

        return redirect()->route('admin.eventos.index')->with('status', '¡Evento agendado en el calendario!');
    }

    public function update(Request $request, $id)
    {
        $evento = Evento::with('tipo')->findOrFail($id);
        $esAdmin = auth()->user()->esAdmin();

        // Bloquear edición si el evento es Interno y el usuario no es Admin
        if ($evento->tipo->tipo === 'Interno' && !$esAdmin) {
            abort(403, 'Solo la administración puede editar eventos internos.');
        }

        $docenteAnterior = $evento->fk_docente_encargado;

        $tipoInterno = TipoEvento::where('tipo', 'Interno')->first();
        $tipoExterno = TipoEvento::where('tipo', 'Externo')->first();

        $idTipoFinal = $esAdmin ? (int) $request->fk_id_tipo_evento : $tipoExterno->id_tipo_evento;
        $esInterno = $tipoInterno && $idTipoFinal === $tipoInterno->id_tipo_evento;

        $request->validate([
            'titulo' => 'required|string|max:255',
            'fk_id_cat_evento' => 'required|exists:cat_eventos,id_cat_evento',
            'fk_docente_encargado' => $esInterno ? 'required|exists:usuarios,id_usuario' : 'nullable',
            'fecha' => 'required|date',
            'hora_inicio' => 'nullable',
            'hora_fin' => 'nullable',
            'lugar' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $evento->update([
            'titulo' => trim($request->titulo),
            'fk_id_cat_evento' => $request->fk_id_cat_evento,
            'fk_id_tipo_evento' => $idTipoFinal,
            'fk_docente_encargado' => $esInterno ? $request->fk_docente_encargado : null,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'lugar' => trim($request->lugar),
            'descripcion' => trim($request->descripcion),
        ]);

        // Refrescar calendarios en tiempo real
        event(new EventoActualizado('actualizado'));

        // Notificar solo si es interno y se asignó o cambió el docente encargado
        if ($esInterno && $evento->fk_docente_encargado && (int) $evento->fk_docente_encargado !== (int) $docenteAnterior) {
            $docente = Usuario::find($evento->fk_docente_encargado);
            if ($docente) {
                $docente->notify(new EventoAsignadoDocente($evento));
            }
        }

        return redirect()->route('admin.eventos.index')->with('status', '¡Evento actualizado!');
    }

    public function destroy($id)
    {
        $evento = Evento::with('tipo')->findOrFail($id);

        if ($evento->tipo->tipo === 'Interno' && !auth()->user()->esAdmin()) {
            abort(403, 'Solo la administración puede eliminar eventos internos.');
        }

        $evento->delete();

        // Refrescar calendarios en tiempo real
        event(new EventoActualizado('eliminado'));

        return redirect()->route('admin.eventos.index')->with('status', '¡Evento eliminado del calendario!');
    }
}