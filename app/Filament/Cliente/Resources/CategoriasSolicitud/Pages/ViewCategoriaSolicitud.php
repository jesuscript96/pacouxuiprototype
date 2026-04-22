<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CategoriasSolicitud\Pages;

use App\Filament\Cliente\Resources\CategoriasSolicitud\CategoriaSolicitudResource;
use App\Models\CategoriaSolicitud;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCategoriaSolicitud extends ViewRecord
{
    protected static string $resource = CategoriaSolicitudResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var CategoriaSolicitud $record */
        $record = $this->record;

        return $record->nombre;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
