<x-filament-panels::page>
    @if ($this->mostrarResultados && count($this->resultados) > 0)
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                Resultados de la importación
            </x-slot>

            <div class="mb-4">
                <x-filament::button color="gray" size="sm" wire:click="limpiarResultados" type="button">
                    Limpiar resultados
                </x-filament::button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="fi-ta-header-cell px-3 py-3 text-start font-semibold text-gray-950 dark:text-white">Fila</th>
                            <th class="fi-ta-header-cell px-3 py-3 text-start font-semibold text-gray-950 dark:text-white">Estado</th>
                            <th class="fi-ta-header-cell px-3 py-3 text-start font-semibold text-gray-950 dark:text-white">Mensaje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($this->resultados as $resultado)
                            <tr class="fi-ta-row @if ($resultado['status'] === 'error') bg-red-50 dark:bg-red-950/20 @else bg-emerald-50 dark:bg-emerald-950/20 @endif">
                                <td class="fi-ta-cell px-3 py-2 text-gray-950 dark:text-white">{{ $resultado['fila'] }}</td>
                                <td class="fi-ta-cell px-3 py-2">
                                    @if ($resultado['status'] === 'success')
                                        <x-filament::badge color="success">Éxito</x-filament::badge>
                                    @else
                                        <x-filament::badge color="danger">Error</x-filament::badge>
                                    @endif
                                </td>
                                <td class="fi-ta-cell px-3 py-2 text-gray-700 dark:text-gray-300">{{ $resultado['mensaje'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif

    {{ $this->content }}

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Instrucciones
        </x-slot>

        <div class="max-w-none text-sm text-gray-700 dark:text-gray-300 space-y-3">
            <ol class="list-decimal space-y-2 ps-5">
                <li>Descargue la plantilla Excel con el botón del encabezado «Descargar plantilla».</li>
                <li>Complete los datos:
                    <ul class="mt-2 list-disc space-y-1 ps-5">
                        <li><strong>email</strong> o <strong>numero_colaborador</strong>: al menos uno obligatorio.</li>
                        <li><strong>fecha_baja</strong>: YYYY-MM-DD (ej. 2026-03-25). Fechas futuras programan la baja.</li>
                        <li><strong>motivo</strong>: ABANDONO, DESPIDO, FALLECIMIENTO, RENUNCIA o TERMINO_CONTRATO.</li>
                        <li><strong>comentarios</strong>: opcional.</li>
                    </ul>
                </li>
                <li>Elimine la fila de ejemplo antes de subir el archivo.</li>
                <li>Suba el archivo y pulse «Procesar bajas».</li>
            </ol>

            <p class="text-amber-700 dark:text-amber-400">
                <strong>Importante:</strong> las bajas con fecha de hoy o anterior se ejecutan al procesar; las fechas futuras quedan programadas.
            </p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
