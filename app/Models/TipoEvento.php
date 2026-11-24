<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEvento extends Model
{
    protected $table = 'tipo_eventos';
    protected $primaryKey = 'id_tipo_evento';
    protected $fillable = ['tipo', 'color_hex'];

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'fk_id_tipo_evento', 'id_tipo_evento');
    }
}