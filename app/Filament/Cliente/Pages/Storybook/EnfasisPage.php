<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class EnfasisPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.enfasis';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Énfasis / Estados';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected static ?string $title = 'Énfasis y estados';

    protected static ?int $navigationSort = 3;
}
