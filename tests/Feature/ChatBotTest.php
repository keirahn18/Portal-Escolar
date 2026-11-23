<?php

namespace Tests\Feature;

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
}