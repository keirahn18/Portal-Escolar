<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements MustVerifyEmail
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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