<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicacionBloque extends Model
{
    protected $table = 'publicacion_bloques';
    protected $primaryKey = 'id_bloque';

    protected $fillable = [
        'fk_id_publicacion',
        'tipo',
        'contenido_texto',
        'ruta_img',
        'orden',
    ];

    public function publicacion()
    {
        return $this->belongsTo(Publicacion::class, 'fk_id_publicacion', 'id_publicacion');
    }
}