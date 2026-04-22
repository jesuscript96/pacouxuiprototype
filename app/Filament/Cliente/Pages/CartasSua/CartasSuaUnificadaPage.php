<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\CartasSua;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use Filament\Pages\Page;
use UnitEnum;

/**
 * Página unificada de Cartas SUA: combina "Ver cartas" y "Cargar" en tabs.
 */
class CartasSuaUnificadaPage extends Page
{
    protected static ?string $navigationLabel = 'Cartas SUA';

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CARTAS_SUA;

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'cartas-sua';

    protected string $view = 'filament.cliente.pages.cartas-sua.cartas-sua-unificada';

    public string $activeTab = 'ver';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:CartaSua')
            || (bool) auth()->user()?->can('Create:CartaSua');
    }

    public function getTitle(): string
    {
        return 'Cartas SUA';
    }

    public function mount(): void
    {
        // Si no puede ver pero sí puede cargar, empezar en la pestaña de carga
        if (! auth()->user()?->can('ViewAny:CartaSua') && auth()->user()?->can('Create:CartaSua')) {
            $this->activeTab = 'cargar';
        }
    }
}
