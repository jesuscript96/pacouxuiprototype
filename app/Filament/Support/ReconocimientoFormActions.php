<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\Empresa;
use App\Models\Reconocmiento;

/**
 * BL: Sincronización del pivot empresas_reconocimientos (crear/editar desde modal o página).
 */
final class ReconocimientoFormActions
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function syncEmpresasPivot(Reconocmiento $record, array $data): void
    {
        $pivot = [
            'es_enviable' => $record->es_enviable,
            'menciones_necesarias' => $record->menciones_necesarias,
        ];

        $empresaIds = $record->es_exclusivo
            ? (array) ($data['empresas'] ?? [])
            : Empresa::query()->pluck('id')->toArray();

        $sync = collect($empresaIds)->mapWithKeys(fn ($id) => [$id => $pivot])->all();
        $record->empresas()->sync($sync);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateFillWithEmpresas(Reconocmiento $record, array $data): array
    {
        $data['empresas'] = $record->empresas->pluck('id')->toArray();

        return $data;
    }
}
