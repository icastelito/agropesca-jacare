<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite de testes completa para todas as rotas da API
 * 
 * Este teste executa um fluxo completo:
 * 1. Registra um usuário
 * 2. Faz login
 * 3. Testa todas as rotas protegidas
 * 4. Faz logout
 * 
 * Execute: php artisan test --filter=ApiTestRunner
 */
class ApiTestRunner extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $user;

    /**
     * Teste de fluxo completo da API
     */
    public function test_complete_api_flow(): void
    {
        echo "\n\n🚀 INICIANDO TESTE COMPLETO DA API\n";
        echo "=====================================\n\n";

        // 1. Registrar usuário
        echo "1️⃣  Testando REGISTRO de usuário...\n";
        $registerResponse = $this->postJson('/api/v1/register', [
            "name" => 'Usuário Teste',
            "email" => 'teste@agropesca.com',
            "password" => 'senha123',
            "password_confirmation" => 'senha123',
        ]);

        $registerResponse->assertStatus(201);
        $this->token = $registerResponse->json('data.token');
        echo "   ✅ Usuário registrado com sucesso!\n";
        echo "   🔑 Token: " . substr($this->token, 0, 20) . "...\n\n";

        // 2. Fazer login
        echo "2️⃣  Testando LOGIN...\n";
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'teste@agropesca.com',
            'password' => 'senha123',
        ]);

        $loginResponse->assertStatus(200);
        $this->token = $loginResponse->json('data.token');
        echo "   ✅ Login realizado com sucesso!\n";
        echo "   🔑 Novo Token: " . substr($this->token, 0, 20) . "...\n\n";

        // 3. Testar rota /me
        echo "3️⃣  Testando GET /api/v1/me...\n";
        $meResponse = $this->withToken($this->token)->getJson('/api/v1/me');
        $meResponse->assertStatus(200);
        echo "   ✅ Perfil do usuário obtido com sucesso!\n\n";

        // 4. Testar Dashboard
        echo "4️⃣  Testando GET /api/v1/dashboard...\n";
        $dashboardResponse = $this->withToken($this->token)->getJson('/api/v1/dashboard');
        $dashboardResponse->assertStatus(200);
        echo "   ✅ Dashboard carregado com sucesso!\n\n";

        // 5. Testar Produtores Rurais - Listagem
        echo "5️⃣  Testando GET /api/v1/produtores-rurais (listagem)...\n";
        $produtoresListResponse = $this->withToken($this->token)->getJson('/api/v1/produtores-rurais');
        $produtoresListResponse->assertStatus(200);
        echo "   ✅ Listagem de produtores rurais OK!\n\n";

        // 6. Testar Produtores Rurais - Criação
        echo "6️⃣  Testando POST /api/v1/produtores-rurais (criar)...\n";
        $produtorData = [
            'nome' => 'João da Silva',
            'cpf_cnpj' => '12345678901',
            'telefone' => '11999999999',
            'email' => 'joao@teste.com',
            'endereco' => 'Rua Teste, 123',
        ];
        $createProdutorResponse = $this->withToken($this->token)
            ->postJson('/api/v1/produtores-rurais', $produtorData);
        $createProdutorResponse->assertStatus(201);
        $produtorId = $createProdutorResponse->json('data.id');
        echo "   ✅ Produtor rural criado! ID: {$produtorId}\n\n";

        // 7. Testar Produtores Rurais - Visualização
        echo "7️⃣  Testando GET /api/v1/produtores-rurais/{id} (visualizar)...\n";
        $showProdutorResponse = $this->withToken($this->token)
            ->getJson("/api/v1/produtores-rurais/{$produtorId}");
        $showProdutorResponse->assertStatus(200);
        echo "   ✅ Produtor rural visualizado!\n\n";

        // 8. Testar Produtores Rurais - Atualização
        echo "8️⃣  Testando PUT /api/v1/produtores-rurais/{id} (atualizar)...\n";
        $produtorData['nome'] = 'João da Silva Atualizado';
        $updateProdutorResponse = $this->withToken($this->token)
            ->putJson("/api/v1/produtores-rurais/{$produtorId}", $produtorData);
        $updateProdutorResponse->assertStatus(200);
        echo "   ✅ Produtor rural atualizado!\n\n";

        // 9. Testar Propriedades - Listagem
        echo "9️⃣  Testando GET /api/v1/propriedades (listagem)...\n";
        $propriedadesListResponse = $this->withToken($this->token)->getJson('/api/v1/propriedades');
        $propriedadesListResponse->assertStatus(200);
        echo "   ✅ Listagem de propriedades OK!\n\n";

        // 10. Testar Propriedades - Criação
        echo "🔟 Testando POST /api/v1/propriedades (criar)...\n";
        $propriedadeData = [
            'produtor_id' => $produtorId,
            'nome' => 'Fazenda Teste',
            'municipio' => 'São Paulo',
            'uf' => 'SP',
            'inscricao_estadual' => '123456789',
            'area_total' => 100.50,
        ];
        $createPropriedadeResponse = $this->withToken($this->token)
            ->postJson('/api/v1/propriedades', $propriedadeData);
        $createPropriedadeResponse->assertStatus(201);
        $propriedadeId = $createPropriedadeResponse->json('data.id');
        echo "   ✅ Propriedade criada! ID: {$propriedadeId}\n\n";

        // 11. Testar Unidades de Produção - Listagem
        echo "1️⃣1️⃣  Testando GET /api/v1/unidades-producao (listagem)...\n";
        $unidadesListResponse = $this->withToken($this->token)->getJson('/api/v1/unidades-producao');
        $unidadesListResponse->assertStatus(200);
        echo "   ✅ Listagem de unidades de produção OK!\n\n";

        // 12. Testar Rebanhos - Listagem
        echo "1️⃣2️⃣  Testando GET /api/v1/rebanhos (listagem)...\n";
        $rebanhosListResponse = $this->withToken($this->token)->getJson('/api/v1/rebanhos');
        $rebanhosListResponse->assertStatus(200);
        echo "   ✅ Listagem de rebanhos OK!\n\n";

        // 13. Testar Relatórios
        echo "1️⃣3️⃣  Testando GET /api/v1/relatorios...\n";
        $relatoriosResponse = $this->withToken($this->token)->getJson('/api/v1/relatorios');
        $relatoriosResponse->assertStatus(200);
        echo "   ✅ Relatórios carregados!\n\n";

        // 14. Testar Logs
        echo "1️⃣4️⃣  Testando GET /api/v1/logs...\n";
        $logsResponse = $this->withToken($this->token)->getJson('/api/v1/logs');
        $logsResponse->assertStatus(200);
        echo "   ✅ Logs carregados!\n\n";

        // 15. Testar Refresh Token
        echo "1️⃣5️⃣  Testando POST /api/v1/refresh (refresh token)...\n";
        $refreshResponse = $this->withToken($this->token)->postJson('/api/v1/refresh');
        $refreshResponse->assertStatus(200);
        $newToken = $refreshResponse->json('data.token');
        echo "   ✅ Token atualizado!\n";
        echo "   🔑 Novo Token: " . substr($newToken, 0, 20) . "...\n\n";

        // 16. Deletar Propriedade
        echo "1️⃣6️⃣  Testando DELETE /api/v1/propriedades/{id}...\n";
        $deletePropriedadeResponse = $this->withToken($newToken)
            ->deleteJson("/api/v1/propriedades/{$propriedadeId}");
        $deletePropriedadeResponse->assertStatus(200);
        echo "   ✅ Propriedade deletada!\n\n";

        // 17. Deletar Produtor
        echo "1️⃣7️⃣  Testando DELETE /api/v1/produtores-rurais/{id}...\n";
        $deleteProdutorResponse = $this->withToken($newToken)
            ->deleteJson("/api/v1/produtores-rurais/{$produtorId}");
        $deleteProdutorResponse->assertStatus(200);
        echo "   ✅ Produtor rural deletado!\n\n";

        // 18. Testar Logout
        echo "1️⃣8️⃣  Testando POST /api/v1/logout...\n";
        $logoutResponse = $this->withToken($newToken)->postJson('/api/v1/logout');
        $logoutResponse->assertStatus(200);
        echo "   ✅ Logout realizado com sucesso!\n\n";

        // 19. Tentar acessar rota protegida após logout
        echo "1️⃣9️⃣  Testando acesso sem autenticação (deve falhar)...\n";
        $unauthorizedResponse = $this->withToken($newToken)->getJson('/api/v1/me');
        $unauthorizedResponse->assertStatus(401);
        echo "   ✅ Acesso negado corretamente!\n\n";

        echo "=====================================\n";
        echo "🎉 TODOS OS TESTES PASSARAM COM SUCESSO!\n";
        echo "=====================================\n\n";

        $this->assertTrue(true);
    }
}
