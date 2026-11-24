<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\PreguntaFrecuenteSeeder;
use Database\Seeders\RolesYInstitucionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatBotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RolesYInstitucionSeeder::class);
        $this->seed(PreguntaFrecuenteSeeder::class);

        // Simula que la API de Gemini no responde.
        // Así los tests nunca llaman a la IA real.
        Http::fake(['*' => Http::response([], 500)]);
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

    private function crearConsulta(Usuario $docente, string $estado = 'pendiente'): Consulta
    {
        return Consulta::create([
            'fk_id_institucion' => Institucion::first()->id_institucion,
            'fk_id_usuario' => $docente->id_usuario,
            'nombre_consultante' => 'María López',
            'contacto' => '3704123456',
            'asunto' => 'Consulta de prueba',
            'estado' => $estado,
            'codigo_seguimiento' => 'ESI-1234',
            'fecha' => now(),
        ]);
    }

    public function test_no_se_puede_preguntar_sin_mensaje(): void
    {
        $response = $this->postJson('/portal/chat/preguntar', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('mensaje');
    }

    public function test_el_asistente_responde_con_una_pregunta_frecuente(): void
    {
        $response = $this->postJson('/portal/chat/preguntar', [
            'mensaje' => '¿Cuál es el horario de la escuela?',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('07:30', $response->json('respuesta'));
    }

    public function test_una_familia_puede_derivar_su_consulta_a_un_docente(): void
    {
        $docente = $this->crearDocente();

        $response = $this->postJson('/portal/chat/derivar', [
            'nombre_consultante' => 'María López',
            'contacto' => '3704123456',
            'asunto' => 'Consulta sobre mi hijo',
            'contenido' => 'Quisiera hablar con la maestra de 3° grado.',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $codigo = $response->json('codigo');
        $this->assertMatchesRegularExpression('/^ESI-\d{4}$/', $codigo);

        $this->assertDatabaseHas('consulta', [
            'codigo_seguimiento' => $codigo,
            'fk_id_usuario' => $docente->id_usuario,
            'estado' => 'pendiente',
        ]);

        $this->assertDatabaseHas('mensaje_consulta', [
            'emisor' => 'familia',
            'contenido' => 'Quisiera hablar con la maestra de 3° grado.',
        ]);
    }

    public function test_no_se_puede_derivar_una_consulta_sin_datos_obligatorios(): void
    {
        $response = $this->postJson('/portal/chat/derivar', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre_consultante', 'contacto', 'contenido']);

        $this->assertDatabaseCount('consulta', 0);
    }

    public function test_una_familia_puede_ver_su_consulta_con_el_codigo(): void
    {
        $consulta = $this->crearConsulta($this->crearDocente());

        $response = $this->getJson('/portal/chat/seguimiento/' . $consulta->codigo_seguimiento);

        $response->assertOk();
        $response->assertJson([
            'encontrado' => true,
            'codigo' => 'ESI-1234',
        ]);
    }

    public function test_un_codigo_inexistente_no_encuentra_ninguna_consulta(): void
    {
        $response = $this->getJson('/portal/chat/seguimiento/ESI-0000');

        $response->assertNotFound();
    }

    public function test_una_familia_no_puede_responder_una_consulta_cerrada(): void
    {
        $consulta = $this->crearConsulta($this->crearDocente(), 'cerrada');

        $response = $this->postJson('/portal/chat/seguimiento/' . $consulta->codigo_seguimiento . '/responder', [
            'contenido' => 'Hola, ¿me responden?',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('mensaje_consulta', 0);
    }
}