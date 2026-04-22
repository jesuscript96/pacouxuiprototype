<?php

namespace App\Filament\Cliente\Widgets;

use App\Models\CartaSua;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CartasSuaStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            return [];
        }

        $base = CartaSua::where('empresa_id', $tenant->id);

        $total = (clone $base)->count();

        $pendientes = (clone $base)
            ->whereNull('primera_visualizacion')
            ->where('firmado', false)
            ->count();

        $vistas = (clone $base)
            ->whereNotNull('primera_visualizacion')
            ->where('firmado', false)
            ->count();

        $firmadas = (clone $base)
            ->where('firmado', true)
            ->count();

        return [
            Stat::make('Total', number_format($total))
                ->description('Documentos registrados')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('gray'),

            Stat::make('Pendientes', number_format($pendientes))
                ->description('Por firmar')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Vistas', number_format($vistas))
                ->description('En revisión')
                ->descriptionIcon('heroicon-o-eye')
                ->color('info'),

            Stat::make('Firmadas', number_format($firmadas))
                ->description('Documentos finalizados')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
