<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Diagrama;
use App\Models\User;

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
    $diagrama = Diagrama::find($diagramaId);
    return $diagrama && $diagrama->usuarios->contains($user);
});