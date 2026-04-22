<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductoResource;
use App\Models\Producto;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewProducto extends ViewRecord
{
    protected static string $resource = ProductoResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Producto $record */
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
