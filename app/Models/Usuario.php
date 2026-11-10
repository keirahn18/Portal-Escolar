<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'fk_id_rol',
        'fk_id_institucion',
        'nombre',
        'apellido',
        'email',
        'password',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Hashea automáticamente la contraseña
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'fk_id_rol', 'id_rol');
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'fk_id_institucion', 'id_institucion');
    }
}