<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('diagrama.{diagramaId}', function (User $user, int $diagramaId) {
    // Verifica si el usuario autenticado tiene acceso a este diagrama.
    return $user->usuarioDiagrama()->where('diagrama_id', $diagramaId)->exists();
});
