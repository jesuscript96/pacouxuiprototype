<?php

namespace App\Filament\Resources\SegmentacionProductos\Pages;

use App\Filament\Resources\SegmentacionProductos\SegmentacionProductosResource;
use Filament\Resources\Pages\ListRecords;

class ListSegmentacionProductos extends ListRecords
{
    protected static string $resource = SegmentacionProductosResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
