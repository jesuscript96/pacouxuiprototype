<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class SelectsPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.selects';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Selects';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chevron-up-down';

    protected static ?string $title = 'Selects';

    protected static ?int $navigationSort = 15;
}
