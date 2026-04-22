<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CategoriasSolicitud\Pages;

use App\Filament\Cliente\Resources\CategoriasSolicitud\CategoriaSolicitudResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaSolicitud extends EditRecord
{
    protected static string $resource = CategoriaSolicitudResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['empresa_id'] = $this->record->getAttribute('empresa_id');

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
