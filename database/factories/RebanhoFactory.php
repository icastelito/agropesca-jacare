<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rebanho>
 */
class RebanhoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $especies = ['Bovino', 'Suíno', 'Ovino', 'Caprino', 'Aves', 'Equino'];
        $finalidades = ['Corte', 'Leite', 'Lã', 'Reprodução', 'Mista'];

        return [
            'especie' => $this->faker->randomElement($especies),
            'quantidade' => $this->faker->numberBetween(10, 500),
            'data_cadastro' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'finalidade' => $this->faker->randomElement($finalidades),
            'data_atualizacao' => now(),
            'propriedade_id' => \App\Models\Propriedade::factory(),
        ];
    }
}
