<?php

namespace App\Filament\Cliente\Widgets;

use Filament\Widgets\Widget;

class DashboardHeroWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.dashboard-hero';

    protected int|string|array $columnSpan = 'full';
}
