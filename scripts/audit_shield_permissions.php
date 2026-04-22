<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\SeederRoleNaming;
use App\Models\SpatieRole;
use Spatie\Permission\Models\Permission;

echo '=== Permisos Departamento ==='.PHP_EOL;
print_r(Permission::where('name', 'like', '%Departamento%')->pluck('name')->toArray());

echo PHP_EOL.'=== Permisos DepartamentoGeneral ==='.PHP_EOL;
print_r(Permission::where('name', 'like', '%DepartamentoGeneral%')->pluck('name')->toArray());

echo PHP_EOL.'=== Permisos Role ==='.PHP_EOL;
print_r(Permission::where('name', 'like', '%Role%')->pluck('name')->toArray());

echo PHP_EOL.'=== admin_empresa (company_id=1) permisos ==='.PHP_EOL;
$adminRol = SeederRoleNaming::findForCompany(1, 'admin_empresa');
echo $adminRol ? implode(', ', $adminRol->permissions->pluck('name')->toArray()) : 'Rol no encontrado';

echo PHP_EOL.'=== rh_empresa (company_id=1) permisos ==='.PHP_EOL;
$rhRol = SeederRoleNaming::findForCompany(1, 'rh_empresa');
echo $rhRol ? implode(', ', $rhRol->permissions->pluck('name')->toArray()) : 'Rol no encontrado';

echo PHP_EOL.'=== Todos los roles (sin scope) ==='.PHP_EOL;
$roles = SpatieRole::withoutGlobalScopes()->get(['id', 'name', 'company_id']);
foreach ($roles as $r) {
    echo $r->id.' | '.$r->name.' | company_id='.$r->company_id.PHP_EOL;
}

echo PHP_EOL."=== Permisos que contienen 'Departamento' o 'Role' (nombres exactos) ===".PHP_EOL;
print_r(Permission::where('name', 'like', '%Departamento%')->orWhere('name', 'like', '%Role%')->pluck('name')->toArray());
echo PHP_EOL;
