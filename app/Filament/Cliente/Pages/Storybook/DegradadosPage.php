<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class DegradadosPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.degradados';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Degradados';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sun';

    protected static ?string $title = 'Degradados';

    protected static ?int $navigationSort = 4;
}
