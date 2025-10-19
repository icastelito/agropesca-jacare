<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rebanho;
use App\Models\Propriedade;
use Faker\Factory as Faker;

class RebanhoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Espécies com finalidades específicas e quantidades típicas
        $especies = [
            'Bovino' => [
                'finalidades' => ['Corte', 'Leite', 'Misto'],
                'min' => 10,
                'max' => 500,
            ],
            'Ovino' => [
                'finalidades' => ['Corte', 'Lã', 'Misto'],
                'min' => 20,
                'max' => 300,
            ],
            'Caprino' => [
                'finalidades' => ['Corte', 'Leite', 'Misto'],
                'min' => 15,
                'max' => 250,
            ],
            'Suíno' => [
                'finalidades' => ['Corte', 'Reprodução', 'Misto'],
                'min' => 10,
                'max' => 200,
            ],
            'Aves' => [
                'finalidades' => ['Corte', 'Postura', 'Misto'],
                'min' => 50,
                'max' => 2000,
            ],
            'Equino' => [
                'finalidades' => ['Trabalho', 'Esporte', 'Reprodução', 'Lazer'],
                'min' => 2,
                'max' => 50,
            ],
            'Bubalino' => [
                'finalidades' => ['Corte', 'Leite', 'Trabalho', 'Misto'],
                'min' => 5,
                'max' => 300,
            ],
            'Muares' => [
                'finalidades' => ['Trabalho', 'Carga'],
                'min' => 2,
                'max' => 20,
            ],
            'Coelhos' => [
                'finalidades' => ['Corte', 'Reprodução'],
                'min' => 10,
                'max' => 100,
            ],
            'Peixes' => [
                'finalidades' => ['Criação', 'Comercial'],
                'min' => 500,
                'max' => 10000,
            ],
        ];

        $propriedades = Propriedade::all();

        if ($propriedades->isEmpty()) {
            $this->command->error('❌ Nenhuma propriedade encontrada! Execute PropriedadeSeeder primeiro.');
            return;
        }

        echo "\n🐄 Criando Rebanhos...\n\n";

        $totalRebanhos = 0;

        foreach ($propriedades as $propriedade) {
            // Distribuição baseada no tamanho da propriedade:
            // Propriedades pequenas (<50ha): mais chance de não ter rebanho
            // Propriedades médias (50-200ha): 1-2 rebanhos
            // Propriedades grandes (>200ha): 2-3 rebanhos

            $area = $propriedade->area_total;

            if ($area < 50) {
                // Pequenas: 50% não tem, 40% tem 1, 10% tem 2
                $rand = rand(1, 100);
                if ($rand <= 50) {
                    $numRebanhos = 0;
                } elseif ($rand <= 90) {
                    $numRebanhos = 1;
                } else {
                    $numRebanhos = 2;
                }
            } elseif ($area < 200) {
                // Médias: 20% não tem, 50% tem 1, 25% tem 2, 5% tem 3
                $rand = rand(1, 100);
                if ($rand <= 20) {
                    $numRebanhos = 0;
                } elseif ($rand <= 70) {
                    $numRebanhos = 1;
                } elseif ($rand <= 95) {
                    $numRebanhos = 2;
                } else {
                    $numRebanhos = 3;
                }
            } else {
                // Grandes: 10% não tem, 30% tem 1, 40% tem 2, 20% tem 3
                $rand = rand(1, 100);
                if ($rand <= 10) {
                    $numRebanhos = 0;
                } elseif ($rand <= 40) {
                    $numRebanhos = 1;
                } elseif ($rand <= 80) {
                    $numRebanhos = 2;
                } else {
                    $numRebanhos = 3;
                }
            }

            $especiesUsadas = [];

            for ($i = 0; $i < $numRebanhos; $i++) {
                // Escolhe espécie não repetida
                do {
                    $especieNome = array_rand($especies);
                } while (in_array($especieNome, $especiesUsadas));

                $especiesUsadas[] = $especieNome;
                $especieConfig = $especies[$especieNome];

                // Quantidade varia conforme tamanho da propriedade
                $minQtd = $especieConfig['min'];
                $maxQtd = $especieConfig['max'];

                // Propriedades maiores tendem a ter rebanhos maiores
                if ($area < 50) {
                    $maxQtd = (int) ($maxQtd * 0.3); // 30% do máximo
                } elseif ($area < 200) {
                    $maxQtd = (int) ($maxQtd * 0.6); // 60% do máximo
                }

                $quantidade = rand($minQtd, max($minQtd + 1, $maxQtd));

                Rebanho::create([
                    'especie' => $especieNome,
                    'quantidade' => $quantidade,
                    'data_cadastro' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'finalidade' => $especieConfig['finalidades'][array_rand($especieConfig['finalidades'])],
                    'data_atualizacao' => $faker->dateTimeBetween('-2 years', 'now'),
                    'propriedade_id' => $propriedade->id,
                ]);

                $totalRebanhos++;
            }

            // Progresso
            if ($totalRebanhos % 100 == 0) {
                echo "   ✓ {$totalRebanhos} rebanhos criados...\n";
            }
        }

        $this->command->info("\n✅ {$totalRebanhos} Rebanhos criados para {$propriedades->count()} propriedades!");
    }
}
