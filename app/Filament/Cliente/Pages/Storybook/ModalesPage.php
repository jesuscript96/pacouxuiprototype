<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class ModalesPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.modales';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Modales';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-window';

    protected static ?string $title = 'Modales y diálogos';

    protected static ?int $navigationSort = 19;
}
