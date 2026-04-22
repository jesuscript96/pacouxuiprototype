<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Empresa;

final class RoleHelper
{
    /**
     * Prefijo visible para "Nombre para mostrar" de roles con empresa.
     */
    public static function displayNamePrefixForEmpresa(Empresa $empresa): string
    {
        return $empresa->nombre.' - ';
    }
}
