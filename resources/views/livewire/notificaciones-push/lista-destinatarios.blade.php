@php
    // BL: Color de acento del panel admin (marca); el badge no usa tokens Tailwind purgables.
    $accent = '#3148c8';
@endphp

{{-- BL: Raíz w-full + filas min-w-0: el ViewField/Livewire puede encogerse; ml-auto en el toggle absorbe el hueco y lo ancla a la derecha. --}}
<div class="w-full min-w-0 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
    {{-- Header --}}
    <div class="w-full border-b border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex w-full items-center justify-between gap-4">
            <span class="text-sm font-medium text-gray-900 dark:text-white">Destinatarios</span>
            <span
                class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                style="background-color: #e8ebfc; color: {{ $accent }};"
            >
                {{ $totalSeleccionados }} de {{ $totalFiltrados }}
            </span>
        </div>
    </div>

    {{-- Buscador --}}
    <div class="w-full border-b border-gray-200 px-5 py-3 dark:border-gray-700">
        <label for="np-busqueda-destinatarios" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Buscar colaborador
        </label>
        <input
            id="np-busqueda-destinatarios"
            type="text"
            wire:model.live.debounce.300ms="busqueda"
            placeholder="Buscar:"
            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
            style="min-height: 2.5rem; box-sizing: border-box;"
        >
    </div>

    {{--
        BL: El toggle de Filament usa Alpine (x-data state + x-on:click). Tras un render de Livewire, el morph puede
        reutilizar el mismo nodo DOM y Alpine no re-lee el estado del servidor → todos se ven “apagados”.
        Solución: wire:key en un wrapper que incluya el estado (0/1) para forzar remount y re-hidratar x-data desde Blade.
        Se mantiene wire:click.prevent hacia los métodos PHP sin tocar ListaDestinatarios.php.
    --}}
    <div class="w-full min-w-0 border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50">
        <div class="flex w-full min-w-0 items-center px-5 py-4">
            <div class="min-w-0 flex-1 pr-4">
                <p class="text-sm font-medium text-gray-900 dark:text-white">Todos</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $selectAll ? 'Permite seleccionar todos los usuarios' : 'Selección manual' }}
                </p>
            </div>
            <div class="ml-auto flex-none self-center" wire:key="np-toggle-wrap-all-{{ (int) $selectAll }}">
                <x-filament::toggle
                    on-color="primary"
                    off-color="gray"
                    :state="(int) $selectAll"
                    wire:click.prevent="toggleSelectAll"
                />
            </div>
        </div>
    </div>

    {{-- Lista de colaboradores --}}
    <div class="w-full min-w-0 max-h-80 divide-y divide-gray-100 overflow-y-auto dark:divide-gray-800">
        @forelse ($colaboradores as $colaborador)
            @php
                $estaSeleccionado = $this->isSelected($colaborador->id);
            @endphp
            <div
                class="flex w-full min-w-0 items-center px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50"
                wire:key="colaborador-{{ $colaborador->id }}"
            >
                <div class="min-w-0 flex-1 pr-4">
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                        {{ $colaborador->nombre_completo ?? trim($colaborador->nombre.' '.$colaborador->apellido_paterno) }}
                    </p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $colaborador->ubicacion?->nombre ?? 'Sin ubicación' }}
                        @if ($colaborador->puesto)
                            | {{ $colaborador->puesto->nombre }}
                        @endif
                    </p>
                </div>
                <div class="ml-auto flex-none self-center" wire:key="np-toggle-wrap-{{ $colaborador->id }}-{{ (int) $estaSeleccionado }}">
                    <x-filament::toggle
                        on-color="primary"
                        off-color="gray"
                        :state="(int) $estaSeleccionado"
                        wire:click.prevent="toggleColaborador({{ $colaborador->id }})"
                    />
                </div>
            </div>
        @empty
            <div class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                @if ($busqueda)
                    No se encontraron resultados para «{{ $busqueda }}».
                @else
                    No hay colaboradores.
                @endif
            </div>
        @endforelse
    </div>

    @if ($colaboradores->hasPages())
        <div class="w-full border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-800">
            {{ $colaboradores->links('livewire.notificaciones-push.pagination') }}
        </div>
    @endif
</div>
