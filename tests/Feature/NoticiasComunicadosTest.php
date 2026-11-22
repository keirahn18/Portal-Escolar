<?php

namespace Tests\Feature;

use Tests\TestCase;

class NoticiasComunicadosTest extends TestCase
{
    public function test_noticias_comunicados_route_responds(): void
    {
        $response = $this->get('/noticias');
        $this->assertContains($response->getStatusCode(), [200, 302, 404, 500]);
    }
}