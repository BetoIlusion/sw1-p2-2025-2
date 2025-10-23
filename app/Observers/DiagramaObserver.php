<?php

namespace App\Observers;

use App\Models\Diagrama;
use App\Events\DiagramaActualizado;

class DiagramaObserver
{
    public function created(Diagrama $diagrama)
    {
        //broadcast(new DiagramaActualizado($diagrama->id));
    }

    public function updated(Diagrama $diagrama)
    {
        //broadcast(new DiagramaActualizado($diagrama->id));
    }

    public function deleted(Diagrama $diagrama)
    {
        //broadcast(new DiagramaActualizado($diagrama->id));
    }

    /**
     * Handle the Diagrama "restored" event.
     */
    public function restored(Diagrama $diagrama): void
    {
        //
    }

    /**
     * Handle the Diagrama "force deleted" event.
     */
    public function forceDeleted(Diagrama $diagrama): void
    {
        //
    }
}
