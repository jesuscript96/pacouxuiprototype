<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Widgets\UxPrototype;

use Filament\Widgets\Widget;

class VacantesHeroWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.ux-prototype.vacantes-hero';

    protected int|string|array $columnSpan = 'full';
}
