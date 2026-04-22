<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class BotonesPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.botones';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Botones';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cursor-arrow-rays';

    protected static ?string $title = 'Botones';

    protected static ?int $navigationSort = 8;
}
