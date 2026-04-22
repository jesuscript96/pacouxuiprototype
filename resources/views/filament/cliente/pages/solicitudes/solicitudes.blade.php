<x-filament-panels::page>
    @php
        $canPermisos = \App\Filament\Cliente\Resources\Permisos\PermisoResource::canViewAny();
        $canCategorias = \App\Filament\Cliente\Resources\CategoriasSolicitud\CategoriaSolicitudResource::canViewAny();

        $tabs = [];
        if ($canPermisos) {
            $tabs[] = ['id' => 'permisos', 'label' => 'Permisos', 'icon' => 'heroicon-o-clipboard-document-check', 'description' => 'Catálogo de permisos'];
        }
        if ($canCategorias) {
            $tabs[] = ['id' => 'categorias', 'label' => 'Categorías', 'icon' => 'heroicon-o-tag', 'description' => 'Agrupación de permisos'];
        }
    @endphp

    <div class="space-y-6" x-data="{ activeTab: @entangle('activeTab') }">

        <x-ux.hero
            eyebrow="Gestión de solicitudes"
            title="Centro de solicitudes del colaborador"
            description="Administra los tipos de permiso disponibles y sus categorías. Define aquí cómo tus equipos piden vacaciones, incapacidades, home office y días personales."
            icon="heroicon-o-inbox-stack"
        />

        <x-ux.tabs :tabs="$tabs" />

        @if ($canPermisos)
            <div x-show="activeTab === 'permisos'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\Permisos\Pages\ListPermisos::class)
            </div>
        @endif

        @if ($canCategorias)
            <div x-show="activeTab === 'categorias'" x-cloak>
                @livewire(\App\Filament\Cliente\Resources\CategoriasSolicitud\Pages\ListCategoriasSolicitud::class)
            </div>
        @endif
    </div>
</x-filament-panels::page>
