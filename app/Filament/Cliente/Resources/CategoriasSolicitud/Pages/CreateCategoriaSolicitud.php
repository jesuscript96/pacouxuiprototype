<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CategoriasSolicitud\Pages;

use App\Filament\Cliente\Resources\CategoriasSolicitud\CategoriaSolicitudResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoriaSolicitud extends CreateRecord
{
    protected static string $resource = CategoriaSolicitudResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = Filament::getTenant()?->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
