<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    Permission::firstOrCreate(['name' => 'ViewAny:Colaborador', 'guard_name' => 'web']);
});

it('responde 200 en la página Ver importaciones (segmento literal, no confundir con {record})', function (): void {
    $user = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->empresas()->attach($this->empresa->id);
    $user->givePermissionTo('ViewAny:Colaborador');

    $url = route('filament.cliente.resources.colaboradores.colaboradors.importaciones', [
        'tenant' => $this->empresa->id,
    ]);

    $this->actingAs($user)->get($url)->assertOk();
});
