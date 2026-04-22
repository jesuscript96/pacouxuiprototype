<?php

declare(strict_types=1);

namespace App\Filament\Pages\Analiticos;

use App\Exceptions\Tableau\TableauReportAccessDeniedException;
use App\Filament\Pages\Analiticos\Concerns\ConfiguresTableauReportNavigation;
use App\Models\User;
use App\Services\Tableau\TableauNavigationGate;
use App\Services\Tableau\TableauReportAccessService;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

/**
 * Base para informes Tableau en el panel Admin (sin tenant, usuario embed global).
 *
 * BL: Las páginas Admin de Tableau se mantienen ocultas hasta que se valide la integración completa.
 * Para re-habilitar: eliminar shouldRegisterNavigation().
 */
abstract class AdminTableauReportPage extends Page
{
    use ConfiguresTableauReportNavigation;

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.cliente.pages.analiticos.tableau-report';

    public ?string $embedSrc = null;

    public ?string $embedToken = null;

    public ?string $accessError = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user instanceof User || ! TableauNavigationGate::adminUserCanSeeTableauNavigation($user)) {
            return false;
        }

        return is_array(config('tableau_reports.'.static::reportKey()));
    }

    public function mount(TableauReportAccessService $access): void
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            $this->accessError = 'Inicia sesión para ver este reporte.';

            return;
        }

        try {
            $session = $access->buildSessionForAdminPanel($user, static::reportKey());
            $this->embedSrc = $session->embedSrc;
            $this->embedToken = $session->token;
        } catch (TableauReportAccessDeniedException $e) {
            $this->accessError = $e->getMessage();
        }
    }
}
