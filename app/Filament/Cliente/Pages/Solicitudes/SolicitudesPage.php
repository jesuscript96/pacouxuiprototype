<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Solicitudes;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\CategoriasSolicitud\CategoriaSolicitudResource;
use App\Filament\Cliente\Resources\Permisos\PermisoResource;
use Filament\Pages\Page;
use UnitEnum;

/**
 * Página unificada del módulo Solicitudes: combina Permisos y Categorías en tabs.
 */
class SolicitudesPage extends Page
{
    protected static ?string $navigationLabel = 'Solicitudes';

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::SOLICITUDES;

    protected static ?int $navigationSort = 40;

    protected static ?string $slug = 'solicitudes';

    protected string $view = 'filament.cliente.pages.solicitudes.solicitudes';

    public string $activeTab = 'permisos';

    public static function canAccess(): bool
    {
        return PermisoResource::canViewAny()
            || CategoriaSolicitudResource::canViewAny();
    }

    public function getTitle(): string
    {
        return 'Solicitudes';
    }

    public function mount(): void
    {
        if (! PermisoResource::canViewAny() && CategoriaSolicitudResource::canViewAny()) {
            $this->activeTab = 'categorias';
        }
    }
}
