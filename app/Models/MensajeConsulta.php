<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeConsulta extends Model
{
    protected $table = 'mensaje_consulta';
    protected $primaryKey = 'id_mensaje_consulta';
    public $timestamps = false;

    protected $fillable = [
        'fk_id_consulta',
        'fk_id_usuario',
        'emisor',
        'contenido',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'fk_id_consulta', 'id_consulta');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'fk_id_usuario', 'id_usuario');
    }
}