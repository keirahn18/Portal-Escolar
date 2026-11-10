<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    /**
     * Muestra el formulario para registrar un nuevo usuario (Docente / Directivo).
     */
    public function create()
    {
        $roles = Rol::all();
        $instituciones = Institucion::all();

        return view('admin.usuarios.create', compact('roles', 'instituciones'));
    }

    /**
     * Guarda el nuevo usuario registrado en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', Password::defaults()],
            'fk_id_rol' => ['required', 'exists:rols,id_rol'],
            'fk_id_institucion' => ['required', 'exists:institucions,id_institucion'],
        ], [
            'email.unique' => 'El correo electrónico ingresado ya se encuentra registrado.',
            'fk_id_rol.exists' => 'El rol seleccionado no es válido.',
        ]);

        Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => $request->password, // El cast 'hashed' en Usuario.php la encripta solo
            'fk_id_rol' => $request->fk_id_rol,
            'fk_id_institucion' => $request->fk_id_institucion,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.dashboard')->with('status', '¡Usuario registrado exitosamente!');
    }
}