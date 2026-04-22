<?php

namespace App\Filament\Cliente\Pages;

use App\Filament\Cliente\Widgets\AccesosRapidosWidget;
use App\Filament\Cliente\Widgets\ActividadTimelineWidget;
use App\Filament\Cliente\Widgets\AniversariosWidget;
use App\Filament\Cliente\Widgets\BajasProgramadasWidget;
use App\Filament\Cliente\Widgets\BienvenidaBannerWidget;
use App\Filament\Cliente\Widgets\CumpleanosWidget;
use App\Filament\Cliente\Widgets\DashboardHeroWidget;
use App\Filament\Cliente\Widgets\DashboardMetricsWidget;
use App\Filament\Cliente\Widgets\DistribucionVisualWidget;
use App\Filament\Cliente\Widgets\ExploraStorybookWidget;
use App\Filament\Cliente\Widgets\KpisProgressWidget;
use App\Filament\Cliente\Widgets\ProximasAccionesWidget;
use App\Filament\Cliente\Widgets\ResumenEjecutivoWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Inicio';

    protected static ?string $title = null;

    protected static ?int $navigationSort = -1;

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            BienvenidaBannerWidget::class,
            DashboardHeroWidget::class,
            AccesosRapidosWidget::class,
            KpisProgressWidget::class,
            DashboardMetricsWidget::class,
            DistribucionVisualWidget::class,
            ActividadTimelineWidget::class,
            ProximasAccionesWidget::class,
            ResumenEjecutivoWidget::class,
            CumpleanosWidget::class,
            AniversariosWidget::class,
            BajasProgramadasWidget::class,
            ExploraStorybookWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
