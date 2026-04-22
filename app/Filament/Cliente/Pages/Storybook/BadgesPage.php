<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class BadgesPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.badges';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Badges';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $title = 'Badges / Etiquetas';

    protected static ?int $navigationSort = 9;
}
