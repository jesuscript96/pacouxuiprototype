<div class="space-y-4">
    {{-- Información de la carta --}}
    <div class="grid grid-cols-2 gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800 md:grid-cols-4">
        <div>
            <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Colaborador</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $carta->colaborador?->nombre_completo ?? '—' }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Bimestre</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $carta->bimestre }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                ${{ number_format($carta->total, 2) }}
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Estado</dt>
            <dd class="mt-1">
                <x-filament::badge :color="$carta->estado_color">
                    {{ $carta->estado_label }}
                </x-filament::badge>
            </dd>
        </div>
    </div>

    {{-- Desglose financiero --}}
    <div class="grid grid-cols-3 gap-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <div class="text-center">
            <div class="text-lg font-bold text-gray-900 dark:text-white">
                ${{ number_format($carta->retiro, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Retiro</div>
        </div>
        <div class="text-center">
            <div class="text-lg font-bold text-gray-900 dark:text-white">
                ${{ number_format($carta->cesantia_vejez, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Cesantía y Vejez</div>
        </div>
        <div class="text-center">
            <div class="text-lg font-bold text-gray-900 dark:text-white">
                ${{ number_format($carta->infonavit, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Infonavit</div>
        </div>
    </div>

    {{-- Preview del PDF --}}
    @if ($pdfUrl)
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <iframe
                src="{{ $pdfUrl }}"
                class="w-full"
                style="height: 500px;"
                frameborder="0"
            ></iframe>
        </div>
    @else
        <div class="flex flex-col items-center justify-center rounded-lg bg-gray-50 p-8 dark:bg-gray-800">
            <x-heroicon-o-document class="mb-2 h-12 w-12 text-gray-400" />
            <p class="text-gray-500 dark:text-gray-400">
                El PDF no está disponible para preview.
            </p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                El archivo puede estar siendo generado o no existe aún.
            </p>
        </div>
    @endif

    {{-- Estado de firma --}}
    @if ($carta->firmado)
        <div class="flex items-center gap-2 rounded-lg bg-success-50 p-3 text-success-700 dark:bg-success-900/20 dark:text-success-400">
            <x-heroicon-o-check-badge class="h-5 w-5 shrink-0" />
            <span class="text-sm">
                Documento firmado el {{ $carta->fecha_firma?->format('d/m/Y H:i') ?? '—' }}
            </span>
        </div>
    @elseif ($carta->primera_visualizacion)
        <div class="flex items-center gap-2 rounded-lg bg-info-50 p-3 text-info-700 dark:bg-info-900/20 dark:text-info-400">
            <x-heroicon-o-eye class="h-5 w-5 shrink-0" />
            <span class="text-sm">
                Visto por primera vez el {{ $carta->primera_visualizacion->format('d/m/Y H:i') }}
            </span>
        </div>
    @endif
</div>
