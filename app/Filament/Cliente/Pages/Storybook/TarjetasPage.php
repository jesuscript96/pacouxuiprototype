<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class TarjetasPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.tarjetas';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Tarjetas';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $title = 'Tarjetas / Cards';

    protected static ?int $navigationSort = 10;
}
