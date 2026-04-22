<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class TipografiaPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.tipografia';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Tipografía';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static ?string $title = 'Tipografía';

    protected static ?int $navigationSort = 2;
}
