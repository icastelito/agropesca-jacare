<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    protected function authenticatedJson($method, $uri, $data = [])
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->json($method, $uri, $data);
    }

    public function test_can_get_dashboard_index(): void
    {
        // Cria dados de teste
        $produtor = ProdutorRural::factory()->create();
        $propriedade = Propriedade::factory()->create(['produtor_id' => $produtor->id]);
        UnidadeProducao::factory()->create(['propriedade_id' => $propriedade->id]);
        Rebanho::factory()->create(['propriedade_id' => $propriedade->id]);

        $response = $this->authenticatedJson('GET', '/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'totais' => [
                        'produtores',
                        'propriedades',
                    ],
                    'propriedades_por_municipio',
                    'animais_por_especie',
                    'hectares_por_cultura',
                    'evolucao_cadastros',
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_can_get_total_produtores(): void
    {
        ProdutorRural::factory()->count(5)->create();

        $response = $this->authenticatedJson('GET', '/api/v1/dashboard/total-produtores');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['total']
            ])
            ->assertJsonPath('data.total', 5);
    }

    public function test_can_get_total_propriedades(): void
    {
        $produtor = ProdutorRural::factory()->create();
        Propriedade::factory()->count(3)->create(['produtor_id' => $produtor->id]);

        $response = $this->authenticatedJson('GET', '/api/v1/dashboard/total-propriedades');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['total']
            ])
            ->assertJsonPath('data.total', 3);
    }

    public function test_can_get_propriedades_por_municipio(): void
    {
        $produtor = ProdutorRural::factory()->create();
        Propriedade::factory()->create(['produtor_id' => $produtor->id, 'municipio' => 'São Paulo']);
        Propriedade::factory()->create(['produtor_id' => $produtor->id, 'municipio' => 'São Paulo']);
        Propriedade::factory()->create(['produtor_id' => $produtor->id, 'municipio' => 'Campinas']);

        $response = $this->authenticatedJson('GET', '/api/v1/dashboard/propriedades-por-municipio');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['municipio', 'total']
                ]
            ]);
    }

    public function test_can_get_animais_por_especie(): void
    {
        $produtor = ProdutorRural::factory()->create();
        $propriedade = Propriedade::factory()->create(['produtor_id' => $produtor->id]);
        Rebanho::factory()->create([
            'propriedade_id' => $propriedade->id,
            'especie' => 'Bovino',
            'quantidade' => 100,
        ]);
        Rebanho::factory()->create([
            'propriedade_id' => $propriedade->id,
            'especie' => 'Suíno',
            'quantidade' => 50,
        ]);

        $response = $this->authenticatedJson('GET', '/api/v1/dashboard/animais-por-especie');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['especie', 'total']
                ]
            ]);
    }

    public function test_can_get_hectares_por_cultura(): void
    {
        $produtor = ProdutorRural::factory()->create();
        $propriedade = Propriedade::factory()->create(['produtor_id' => $produtor->id]);
        UnidadeProducao::factory()->create([
            'propriedade_id' => $propriedade->id,
            'nome_cultura' => 'Soja',
            'area_total_ha' => 50.00,
        ]);
        UnidadeProducao::factory()->create([
            'propriedade_id' => $propriedade->id,
            'nome_cultura' => 'Milho',
            'area_total_ha' => 30.00,
        ]);

        $response = $this->authenticatedJson('GET', '/api/v1/dashboard/hectares-por-cultura');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['cultura', 'total_hectares']
                ]
            ]);
    }

    public function test_cannot_access_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(401);
    }
}
