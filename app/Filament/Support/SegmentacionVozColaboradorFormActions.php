<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\TemaVozColaborador;
use App\Models\User;

/**
 * BL: Lógica compartida del formulario de segmentación voz (modal y páginas create/edit).
 */
final class SegmentacionVozColaboradorFormActions
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: list<int>}
     */
    public static function splitPayloadForSave(array $data): array
    {
        if (! ($data['exclusivo_para_empresa_toggle'] ?? false)) {
            $data['exclusivo_para_empresa'] = null;
        }
        unset($data['exclusivo_para_empresa_toggle']);

        $todos = $data['todos_colaboradores_toggle'] ?? true;
        $colaboradoresIds = $todos
            ? User::query()->whereJsonContains('tipo', 'cliente')->pluck('id')->all()
            : self::collectColaboradorIdsFromData($data);
        unset($data['todos_colaboradores_toggle']);
        foreach (array_keys($data) as $key) {
            if (str_starts_with((string) $key, 'colaborador_')) {
                unset($data[$key]);
            }
        }

        return [$data, $colaboradoresIds];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateFill(TemaVozColaborador $record, array $data): array
    {
        $data['exclusivo_para_empresa_toggle'] = filled($data['exclusivo_para_empresa'] ?? null);

        $linkedIds = $record->usuarios->modelKeys();
        $allClienteIds = User::query()->whereJsonContains('tipo', 'cliente')->pluck('id')->all();
        $data['todos_colaboradores_toggle'] = count($linkedIds) === count($allClienteIds) && count($allClienteIds) > 0;
        foreach ($linkedIds as $id) {
            $data['colaborador_'.$id] = true;
        }

        return $data;
    }

    public static function afterPersist(TemaVozColaborador $record, array $colaboradorIds): void
    {
        $record->syncEmpresasSegunExclusivo();
        $record->usuarios()->sync($colaboradorIds);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private static function collectColaboradorIdsFromData(array $data): array
    {
        $ids = [];
        foreach ($data as $key => $value) {
            if (str_starts_with((string) $key, 'colaborador_') && $value) {
                $ids[] = (int) str_replace('colaborador_', '', (string) $key);
            }
        }

        return $ids;
    }
}
