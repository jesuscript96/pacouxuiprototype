<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\UsuariosEmpresa\Pages;

use App\Filament\Cliente\Resources\UsuariosEmpresa\UsuarioEmpresaResource;
use Filament\Resources\Pages\ListRecords;

class ListUsuariosEmpresa extends ListRecords
{
    protected static string $resource = UsuarioEmpresaResource::class;
}
