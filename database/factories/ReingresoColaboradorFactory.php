<?php

namespace Database\Factories;

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Models\ReingresoColaborador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReingresoColaborador>
 */
class ReingresoColaboradorFactory extends Factory
{
    protected $model = ReingresoColaborador::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'motivo_reingreso' => fake()->optional()->sentence(),
            'comentarios' => fake()->optional()->paragraph(),
            'user_anterior_id' => null,
            'user_nuevo_id' => null,
            'registrado_por' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ReingresoColaborador $reingreso): void {
            if ($reingreso->baja_colaborador_id !== null) {
                return;
            }

            $colaboradorAnterior = Colaborador::factory()->create();
            $baja = BajaColaborador::factory()
                ->ejecutada()
                ->create([
                    'colaborador_id' => $colaboradorAnterior->id,
                    'empresa_id' => $colaboradorAnterior->empresa_id,
                ]);
            $colaboradorNuevo = Colaborador::factory()->create([
                'empresa_id' => $colaboradorAnterior->empresa_id,
                'numero_colaborador' => 'RE-FCT'.strtoupper(fake()->unique()->bothify('??????')),
            ]);

            $reingreso->forceFill([
                'baja_colaborador_id' => $baja->id,
                'colaborador_anterior_id' => $colaboradorAnterior->id,
                'colaborador_nuevo_id' => $colaboradorNuevo->id,
                'empresa_id' => $colaboradorAnterior->empresa_id,
                'fecha_ingreso_anterior' => $colaboradorAnterior->fecha_ingreso,
                'fecha_ingreso_nuevo' => now()->toDateString(),
            ]);
        });
    }
}
