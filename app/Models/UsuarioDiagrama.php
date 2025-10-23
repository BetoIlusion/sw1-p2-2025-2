<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioDiagrama extends Model
{
    protected $fillable = [
        'user_id',
        'diagrama_id',
        'actividad',
        'tipo_usuario',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function diagrama()
    {
        return $this->belongsTo(Diagrama::class);
    }
    public static function crearRelacion($userId, $diagramaId, $actividad, $tipoUsuario)
    {
        return static::create([
            'user_id' => $userId,
            'diagrama_id' => $diagramaId,
            'actividad' => $actividad,
            'tipo_usuario' => $tipoUsuario,
            'estado' => true,
        ]);
    }
}
