<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * BL: Middleware del prototipo UX/UI — el panel cliente no tiene login.
 * En cada request autentica al usuario demo (creándolo si no existe) y
 * redirige cualquier intento de acceder a pantallas de login o al panel admin
 * hacia el dashboard del primer tenant disponible.
 */
class PrototipoAutoLogin
{
    private const DEMO_EMAIL = 'cliente@tecben.com';

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            $user = $this->obtenerOCrearUsuarioDemo();
            auth()->login($user, remember: true);
        }

        $path = rtrim($request->path(), '/');

        if ($path === '' || $path === 'admin' || str_starts_with($path, 'admin/')) {
            return redirect($this->resolverUrlDashboardCliente());
        }

        if ($path === 'cliente/login' || str_starts_with($path, 'cliente/login/')) {
            return redirect($this->resolverUrlDashboardCliente());
        }

        if ($path === 'cliente') {
            return redirect($this->resolverUrlDashboardCliente());
        }

        return $next($request);
    }

    private function resolverUrlDashboardCliente(): string
    {
        $empresa = auth()->user()?->empresas()->first();

        return $empresa ? "/cliente/{$empresa->getKey()}" : '/cliente';
    }

    private function obtenerOCrearUsuarioDemo(): User
    {
        $user = User::query()->where('email', self::DEMO_EMAIL)->first();

        if ($user instanceof User) {
            $this->asegurarEmpresaVinculada($user);

            return $user;
        }

        $empresa = $this->obtenerOCrearEmpresaDemo();

        $user = User::create([
            'name' => 'Cliente',
            'apellido_paterno' => 'Demo',
            'apellido_materno' => 'Prototipo',
            'email' => self::DEMO_EMAIL,
            'password' => 'password',
            'tipo' => ['cliente'],
            'empresa_id' => $empresa->id,
        ]);

        $user->empresas()->syncWithoutDetaching([$empresa->id]);

        return $user;
    }

    private function asegurarEmpresaVinculada(User $user): void
    {
        if ($user->empresas()->exists()) {
            return;
        }

        $empresa = $this->obtenerOCrearEmpresaDemo();
        $user->empresas()->syncWithoutDetaching([$empresa->id]);

        if (! $user->empresa_id) {
            $user->forceFill(['empresa_id' => $empresa->id])->save();
        }
    }

    private function obtenerOCrearEmpresaDemo(): Empresa
    {
        $empresa = Empresa::query()->first();

        if ($empresa instanceof Empresa) {
            return $empresa;
        }

        return Empresa::create([
            'nombre' => 'Empresa Demo Prototipo',
            'nombre_contacto' => 'Demo',
            'email_contacto' => 'demo@prototipo.test',
            'telefono_contacto' => '5500000000',
            'movil_contacto' => '5500000000',
            'email_facturacion' => 'demo@prototipo.test',
            'fecha_inicio_contrato' => now()->format('Y-m-d'),
            'fecha_fin_contrato' => now()->addYears(2)->format('Y-m-d'),
            'num_usuarios_reportes' => 50,
            'activo' => true,
            'fecha_activacion' => now(),
        ]);
    }
}
