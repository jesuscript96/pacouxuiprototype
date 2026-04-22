<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EstadoNotificacionPush;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificacionPush>
 */
class NotificacionPushFactory extends Factory
{
    protected $model = NotificacionPush::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'titulo' => fake()->sentence(4),
            'mensaje' => fake()->paragraph(2),
            'url' => fake()->optional(0.3)->url(),
            'data' => ['type' => 'NOTIFICACION_CUSTOM'],
            'filtros' => null,
            'estado' => EstadoNotificacionPush::BORRADOR,
            'programada_para' => null,
            'enviada_at' => null,
            'total_destinatarios' => 0,
            'total_enviados' => 0,
            'total_fallidos' => 0,
            'creado_por' => User::factory(),
        ];
    }

    public function programada(?DateTimeInterface $fecha = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'estado' => EstadoNotificacionPush::PROGRAMADA,
            'programada_para' => $fecha ?? now()->addHour(),
        ]);
    }

    public function enviada(): static
    {
        return $this->state(function (array $attributes): array {
            $dest = fake()->numberBetween(10, 1000);

            return [
                'estado' => EstadoNotificacionPush::ENVIADA,
                'enviada_at' => now(),
                'total_destinatarios' => $dest,
                'total_enviados' => $dest,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function conFiltros(array $filtros): static
    {
        return $this->state(fn (array $attributes): array => [
            'filtros' => $filtros,
        ]);
    }
}
