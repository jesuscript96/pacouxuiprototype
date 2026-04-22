<?php

namespace Database\Factories;

use App\Models\Carrusel;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Carrusel> */
class CarruselFactory extends Factory
{
    protected $model = Carrusel::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'nombre_archivo' => fake()->word().'.jpg',
            'ruta' => fn (array $attrs) => "companies/{$attrs['empresa_id']}/carousel/".fake()->word().'.jpg',
            'orden' => fake()->numberBetween(0, 4),
        ];
    }
}
