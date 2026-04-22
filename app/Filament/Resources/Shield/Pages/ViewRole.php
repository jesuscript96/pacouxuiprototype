<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shield\Pages;

use App\Filament\Resources\Shield\RoleResource;
use Filament\Actions\EditAction;

class ViewRole extends \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
