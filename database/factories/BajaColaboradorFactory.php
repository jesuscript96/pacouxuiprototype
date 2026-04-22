<?php

namespace Database\Factories;

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BajaColaborador>
 */
class BajaColaboradorFactory extends Factory
{
    protected $model = BajaColaborador::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'colaborador_id' => Colaborador::factory(),
            'user_id' => null,
            'empresa_id' => 1,
            'fecha_baja' => now()->addWeek()->toDateString(),
            'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
            'comentarios' => null,
            'estado' => BajaColaborador::ESTADO_PROGRAMADA,
            'registrado_por' => null,
        ];
    }

    public function ejecutada(): static
    {
        return $this->state(fn (): array => [
            'estado' => BajaColaborador::ESTADO_EJECUTADA,
            'fecha_baja' => now()->subDay()->toDateString(),
            'ejecutada_at' => now(),
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (BajaColaborador $baja): void {
            $colaborador = Colaborador::query()->find($baja->colaborador_id);
            if ($colaborador !== null && (int) $baja->empresa_id !== (int) $colaborador->empresa_id) {
                $baja->forceFill(['empresa_id' => $colaborador->empresa_id])->saveQuietly();
            }
        });
    }
}
