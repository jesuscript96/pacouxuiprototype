<?php

namespace App\Filament\Cliente\Widgets;

use Filament\Widgets\Widget;

class ResumenEjecutivoWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.resumen-ejecutivo';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{departamento: string, headcount: int, rotacion: float, cumpleanos: int, satisfaccion: int, tendencia: string}>
     */
    public function getResumenPorDepartamento(): array
    {
        return [
            ['departamento' => 'Operaciones', 'headcount' => 9812, 'rotacion' => 2.4, 'cumpleanos' => 812, 'satisfaccion' => 88, 'tendencia' => 'sube'],
            ['departamento' => 'Ventas', 'headcount' => 6124, 'rotacion' => 4.1, 'cumpleanos' => 534, 'satisfaccion' => 79, 'tendencia' => 'baja'],
            ['departamento' => 'Producción', 'headcount' => 4978, 'rotacion' => 3.2, 'cumpleanos' => 421, 'satisfaccion' => 83, 'tendencia' => 'estable'],
            ['departamento' => 'Administración', 'headcount' => 3846, 'rotacion' => 1.8, 'cumpleanos' => 298, 'satisfaccion' => 91, 'tendencia' => 'sube'],
            ['departamento' => 'Tecnología', 'headcount' => 3203, 'rotacion' => 2.9, 'cumpleanos' => 256, 'satisfaccion' => 95, 'tendencia' => 'sube'],
            ['departamento' => 'Recursos Humanos', 'headcount' => 2561, 'rotacion' => 1.5, 'cumpleanos' => 126, 'satisfaccion' => 93, 'tendencia' => 'estable'],
        ];
    }
}
