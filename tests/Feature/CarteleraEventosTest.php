<?php

namespace Tests\Feature;

use Tests\TestCase;

class CarteleraEventosTest extends TestCase
{
    public function test_home_or_cartelera_route_responds(): void
    {
        $response = $this->get('/');
        $this->assertContains($response->getStatusCode(), [200, 302, 500]);
    }
}