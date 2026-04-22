<x-filament-panels::page>
    <div wire:poll.5s class="space-y-4">
        @php
            $importaciones = $this->getImportaciones();
        @endphp

        @if($importaciones->isEmpty())
            <x-filament::section>
                <p class="text-gray-500 dark:text-gray-400">No hay importaciones registradas.</p>
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">Historial de importaciones</x-slot>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Se actualiza cada 5 segundos.</p>

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="fi-ta-header-cell px-3 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">ID</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">Tipo</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">Estado</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-end text-sm font-semibold text-gray-950 dark:text-white">Total</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-end text-sm font-semibold text-gray-950 dark:text-white">Procesadas</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-end text-sm font-semibold text-gray-950 dark:text-white">Éxito</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-end text-sm font-semibold text-gray-950 dark:text-white">Errores</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">Iniciado</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">Completado</th>
                                <th class="fi-ta-header-cell px-3 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($importaciones as $imp)
                                <tr class="fi-ta-row">
                                    <td class="fi-ta-cell px-3 py-2 text-sm text-gray-950 dark:text-white">{{ $imp->id }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $imp->tipo }}</td>
                                    <td class="fi-ta-cell px-3 py-2">
                                        @php
                                            $estadoBadge = match($imp->estado) {
                                                \App\Models\Importacion::ESTADO_PENDIENTE => 'gray',
                                                \App\Models\Importacion::ESTADO_PROCESANDO => 'warning',
                                                \App\Models\Importacion::ESTADO_COMPLETADA => 'success',
                                                \App\Models\Importacion::ESTADO_CON_ERRORES => 'danger',
                                                \App\Models\Importacion::ESTADO_FALLIDA => 'danger',
                                                default => 'gray',
                                            };
                                        @endphp
                                        <x-filament::badge :color="$estadoBadge">{{ $imp->estado }}</x-filament::badge>
                                    </td>
                                    <td class="fi-ta-cell px-3 py-2 text-end text-sm text-gray-600 dark:text-gray-300">{{ $imp->total_filas }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-end text-sm text-gray-600 dark:text-gray-300">{{ $imp->filas_procesadas }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-end text-sm text-gray-600 dark:text-gray-300">{{ $imp->filas_exitosas }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-end text-sm text-gray-600 dark:text-gray-300">{{ $imp->filas_con_error }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $imp->iniciado_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $imp->completado_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="fi-ta-cell px-3 py-2">
                                        @if($imp->estado === \App\Models\Importacion::ESTADO_CON_ERRORES && $this->getDescargaErroresUrl($imp))
                                            <a href="{{ $this->getDescargaErroresUrl($imp) }}"
                                               class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-sm gap-1 px-2 py-1.5 text-sm inline-grid">
                                                Descargar errores
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
