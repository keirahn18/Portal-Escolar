<?php

namespace App\Notifications;

use App\Models\Evento;
use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class EventoAsignadoDocente extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public Evento $evento;

    public function __construct(Evento $evento)
    {
        $this->evento = $evento;
    }

    /**
     * Guarda en BD (bandeja de entrada) Y transmite por WebSocket (en vivo).
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'evento_id' => $this->evento->id_evento,
            'titulo' => $this->evento->titulo,
            'fecha' => $this->evento->fecha->format('d/m/Y'),
            'hora_inicio' => $this->evento->hora_inicio ? substr($this->evento->hora_inicio, 0, 5) : null,
            'lugar' => $this->evento->lugar,
            'mensaje' => "Se te asignó como docente encargado/a del evento interno: \"{$this->evento->titulo}\" para el {$this->evento->fecha->format('d/m/Y')}.",
            'url' => route('admin.eventos.index'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('docente.' . $this->evento->fk_docente_encargado),
        ];
    }
}