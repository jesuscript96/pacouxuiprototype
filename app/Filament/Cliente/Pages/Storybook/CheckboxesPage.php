<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class CheckboxesPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.checkboxes';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Checkboxes / Toggles';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $title = 'Checkboxes y Toggles';

    protected static ?int $navigationSort = 16;
}
