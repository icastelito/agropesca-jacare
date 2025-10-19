<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Propriedade;
use App\Models\ProdutorRural;
use Faker\Factory as Faker;

class PropriedadeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $tiposPropriedade = ['Fazenda', 'Sítio', 'Chácara', 'Rancho', 'Granja', 'Haras', 'Estância'];

        $nomesPropriedades = [
            'Boa Esperança',
            'São José',
            'Santa Maria',
            'Vista Alegre',
            'Primavera',
            'Recanto Verde',
            'Buriti Dourado',
            'Sol Nascente',
            'Água Limpa',
            'Ipê Amarelo',
            'Três Marias',
            'Bela Vista',
            'Boa Sorte',
            'Palmital',
            'Aurora',
            'Esperança',
            'Progresso',
            'União',
            'Santa Clara',
            'Paraíso',
            'Monte Alegre',
            'Flores do Campo',
            'Cajueiro',
            'Araçá',
            'Bacuri',
            'Dois Irmãos',
            'Santa Luzia',
            'Verde Vale',
            'Feliz',
            'Bom Jesus',
        ];

        // Municípios de diversos estados brasileiros (regiões agropecuárias)
        $municipios = [
            // Maranhão
            ['nome' => 'São Luís', 'uf' => 'MA'],
            ['nome' => 'Imperatriz', 'uf' => 'MA'],
            ['nome' => 'Balsas', 'uf' => 'MA'],
            ['nome' => 'Açailândia', 'uf' => 'MA'],
            ['nome' => 'Bacabal', 'uf' => 'MA'],
            ['nome' => 'Caxias', 'uf' => 'MA'],

            // São Paulo
            ['nome' => 'Ribeirão Preto', 'uf' => 'SP'],
            ['nome' => 'Piracicaba', 'uf' => 'SP'],
            ['nome' => 'Araçatuba', 'uf' => 'SP'],
            ['nome' => 'Presidente Prudente', 'uf' => 'SP'],
            ['nome' => 'Barretos', 'uf' => 'SP'],
            ['nome' => 'Andradina', 'uf' => 'SP'],

            // Minas Gerais
            ['nome' => 'Uberlândia', 'uf' => 'MG'],
            ['nome' => 'Uberaba', 'uf' => 'MG'],
            ['nome' => 'Patos de Minas', 'uf' => 'MG'],
            ['nome' => 'Patrocínio', 'uf' => 'MG'],
            ['nome' => 'Passos', 'uf' => 'MG'],
            ['nome' => 'Montes Claros', 'uf' => 'MG'],

            // Bahia
            ['nome' => 'Barreiras', 'uf' => 'BA'],
            ['nome' => 'Luís Eduardo Magalhães', 'uf' => 'BA'],
            ['nome' => 'Formosa do Rio Preto', 'uf' => 'BA'],
            ['nome' => 'Vitória da Conquista', 'uf' => 'BA'],
            ['nome' => 'Feira de Santana', 'uf' => 'BA'],

            // Paraná
            ['nome' => 'Cascavel', 'uf' => 'PR'],
            ['nome' => 'Toledo', 'uf' => 'PR'],
            ['nome' => 'Maringá', 'uf' => 'PR'],
            ['nome' => 'Londrina', 'uf' => 'PR'],
            ['nome' => 'Ponta Grossa', 'uf' => 'PR'],
            ['nome' => 'Palotina', 'uf' => 'PR'],

            // Rio Grande do Sul
            ['nome' => 'Ijuí', 'uf' => 'RS'],
            ['nome' => 'Passo Fundo', 'uf' => 'RS'],
            ['nome' => 'Cruz Alta', 'uf' => 'RS'],
            ['nome' => 'Santa Maria', 'uf' => 'RS'],
            ['nome' => 'Uruguaiana', 'uf' => 'RS'],
            ['nome' => 'Bagé', 'uf' => 'RS'],

            // Goiás
            ['nome' => 'Rio Verde', 'uf' => 'GO'],
            ['nome' => 'Jataí', 'uf' => 'GO'],
            ['nome' => 'Cristalina', 'uf' => 'GO'],
            ['nome' => 'Catalão', 'uf' => 'GO'],
            ['nome' => 'Itumbiara', 'uf' => 'GO'],

            // Mato Grosso
            ['nome' => 'Sorriso', 'uf' => 'MT'],
            ['nome' => 'Lucas do Rio Verde', 'uf' => 'MT'],
            ['nome' => 'Sinop', 'uf' => 'MT'],
            ['nome' => 'Rondonópolis', 'uf' => 'MT'],
            ['nome' => 'Primavera do Leste', 'uf' => 'MT'],
            ['nome' => 'Campo Novo do Parecis', 'uf' => 'MT'],

            // Mato Grosso do Sul
            ['nome' => 'Dourados', 'uf' => 'MS'],
            ['nome' => 'Maracaju', 'uf' => 'MS'],
            ['nome' => 'Sidrolândia', 'uf' => 'MS'],
            ['nome' => 'São Gabriel do Oeste', 'uf' => 'MS'],

            // Pará
            ['nome' => 'Santarém', 'uf' => 'PA'],
            ['nome' => 'Paragominas', 'uf' => 'PA'],
            ['nome' => 'Redenção', 'uf' => 'PA'],

            // Tocantins
            ['nome' => 'Palmas', 'uf' => 'TO'],
            ['nome' => 'Gurupi', 'uf' => 'TO'],
            ['nome' => 'Porto Nacional', 'uf' => 'TO'],
        ];

        $produtores = ProdutorRural::all();

        if ($produtores->isEmpty()) {
            $this->command->error('❌ Nenhum produtor encontrado! Execute ProdutorRuralSeeder primeiro.');
            return;
        }

        echo "\n🏞️  Criando Propriedades...\n\n";

        $totalPropriedades = 0;

        foreach ($produtores as $produtor) {
            // Distribuição mais realista:
            // 45% - 1 propriedade
            // 30% - 2 propriedades
            // 15% - 3 propriedades
            // 7% - 4 propriedades
            // 3% - 5 propriedades
            $rand = rand(1, 100);
            if ($rand <= 45) {
                $numPropriedades = 1;
            } elseif ($rand <= 75) {
                $numPropriedades = 2;
            } elseif ($rand <= 90) {
                $numPropriedades = 3;
            } elseif ($rand <= 97) {
                $numPropriedades = 4;
            } else {
                $numPropriedades = 5;
            }

            $propriedadesUsadas = [];

            for ($i = 0; $i < $numPropriedades; $i++) {
                // Evita nomes duplicados para o mesmo produtor
                do {
                    $tipo = $tiposPropriedade[array_rand($tiposPropriedade)];
                    $nomeBase = $nomesPropriedades[array_rand($nomesPropriedades)];
                    $nome = "{$tipo} {$nomeBase}";
                } while (in_array($nome, $propriedadesUsadas));

                $propriedadesUsadas[] = $nome;

                // Áreas variadas de forma realista
                $categoriaArea = rand(1, 100);
                if ($categoriaArea <= 40) {
                    // 40% - pequenas propriedades (5-50 ha)
                    $area = $faker->randomFloat(2, 5, 50);
                } elseif ($categoriaArea <= 75) {
                    // 35% - médias propriedades (50-200 ha)
                    $area = $faker->randomFloat(2, 50, 200);
                } elseif ($categoriaArea <= 95) {
                    // 20% - grandes propriedades (200-500 ha)
                    $area = $faker->randomFloat(2, 200, 500);
                } else {
                    // 5% - muito grandes (500-2000 ha)
                    $area = $faker->randomFloat(2, 500, 2000);
                }

                $municipioInfo = $municipios[array_rand($municipios)];

                Propriedade::create([
                    'nome' => $nome,
                    'municipio' => $municipioInfo['nome'],
                    'uf' => $municipioInfo['uf'],
                    'inscricao_estadual' => $this->gerarInscricaoEstadual(),
                    'area_total' => $area,
                    'data_cadastro' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'produtor_id' => $produtor->id,
                ]);

                $totalPropriedades++;
            }

            // Progresso
            if ($totalPropriedades % 100 == 0) {
                echo "   ✓ {$totalPropriedades} propriedades criadas...\n";
            }
        }

        $this->command->info("\n✅ {$totalPropriedades} Propriedades criadas para {$produtores->count()} produtores!");
    }

    private function gerarInscricaoEstadual(): string
    {
        // Formato: 123456789
        return sprintf('%09d', rand(100000000, 999999999));
    }
}
