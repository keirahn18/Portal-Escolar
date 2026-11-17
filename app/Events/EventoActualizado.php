<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventoActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $accion;

    public function __construct(string $accion = 'actualizado')
    {
        $this->accion = $accion;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('portal-escolar'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'evento.actualizado';
    }
}