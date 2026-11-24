<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreguntaFrecuente extends Model
{
    protected $table = 'preguntas_frecuentes';
    protected $primaryKey = 'id_preguntas_frecuentes';

    protected $fillable = [
        'fk_id_institucion',
        'pregunta',
        'respuesta',
        'palabras_clave',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'fk_id_institucion', 'id_institucion');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true)->orderBy('orden', 'asc');
    }
}