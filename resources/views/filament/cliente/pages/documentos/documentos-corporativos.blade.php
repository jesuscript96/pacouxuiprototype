<x-filament-panels::page>
    @php
        $canCargar = \App\Filament\Cliente\Resources\CargarDocumentos\CargarDocumentosResource::canViewAny();
        $canDestinatarios = \App\Filament\Cliente\Resources\VerDestinatariosDocumentos\VerDestinatariosDocumentosResource::canViewAny();
        $tabs = [];
        if ($canCargar) {
            $tabs[] = ['id' => 'cargar', 'label' => 'Cargar documentos', 'icon' => 'heroicon-o-arrow-up-tray', 'description' => 'Publicación y difusión'];
        }
        if ($canDestinatarios) {
            $tabs[] = ['id' => 'destinatarios', 'label' => 'Destinatarios', 'icon' => 'heroicon-o-user-group', 'description' => 'Lectura y firmas'];
        }
    @endphp

    <div class="space-y-6" x-data="{ activeTab: @entangle('activeTab') }">

        <x-ux.hero
            eyebrow="Comunicación corporativa"
            title="Biblioteca corporativa"
            description="Publica políticas, manuales y comunicados oficiales. Controla quién los recibe, quién los ha leído y quién los ha firmado con trazabilidad legal."
            icon="heroicon-o-building-library"
        />

        <x-ux.tabs :tabs="$tabs" />

        @if ($canCargar)
            <div x-show="activeTab === 'cargar'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\CargarDocumentos\Pages\ListCargarDocumentos::class)
            </div>
        @endif

        @if ($canDestinatarios)
            <div x-show="activeTab === 'destinatarios'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\VerDestinatariosDocumentos\Pages\ListVerDestinatariosDocumentos::class)
            </div>
        @endif
    </div>
</x-filament-panels::page>
