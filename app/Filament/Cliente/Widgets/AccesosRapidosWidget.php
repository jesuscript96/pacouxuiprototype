<?php

namespace App\Filament\Cliente\Widgets;

use App\Filament\Cliente\Pages\CartasSua\CartasSuaUnificadaPage;
use App\Filament\Cliente\Pages\Catalogos\CatalogosPage;
use App\Filament\Cliente\Pages\Documentos\DocumentosCorporativosPage;
use App\Filament\Cliente\Pages\Solicitudes\SolicitudesPage;
use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Filament\Cliente\Resources\Vacantes\VacanteResource;
use Filament\Widgets\Widget;

class AccesosRapidosWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.accesos-rapidos';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array<string, string>>
     */
    public function getLinks(): array
    {
        $links = [];

        if (ColaboradorResource::canViewAny()) {
            $links[] = [
                'label' => 'Colaboradores',
                'description' => 'Ver y gestionar plantilla',
                'icon' => 'heroicon-o-users',
                'url' => ColaboradorResource::getUrl('index'),
                'color' => 'primary',
            ];
        }

        if (BajaColaboradorResource::canViewAny()) {
            $links[] = [
                'label' => 'Bajas',
                'description' => 'Gestionar bajas de personal',
                'icon' => 'heroicon-o-user-minus',
                'url' => BajaColaboradorResource::getUrl('index'),
                'color' => 'danger',
            ];
        }

        if (VacanteResource::canViewAny()) {
            $links[] = [
                'label' => 'Vacantes',
                'description' => 'Reclutamiento y selección',
                'icon' => 'heroicon-o-briefcase',
                'url' => VacanteResource::getUrl('index'),
                'color' => 'success',
            ];
        }

        $links[] = [
            'label' => 'Solicitudes',
            'description' => 'Tipos de permisos y flujos',
            'icon' => 'heroicon-o-clipboard-document-list',
            'url' => SolicitudesPage::getUrl(),
            'color' => 'warning',
        ];

        $links[] = [
            'label' => 'Catálogos',
            'description' => 'Departamentos, puestos y más',
            'icon' => 'heroicon-o-squares-2x2',
            'url' => CatalogosPage::getUrl(),
            'color' => 'info',
        ];

        $links[] = [
            'label' => 'Documentos',
            'description' => 'Documentos corporativos',
            'icon' => 'heroicon-o-folder-open',
            'url' => DocumentosCorporativosPage::getUrl(),
            'color' => 'gray',
        ];

        $links[] = [
            'label' => 'Cartas SUA',
            'description' => 'Firma de cartas SUA',
            'icon' => 'heroicon-o-document-check',
            'url' => CartasSuaUnificadaPage::getUrl(),
            'color' => 'gray',
        ];

        return $links;
    }
}
