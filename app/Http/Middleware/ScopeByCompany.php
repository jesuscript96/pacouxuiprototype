<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class ScopeByCompany
{
    /**
     * Establece company_id en la request para que SpatieRole aplique el scope por empresa.
     * Los super_admin no se limitan por empresa.
     * En el panel Cliente con tenant, usa la empresa activa (Filament tenant).
     * Sin bypass por entorno: en local el filtrado debe coincidir con producción.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }
        $user = auth()->user();
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $empresaId = $this->resolveEmpresaIdForScope($user);

        if ($empresaId) {
            $request->attributes->set('shield.company_id', $empresaId);

            $sessionKey = 'scope_by_company.context.'.$user->getKey();
            $current = (int) $empresaId;
            $previous = session($sessionKey);

            if ($previous !== $current) {
                session([$sessionKey => $current]);
                $this->clearUserPermissionCache($user);
            }
        }

        return $next($request);
    }

    /**
     * Limpia caché global de permisos de Spatie y relaciones del usuario.
     * Debe ejecutarse al cambiar el contexto de empresa (tenant); no en cada petición
     * para no vaciar el caché de permisos en todo el sitio.
     */
    protected function clearUserPermissionCache(User $user): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
    }

    /**
     * Prioridad: tenant de Filament → parámetro de ruta `tenant` (p. ej. /cliente/{tenant}/...)
     * → empresa principal / primera del pivote.
     *
     * Si solo se usa Filament::getTenant(), a menudo es null en el mismo ciclo en que corre este
     * middleware y el scope queda fijado a empresa_id: al cambiar de empresa en el panel no cambia
     * el contexto ni se dispara la limpieza de caché de permisos.
     */
    protected function resolveEmpresaIdForScope(User $user): ?int
    {
        $tenant = Filament::getTenant();
        if ($tenant instanceof Empresa) {
            return (int) $tenant->id;
        }

        $routeTenant = request()->route()?->parameter('tenant');
        if ($routeTenant instanceof Empresa) {
            return (int) $routeTenant->id;
        }

        if ($user->empresa_id !== null) {
            return (int) $user->empresa_id;
        }

        $primeraEmpresaPivot = $user->empresas()->first();

        return $primeraEmpresaPivot !== null ? (int) $primeraEmpresaPivot->id : null;
    }
}
