<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use App\Exceptions\Tableau\TableauReportAccessDeniedException;
use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Pages\Analiticos\Concerns\ConfiguresTableauReportNavigation;
use App\Models\Empresa;
use App\Models\User;
use App\Services\Tableau\TableauNavigationGate;
use App\Services\Tableau\TableauReportAccessService;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

/**
 * Página base para informes Tableau en el panel Cliente.
 *
 * Para un informe nuevo: crear una subclase, definir {@see static::reportKey()} y
 * {@see static::getSlug()}; añadir la entrada en config/tableau_reports.php.
 */
abstract class TableauReportPage extends Page
{
    use ConfiguresTableauReportNavigation;

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::ANALITICOS;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.cliente.pages.analiticos.tableau-report';

    public ?string $embedSrc = null;

    public ?string $embedToken = null;

    public ?string $accessError = null;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'UX prototype';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user instanceof User || ! TableauNavigationGate::clienteUserCanSeeTableauNavigation($user)) {
            return false;
        }

        if (! Filament::getTenant() instanceof Empresa) {
            return false;
        }

        return is_array(config('tableau_reports.'.static::reportKey()));
    }

    public function mount(TableauReportAccessService $access): void
    {
        $user = auth()->user();
        $tenant = Filament::getTenant();

        if (! $user instanceof User || ! $tenant instanceof Empresa) {
            $this->accessError = 'Selecciona una empresa para ver este reporte.';

            return;
        }

        try {
            $session = $access->buildSession($user, $tenant, static::reportKey());
            $this->embedSrc = $session->embedSrc;
            $this->embedToken = $session->token;
        } catch (TableauReportAccessDeniedException $e) {
            $this->accessError = $e->getMessage();
        }
    }
}
