<?php

namespace Database\Factories;

use App\Models\BeneficiarioColaborador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BeneficiarioColaborador>
 */
class BeneficiarioColaboradorFactory extends Factory
{
    protected $model = BeneficiarioColaborador::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->colaborador(),
            'nombre_completo' => fake()->name(),
            'parentesco' => fake()->randomElement(['Cónyuge', 'Hijo', 'Hija', 'Padre', 'Madre', 'Hermano', 'Hermana', 'Otro']),
            'porcentaje' => fake()->optional(0.8)->randomFloat(2, 5, 100),
        ];
    }
}
