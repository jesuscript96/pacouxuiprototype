<?php

namespace Database\Factories;

use App\Models\Banco;
use App\Models\CuentaNomina;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CuentaNomina>
 */
class CuentaNominaFactory extends Factory
{
    protected $model = CuentaNomina::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->colaborador(),
            'banco_id' => Banco::query()->first()?->id ?? 1,
            'numero_cuenta' => (string) fake()->unique()->numberBetween(1000000000000000, 9999999999999999),
            'tipo_cuenta' => fake()->randomElement(['CLABE', 'TARJETA', 'CUENTA']),
            'estado' => 'ACTIVA',
        ];
    }
}
