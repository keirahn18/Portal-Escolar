<?php

namespace Tests\Feature;

use Tests\TestCase;

class GestionUsuariosTest extends TestCase
{
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
}
