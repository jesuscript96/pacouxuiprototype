<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Colaborador;
use App\Models\Jefe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Jefe>
 */
class JefeFactory extends Factory
{
    protected $model = Jefe::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'colaborador_id' => Colaborador::factory(),
            'codigo_nivel_1' => fake()->numerify('########'),
            'codigo_nivel_2' => fake()->optional()->numerify('########'),
            'codigo_nivel_3' => null,
            'codigo_nivel_4' => null,
        ];
    }

    public function forColaborador(Colaborador $colaborador): static
    {
        return $this->state(fn (): array => [
            'colaborador_id' => $colaborador->id,
            'codigo_nivel_1' => $colaborador->codigo_boss !== '' ? $colaborador->codigo_boss : '1',
        ]);
    }
}
