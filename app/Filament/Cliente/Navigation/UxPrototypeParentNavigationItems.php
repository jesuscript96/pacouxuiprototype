<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Navigation;

use App\Filament\Cliente\Pages\Analiticos\AnaliticosHomePage;
use App\Filament\Cliente\Pages\CartasSua\CartasSuaUnificadaPage;
use App\Filament\Cliente\Pages\Catalogos\CatalogosPage;
use App\Filament\Cliente\Pages\Documentos\DocumentosCorporativosPage;
use App\Filament\Cliente\Pages\Solicitudes\SolicitudesPage;
use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Filament\Cliente\Resources\Roles\RolResource;
use App\Filament\Cliente\Resources\Vacantes\VacanteResource;
use Filament\Navigation\NavigationItem;

/**
 * BL: Etiquetas de ítems padre del sidebar bajo «UX prototype» (deben coincidir con
 * {@see \Filament\Resources\Resource::$navigationParentItem} y páginas equivalentes).
 *
 * Nota: en el provider histórico existía «Homologaciones» sin páginas en este panel;
 * no se registra ítem padre vacío para no mostrar un desplegable sin destinos.
 */
final class UxPrototypeParentNavigationItems
{
    public const ANALITICOS = 'Analíticos';

    public const SOLICITUDES = 'Solicitudes';

    public const CATALOGOS_COLABORADORES = 'Catálogos Colaboradores';

    public const GESTION_PERSONAL = 'Gestión de personal';

    public const RECLUTAMIENTO = 'Reclutamiento';

    public const CARTAS_SUA = 'Cartas SUA';

    public const DOCUMENTOS_CORPORATIVOS = 'Documentos Corporativos';

    public const CONFIGURACION = 'Configuración';

    /**
     * @return array<int, NavigationItem>
     */
    public static function definitions(): array
    {
        return [
            NavigationItem::make(self::ANALITICOS)
                ->group('UX prototype')
                ->icon('heroicon-o-chart-bar-square')
                ->sort(10)
                ->url(fn (): string => AnaliticosHomePage::getUrl()),
            NavigationItem::make(self::SOLICITUDES)
                ->group('UX prototype')
                ->icon('heroicon-o-inbox')
                ->sort(20)
                ->url(fn (): string => SolicitudesPage::getUrl()),
            NavigationItem::make(self::CATALOGOS_COLABORADORES)
                ->group('UX prototype')
                ->icon('heroicon-o-rectangle-stack')
                ->sort(30)
                ->url(fn (): string => CatalogosPage::getUrl()),
            NavigationItem::make(self::GESTION_PERSONAL)
                ->group('UX prototype')
                ->icon('heroicon-o-user-group')
                ->sort(40)
                ->url(fn (): string => ColaboradorResource::getUrl()),
            NavigationItem::make(self::RECLUTAMIENTO)
                ->group('UX prototype')
                ->icon('heroicon-o-briefcase')
                ->sort(50)
                ->url(fn (): string => VacanteResource::getUrl()),
            NavigationItem::make(self::CARTAS_SUA)
                ->group('UX prototype')
                ->icon('heroicon-o-document-text')
                ->sort(60)
                ->url(fn (): string => CartasSuaUnificadaPage::getUrl()),
            NavigationItem::make(self::DOCUMENTOS_CORPORATIVOS)
                ->group('UX prototype')
                ->icon('heroicon-o-building-library')
                ->sort(70)
                ->url(fn (): string => DocumentosCorporativosPage::getUrl()),
            NavigationItem::make(self::CONFIGURACION)
                ->group('UX prototype')
                ->icon('heroicon-o-cog-6-tooth')
                ->sort(80)
                ->url(fn (): string => RolResource::getUrl()),
        ];
    }
}
