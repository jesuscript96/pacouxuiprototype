<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuarioResource;
use App\Filament\Support\CatalogAdminListRecords;
use App\Services\UsuarioService;
use Filament\Actions\CreateAction;

class ListUsuarios extends CatalogAdminListRecords
{
    protected static string $resource = UsuarioResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action
            ->mutateDataUsing(fn (array $data): array => UsuarioResource::mutateFormDataBeforeCreateForModal($data))
            ->using(function (
                array $data,
                \Filament\Actions\Contracts\HasActions&\Filament\Schemas\Contracts\HasSchemas $livewire
            ): \Illuminate\Database\Eloquent\Model {
                return app(UsuarioService::class)->create($data);
            });
    }
}
