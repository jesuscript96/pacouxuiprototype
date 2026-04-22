<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class DatePickersPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.date-pickers';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Date Pickers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $title = 'Date Pickers';

    protected static ?int $navigationSort = 17;
}
