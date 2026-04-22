<?php

namespace App\Filament\Cliente\Resources\BajasColaboradores\Pages;

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Models\BajaColaborador;
use App\Services\ColaboradorBajaService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBajaColaborador extends EditRecord
{
    protected static string $resource = BajaColaboradorResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof BajaColaborador) {
            return parent::handleRecordUpdate($record, $data);
        }

        return app(ColaboradorBajaService::class)->actualizarBaja($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
