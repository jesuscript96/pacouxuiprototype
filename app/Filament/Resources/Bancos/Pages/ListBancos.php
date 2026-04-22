<?php

namespace App\Filament\Resources\Bancos\Pages;

use App\Filament\Resources\Bancos\BancoResource;
use App\Filament\Support\CatalogAdminListRecords;

class ListBancos extends CatalogAdminListRecords
{
    protected static string $resource = BancoResource::class;
}
