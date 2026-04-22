<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Concerns;

use Livewire\Attributes\On;

trait SincronizaDestinatariosNotificacionPushForm
{
    #[On('seleccionActualizada')]
    public function sincronizarDestinatariosNotificacionPush(
        bool $selectAll,
        array $manualActivation,
        array $manualDeactivation,
        int $totalSeleccionados,
    ): void {
        $this->data['filtros'] ??= [];
        $this->data['filtros']['destinatarios'] = [
            'select_all' => $selectAll,
            'manual_activation' => $manualActivation,
            'manual_deactivation' => $manualDeactivation,
            'total_seleccionados' => $totalSeleccionados,
        ];
    }
}
