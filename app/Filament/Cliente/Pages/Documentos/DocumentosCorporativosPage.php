<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Documentos;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\CargarDocumentos\CargarDocumentosResource;
use App\Filament\Cliente\Resources\VerDestinatariosDocumentos\VerDestinatariosDocumentosResource;
use Filament\Pages\Page;
use UnitEnum;

/**
 * Página unificada de Documentos Corporativos: combina Cargar y Ver destinatarios en tabs.
 */
class DocumentosCorporativosPage extends Page
{
    protected static ?string $navigationLabel = 'Documentos Corporativos';

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::DOCUMENTOS_CORPORATIVOS;

    protected static ?int $navigationSort = 60;

    protected static ?string $slug = 'documentos-corporativos';

    protected string $view = 'filament.cliente.pages.documentos.documentos-corporativos';

    public string $activeTab = 'cargar';

    public static function canAccess(): bool
    {
        return CargarDocumentosResource::canViewAny()
            || VerDestinatariosDocumentosResource::canViewAny();
    }

    public function getTitle(): string
    {
        return 'Documentos Corporativos';
    }

    public function mount(): void
    {
        if (! CargarDocumentosResource::canViewAny() && VerDestinatariosDocumentosResource::canViewAny()) {
            $this->activeTab = 'destinatarios';
        }
    }
}
