<div class="space-y-4">
    {{-- Resumen en cards --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-filament::section compact>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $importacion->total_filas ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total filas</div>
            </div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="text-center">
                <div class="text-2xl font-bold text-success-600">
                    {{ $importacion->filas_exitosas ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Creadas</div>
            </div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="text-center">
                <div class="text-2xl font-bold text-warning-600">
                    {{ ($importacion->filas_procesadas ?? 0) - ($importacion->filas_exitosas ?? 0) - ($importacion->filas_con_error ?? 0) }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Omitidas</div>
            </div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="text-center">
                <div class="text-2xl font-bold text-danger-600">
                    {{ $importacion->filas_con_error ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Errores</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Información general --}}
    <x-filament::section>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 text-sm md:grid-cols-2">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Archivo</dt>
                <dd class="text-gray-900 dark:text-white">
                    {{ $importacion->archivo_original ? basename($importacion->archivo_original) : '—' }}
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Fecha</dt>
                <dd class="text-gray-900 dark:text-white">
                    {{ $importacion->created_at->format('d/m/Y H:i:s') }}
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                <dd>
                    @php
                        $badgeColor = match($importacion->estado) {
                            \App\Models\Importacion::ESTADO_COMPLETADA => 'success',
                            \App\Models\Importacion::ESTADO_PROCESANDO => 'warning',
                            \App\Models\Importacion::ESTADO_CON_ERRORES => 'warning',
                            \App\Models\Importacion::ESTADO_FALLIDA => 'danger',
                            default => 'gray',
                        };
                    @endphp
                    <x-filament::badge :color="$badgeColor">
                        {{ ucfirst(strtolower(str_replace('_', ' ', $importacion->estado))) }}
                    </x-filament::badge>
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Usuario</dt>
                <dd class="text-gray-900 dark:text-white">
                    {{ $importacion->usuario?->name ?? '—' }}
                </dd>
            </div>
            @if($importacion->iniciado_en)
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Inicio procesamiento</dt>
                    <dd class="text-gray-900 dark:text-white">
                        {{ $importacion->iniciado_en->format('d/m/Y H:i:s') }}
                    </dd>
                </div>
            @endif
            @if($importacion->completado_en)
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Fin procesamiento</dt>
                    <dd class="text-gray-900 dark:text-white">
                        {{ $importacion->completado_en->format('d/m/Y H:i:s') }}
                    </dd>
                </div>
            @endif
        </dl>
    </x-filament::section>

    {{-- Lista de errores --}}
    @if($importacion->errores->isNotEmpty())
        <x-filament::section>
            <x-slot name="heading">
                Errores encontrados ({{ $importacion->errores->count() }})
            </x-slot>

            <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Fila</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($importacion->errores->take(50) as $error)
                            <tr>
                                <td class="px-3 py-2 font-mono text-gray-700 dark:text-gray-300">
                                    {{ $error->fila ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-danger-600 dark:text-danger-400">
                                    {{ $error->mensaje_error ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($importacion->errores->count() > 50)
                    <div class="border-t border-gray-200 p-3 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Mostrando 50 de {{ $importacion->errores->count() }} errores.
                        Descarga el archivo de errores para ver el listado completo.
                    </div>
                @endif
            </div>
        </x-filament::section>
    @endif

    {{-- Mensaje de éxito --}}
    @if($importacion->estado === \App\Models\Importacion::ESTADO_COMPLETADA)
        <x-filament::section>
            <div class="flex items-center gap-2 text-success-600">
                <x-heroicon-o-check-circle class="h-5 w-5" />
                <span>La importación se completó exitosamente sin errores.</span>
            </div>
        </x-filament::section>
    @endif

    {{-- Mensaje de fallo --}}
    @if($importacion->estado === \App\Models\Importacion::ESTADO_FALLIDA)
        <x-filament::section>
            <div class="flex items-center gap-2 text-danger-600">
                <x-heroicon-o-x-circle class="h-5 w-5" />
                <span>La importación falló. Verifica que el archivo tenga el formato correcto e intenta nuevamente.</span>
            </div>
        </x-filament::section>
    @endif
</div>
