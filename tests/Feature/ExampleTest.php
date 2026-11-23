<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_portal_publico_carga_correctamente(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertOk();
    }
}