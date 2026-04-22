<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Carpeta;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Carpeta>
 */
class CarpetaFactory extends Factory
{
    protected $model = Carpeta::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $empresaId = Empresa::factory();

        return [
            'nombre' => fake()->unique()->words(3, true),
            'empresa_id' => $empresaId,
            'url' => 'assets/companies/files/1/documentos/'.fake()->slug(2),
            'tipo' => Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS,
            'usuario_id' => null,
        ];
    }
}
