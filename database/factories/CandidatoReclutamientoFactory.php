<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CandidatoReclutamiento;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidatoReclutamiento>
 */
class CandidatoReclutamientoFactory extends Factory
{
    protected $model = CandidatoReclutamiento::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->firstName();
        $apellidoPaterno = fake()->lastName();
        $apellidoMaterno = fake()->lastName();

        return [
            'vacante_id' => Vacante::factory(),
            'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
            'valores_formulario' => [
                'nombre' => $nombre,
                'apellido_paterno' => $apellidoPaterno,
                'apellido_materno' => $apellidoMaterno,
                'email' => fake()->safeEmail(),
                'telefono' => fake()->numerify('55########'),
            ],
            'nombre_completo' => "{$nombre} {$apellidoPaterno} {$apellidoMaterno}",
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->numerify('55########'),
            'curp' => strtoupper(fake()->bothify('????######??????##')),
        ];
    }

    public function enProceso(): static
    {
        return $this->state(fn (): array => [
            'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
        ]);
    }

    public function contratado(): static
    {
        return $this->state(fn (): array => [
            'estatus' => CandidatoReclutamiento::ESTATUS_CONTRATADO,
        ]);
    }

    public function rechazado(): static
    {
        return $this->state(fn (): array => [
            'estatus' => CandidatoReclutamiento::ESTATUS_RECHAZADO,
        ]);
    }

    public function noSePresento(): static
    {
        return $this->state(fn (): array => [
            'estatus' => CandidatoReclutamiento::ESTATUS_NO_SE_PRESENTO,
        ]);
    }

    public function conEvaluacionCv(float $score = 7.5): static
    {
        return $this->state(fn (): array => [
            'evaluacion_cv' => $score,
        ]);
    }

    public function sinCurp(): static
    {
        return $this->state(fn (): array => ['curp' => null]);
    }

    public function paraVacante(Vacante $vacante): static
    {
        return $this->state(fn (): array => [
            'vacante_id' => $vacante->id,
        ]);
    }
}
