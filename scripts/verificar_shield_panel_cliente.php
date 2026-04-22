<?php

/**
 * Verificación post-seed del panel Cliente (Paso 7).
 * Ejecutar: php scripts/verificar_shield_panel_cliente.php
 *
 * Requiere `php artisan db:seed` (o al menos ClienteEjemploSeeder): usuario cliente@tecben.com, empresa id=1 y ficha en colaboradores.
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$email = 'cliente@tecben.com';
$cliente = User::where('email', $email)->first();

if (! $cliente) {
    echo "❌ Usuario {$email} no encontrado. Ejecuta `php artisan db:seed` o `--class=ClienteEjemploSeeder`.".PHP_EOL;
    exit(1);
}

if (! $cliente->colaborador_id) {
    echo "❌ Usuario {$email} no tiene colaborador_id (debe existir ficha RH). Vuelve a ejecutar ClienteEjemploSeeder.".PHP_EOL;
    exit(1);
}

echo "=== Usuario: {$cliente->email} ===".PHP_EOL;
echo 'Roles: '.$cliente->roles->pluck('name')->join(', ').PHP_EOL;
echo 'Permisos (getAllPermissions): '.$cliente->getAllPermissions()->pluck('name')->join(', ').PHP_EOL;
echo 'hasEmpresasAsignadas(): '.($cliente->hasEmpresasAsignadas() ? 'true' : 'false').PHP_EOL;
echo "can('ViewAny:Departamento'): ".($cliente->can('ViewAny:Departamento') ? 'true' : 'false').PHP_EOL;
echo "can('ViewAny:DepartamentoGeneral'): ".($cliente->can('ViewAny:DepartamentoGeneral') ? 'true' : 'false').PHP_EOL;
echo PHP_EOL;

$ok = $cliente->hasEmpresasAsignadas()
    && $cliente->can('ViewAny:Departamento')
    && $cliente->can('ViewAny:DepartamentoGeneral');

if ($ok) {
    echo '✅ Verificación OK. Login en /cliente → debería ver Dashboard, Departamentos y Departamentos generales.'.PHP_EOL;
} else {
    echo '⚠️ Algún check falló. Revisa permisos y empresa asignada.'.PHP_EOL;
    exit(1);
}
