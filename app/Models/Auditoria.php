<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    protected $primaryKey = 'id_auditoria';

    protected $fillable = [
        'fk_id_usuario',
        'accion',
        'entidad',
        'id_registro',
        'detalle',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'fk_id_usuario');
    }

    public static function registrar(int $userId, string $accion, string $entidad, ?int $idRegistro = null, ?string $detalle = null): self
    {
        return self::create([
            'fk_id_usuario' => $userId,
            'accion' => $accion,
            'entidad' => $entidad,
            'id_registro' => $idRegistro,
            'detalle' => $detalle,
            'fecha' => now(),
        ]);
    }
}