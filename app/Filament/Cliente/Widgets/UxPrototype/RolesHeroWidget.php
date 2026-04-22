<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Widgets\UxPrototype;

use Filament\Widgets\Widget;

class RolesHeroWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.ux-prototype.roles-hero';

    protected int|string|array $columnSpan = 'full';
}
