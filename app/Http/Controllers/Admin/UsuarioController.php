<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Institucion;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Verifica que el usuario autenticado tenga rol de Administrador / Directivo.
     */
    private function verificarAdmin(): void
    {
        if (!auth()->user()->esAdmin()) {
            abort(403, 'Acceso exclusivo para personal Directivo / Administrador.');
        }
    }

    /**
     * Muestra la lista general de usuarios registrados.
     */
    public function index()
    {
        $this->verificarAdmin();

        $usuarios = Usuario::with('rol')->orderBy('id_usuario', 'desc')->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Muestra el formulario para registrar un nuevo docente/administrador.
     */
    public function create()
    {
        $this->verificarAdmin();

        $roles = Rol::all();

        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Guarda el nuevo usuario con el correo esc-DNI@gmail.com.
     */
    public function store(Request $request)
    {
        $this->verificarAdmin();

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:usuarios,email',
                'regex:/^esc-[0-9]{7,8}@gmail\.com$/i'
            ],
            'password' => ['required', 'string', 'min:8'],
            'fk_id_rol' => ['required', 'exists:rols,id_rol'],
        ], [
            'nombre.required' => 'El nombre del docente es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo/DNI ya se encuentra registrado.',
            'email.regex' => 'El formato debe ser obligatorio: esc-DNI@gmail.com (Ej: esc-40123456@gmail.com).',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'fk_id_rol.required' => 'Debés seleccionar un rol.',
        ]);

        $institucion = Institucion::first();

        Usuario::create([
            'nombre' => trim($request->nombre),
            'apellido' => trim($request->apellido),
            'email' => strtolower(trim($request->email)),
            'password' => $request->password, // El cast 'password' => 'hashed' lo encripta
            'fk_id_rol' => $request->fk_id_rol,
            'fk_id_institucion' => $institucion->id_institucion,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.usuarios.index')->with('status', '¡Usuario creado exitosamente!');
    }

    /**
     * Muestra el formulario para editar a un usuario existente.
     */
    public function edit($id)
    {
        $this->verificarAdmin();

        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all();

        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualiza los datos del usuario en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $this->verificarAdmin();

        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
                'regex:/^esc-[0-9]{7,8}@gmail\.com$/i'
            ],
            'fk_id_rol' => ['required', 'exists:rols,id_rol'],
        ], [
            'email.regex' => 'El formato debe ser esc-DNI@gmail.com.',
            'email.unique' => 'Este correo ya lo está usando otro usuario.',
        ]);

        $usuario->nombre = trim($request->nombre);
        $usuario->apellido = trim($request->apellido);
        $usuario->email = strtolower(trim($request->email));
        $usuario->fk_id_rol = $request->fk_id_rol;

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['min:8']
            ], [
                'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.'
            ]);
            $usuario->password = $request->password;
        }

        $usuario->save();

        return redirect()->route('admin.usuarios.index')->with('status', '¡Usuario actualizado exitosamente!');
    }

    /**
     * Elimina el usuario del sistema.
     */
    public function destroy($id)
    {
        $this->verificarAdmin();

        $usuario = Usuario::findOrFail($id);

        if (auth()->id() == $usuario->id_usuario) {
            return back()->withErrors(['error' => 'No podés eliminar tu propia cuenta de administrador.']);
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('status', '¡Usuario eliminado con éxito!');
    }
}