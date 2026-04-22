<?php

namespace App\Filament\Cliente\Widgets;

use Filament\Widgets\Widget;

class DashboardMetricsWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.dashboard-metrics';

    protected int|string|array $columnSpan = 'full';
}
