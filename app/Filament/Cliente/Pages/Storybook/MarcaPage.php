<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class MarcaPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.marca';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Marca Paco';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $title = 'Marca Paco';

    protected static ?int $navigationSort = 5;
}
