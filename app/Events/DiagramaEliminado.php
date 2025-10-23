<?php
// app/Events/DiagramaEliminado.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class DiagramaEliminado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public $diagramaId;
    public $usuarioId;
    public $accion = 'eliminado';

    public function __construct($diagramaId, $usuarioId)
    {
        $this->diagramaId = $diagramaId;
        $this->usuarioId = $usuarioId;
    }

    public function broadcastOn(): array
    {
        return [new Channel('diagramas')];
    }

    public function broadcastAs(): string
    {
        return 'diagrama.eliminado';
    }
}