<?php

declare(strict_types=1);

namespace App\Filament\Resources\VerificacionCuentas\Widgets;

use App\Models\CuentaBancaria;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContadorCuentasPendientesWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $sinVerificar = CuentaBancaria::query()->sinVerificar()->count();
        $pendientesEnvio = CuentaBancaria::query()->sinVerificar()->pendientesDeEnvio()->count();
        $verificadas = CuentaBancaria::query()->verificadas()->count();
        $rechazadas = CuentaBancaria::query()->rechazadas()->count();

        return [
            Stat::make('Sin verificar', $sinVerificar)
                ->description('Cuentas pendientes de verificación')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Pendientes de envío', $pendientesEnvio)
                ->description('Listas para verificación bancaria (STP)')
                ->descriptionIcon('heroicon-o-paper-airplane')
                ->color('info'),

            Stat::make('Verificadas', $verificadas)
                ->description('Cuentas aprobadas')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Rechazadas', $rechazadas)
                ->description('Cuentas rechazadas')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
