<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Empresa;
use App\Models\SpatieRole;
use Illuminate\Support\Str;

/**
 * Convención alineada con RoleResource / RoleHelper:
 * - name: {slug_empresa}_{base_slug}
 * - display_name: {nombre_empresa} - {etiqueta}
 */
final class SeederRoleNaming
{
    public static function technical(Empresa $empresa, string $baseSlug): string
    {
        return Str::slug($empresa->nombre, '_').'_'.$baseSlug;
    }

    public static function display(Empresa $empresa, string $humanLabel): string
    {
        return RoleHelper::displayNamePrefixForEmpresa($empresa).$humanLabel;
    }

    public static function findForCompany(int $empresaId, string $baseSlug): ?SpatieRole
    {
        $empresa = Empresa::query()->find($empresaId);
        if ($empresa === null) {
            return null;
        }

        return SpatieRole::withoutGlobalScopes()
            ->where('name', self::technical($empresa, $baseSlug))
            ->where('company_id', $empresaId)
            ->first();
    }
}
