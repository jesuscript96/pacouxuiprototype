<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CargarDocumentos\Pages;

use App\Filament\Cliente\Resources\CargarDocumentos\CargarDocumentosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListCargarDocumentos extends ListRecords
{
    protected static string $resource = CargarDocumentosResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Cargar documentos';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva carpeta'),
        ];
    }
}
