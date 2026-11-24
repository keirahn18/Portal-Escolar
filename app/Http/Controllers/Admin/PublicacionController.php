<?php

namespace App\Http\Controllers\Admin;

use App\Events\NoticiaActualizada;
use App\Http\Controllers\Controller;
use App\Models\Publicacion;
use App\Models\PublicacionBloque;
use App\Models\CatPublicacion;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicacionController extends Controller
{
    public function index()
    {
        $publicaciones = Publicacion::with(['usuario', 'categoria', 'bloques'])
            ->orderBy('fecha_publicacion', 'desc')
            ->get();

        return view('admin.publicaciones.index', compact('publicaciones'));
    }

    public function show($id)
    {
        $publicacion = Publicacion::with(['usuario', 'categoria', 'bloques'])->findOrFail($id);
        return view('admin.publicaciones.show', compact('publicacion'));
    }

    public function create()
    {
        $categorias = CatPublicacion::all();
        return view('admin.publicaciones.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'fk_id_cat_publicacion' => 'required|exists:cat_publicacions,id_cat_publicacion',
            'estado' => 'required|in:publicado,borrador',
            'bloques' => 'required|array|min:1',
            'bloques.*.tipo' => 'required|in:texto,imagen',
            'bloques.*.texto' => 'required_if:bloques.*.tipo,texto|nullable|string',
            'bloques.*.imagen' => 'required_if:bloques.*.tipo,imagen|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'titulo.required' => 'El título de la noticia es obligatorio.',
            'bloques.required' => 'Debés agregar al menos un bloque de contenido o imagen.',
            'bloques.*.texto.required_if' => 'Los bloques de texto no pueden quedar vacíos.',
            'bloques.*.imagen.required_if' => 'Debés seleccionar una imagen para cada bloque de foto.',
            'bloques.*.imagen.max' => 'Cada imagen no puede superar los 2MB.',
        ]);

        $institucion = Institucion::first();

        $publicacion = Publicacion::create([
            'fk_usuario' => auth()->id(),
            'fk_id_cat_publicacion' => $request->fk_id_cat_publicacion,
            'fk_id_institucion' => $institucion->id_institucion,
            'titulo' => trim($request->titulo),
            'estado' => $request->estado,
            'fecha_publicacion' => now(),
        ]);

        $orden = 1;
        foreach ($request->bloques as $index => $bloqueData) {
            if ($bloqueData['tipo'] === 'texto') {
                PublicacionBloque::create([
                    'fk_id_publicacion' => $publicacion->id_publicacion,
                    'tipo' => 'texto',
                    'contenido_texto' => trim($bloqueData['texto']),
                    'orden' => $orden++,
                ]);
            } elseif ($bloqueData['tipo'] === 'imagen' && $request->hasFile("bloques.$index.imagen")) {
                $path = $request->file("bloques.$index.imagen")->store('publicaciones', 'public');
                PublicacionBloque::create([
                    'fk_id_publicacion' => $publicacion->id_publicacion,
                    'tipo' => 'imagen',
                    'ruta_img' => Storage::url($path),
                    'orden' => $orden++,
                ]);
            }
        }

        // Transmitir en tiempo real al portal público (si está publicada) y al panel interno
        $publicacion->load(['categoria', 'bloques']);
        $accion = $publicacion->estado === 'publicado' ? 'publicada' : 'borrador';
        event(new NoticiaActualizada($publicacion, $accion));

        return redirect()->route('admin.publicaciones.index')
            ->with('status', '¡Noticia publicada con éxito!');
    }

    public function edit($id)
    {
        $publicacion = Publicacion::with('bloques')->findOrFail($id);
        $categorias = CatPublicacion::all();

        return view('admin.publicaciones.edit', compact('publicacion', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $publicacion = Publicacion::findOrFail($id);
        $estadoAnterior = $publicacion->estado;

        $request->validate([
            'titulo' => 'required|string|max:255',
            'fk_id_cat_publicacion' => 'required|exists:cat_publicacions,id_cat_publicacion',
            'estado' => 'required|in:publicado,borrador',
            'bloques' => 'required|array|min:1',
            'bloques.*.tipo' => 'required|in:texto,imagen',
            'bloques.*.texto' => 'required_if:bloques.*.tipo,texto|nullable|string',
            'bloques.*.imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $publicacion->update([
            'titulo' => trim($request->titulo),
            'fk_id_cat_publicacion' => $request->fk_id_cat_publicacion,
            'estado' => $request->estado,
        ]);

        $idsConservados = [];
        $orden = 1;

        foreach ($request->bloques as $index => $bloqueData) {
            $idBloque = $bloqueData['id_bloque'] ?? null;

            if ($idBloque) {
                $bloque = PublicacionBloque::find($idBloque);
                if ($bloque) {
                    $bloque->orden = $orden++;
                    if ($bloque->tipo === 'texto') {
                        $bloque->contenido_texto = trim($bloqueData['texto']);
                    } elseif ($bloque->tipo === 'imagen' && $request->hasFile("bloques.$index.imagen")) {
                        if ($bloque->ruta_img) {
                            Storage::disk('public')->delete(str_replace('/storage/', '', $bloque->ruta_img));
                        }
                        $path = $request->file("bloques.$index.imagen")->store('publicaciones', 'public');
                        $bloque->ruta_img = Storage::url($path);
                    }
                    $bloque->save();
                    $idsConservados[] = $bloque->id_bloque;
                }
            } else {
                if ($bloqueData['tipo'] === 'texto') {
                    $nuevo = PublicacionBloque::create([
                        'fk_id_publicacion' => $publicacion->id_publicacion,
                        'tipo' => 'texto',
                        'contenido_texto' => trim($bloqueData['texto']),
                        'orden' => $orden++,
                    ]);
                    $idsConservados[] = $nuevo->id_bloque;
                } elseif ($bloqueData['tipo'] === 'imagen' && $request->hasFile("bloques.$index.imagen")) {
                    $path = $request->file("bloques.$index.imagen")->store('publicaciones', 'public');
                    $nuevo = PublicacionBloque::create([
                        'fk_id_publicacion' => $publicacion->id_publicacion,
                        'tipo' => 'imagen',
                        'ruta_img' => Storage::url($path),
                        'orden' => $orden++,
                    ]);
                    $idsConservados[] = $nuevo->id_bloque;
                }
            }
        }

        // Eliminar los bloques que fueron quitados en el editor
        $bloquesAEliminar = PublicacionBloque::where('fk_id_publicacion', $publicacion->id_publicacion)
            ->whereNotIn('id_bloque', $idsConservados)
            ->get();

        foreach ($bloquesAEliminar as $b) {
            if ($b->tipo === 'imagen' && $b->ruta_img) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $b->ruta_img));
            }
            $b->delete();
        }

        // Transmitir cambio en tiempo real según su estado
        $publicacion->load(['categoria', 'bloques']);
        if ($publicacion->estado === 'publicado') {
            $accion = $estadoAnterior === 'publicado' ? 'actualizada' : 'publicada';
        } elseif ($estadoAnterior === 'publicado' && $publicacion->estado === 'borrador') {
            $accion = 'eliminada';
        } else {
            $accion = 'borrador';
        }
        event(new NoticiaActualizada($publicacion, $accion));

        return redirect()->route('admin.publicaciones.index')
            ->with('status', '¡Noticia actualizada correctamente!');
    }

    public function destroy($id)
    {
        $publicacion = Publicacion::with(['categoria', 'bloques'])->findOrFail($id);

        event(new NoticiaActualizada($publicacion, 'eliminada'));

        $publicacion->delete();

        return redirect()->route('admin.publicaciones.index')
            ->with('status', '¡Noticia eliminada!');
    }
}