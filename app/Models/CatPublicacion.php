<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatPublicacion extends Model
{
    protected $table = 'cat_publicacions';
    protected $primaryKey = 'id_cat_publicacion';
    protected $fillable = ['nombre'];

    public function publicaciones()
    {
        return $this->hasMany(Publicacion::class, 'fk_id_cat_publicacion', 'id_cat_publicacion');
    }
}