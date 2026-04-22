<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class ColoresPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.colores';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Colores';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $title = 'Paleta de colores';

    protected static ?int $navigationSort = 1;
}
