<?php

namespace App\Filament\Resources\Felicitaciones\Pages;

use App\Filament\Resources\Felicitaciones\FelicitacionResource;
use App\Filament\Support\CatalogAdminListRecords;

class ListFelicitaciones extends CatalogAdminListRecords
{
    protected static string $resource = FelicitacionResource::class;
}
