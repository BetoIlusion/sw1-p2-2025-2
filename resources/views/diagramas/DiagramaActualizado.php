<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiagramaActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $diagramaId;
    public $diagramaJson;

    /**
     * Create a new event instance.
     */
    public function __construct($diagramaId, $diagramaJson)
    {
        $this->diagramaId = $diagramaId;
        $this->diagramaJson = $diagramaJson;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('diagrama.' . $this->diagramaId)];
    }

    public function broadcastAs(): string
    {
        return 'diagrama.actualizado';
    }
}