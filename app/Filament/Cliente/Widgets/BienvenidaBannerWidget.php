<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class BienvenidaBannerWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.bienvenida-banner';

    protected int|string|array $columnSpan = 'full';

    public function getSaludo(): string
    {
        $hora = (int) now()->format('G');

        return match (true) {
            $hora < 12 => 'Buenos días',
            $hora < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };
    }

    public function getNombreEmpresa(): string
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Empresa ? $tenant->nombre : 'Tu empresa';
    }

    public function getNombreUsuario(): string
    {
        $user = auth()->user();

        if ($user === null) {
            return '';
        }

        $primerNombre = explode(' ', trim((string) $user->name))[0] ?? '';

        return $primerNombre;
    }

    /**
     * @return array<int, array{label: string, tone: string}>
     */
    public function getChips(): array
    {
        return [
            ['label' => '5 solicitudes por aprobar', 'tone' => 'warning'],
            ['label' => '2 cartas SUA pendientes', 'tone' => 'danger'],
            ['label' => '98% registro al día', 'tone' => 'success'],
            ['label' => 'Nueva encuesta activa', 'tone' => 'info'],
        ];
    }
}
