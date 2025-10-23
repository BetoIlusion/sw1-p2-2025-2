<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ColaboradorAgregado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $diagramaId;
    public $colaboradorId;
    public $colaboradorNombre;

    /**
     * Create a new event instance.
     */
    public function __construct($diagramaId, $colaboradorId, $colaboradorNombre)
    {
        $this->diagramaId = $diagramaId;
        $this->colaboradorId = $colaboradorId;
        $this->colaboradorNombre = $colaboradorNombre;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [new Channel('diagramas')];
    }

    public function broadcastAs(): string
    {
        return 'colaborador.agregado';
    }
}