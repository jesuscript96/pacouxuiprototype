<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    foreach (['ViewAny:BajaColaborador', 'Create:BajaColaborador'] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
});

it('la ruta baja masiva /importar responde 200 y no 404', function (): void {
    $user = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->empresas()->attach($this->empresa->id);
    $user->givePermissionTo(['ViewAny:BajaColaborador', 'Create:BajaColaborador']);

    $url = route('filament.cliente.resources.bajas-colaboradores.importar', [
        'tenant' => $this->empresa->id,
    ]);

    $this->actingAs($user)->get($url)->assertOk();
});
