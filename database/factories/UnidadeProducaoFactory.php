<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnidadeProducao>
 */
class UnidadeProducaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $culturas = ['Soja', 'Milho', 'Café', 'Cana-de-açúcar', 'Arroz', 'Feijão', 'Algodão', 'Trigo'];

        return [
            'nome_cultura' => $this->faker->randomElement($culturas),
            'area_total_ha' => $this->faker->randomFloat(2, 5, 500),
            'data_cadastro' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'coordenadas_geograficas' => $this->faker->latitude() . ', ' . $this->faker->longitude(),
            'propriedade_id' => \App\Models\Propriedade::factory(),
        ];
    }
}
