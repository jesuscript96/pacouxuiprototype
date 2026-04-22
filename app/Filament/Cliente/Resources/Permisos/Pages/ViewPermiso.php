<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Permisos\Pages;

use App\Filament\Cliente\Resources\Permisos\PermisoResource;
use App\Filament\Cliente\Resources\Permisos\Schemas\PermisoForm;
use App\Filament\Cliente\Resources\Permisos\Support\PermisoFormHydrator;
use App\Models\TipoSolicitud;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Vista de permiso ({@see \App\Models\TipoSolicitud}), solo lectura.
 */
class ViewPermiso extends ViewRecord
{
    protected static string $resource = PermisoResource::class;

    public function form(Schema $schema): Schema
    {
        /** @var TipoSolicitud $record */
        $record = $this->record;

        return PermisoForm::configure($schema, (int) $record->id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TipoSolicitud $record */
        $record = $this->getRecord();

        return PermisoFormHydrator::mergeRelaciones($record, $data);
    }

    public function getTitle(): string|Htmlable
    {
        /** @var TipoSolicitud $record */
        $record = $this->record;

        return $record->nombre;
    }

    /**
     * @return array<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
