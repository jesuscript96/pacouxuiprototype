<?php

namespace App\Filament\Resources\SegmentacionVozColaboradores\Pages;

use App\Filament\Resources\SegmentacionVozColaboradores\SegmentacionVozColaboradorResource;
use App\Filament\Support\SegmentacionVozColaboradorFormActions;
use App\Models\TemaVozColaborador;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSegmentacionVozColaborador extends EditRecord
{
    protected static string $resource = SegmentacionVozColaboradorResource::class;

    /** @var list<int> */
    protected array $colaboradoresIdsToSync = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return SegmentacionVozColaboradorFormActions::mutateFill($this->record, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        [$clean, $this->colaboradoresIdsToSync] = SegmentacionVozColaboradorFormActions::splitPayloadForSave($data);

        return $clean;
    }

    protected function afterSave(): void
    {
        /** @var TemaVozColaborador $record */
        $record = $this->record;
        SegmentacionVozColaboradorFormActions::afterPersist($record, $this->colaboradoresIdsToSync);

        $this->dispatch('refresh-empresas-relation-manager');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
