<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Propriedade;
use App\Models\ProdutorRural;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropriedadeApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $produtor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->produtor = ProdutorRural::factory()->create();
    }

    protected function authenticatedJson($method, $uri, $data = [])
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->json($method, $uri, $data);
    }

    public function test_can_list_propriedades(): void
    {
        Propriedade::factory()->count(5)->create([
            'produtor_id' => $this->produtor->id,
        ]);

        $response = $this->authenticatedJson('GET', '/api/v1/propriedades');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'nome',
                        'municipio',
                        'uf',
                        'inscricao_estadual',
                        'area_total',
                        'area_total_formatada',
                        'produtor_nome',
                        'produtor_id',
                        'total_unidades',
                        'total_rebanhos',
                        'created_at',
                        'created_at_formatada',
                    ]
                ],
                'pagination',
                'filters',
            ]);
    }

    public function test_can_filter_propriedades_by_nome(): void
    {
        Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
            'nome' => 'Fazenda Teste',
        ]);
        Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
            'nome' => 'Sítio Outro',
        ]);

        $response = $this->authenticatedJson('GET', '/api/v1/propriedades?nome=Fazenda');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.nome', 'Fazenda Teste');
    }

    public function test_can_filter_propriedades_by_uf(): void
    {
        Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
            'uf' => 'SP',
        ]);
        Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
            'uf' => 'RJ',
        ]);

        $response = $this->authenticatedJson('GET', '/api/v1/propriedades?uf=SP');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uf', 'SP');
    }

    public function test_can_filter_propriedades_by_area_range(): void
    {
        Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
            'area_total' => 50.00,
        ]);
        Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
            'area_total' => 150.00,
        ]);

        $response = $this->authenticatedJson('GET', '/api/v1/propriedades?area_total_min=100&area_total_max=200');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.area_total', '150.00');
    }

    public function test_can_create_propriedade(): void
    {
        $propriedadeData = [
            'produtor_id' => $this->produtor->id,
            'nome' => 'Nova Propriedade',
            'municipio' => 'São Paulo',
            'uf' => 'SP',
            'inscricao_estadual' => '123456789',
            'area_total' => 100.50,
        ];

        $response = $this->authenticatedJson('POST', '/api/v1/propriedades', $propriedadeData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('propriedades', [
            'nome' => 'Nova Propriedade',
        ]);
    }

    public function test_can_show_propriedade(): void
    {
        $propriedade = Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
        ]);

        $response = $this->authenticatedJson('GET', "/api/v1/propriedades/{$propriedade->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $propriedade->id);
    }

    public function test_can_update_propriedade(): void
    {
        $propriedade = Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
        ]);

        $updateData = [
            'produtor_id' => $this->produtor->id,
            'nome' => 'Propriedade Atualizada',
            'municipio' => $propriedade->municipio,
            'uf' => $propriedade->uf,
            'inscricao_estadual' => $propriedade->inscricao_estadual,
            'area_total' => 200.00,
        ];

        $response = $this->authenticatedJson('PUT', "/api/v1/propriedades/{$propriedade->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('propriedades', [
            'id' => $propriedade->id,
            'nome' => 'Propriedade Atualizada',
        ]);
    }

    public function test_can_delete_propriedade(): void
    {
        $propriedade = Propriedade::factory()->create([
            'produtor_id' => $this->produtor->id,
        ]);

        $response = $this->authenticatedJson('DELETE', "/api/v1/propriedades/{$propriedade->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('propriedades', [
            'id' => $propriedade->id,
        ]);
    }

    public function test_validation_on_create(): void
    {
        $response = $this->authenticatedJson('POST', '/api/v1/propriedades', [
            'nome' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome', 'produtor_id', 'municipio', 'uf']);
    }

    public function test_cannot_access_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/propriedades');

        $response->assertStatus(401);
    }
}
