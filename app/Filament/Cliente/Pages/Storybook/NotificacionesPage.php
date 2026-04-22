<?php

namespace App\Filament\Cliente\Pages\Storybook;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class NotificacionesPage extends Page
{
    protected string $view = 'filament.cliente.pages.storybook.notificaciones';

    protected static string|UnitEnum|null $navigationGroup = 'Storybook';

    protected static ?string $navigationLabel = 'Notificaciones';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $title = 'Notificaciones';

    protected static ?int $navigationSort = 18;
}
