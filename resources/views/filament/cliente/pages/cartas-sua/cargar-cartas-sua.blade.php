<x-filament-panels::page>
    {{-- Sección de carga --}}
    <x-filament::section>
        <x-slot name="heading">
            Cargar archivo
        </x-slot>

        <x-slot name="description">
            Sube un archivo Excel con el formato de la plantilla para generar cartas SUA.
        </x-slot>

        <form wire:submit="procesarCartas">
            {{ $this->form }}

            <div class="mt-4 flex justify-end">
                <x-filament::button
                    type="submit"
                    icon="heroicon-o-arrow-up-tray"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="procesarCartas">Procesar cartas</span>
                    <span wire:loading wire:target="procesarCartas">Procesando…</span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- Tabla de importaciones recientes --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Importaciones recientes
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
