<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\RolesYInstitucionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionUsuariosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolesYInstitucionSeeder::class);
    }

    private function crearDocente(): Usuario
    {
        return Usuario::create([
            'fk_id_rol' => Rol::where('nombre', 'Docente')->first()->id_rol,
            'fk_id_institucion' => Institucion::first()->id_institucion,
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'esc-30111222@gmail.com',
            'password' => 'docente1234',
        ]);
    }

    public function test_un_visitante_no_puede_ver_el_listado_de_usuarios(): void
    {
        $response = $this->get('/admin/usuarios');

        $response->assertRedirect(route('login'));
    }

    public function test_un_visitante_no_puede_ver_el_formulario_de_crear_usuario(): void
    {
        $response = $this->get('/admin/usuarios/crear');

        $response->assertRedirect(route('login'));
    }

    public function test_el_admin_puede_ver_el_listado_de_usuarios(): void
    {
        $admin = Usuario::where('email', 'admin@gmail.com')->first();

        $response = $this->actingAs($admin)->get('/admin/usuarios');

        $response->assertOk();
    }

    public function test_el_admin_puede_crear_un_usuario(): void
    {
        $admin = Usuario::where('email', 'admin@gmail.com')->first();
        $rolDocente = Rol::where('nombre', 'Docente')->first();

        $response = $this->actingAs($admin)->post('/admin/usuarios', [
            'nombre' => 'Ana',
            'apellido' => 'Gómez',
            'email' => 'esc-40123456@gmail.com',
            'password' => 'clave1234',
            'fk_id_rol' => $rolDocente->id_rol,
        ]);

        $response->assertRedirect('/admin/usuarios');

        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Ana',
            'email' => 'esc-40123456@gmail.com',
        ]);
    }

    public function test_no_se_puede_crear_un_usuario_con_campos_vacios(): void
    {
        $admin = Usuario::where('email', 'admin@gmail.com')->first();

        $response = $this->actingAs($admin)->post('/admin/usuarios', []);

        $response->assertSessionHasErrors(['nombre', 'apellido', 'email', 'password', 'fk_id_rol']);

        $this->assertDatabaseCount('usuarios', 1);
    }

    public function test_no_se_puede_crear_un_usuario_con_email_de_formato_invalido(): void
    {
        $admin = Usuario::where('email', 'admin@gmail.com')->first();
        $rolDocente = Rol::where('nombre', 'Docente')->first();

        $response = $this->actingAs($admin)->post('/admin/usuarios', [
            'nombre' => 'Ana',
            'apellido' => 'Gómez',
            'email' => 'ana.gomez@gmail.com',
            'password' => 'clave1234',
            'fk_id_rol' => $rolDocente->id_rol,
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('usuarios', [
            'email' => 'ana.gomez@gmail.com',
        ]);
    }

    public function test_el_admin_puede_editar_un_usuario(): void
    {
        $admin = Usuario::where('email', 'admin@gmail.com')->first();
        $docente = $this->crearDocente();

        $response = $this->actingAs($admin)->put('/admin/usuarios/' . $docente->id_usuario, [
            'nombre' => 'Juan Carlos',
            'apellido' => 'Pérez',
            'email' => $docente->email,
            'fk_id_rol' => $docente->fk_id_rol,
        ]);

        $response->assertRedirect('/admin/usuarios');

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $docente->id_usuario,
            'nombre' => 'Juan Carlos',
        ]);
    }

    public function test_el_admin_puede_eliminar_un_usuario(): void
    {
        $admin = Usuario::where('email', 'admin@gmail.com')->first();
        $docente = $this->crearDocente();

        $response = $this->actingAs($admin)->delete('/admin/usuarios/' . $docente->id_usuario);

        $response->assertRedirect('/admin/usuarios');

        $this->assertDatabaseMissing('usuarios', [
            'id_usuario' => $docente->id_usuario,
        ]);
    }

    public function test_el_admin_no_puede_eliminarse_a_si_mismo(): void
    {
        $admin = Usuario::where('email', 'admin@gmail.com')->first();

        $response = $this->actingAs($admin)->delete('/admin/usuarios/' . $admin->id_usuario);

        $response->assertSessionHasErrors('error');

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $admin->id_usuario,
        ]);
    }
}