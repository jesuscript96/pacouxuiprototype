<?php

namespace App\Filament\Resources\SegmentacionVozColaboradores\Pages;

use App\Filament\Resources\SegmentacionVozColaboradores\SegmentacionVozColaboradorResource;
use App\Filament\Support\CatalogAdminListRecords;
use App\Filament\Support\SegmentacionVozColaboradorFormActions;
use App\Models\TemaVozColaborador;
use Filament\Actions\CreateAction;

class ListSegmentacionVozColaboradores extends CatalogAdminListRecords
{
    protected static string $resource = SegmentacionVozColaboradorResource::class;

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action->using(function (
            array $data,
            \Filament\Actions\Contracts\HasActions&\Filament\Schemas\Contracts\HasSchemas $livewire
        ): \Illuminate\Database\Eloquent\Model {
            [$clean, $ids] = SegmentacionVozColaboradorFormActions::splitPayloadForSave($data);
            $record = new TemaVozColaborador;
            $record->fill($clean);
            $record->save();
            SegmentacionVozColaboradorFormActions::afterPersist($record, $ids);

            return $record;
        });
    }
}
