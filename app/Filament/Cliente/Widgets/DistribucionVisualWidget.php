<?php

namespace App\Filament\Cliente\Widgets;

use Filament\Widgets\Widget;

class DistribucionVisualWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.distribucion-visual';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{label: string, valor: int, porcentaje: int, tono: string}>
     */
    public function getDepartamentos(): array
    {
        return [
            ['label' => 'Operaciones', 'valor' => 9812, 'porcentaje' => 32, 'tono' => 'indigo'],
            ['label' => 'Ventas', 'valor' => 6124, 'porcentaje' => 20, 'tono' => 'indigo'],
            ['label' => 'Producción', 'valor' => 4978, 'porcentaje' => 16, 'tono' => 'indigo'],
            ['label' => 'Administración', 'valor' => 3846, 'porcentaje' => 13, 'tono' => 'indigo'],
            ['label' => 'Tecnología', 'valor' => 3203, 'porcentaje' => 11, 'tono' => 'indigo'],
            ['label' => 'Recursos Humanos', 'valor' => 2561, 'porcentaje' => 8, 'tono' => 'indigo'],
        ];
    }

    /**
     * @return array<int, array{label: string, valor: int, porcentaje: int, tono: string}>
     */
    public function getAntiguedad(): array
    {
        return [
            ['label' => '0 – 1 año', 'valor' => 5234, 'porcentaje' => 17, 'tono' => 'emerald'],
            ['label' => '1 – 3 años', 'valor' => 9821, 'porcentaje' => 32, 'tono' => 'emerald'],
            ['label' => '3 – 5 años', 'valor' => 7845, 'porcentaje' => 26, 'tono' => 'emerald'],
            ['label' => '5 – 10 años', 'valor' => 5412, 'porcentaje' => 18, 'tono' => 'emerald'],
            ['label' => '+10 años', 'valor' => 2212, 'porcentaje' => 7, 'tono' => 'emerald'],
        ];
    }

    /**
     * @return array<int, array{label: string, valor: int, porcentaje: int, tono: string}>
     */
    public function getGenero(): array
    {
        return [
            ['label' => 'Mujeres', 'valor' => 15892, 'porcentaje' => 52, 'tono' => 'violet'],
            ['label' => 'Hombres', 'valor' => 14021, 'porcentaje' => 46, 'tono' => 'sky'],
            ['label' => 'No binario', 'valor' => 611, 'porcentaje' => 2, 'tono' => 'amber'],
        ];
    }
}
