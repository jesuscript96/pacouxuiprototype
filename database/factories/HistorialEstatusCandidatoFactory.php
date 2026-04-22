<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CandidatoReclutamiento;
use App\Models\HistorialEstatusCandidato;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistorialEstatusCandidato>
 */
class HistorialEstatusCandidatoFactory extends Factory
{
    protected $model = HistorialEstatusCandidato::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'candidato_id' => CandidatoReclutamiento::factory(),
            'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
            'creado_por' => null,
            'fecha_inicio' => now(),
            'fecha_fin' => null,
            'duracion' => null,
        ];
    }

    public function cerrado(): static
    {
        $inicio = now()->subDays(fake()->numberBetween(1, 60));
        $fin = now();

        return $this->state(fn (): array => [
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'duracion' => HistorialEstatusCandidato::calcularDuracion($inicio, $fin),
        ]);
    }

    public function conEstatus(string $estatus): static
    {
        return $this->state(fn (): array => ['estatus' => $estatus]);
    }

    public function creadoPor(User $user): static
    {
        return $this->state(fn (): array => ['creado_por' => $user->id]);
    }
}
