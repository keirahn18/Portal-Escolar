<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthRbacTest extends TestCase
{
    public function test_login_page_is_accessible_and_dashboard_requires_auth(): void
    {
        $this->get('/login')->assertStatus(200);
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }
}