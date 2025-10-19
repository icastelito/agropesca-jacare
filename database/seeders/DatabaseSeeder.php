<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ordem de execução respeitando relacionamentos:
        // 1. ProdutorRural (sem dependências)
        // 2. Propriedade (depende de ProdutorRural)
        // 3. UnidadeProducao e Rebanho (dependem de Propriedade)

        $this->call([
            ProdutorRuralSeeder::class,
            PropriedadeSeeder::class,
            UnidadeProducaoSeeder::class,
            RebanhoSeeder::class,
        ]);
    }
}
