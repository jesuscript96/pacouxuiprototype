<?php

namespace App\Filament\Resources\SegmentacionVozColaboradores\Pages;

use App\Filament\Resources\SegmentacionVozColaboradores\SegmentacionVozColaboradorResource;
use App\Filament\Support\SegmentacionVozColaboradorFormActions;
use App\Models\TemaVozColaborador;
use Filament\Resources\Pages\CreateRecord;

class CreateSegmentacionVozColaborador extends CreateRecord
{
    protected static string $resource = SegmentacionVozColaboradorResource::class;

    /** @var list<int> */
    protected array $colaboradoresIdsToSync = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$clean, $this->colaboradoresIdsToSync] = SegmentacionVozColaboradorFormActions::splitPayloadForSave($data);

        return $clean;
    }

    protected function afterCreate(): void
    {
        /** @var TemaVozColaborador $record */
        $record = $this->record;
        SegmentacionVozColaboradorFormActions::afterPersist($record, $this->colaboradoresIdsToSync);
    }
}
