<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use App\Filament\Support\UrlInicioPanelCliente;
use Filament\Auth\Http\Responses\LoginResponse as FilamentLoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * BL: Tras autenticar en /admin/login o /cliente/login, llevar siempre al dashboard del panel cliente (tenant).
 */
class RedirigirLoginAlPanelClienteResponse extends FilamentLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $url = UrlInicioPanelCliente::paraUsuario(auth()->user());

        if ($url === null) {
            return parent::toResponse($request);
        }

        return redirect()->intended($url);
    }
}
