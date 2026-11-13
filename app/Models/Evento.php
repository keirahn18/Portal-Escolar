<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use SoftDeletes;

    protected $table = 'eventos';
    protected $primaryKey = 'id_evento';

    protected $fillable = [
        'fk_usuario',
        'fk_id_cat_evento',
        'fk_id_tipo_evento',
        'fk_id_institucion',
        'titulo',
        'descripcion',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'lugar',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'fk_usuario', 'id_usuario');
    }

    public function categoria()
    {
        return $this->belongsTo(CatEvento::class, 'fk_id_cat_evento', 'id_cat_evento');
    }

    public function tipo()
    {
        return $this->belongsTo(TipoEvento::class, 'fk_id_tipo_evento', 'id_tipo_evento');
    }
}