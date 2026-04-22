<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Concerns;

trait SanitizesNotificacionPushFiltros
{
    /**
     * @param  array<string, mixed>|null  $filtros
     * @return array<string, mixed>|null
     */
    protected function sanitizarFiltros(?array $filtros): ?array
    {
        if ($filtros === null) {
            return null;
        }

        $out = [];
        foreach ($filtros as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            $out[$key] = $value;
        }

        if (array_key_exists('con_adeudos', $out)) {
            $raw = $out['con_adeudos'];
            $out['con_adeudos'] = $raw === '1' || $raw === 1 || $raw === true;
        }

        return $out === [] ? null : $out;
    }
}
