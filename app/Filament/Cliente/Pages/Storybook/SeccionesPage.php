<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class SeccionesPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.secciones';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Secciones';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $title = 'Secciones y contenedores';

    protected static ?int $navigationSort = 6;
}
