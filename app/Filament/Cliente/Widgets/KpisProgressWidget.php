<?php

namespace App\Filament\Cliente\Widgets;

use Filament\Widgets\Widget;

class KpisProgressWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.kpis-progress';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{titulo: string, valor: int, meta: int, descripcion: string, icono: string, tono: string}>
     */
    public function getKpis(): array
    {
        return [
            [
                'titulo' => 'Tasa de registro',
                'valor' => 78,
                'meta' => 90,
                'descripcion' => '7,844 de 10,000 colaboradores registrados',
                'icono' => 'heroicon-o-identification',
                'tono' => 'indigo',
            ],
            [
                'titulo' => 'Clima laboral',
                'valor' => 92,
                'meta' => 85,
                'descripcion' => 'Encuesta semestral · 1,423 respuestas',
                'icono' => 'heroicon-o-heart',
                'tono' => 'emerald',
            ],
            [
                'titulo' => 'Cumplimiento SUA',
                'valor' => 64,
                'meta' => 100,
                'descripcion' => '2,890 cartas firmadas de 4,500',
                'icono' => 'heroicon-o-document-check',
                'tono' => 'amber',
            ],
            [
                'titulo' => 'Engagement app',
                'valor' => 81,
                'meta' => 75,
                'descripcion' => 'Usuarios activos últimos 30 días',
                'icono' => 'heroicon-o-device-phone-mobile',
                'tono' => 'violet',
            ],
        ];
    }
}
