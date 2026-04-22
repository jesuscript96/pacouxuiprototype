<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Carpeta;
use App\Models\DocumentoCorporativo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentoCorporativo>
 */
class DocumentoCorporativoFactory extends Factory
{
    protected $model = DocumentoCorporativo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'carpeta_id' => Carpeta::factory(),
            'subcarpeta' => null,
            'nombre_documento' => fake()->word().'.pdf',
            'fecha_carga' => now(),
            'primera_visualizacion' => null,
            'ultima_visualizacion' => null,
        ];
    }
}
