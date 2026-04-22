<?php

declare(strict_types=1);

namespace App\Livewire\NotificacionesPush;

use App\Contracts\ObtenerDestinatariosPushInterface;
use App\Models\NotificacionPush;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ListaDestinatarios extends Component
{
    use WithPagination;

    public int $empresaId;

    /** @var array<string, mixed> */
    public array $filtros = [];

    public bool $selectAll = true;

    /** @var list<int> */
    public array $manualActivation = [];

    /** @var list<int> */
    public array $manualDeactivation = [];

    public string $busqueda = '';

    public int $perPage = 20;

    /**
     * @param  array<string, mixed>  $filtros
     * @param  array<string, mixed>|null  $destinatariosEstado
     */
    public function mount(
        int $empresaId,
        array $filtros = [],
        ?array $destinatariosEstado = null,
    ): void {
        $this->empresaId = $empresaId;
        $this->filtros = $filtros;

        if (is_array($destinatariosEstado)) {
            $this->selectAll = (bool) ($destinatariosEstado['select_all'] ?? true);
            $this->manualActivation = array_values(array_map('intval', $destinatariosEstado['manual_activation'] ?? []));
            $this->manualDeactivation = array_values(array_map('intval', $destinatariosEstado['manual_deactivation'] ?? []));
        }

        $this->dispatchSeleccionActualizada();
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = ! $this->selectAll;
        $this->manualActivation = [];
        $this->manualDeactivation = [];

        $this->dispatchSeleccionActualizada();
    }

    public function toggleColaborador(int $colaboradorId): void
    {
        if ($this->selectAll) {
            if (in_array($colaboradorId, $this->manualDeactivation, true)) {
                $this->manualDeactivation = array_values(array_diff($this->manualDeactivation, [$colaboradorId]));
            } else {
                $this->manualDeactivation[] = $colaboradorId;
            }
        } elseif (in_array($colaboradorId, $this->manualActivation, true)) {
            $this->manualActivation = array_values(array_diff($this->manualActivation, [$colaboradorId]));
        } else {
            $this->manualActivation[] = $colaboradorId;
        }

        $this->dispatchSeleccionActualizada();
    }

    public function isSelected(int $colaboradorId): bool
    {
        if ($this->selectAll) {
            return ! in_array($colaboradorId, $this->manualDeactivation, true);
        }

        return in_array($colaboradorId, $this->manualActivation, true);
    }

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSeleccionState(): array
    {
        $totalFiltrados = $this->contarTotalFiltrados();

        return [
            'select_all' => $this->selectAll,
            'manual_activation' => $this->manualActivation,
            'manual_deactivation' => $this->manualDeactivation,
            'total_seleccionados' => $this->calcularTotalSeleccionados($totalFiltrados),
        ];
    }

    public function render(): View
    {
        $notificacionTemp = new NotificacionPush([
            'empresa_id' => $this->empresaId,
            'filtros' => $this->filtros,
        ]);

        $servicio = app(ObtenerDestinatariosPushInterface::class);

        /** @var LengthAwarePaginator<int, \App\Models\Colaborador> $colaboradores */
        $colaboradores = $servicio->obtenerColaboradoresPaginados(
            $notificacionTemp,
            $this->perPage,
            $this->busqueda !== '' ? $this->busqueda : null
        );

        $totalFiltrados = $servicio->contarDestinatarios($notificacionTemp);
        $totalSeleccionados = $this->calcularTotalSeleccionados($totalFiltrados);

        return view('livewire.notificaciones-push.lista-destinatarios', [
            'colaboradores' => $colaboradores,
            'totalFiltrados' => $totalFiltrados,
            'totalSeleccionados' => $totalSeleccionados,
        ]);
    }

    protected function contarTotalFiltrados(): int
    {
        $notificacionTemp = new NotificacionPush([
            'empresa_id' => $this->empresaId,
            'filtros' => $this->filtros,
        ]);

        return app(ObtenerDestinatariosPushInterface::class)->contarDestinatarios($notificacionTemp);
    }

    protected function calcularTotalSeleccionados(int $totalFiltrados): int
    {
        if ($this->selectAll) {
            return max(0, $totalFiltrados - count($this->manualDeactivation));
        }

        return count($this->manualActivation);
    }

    protected function dispatchSeleccionActualizada(): void
    {
        $totalFiltrados = $this->contarTotalFiltrados();

        $this->dispatch(
            'seleccionActualizada',
            selectAll: $this->selectAll,
            manualActivation: $this->manualActivation,
            manualDeactivation: $this->manualDeactivation,
            totalSeleccionados: $this->calcularTotalSeleccionados($totalFiltrados),
        );
    }
}
