<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Propriedade>
 */
class PropriedadeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ufs = ['SP', 'RJ', 'MG', 'BA', 'RS', 'PR', 'SC', 'GO', 'MS', 'MT'];

        return [
            'nome' => 'Fazenda ' . $this->faker->lastName(),
            'municipio' => $this->faker->city(),
            'uf' => $this->faker->randomElement($ufs),
            'inscricao_estadual' => $this->faker->numerify('###.###.###'),
            'area_total' => $this->faker->randomFloat(2, 10, 5000),
            'data_cadastro' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'produtor_id' => \App\Models\ProdutorRural::factory(),
        ];
    }
}
