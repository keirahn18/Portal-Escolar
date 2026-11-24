<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $table = 'consulta';
    protected $primaryKey = 'id_consulta';
    public $timestamps = false;

    protected $fillable = [
        'fk_id_institucion',
        'fk_id_usuario',
        'nombre_consultante',
        'contacto',
        'asunto',
        'estado',
        'codigo_seguimiento',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'fk_id_institucion', 'id_institucion');
    }

    public function docenteAsignado()
    {
        return $this->belongsTo(Usuario::class, 'fk_id_usuario', 'id_usuario');
    }

    public function mensajes()
    {
        return $this->hasMany(MensajeConsulta::class, 'fk_id_consulta', 'id_consulta')
            ->orderBy('fecha', 'asc');
    }
}