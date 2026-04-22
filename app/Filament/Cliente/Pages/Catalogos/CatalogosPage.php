<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Catalogos;

use App\Filament\Cliente\Resources\Areas\AreaResource;
use App\Filament\Cliente\Resources\AreasGenerales\AreaGeneralResource;
use App\Filament\Cliente\Resources\CentrosPagos\CentroPagoResource;
use App\Filament\Cliente\Resources\Departamentos\DepartamentoResource;
use App\Filament\Cliente\Resources\DepartamentosGenerales\DepartamentoGeneralResource;
use App\Filament\Cliente\Resources\Puestos\PuestoResource;
use App\Filament\Cliente\Resources\PuestosGenerales\PuestoGeneralResource;
use App\Filament\Cliente\Resources\Regiones\RegionResource;
use App\Filament\Cliente\Resources\Ubicaciones\UbicacionResource;
use Filament\Pages\Page;
use UnitEnum;

/**
 * Página unificada de catálogos de colaboradores.
 * Agrupa en tabs los 8 catálogos simples para reducir el sidebar.
 */
class CatalogosPage extends Page
{
    protected static ?string $navigationLabel = 'Catálogos de colaboradores';

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'catalogos';

    protected string $view = 'filament.cliente.pages.catalogos.catalogos';

    public string $activeTab = 'regiones';

    public static function canAccess(): bool
    {
        return RegionResource::canViewAny()
            || DepartamentoResource::canViewAny()
            || AreaResource::canViewAny()
            || PuestoResource::canViewAny()
            || CentroPagoResource::canViewAny()
            || UbicacionResource::canViewAny()
            || AreaGeneralResource::canViewAny()
            || DepartamentoGeneralResource::canViewAny()
            || PuestoGeneralResource::canViewAny();
    }

    public function getTitle(): string
    {
        return 'Catálogos de colaboradores';
    }

    /**
     * @return array<string, bool>
     */
    public function tabsVisibles(): array
    {
        return [
            'regiones' => RegionResource::canViewAny(),
            'departamentos' => DepartamentoResource::canViewAny(),
            'departamentos_generales' => DepartamentoGeneralResource::canViewAny(),
            'areas' => AreaResource::canViewAny(),
            'areas_generales' => AreaGeneralResource::canViewAny(),
            'puestos' => PuestoResource::canViewAny(),
            'puestos_generales' => PuestoGeneralResource::canViewAny(),
            'centros_pago' => CentroPagoResource::canViewAny(),
            'ubicaciones' => UbicacionResource::canViewAny(),
        ];
    }

    public function mount(): void
    {
        $visibles = $this->tabsVisibles();

        $requested = request()->query('tab');

        if ($requested && ($visibles[$requested] ?? false)) {
            $this->activeTab = $requested;
        } else {
            $primerTab = array_key_first(array_filter($visibles));

            if ($primerTab !== null) {
                $this->activeTab = $primerTab;
            }
        }
    }
}
