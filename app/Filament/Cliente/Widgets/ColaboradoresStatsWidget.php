<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ColaboradoresStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            return [];
        }

        $empresaId = $tenant->id;

        $totalActivos = Colaborador::query()
            ->porEmpresa($empresaId)
            ->activos()
            ->count();

        $ingresosEsteMes = Colaborador::query()
            ->porEmpresa($empresaId)
            ->activos()
            ->whereMonth('fecha_ingreso', now()->month)
            ->whereYear('fecha_ingreso', now()->year)
            ->count();

        $bajasEsteMes = BajaColaborador::query()
            ->deEmpresa($empresaId)
            ->ejecutadas()
            ->whereMonth('fecha_baja', now()->month)
            ->whereYear('fecha_baja', now()->year)
            ->count();

        $bajasProgramadas = BajaColaborador::query()
            ->deEmpresa($empresaId)
            ->programadas()
            ->whereDate('fecha_baja', '>=', now()->toDateString())
            ->count();

        return [
            Stat::make('Colaboradores activos', number_format($totalActivos))
                ->description('Plantilla actual')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Ingresos este mes', number_format($ingresosEsteMes))
                ->description('Nuevos en '.now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('success'),

            Stat::make('Bajas este mes', number_format($bajasEsteMes))
                ->description('Ejecutadas en '.now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-o-user-minus')
                ->color($bajasEsteMes > 0 ? 'danger' : 'gray'),

            Stat::make('Bajas programadas', number_format($bajasProgramadas))
                ->description('Próximas a ejecutarse')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($bajasProgramadas > 0 ? 'warning' : 'gray'),
        ];
    }
}
