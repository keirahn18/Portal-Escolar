<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = 'institucions';
    protected $primaryKey = 'id_institucion';

    protected $fillable = [
        'nombre',
        'cue',
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'fk_id_institucion', 'id_institucion');
    }
}