<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Roles\Pages;

use App\Filament\Cliente\Resources\Roles\RolResource;
use App\Filament\Cliente\Widgets\UxPrototype\RolesHeroWidget;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RolResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            RolesHeroWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
