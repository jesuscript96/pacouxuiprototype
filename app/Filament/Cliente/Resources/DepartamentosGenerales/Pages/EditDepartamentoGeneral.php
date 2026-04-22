<?php

namespace App\Filament\Cliente\Resources\DepartamentosGenerales\Pages;

use App\Filament\Cliente\Resources\DepartamentosGenerales\DepartamentoGeneralResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDepartamentoGeneral extends EditRecord
{
    protected static string $resource = DepartamentoGeneralResource::class;

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
