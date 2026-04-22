<?php

namespace App\Filament\Cliente\Widgets;

use Filament\Widgets\Widget;

class ProximasAccionesWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.proximas-acciones';

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    /**
     * @return array<int, array{titulo: string, descripcion: string, cta: string, urgencia: string, icono: string, tiempo: string}>
     */
    public function getAcciones(): array
    {
        return [
            [
                'titulo' => '5 solicitudes por aprobar',
                'descripcion' => 'La más antigua lleva 3 días esperando respuesta.',
                'cta' => 'Revisar solicitudes',
                'urgencia' => 'alta',
                'icono' => 'heroicon-o-clipboard-document-check',
                'tiempo' => 'Hoy',
            ],
            [
                'titulo' => '2 cartas SUA sin firmar',
                'descripcion' => 'Cierre del periodo mensual en 4 días.',
                'cta' => 'Completar firmas',
                'urgencia' => 'media',
                'icono' => 'heroicon-o-document-check',
                'tiempo' => '4 días',
            ],
            [
                'titulo' => 'Encuesta de clima',
                'descripcion' => 'Lanza la siguiente oleada antes del 30 de abril.',
                'cta' => 'Programar envío',
                'urgencia' => 'baja',
                'icono' => 'heroicon-o-megaphone',
                'tiempo' => '15 días',
            ],
        ];
    }
}
