<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shield\Pages;

use App\Filament\Resources\Shield\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * BL: Los roles usan páginas dedicadas (CreateRole/EditRole) porque la lógica de
 * sincronización de permisos vive en hooks afterCreate/afterSave de esas páginas.
 * Un modal (CreateAction/EditAction) no ejecuta esos hooks y los permisos nunca
 * llegan a la BD. No migrar a CatalogAdminListRecords.
 */
class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
