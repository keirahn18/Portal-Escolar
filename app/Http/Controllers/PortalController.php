<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\Publicacion;
use App\Models\CatPublicacion;
use App\Models\Evento;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Página principal pública del Portal Escolar.
     */
    public function index(Request $request)
    {
        $institucion = Institucion::first();
        $categorias = CatPublicacion::all();

        $query = Publicacion::with(['categoria', 'bloques', 'usuario'])
            ->where('estado', 'publicado')
            ->orderBy('fecha_publicacion', 'desc');

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('fk_id_cat_publicacion', $request->categoria);
        }

        // Buscador por título o contenido de los bloques
        if ($request->filled('buscar')) {
            $termino = trim($request->buscar);
            $query->where(function ($q) use ($termino) {
                $q->where('titulo', 'like', "%{$termino}%")
                  ->orWhereHas('bloques', function ($b) use ($termino) {
                      $b->where('tipo', 'texto')
                        ->where('contenido_texto', 'like', "%{$termino}%");
                  });
            });
        }

        // Paginación de 6 noticias por página conservando filtros y ancla #noticias
        $publicaciones = $query->paginate(6)
            ->withQueryString()
            ->fragment('noticias');

        // Próximos 4 eventos externos activos para el resumen lateral del calendario
        $proximosEventos = Evento::with('categoria')
            ->whereHas('tipo', function ($q) {
                $q->where('tipo', 'Externo');
            })
            ->where('estado', 'activo')
            ->whereDate('fecha', '>=', now()->toDateString())
            ->orderBy('fecha', 'asc')
            ->take(4)
            ->get();

        return view('portal.index', compact('institucion', 'categorias', 'publicaciones', 'proximosEventos'));
    }

    /**
     * Vista de lectura completa de una noticia publicada.
     */
    public function showNoticia($id)
    {
        $institucion = Institucion::first();
        $publicacion = Publicacion::with(['categoria', 'bloques', 'usuario'])
            ->where('estado', 'publicado')
            ->findOrFail($id);

        return view('portal.noticia', compact('institucion', 'publicacion'));
    }

    /**
     * Feed JSON público para FullCalendar (SOLO eventos Externos activos).
     */
    public function eventosFeed()
    {
        $eventos = Evento::with(['categoria', 'tipo'])
            ->whereHas('tipo', function ($q) {
                $q->where('tipo', 'Externo');
            })
            ->where('estado', 'activo')
            ->get()
            ->map(function ($evento) {
                $start = $evento->fecha->format('Y-m-d');
                if ($evento->hora_inicio) {
                    $start .= 'T' . $evento->hora_inicio;
                }

                $end = null;
                if ($evento->hora_fin) {
                    $end = $evento->fecha->format('Y-m-d') . 'T' . $evento->hora_fin;
                }

                return [
                    'id' => $evento->id_evento,
                    'title' => $evento->titulo,
                    'start' => $start,
                    'end' => $end,
                    'backgroundColor' => $evento->tipo->color_hex ?? '#0284c7',
                    'borderColor' => $evento->tipo->color_hex ?? '#0284c7',
                    'extendedProps' => [
                        'descripcion' => $evento->descripcion,
                        'lugar' => $evento->lugar,
                        'categoria' => $evento->categoria->nombre,
                        'fecha_formateada' => $evento->fecha->format('d/m/Y'),
                        'hora_inicio' => $evento->hora_inicio ? substr($evento->hora_inicio, 0, 5) : null,
                        'hora_fin' => $evento->hora_fin ? substr($evento->hora_fin, 0, 5) : null,
                    ],
                ];
            });

        return response()->json($eventos);
    }
}