<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class CamposTextoPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.campos-texto';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Campos de texto';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $title = 'Campos de texto';

    protected static ?int $navigationSort = 14;
}
