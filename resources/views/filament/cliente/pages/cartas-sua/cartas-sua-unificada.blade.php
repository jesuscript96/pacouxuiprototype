<x-filament-panels::page>
    @php
        $canVer = auth()->user()?->can('ViewAny:CartaSua');
        $canCargar = auth()->user()?->can('Create:CartaSua');
        $tabs = [];
        if ($canVer) {
            $tabs[] = ['id' => 'ver', 'label' => 'Ver cartas', 'icon' => 'heroicon-o-document-text', 'description' => 'Consultar emitidas'];
        }
        if ($canCargar) {
            $tabs[] = ['id' => 'cargar', 'label' => 'Cargar registros', 'icon' => 'heroicon-o-arrow-up-tray', 'description' => 'Batch de nuevas cartas'];
        }
    @endphp

    <div class="space-y-6" x-data="{ activeTab: @entangle('activeTab') }">

        <x-ux.hero
            eyebrow="Nómina · IMSS · SUA"
            title="Cartas del ciclo de nómina"
            description="Genera y administra las cartas SUA de tus colaboradores. Carga los registros en lote, consulta las emitidas y monitorea firmas electrónicas."
            icon="heroicon-o-document-text"
        />

        <x-ux.tabs :tabs="$tabs" />

        @if ($canVer)
            <div x-show="activeTab === 'ver'" x-cloak>
                @livewire(\App\Filament\Cliente\Pages\CartasSua\VerCartasSua::class)
            </div>
        @endif

        @if ($canCargar)
            <div x-show="activeTab === 'cargar'" x-cloak>
                @livewire(\App\Filament\Cliente\Pages\CartasSua\CargarCartasSua::class)
            </div>
        @endif
    </div>
</x-filament-panels::page>
