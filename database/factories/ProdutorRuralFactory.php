<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProdutorRural>
 */
class ProdutorRuralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'cpf_cnpj' => $this->faker->numerify('###.###.###-##'),
            'telefone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'endereco' => $this->faker->address(),
            'data_cadastro' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
