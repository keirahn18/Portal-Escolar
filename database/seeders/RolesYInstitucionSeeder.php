<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class RolesYInstitucionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear los Roles
        $rolAdmin = Rol::create(['nombre' => 'Administrador']);
        $rolDocente = Rol::create(['nombre' => 'Docente']);

        // 2. Crear la Institución
        $institucion = Institucion::create([
            'nombre' => 'E.P.E.P. N° 436 "Simón Bolívar"',
            'cue' => '340043600',
        ]);

        // 3. Crear el Usuario Administrador inicial
        Usuario::create([
            'fk_id_rol' => $rolAdmin->id_rol,
            'fk_id_institucion' => $institucion->id_institucion,
            'nombre' => 'Ana',
            'apellido' => 'Rahn',
            'email' => 'admin@epep436.edu.ar',
            'password' => Hash::make('admin1234'),
            'email_verified_at' => now(),
        ]);
    }
}