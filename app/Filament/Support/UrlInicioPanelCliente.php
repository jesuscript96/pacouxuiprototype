<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * BL: URL del inicio del panel Cliente (con tenant si aplica), para prototipo enseñable y accesos desde Admin.
 */
final class UrlInicioPanelCliente
{
    public static function paraUsuario(?Authenticatable $user): ?string
    {
        if (! $user instanceof User) {
            return null;
        }

        $panelCliente = Filament::getPanel('cliente');

        if (! $user->canAccessPanel($panelCliente)) {
            return null;
        }

        $tenant = Filament::getUserDefaultTenant($user) ?? $user->getTenants($panelCliente)->first();

        return $tenant !== null
            ? $panelCliente->getUrl($tenant)
            : $panelCliente->getUrl();
    }
}
