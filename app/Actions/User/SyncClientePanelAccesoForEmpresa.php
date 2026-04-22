<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\Empresa;
use App\Models\User;

/**
 * BL: acceso al panel Cliente por empresa = pivote empresa_user + tipo «cliente».
 */
final class SyncClientePanelAccesoForEmpresa
{
    public function __invoke(User $user, Empresa $tenant, bool $activar): void
    {
        $tipos = $user->tipo ?? [];
        $tipos = is_array($tipos) ? $tipos : [];

        if ($activar) {
            if (! in_array('cliente', $tipos, true)) {
                $tipos[] = 'cliente';
                $user->tipo = array_values($tipos);
                $user->saveQuietly();
            }

            if (! $user->empresas()->where('empresas.id', $tenant->id)->exists()) {
                $user->empresas()->attach($tenant->id);
            }

            return;
        }

        $user->empresas()->detach($tenant->id);

        if ($user->empresas()->count() === 0) {
            $tipos = array_values(array_diff($tipos, ['cliente']));
            $user->tipo = $tipos;
            $user->saveQuietly();
        }
    }
}
