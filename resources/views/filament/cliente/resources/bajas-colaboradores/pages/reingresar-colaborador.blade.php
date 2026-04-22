<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Información del colaborador anterior
        </x-slot>
        <x-slot name="description">
            Datos del colaborador dado de baja que sirven de base para el reingreso (se creará una ficha nueva).
        </x-slot>

        <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div>
                <span class="font-medium text-gray-500 dark:text-gray-400">Nombre:</span>
                <p class="text-gray-950 dark:text-white">{{ $baja->colaborador?->nombre_completo ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="font-medium text-gray-500 dark:text-gray-400">Email:</span>
                <p class="text-gray-950 dark:text-white">{{ $baja->colaborador?->email ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="font-medium text-gray-500 dark:text-gray-400">Fecha de baja:</span>
                <p class="text-gray-950 dark:text-white">{{ $baja->fecha_baja?->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="font-medium text-gray-500 dark:text-gray-400">Motivo de baja:</span>
                <p class="text-gray-950 dark:text-white">{{ \App\Models\BajaColaborador::motivosDisponibles()[$baja->motivo] ?? $baja->motivo ?? 'N/A' }}</p>
            </div>
        </div>
    </x-filament::section>

    {{ $this->content }}

    <div class="mt-6">
        <x-filament::button color="gray" tag="a" :href="$this->getCancelUrl()">
            Cancelar
        </x-filament::button>
    </div>
</x-filament-panels::page>
