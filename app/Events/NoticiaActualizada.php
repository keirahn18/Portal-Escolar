<?php

namespace App\Events;

use App\Models\Publicacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NoticiaActualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $noticia;
    public string $accion;

    public function __construct(Publicacion $publicacion, string $accion = 'publicada')
    {
        $publicacion->loadMissing(['categoria', 'bloques']);

        $imagenes = $publicacion->bloques->where('tipo', 'imagen');
        $primerTexto = $publicacion->bloques->where('tipo', 'texto')->first();

        $this->accion = $accion;
        $this->noticia = [
            'id' => $publicacion->id_publicacion,
            'titulo' => $publicacion->titulo,
            'categoria_id' => $publicacion->fk_id_cat_publicacion,
            'categoria' => $publicacion->categoria->nombre ?? 'Institucional',
            'estado' => $publicacion->estado,
            'fecha' => $publicacion->fecha_publicacion->format('d/m/Y'),
            'extracto' => $primerTexto
                ? Str::limit($primerTexto->contenido_texto, 140)
                : 'Hacé clic para ver el detalle completo de esta publicación.',
            'imagen_portada' => $imagenes->isNotEmpty() ? asset($imagenes->first()->ruta_img) : null,
            'fotos_extra' => max(0, $imagenes->count() - 1),
            'logo_fallback' => asset('images/logo.png'),
            'url' => route('portal.noticia', $publicacion->id_publicacion),
        ];
    }

    public function broadcastOn(): array
    {
        if ($this->accion === 'borrador' || ($this->accion !== 'eliminada' && ($this->noticia['estado'] ?? '') !== 'publicado')) {
            return [
                new PrivateChannel('panel-interno'),
            ];
        }

        return [
            new Channel('portal-escolar'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'noticia.actualizada';
    }
}