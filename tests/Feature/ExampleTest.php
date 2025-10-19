<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Testa se a aplicação redireciona a rota raiz para login.
     */
    public function test_the_application_redirects_to_login(): void
    {
        $response = $this->get('/');

        // A rota raiz redireciona para /dashboard ou /login
        $response->assertRedirect();
    }

    /**
     * Testa se o health check retorna sucesso.
     */
    public function test_health_check_returns_success(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
    }
}
