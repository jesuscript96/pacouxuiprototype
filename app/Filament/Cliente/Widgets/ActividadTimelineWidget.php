<?php

namespace App\Filament\Cliente\Widgets;

use Filament\Widgets\Widget;

class ActividadTimelineWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.actividad-timeline';

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 2];

    /**
     * @return array<int, array{tipo: string, titulo: string, descripcion: string, cuando: string, icono: string}>
     */
    public function getEventos(): array
    {
        return [
            [
                'tipo' => 'success',
                'titulo' => '3 nuevos ingresos',
                'descripcion' => 'Ana López, Carlos Ruiz y María Tamez se integraron al equipo de Operaciones.',
                'cuando' => 'Hace 12 minutos',
                'icono' => 'heroicon-o-user-plus',
            ],
            [
                'tipo' => 'info',
                'titulo' => '42 cartas SUA firmadas',
                'descripcion' => 'Progreso del batch mensual: 64% completado.',
                'cuando' => 'Hace 1 hora',
                'icono' => 'heroicon-o-document-check',
            ],
            [
                'tipo' => 'warning',
                'titulo' => '5 solicitudes esperan aprobación',
                'descripcion' => 'Vacaciones, permisos y cambios de datos personales.',
                'cuando' => 'Hace 2 horas',
                'icono' => 'heroicon-o-clipboard-document-list',
            ],
            [
                'tipo' => 'primary',
                'titulo' => 'Encuesta de clima laboral publicada',
                'descripcion' => '1,423 respuestas recibidas en las primeras 3 horas.',
                'cuando' => 'Hace 5 horas',
                'icono' => 'heroicon-o-megaphone',
            ],
            [
                'tipo' => 'danger',
                'titulo' => '1 baja programada',
                'descripcion' => 'Luis Hernández · Último día: 30 de abril.',
                'cuando' => 'Ayer · 18:42',
                'icono' => 'heroicon-o-user-minus',
            ],
            [
                'tipo' => 'info',
                'titulo' => '4 documentos corporativos publicados',
                'descripcion' => 'Política de home office, manual de bienvenida y 2 más.',
                'cuando' => 'Ayer · 15:10',
                'icono' => 'heroicon-o-folder-open',
            ],
            [
                'tipo' => 'success',
                'titulo' => '12 aniversarios celebrados hoy',
                'descripcion' => 'Se enviaron reconocimientos automáticos a todos.',
                'cuando' => 'Hoy · 09:00',
                'icono' => 'heroicon-o-trophy',
            ],
        ];
    }
}
