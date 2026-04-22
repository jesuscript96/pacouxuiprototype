<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class IconosPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.iconos';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Iconos';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $title = 'Iconos';

    protected static ?int $navigationSort = 13;
}
