<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatEvento extends Model
{
    protected $table = 'cat_eventos';
    protected $primaryKey = 'id_cat_evento';
    protected $fillable = ['nombre'];

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'fk_id_cat_evento', 'id_cat_evento');
    }
}