<?php

namespace Tests\Feature;

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
}