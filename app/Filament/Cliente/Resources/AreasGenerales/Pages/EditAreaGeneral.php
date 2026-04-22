<?php

namespace App\Filament\Cliente\Resources\AreasGenerales\Pages;

use App\Filament\Cliente\Resources\AreasGenerales\AreaGeneralResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAreaGeneral extends EditRecord
{
    protected static string $resource = AreaGeneralResource::class;

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
