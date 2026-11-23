<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\RolesYInstitucionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultasPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolesYInstitucionSeeder::class);
    }

    private function admin(): Usuario
    {
        return Usuario::where('email', 'admin@gmail.com')->first();
    }

    private function crearDocente(string $email): Usuario
    {
        return Usuario::create([
            'fk_id_rol' => Rol::where('nombre', 'Docente')->first()->id_rol,
            'fk_id_institucion' => Institucion::first()->id_institucion,
            'nombre' => 'Docente',
            'apellido' => 'Prueba',
            'email' => $email,
            'password' => 'docente1234',
        ]);
    }

    private function crearConsulta(Usuario $docente, string $codigo): Consulta
    {
        return Consulta::create([
            'fk_id_institucion' => Institucion::first()->id_institucion,
            'fk_id_usuario' => $docente->id_usuario,
            'nombre_consultante' => 'María López',
            'contacto' => '3704123456',
            'asunto' => 'Consulta de prueba',
            'estado' => 'pendiente',
            'codigo_seguimiento' => $codigo,
            'fecha' => now(),
        ]);
    }

    public function test_un_visitante_no_puede_ver_la_bandeja_de_consultas(): void
    {
        $response = $this->get('/admin/consultas');

        $response->assertRedirect(route('login'));
    }

    public function test_el_admin_puede_ver_la_bandeja_de_consultas(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/consultas');

        $response->assertOk();
    }

    public function test_un_docente_solo_ve_sus_propias_consultas(): void
    {
        $docente1 = $this->crearDocente('esc-30111111@gmail.com');
        $docente2 = $this->crearDocente('esc-30222222@gmail.com');
        $this->crearConsulta($docente1, 'ESI-1111');
        $this->crearConsulta($docente2, 'ESI-2222');

        $response = $this->actingAs($docente1)->getJson('/admin/consultas/feed');

        $response->assertOk();
        $response->assertJsonFragment(['codigo_seguimiento' => 'ESI-1111']);
        $response->assertJsonMissing(['codigo_seguimiento' => 'ESI-2222']);
    }

    public function test_el_admin_ve_las_consultas_de_todos_los_docentes(): void
    {
        $docente1 = $this->crearDocente('esc-30111111@gmail.com');
        $docente2 = $this->crearDocente('esc-30222222@gmail.com');
        $this->crearConsulta($docente1, 'ESI-1111');
        $this->crearConsulta($docente2, 'ESI-2222');

        $response = $this->actingAs($this->admin())->getJson('/admin/consultas/feed');

        $response->assertOk();
        $response->assertJsonCount(2, 'consultas');
    }

    public function test_un_docente_no_puede_ver_los_mensajes_de_una_consulta_ajena(): void
    {
        $docente1 = $this->crearDocente('esc-30111111@gmail.com');
        $docente2 = $this->crearDocente('esc-30222222@gmail.com');
        $consultaAjena = $this->crearConsulta($docente2, 'ESI-2222');

        $response = $this->actingAs($docente1)
            ->getJson('/admin/consultas/' . $consultaAjena->id_consulta . '/mensajes');

        $response->assertForbidden();
    }

    public function test_un_docente_puede_responder_su_consulta(): void
    {
        $docente = $this->crearDocente('esc-30111111@gmail.com');
        $consulta = $this->crearConsulta($docente, 'ESI-1111');

        $response = $this->actingAs($docente)
            ->postJson('/admin/consultas/' . $consulta->id_consulta . '/responder', [
                'contenido' => 'Hola María, mañana te espero a las 8.',
            ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('consulta', [
            'id_consulta' => $consulta->id_consulta,
            'estado' => 'en_atencion',
        ]);

        $this->assertDatabaseHas('mensaje_consulta', [
            'fk_id_consulta' => $consulta->id_consulta,
            'fk_id_usuario' => $docente->id_usuario,
            'emisor' => 'docente',
        ]);
    }

    public function test_un_docente_puede_cerrar_su_consulta(): void
    {
        $docente = $this->crearDocente('esc-30111111@gmail.com');
        $consulta = $this->crearConsulta($docente, 'ESI-1111');

        $response = $this->actingAs($docente)
            ->patchJson('/admin/consultas/' . $consulta->id_consulta . '/cerrar');

        $response->assertOk();

        $this->assertDatabaseHas('consulta', [
            'id_consulta' => $consulta->id_consulta,
            'estado' => 'cerrada',
        ]);
    }

    public function test_un_docente_puede_derivar_su_consulta_a_otro_docente(): void
    {
        $docente1 = $this->crearDocente('esc-30111111@gmail.com');
        $docente2 = $this->crearDocente('esc-30222222@gmail.com');
        $consulta = $this->crearConsulta($docente1, 'ESI-1111');

        $response = $this->actingAs($docente1)
            ->patchJson('/admin/consultas/' . $consulta->id_consulta . '/derivar', [
                'fk_id_usuario' => $docente2->id_usuario,
                'motivo' => 'Es tema de su grado.',
            ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('consulta', [
            'id_consulta' => $consulta->id_consulta,
            'fk_id_usuario' => $docente2->id_usuario,
            'estado' => 'pendiente',
        ]);
    }
}