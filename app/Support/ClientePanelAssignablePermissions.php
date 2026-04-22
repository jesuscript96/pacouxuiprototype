<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

final class ClientePanelAssignablePermissions
{
    /**
     * Sufijos Studly tras ":" en permisos (Acción:Modelo), alineados con recursos del panel Cliente
     * y con la gestión de roles/usuarios en ese panel.
     *
     * @var list<string>
     */
    public const MODEL_SUFFIXES = [
        'Colaborador',
        'BajaColaborador',
        'Departamento',
        'DepartamentoGeneral',
        'Area',
        'AreaGeneral',
        'Puesto',
        'PuestoGeneral',
        'Region',
        'Ubicacion',
        'CentroPago',
        'DocumentoCorporativo',
        'SpatieRole',
        'User',
    ];

    /**
     * @return Builder<Permission>
     */
    public static function query(): Builder
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->where(function (Builder $q): void {
                foreach (self::MODEL_SUFFIXES as $suffix) {
                    $q->orWhere('name', 'like', '%:'.$suffix);
                }
            })
            ->orderBy('name');
    }
}
