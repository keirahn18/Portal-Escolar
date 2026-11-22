<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publicacion extends Model
{
    use SoftDeletes;

    protected $table = 'publicacions';
    protected $primaryKey = 'id_publicacion';

    protected $fillable = [
        'fk_usuario',
        'fk_id_cat_publicacion',
        'fk_id_institucion',
        'titulo',
        'contenido',
        'posicion_texto',
        'estado',
        'fecha_publicacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'fk_usuario', 'id_usuario');
    }

    public function categoria()
    {
        return $this->belongsTo(CatPublicacion::class, 'fk_id_cat_publicacion', 'id_cat_publicacion');
    }

    public function bloques()
    {
        return $this->hasMany(PublicacionBloque::class, 'fk_id_publicacion', 'id_publicacion')->orderBy('orden', 'asc');
    }
}