<x-filament-panels::page>
    @php
        $metaTabs = [
            'regiones'                => ['label' => 'Regiones', 'icon' => 'heroicon-o-map', 'description' => 'Zonas geográficas'],
            'departamentos'           => ['label' => 'Departamentos', 'icon' => 'heroicon-o-building-office', 'description' => 'Unidades funcionales'],
            'departamentos_generales' => ['label' => 'Tipos generales', 'icon' => 'heroicon-o-rectangle-stack', 'description' => 'Agrupaciones base'],
            'areas'                   => ['label' => 'Áreas', 'icon' => 'heroicon-o-squares-plus', 'description' => 'Subunidades'],
            'areas_generales'         => ['label' => 'Áreas generales', 'icon' => 'heroicon-o-squares-2x2', 'description' => 'Plantillas'],
            'puestos'                 => ['label' => 'Puestos', 'icon' => 'heroicon-o-identification', 'description' => 'Cargos y roles'],
            'puestos_generales'       => ['label' => 'Puestos generales', 'icon' => 'heroicon-o-clipboard-document-list', 'description' => 'Plantillas'],
            'centros_pago'            => ['label' => 'Centros de pago', 'icon' => 'heroicon-o-banknotes', 'description' => 'Nómina y pago'],
            'ubicaciones'             => ['label' => 'Ubicaciones', 'icon' => 'heroicon-o-map-pin', 'description' => 'Sedes físicas'],
        ];
        $visibles = $this->tabsVisibles();
        $tabs = [];
        foreach ($metaTabs as $id => $meta) {
            if ($visibles[$id] ?? false) {
                $tabs[] = ['id' => $id, ...$meta];
            }
        }
    @endphp

    <div class="space-y-6" x-data="{ activeTab: @entangle('activeTab') }">

        <x-ux.hero
            eyebrow="Catálogos de colaboradores"
            title="Estructura organizacional"
            description="Define y mantiene la taxonomía de tu compañía: regiones, departamentos, áreas, puestos y ubicaciones. Todos los módulos de tecben-core consumen estos catálogos."
            icon="heroicon-o-rectangle-stack"
        />

        <x-ux.tabs :tabs="$tabs" />

        @if ($visibles['regiones'] ?? false)
            <div x-show="activeTab === 'regiones'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\Regiones\Pages\ListRegiones::class)
            </div>
        @endif

        @if ($visibles['departamentos'] ?? false)
            <div x-show="activeTab === 'departamentos'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\Departamentos\Pages\ListDepartamentos::class)
            </div>
        @endif

        @if ($visibles['departamentos_generales'] ?? false)
            <div x-show="activeTab === 'departamentos_generales'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\DepartamentosGenerales\Pages\ListDepartamentosGenerales::class)
            </div>
        @endif

        @if ($visibles['areas'] ?? false)
            <div x-show="activeTab === 'areas'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\Areas\Pages\ListAreas::class)
            </div>
        @endif

        @if ($visibles['areas_generales'] ?? false)
            <div x-show="activeTab === 'areas_generales'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\AreasGenerales\Pages\ListAreasGenerales::class)
            </div>
        @endif

        @if ($visibles['puestos'] ?? false)
            <div x-show="activeTab === 'puestos'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\Puestos\Pages\ListPuestos::class)
            </div>
        @endif

        @if ($visibles['puestos_generales'] ?? false)
            <div x-show="activeTab === 'puestos_generales'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\PuestosGenerales\Pages\ListPuestosGenerales::class)
            </div>
        @endif

        @if ($visibles['centros_pago'] ?? false)
            <div x-show="activeTab === 'centros_pago'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\CentrosPagos\Pages\ListCentrosPagos::class)
            </div>
        @endif

        @if ($visibles['ubicaciones'] ?? false)
            <div x-show="activeTab === 'ubicaciones'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\Ubicaciones\Pages\ListUbicaciones::class)
            </div>
        @endif
    </div>
</x-filament-panels::page>
