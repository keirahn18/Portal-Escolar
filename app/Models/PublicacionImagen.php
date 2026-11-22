<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicacionImagen extends Model
{
    protected $table = 'publicacion_imagenes';
    protected $primaryKey = 'id_imagen';

    protected $fillable = [
        'fk_id_publicacion',
        'ruta_img',
        'orden',
    ];

    public function publicacion()
    {
        return $this->belongsTo(Publicacion::class, 'fk_id_publicacion', 'id_publicacion');
    }
}