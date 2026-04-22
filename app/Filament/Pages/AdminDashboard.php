<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Support\UrlInicioPanelCliente;
use Filament\Pages\Dashboard as FilamentDashboard;

/**
 * BL: La «home» de /admin lleva al panel Cliente si el usuario puede acceder (p. ej. cliente@tecben.com).
 */
class AdminDashboard extends FilamentDashboard
{
    public function mount(): void
    {
        $url = UrlInicioPanelCliente::paraUsuario(auth()->user());

        if ($url !== null) {
            $this->redirect($url, navigate: false);

            return;
        }

        parent::mount();
    }
}
