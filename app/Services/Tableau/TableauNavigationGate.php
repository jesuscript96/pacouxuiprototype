<?php

declare(strict_types=1);

namespace App\Services\Tableau;

use App\Models\User;

/**
 * Condiciones para mostrar el grupo de navegación «Analíticos» (informes Tableau).
 *
 * Filament solo registra ítems cuando {@see \Filament\Pages\Page::canAccess()} es true y elimina
 * grupos sin ítems; aquí se centraliza que Tableau esté configurado además del usuario/tenant.
 */
final class TableauNavigationGate
{
    public static function integrationIsConfigured(): bool
    {
        $baseUrl = trim((string) config('tableau.base_url'));
        $clientId = trim((string) config('tableau.connected_app.client_id'));
        $secretId = trim((string) config('tableau.connected_app.secret_id'));
        $secretKey = trim((string) config('tableau.connected_app.secret_key'));

        return $baseUrl !== '' && $clientId !== '' && $secretId !== '' && $secretKey !== '';
    }

    /**
     * Panel Cliente: ver reportes + integración lista (el tenant se valida en cada página).
     */
    public static function clienteUserCanSeeTableauNavigation(User $user): bool
    {
        if (! self::integrationIsConfigured()) {
            return false;
        }

        return $user->ver_reportes;
    }

    /**
     * Panel Admin: rol admin + integración + usuario embed global (mismo requisito que buildSessionForAdminPanel).
     */
    public static function adminUserCanSeeTableauNavigation(User $user): bool
    {
        if (! self::integrationIsConfigured()) {
            return false;
        }

        if (trim((string) config('tableau.embed_admin_username')) === '') {
            return false;
        }

        return $user->puedeAccederAlPanelAdminPaco();
    }
}
