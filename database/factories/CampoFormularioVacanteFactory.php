<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampoFormularioVacante;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampoFormularioVacante>
 */
class CampoFormularioVacanteFactory extends Factory
{
    protected $model = CampoFormularioVacante::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacante_id' => Vacante::factory(),
            'tipo' => 'text',
            'etiqueta' => fake()->words(2, true),
            'nombre' => fake()->unique()->slug(2),
            'requerido' => fake()->boolean(70),
            'placeholder' => fake()->optional(0.5)->sentence(3),
            'orden' => fake()->numberBetween(0, 20),
        ];
    }

    public function tipoSelect(): static
    {
        return $this->state(fn (): array => [
            'tipo' => 'select',
            'opciones' => ['Opción A', 'Opción B', 'Opción C'],
        ]);
    }

    public function tipoArchivo(): static
    {
        return $this->state(fn (): array => [
            'tipo' => 'file',
            'tipos_archivo' => 'image/png,image/jpeg,application/pdf',
        ]);
    }

    public function tipoEmail(): static
    {
        return $this->state(fn (): array => [
            'tipo' => 'email',
            'etiqueta' => 'Correo electrónico',
            'nombre' => 'email',
            'requerido' => true,
        ]);
    }

    public function requerido(): static
    {
        return $this->state(fn (): array => ['requerido' => true]);
    }

    public function dependienteDe(string $campoPadre, string $valorActivador): static
    {
        return $this->state(fn (): array => [
            'es_dependiente' => true,
            'campo_padre' => $campoPadre,
            'valor_activador' => $valorActivador,
        ]);
    }
}
