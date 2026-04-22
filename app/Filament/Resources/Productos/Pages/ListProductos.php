<?php

namespace App\Filament\Resources\Productos\Pages;

use App\Filament\Resources\Productos\ProductoResource;
use App\Filament\Support\CatalogAdminListRecords;

class ListProductos extends CatalogAdminListRecords
{
    protected static string $resource = ProductoResource::class;
}
