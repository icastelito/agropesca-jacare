<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UnidadeProducao;
use App\Models\Propriedade;
use Faker\Factory as Faker;

class UnidadeProducaoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Culturas agrícolas típicas do Maranhão (mais completo)
        $culturas = [
            // Grãos
            'Arroz',
            'Milho',
            'Soja',
            'Feijão',
            'Sorgo',
            // Raízes e tubérculos
            'Mandioca',
            'Inhame',
            'Batata-doce',
            // Cana e fibras
            'Cana-de-açúcar',
            'Algodão',
            // Frutas tropicais
            'Banana',
            'Abacaxi',
            'Laranja',
            'Limão',
            'Manga',
            'Mamão',
            'Melancia',
            'Melão',
            'Maracujá',
            'Goiaba',
            'Caju',
            // Frutas regionais
            'Açaí',
            'Cupuaçu',
            'Bacuri',
            'Buriti',
            'Murici',
            'Pequi',
            // Hortaliças
            'Alface',
            'Tomate',
            'Pimentão',
            'Cebolinha',
            'Coentro',
            'Quiabo',
            'Maxixe',
            'Jerimum',
            'Pepino',
            'Berinjela',
            // Outras
            'Coco',
            'Cacau',
            'Café',
            'Urucum',
            // Do Teste
            'Laranja Pera',
            'Melancia Crimson Sweet',
            'Goiaba Paluma',
        ];

        $propriedades = Propriedade::all();

        if ($propriedades->isEmpty()) {
            $this->command->error('❌ Nenhuma propriedade encontrada! Execute PropriedadeSeeder primeiro.');
            return;
        }

        echo "\n🌾 Criando Unidades de Produção...\n\n";

        $totalUnidades = 0;

        foreach ($propriedades as $propriedade) {
            // Distribuição mais realista:
            // 15% - 0 unidades (propriedade sem cultivo ativo)
            // 35% - 1 unidade
            // 28% - 2 unidades
            // 15% - 3 unidades
            // 7% - 4 unidades
            $rand = rand(1, 100);
            if ($rand <= 15) {
                $numUnidades = 0;
            } elseif ($rand <= 50) {
                $numUnidades = 1;
            } elseif ($rand <= 78) {
                $numUnidades = 2;
            } elseif ($rand <= 93) {
                $numUnidades = 3;
            } else {
                $numUnidades = 4;
            }

            $culturasUsadas = [];
            $areaUsadaTotal = 0;

            for ($i = 0; $i < $numUnidades; $i++) {
                // Evita culturas duplicadas na mesma propriedade
                do {
                    $cultura = $culturas[array_rand($culturas)];
                } while (in_array($cultura, $culturasUsadas));

                $culturasUsadas[] = $cultura;

                // Área da unidade não pode ultrapassar a área disponível da propriedade
                $areaDisponivel = $propriedade->area_total - $areaUsadaTotal;

                if ($areaDisponivel <= 0) {
                    break;
                }

                // Define área baseada no tipo de cultura e área disponível
                if ($numUnidades == 1) {
                    // Se é única, usa 50-80% da propriedade
                    $percentual = rand(50, 80) / 100;
                    $area = min($propriedade->area_total * $percentual, $areaDisponivel);
                } else {
                    // Divide proporcionalmente considerando quantas ainda faltam
                    $unidadesRestantes = $numUnidades - $i;
                    $areaMedia = $areaDisponivel / $unidadesRestantes;
                    $variacao = rand(50, 150) / 100; // 50% a 150% da média
                    $area = min($areaMedia * $variacao, $areaDisponivel);
                }

                $area = max($area, 0.5); // Mínimo de 0.5 hectare
                $area = round($area, 2);
                $areaUsadaTotal += $area;

                // Gera coordenadas fictícias dentro do Maranhão
                // Maranhão: Latitude -2.5 a -10.5, Longitude -41 a -48
                $latitude = $faker->randomFloat(6, -10.5, -2.5);
                $longitude = $faker->randomFloat(6, -48, -41);

                UnidadeProducao::create([
                    'nome_cultura' => $cultura,
                    'area_total_ha' => $area,
                    'data_cadastro' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'coordenadas_geograficas' => sprintf("%.6f, %.6f", $latitude, $longitude),
                    'propriedade_id' => $propriedade->id,
                ]);

                $totalUnidades++;
            }

            // Progresso
            if ($totalUnidades % 100 == 0) {
                echo "   ✓ {$totalUnidades} unidades criadas...\n";
            }
        }

        $this->command->info("\n✅ {$totalUnidades} Unidades de Produção criadas para {$propriedades->count()} propriedades!");
    }
}
