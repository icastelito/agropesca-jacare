<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\ProdutorRural;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProdutorRuralApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria usuário e token para todos os testes
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Helper para fazer requisições autenticadas
     */
    protected function authenticatedJson($method, $uri, $data = [])
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->json($method, $uri, $data);
    }

    /**
     * Testa listagem de produtores
     */
    public function test_can_list_produtores(): void
    {
        ProdutorRural::factory()->count(5)->create();

        $response = $this->authenticatedJson('GET', '/api/v1/produtores-rurais');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'nome',
                        'cpf_cnpj',
                        'telefone',
                        'email',
                        'endereco',
                        'data_cadastro',
                        'data_cadastro_formatada',
                        'total_propriedades',
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
                'filters',
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Testa listagem com filtros
     */
    public function test_can_filter_produtores(): void
    {
        ProdutorRural::factory()->create(['nome' => 'João Silva']);
        ProdutorRural::factory()->create(['nome' => 'Maria Santos']);

        $response = $this->authenticatedJson('GET', '/api/v1/produtores-rurais?nome=João');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.nome', 'João Silva');
    }

    /**
     * Testa paginação
     */
    public function test_can_paginate_produtores(): void
    {
        ProdutorRural::factory()->count(30)->create();

        $response = $this->authenticatedJson('GET', '/api/v1/produtores-rurais?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 30);
    }

    /**
     * Testa criação de produtor
     */
    public function test_can_create_produtor(): void
    {
        $produtorData = [
            'nome' => 'Teste Produtor',
            'cpf_cnpj' => '12345678901',
            'telefone' => '11999999999',
            'email' => 'produtor@test.com',
            'endereco' => 'Rua Teste, 123',
        ];

        $response = $this->authenticatedJson('POST', '/api/v1/produtores-rurais', $produtorData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'nome', 'cpf_cnpj']
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('produtores_rurais', [
            'email' => 'produtor@test.com',
        ]);
    }

    /**
     * Testa visualização de produtor específico
     */
    public function test_can_show_produtor(): void
    {
        $produtor = ProdutorRural::factory()->create();

        $response = $this->authenticatedJson('GET', "/api/v1/produtores-rurais/{$produtor->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'nome', 'cpf_cnpj', 'email']
            ])
            ->assertJsonPath('data.id', $produtor->id);
    }

    /**
     * Testa atualização de produtor
     */
    public function test_can_update_produtor(): void
    {
        $produtor = ProdutorRural::factory()->create();

        $updateData = [
            'nome' => 'Nome Atualizado',
            'cpf_cnpj' => $produtor->cpf_cnpj,
            'telefone' => '11988888888',
            'email' => $produtor->email,
            'endereco' => $produtor->endereco,
        ];

        $response = $this->authenticatedJson('PUT', "/api/v1/produtores-rurais/{$produtor->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('produtores_rurais', [
            'id' => $produtor->id,
            'nome' => 'Nome Atualizado',
        ]);
    }

    /**
     * Testa exclusão de produtor
     */
    public function test_can_delete_produtor(): void
    {
        $produtor = ProdutorRural::factory()->create();

        $response = $this->authenticatedJson('DELETE', "/api/v1/produtores-rurais/{$produtor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('produtores_rurais', [
            'id' => $produtor->id,
        ]);
    }

    /**
     * Testa validação ao criar produtor
     */
    public function test_validation_on_create(): void
    {
        $response = $this->authenticatedJson('POST', '/api/v1/produtores-rurais', [
            'nome' => '',
            'cpf_cnpj' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome', 'cpf_cnpj']);
    }

    /**
     * Testa acesso sem autenticação
     */
    public function test_cannot_access_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/produtores-rurais');

        $response->assertStatus(401);
    }

    /**
     * Testa ordenação
     */
    public function test_can_sort_produtores(): void
    {
        ProdutorRural::factory()->create(['nome' => 'Zebra']);
        ProdutorRural::factory()->create(['nome' => 'Alpha']);

        $response = $this->authenticatedJson('GET', '/api/v1/produtores-rurais?sort_field=nome&sort_direction=asc');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.nome', 'Alpha');
    }
}
