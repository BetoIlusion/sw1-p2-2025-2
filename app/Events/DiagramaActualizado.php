<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiagramaActualizado implements ShouldBroadcastNow // Asegúrate que implemente ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * El contenido actualizado del diagrama.
     *
     * @var string|array
     */
    public $diagramaJson;

    /**
     * El ID del diagrama.
     *
     * @var int
     */
    public $diagramaId;

    public function __construct($diagramaId, $diagramaJson)
    {
        $this->diagramaId = $diagramaId;
        $this->diagramaJson = $diagramaJson;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Debe retornar un array de canales
        return [new PrivateChannel('diagrama.' . $this->diagramaId)];
    }

    /**
     * El nombre con el que se transmitirá el evento.
     */
    public function broadcastAs(): string
    {
        return 'diagrama.actualizado';
    }
}
