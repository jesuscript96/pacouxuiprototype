<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class TablasPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.tablas';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Tablas';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $title = 'Tablas';

    protected static ?int $navigationSort = 11;
}
