<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class GridsPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.grids';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Grids';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $title = 'Grids y distribución';

    protected static ?int $navigationSort = 7;
}
