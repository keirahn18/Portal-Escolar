<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('docente.{idUsuario}', function ($user, $idUsuario) {
    return (int) $user->id_usuario === (int) $idUsuario;
});