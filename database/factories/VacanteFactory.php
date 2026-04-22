<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacante>
 */
class VacanteFactory extends Factory
{
    protected $model = Vacante::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $puestos = [
            'Analista de Recursos Humanos',
            'Desarrollador Full Stack',
            'Contador General',
            'Diseñador Gráfico',
            'Ejecutivo de Ventas',
            'Gerente de Operaciones',
            'Asistente Administrativo',
            'Ingeniero de Calidad',
            'Coordinador de Logística',
            'Especialista en Marketing Digital',
        ];

        return [
            'empresa_id' => Empresa::query()->first()?->id ?? Empresa::factory(),
            'creado_por' => User::query()->first()?->id,
            'puesto' => fake()->unique()->randomElement($puestos),
            'requisitos' => '<p>'.fake()->paragraphs(2, true).'</p>',
            'aptitudes' => '<p>'.fake()->paragraphs(1, true).'</p>',
            'prestaciones' => '<p>'.fake()->paragraphs(1, true).'</p>',
        ];
    }

    public function paraEmpresa(Empresa $empresa): static
    {
        return $this->state(fn (): array => [
            'empresa_id' => $empresa->id,
        ]);
    }

    public function creadaPor(User $user): static
    {
        return $this->state(fn (): array => [
            'creado_por' => $user->id,
        ]);
    }
}
