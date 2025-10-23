<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class DiagramaReporte extends Model
{
    protected $fillable = [
        'user_id',
        'diagrama_id',
        'contenido',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function diagrama()
    {
        return $this->belongsTo(Diagrama::class);
    }
    public static function crear($userId, $diagramaId, $diagramaData)
    {
        try {
            return static::create([
                'user_id' => $userId,
                'diagrama_id' => $diagramaId,
                'contenido' => is_array($diagramaData) ? json_encode($diagramaData, JSON_PRETTY_PRINT) : $diagramaData,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Error al crear el reporte del diagrama: ' . $e->getMessage());
        }
    }
}
