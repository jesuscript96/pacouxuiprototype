<?php

use App\Models\BajaColaborador;
use App\Models\User;
use App\Services\ColaboradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    foreach (['ViewAny:BajaColaborador', 'Create:Colaborador'] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
});

it('la página de reingreso carga sin error (Filament v4 Section en Schemas)', function (): void {
    $this->actingAs(User::query()->where('email', 'admin@paco.com')->firstOrFail());
    $userColab = app(ColaboradorService::class)->crearColaborador([
        'name' => 'Reingreso',
        'apellido_paterno' => 'Test',
        'apellido_materno' => 'Page',
        'email' => 'reingreso.page.'.uniqid().'@test.com',
        'fecha_nacimiento' => '1990-01-01',
        'fecha_ingreso' => '2024-06-01',
        'periodicidad_pago' => 'QUINCENAL',
    ], $this->empresa);
    $colaborador = $userColab->refresh()->colaborador;

    $baja = BajaColaborador::factory()->ejecutada()->create([
        'colaborador_id' => $colaborador->id,
        'empresa_id' => $this->empresa->id,
    ]);

    $panelUser = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $panelUser->empresas()->attach($this->empresa->id);
    $panelUser->givePermissionTo(['ViewAny:BajaColaborador', 'Create:Colaborador']);

    $url = route('filament.cliente.resources.bajas-colaboradores.reingresar', [
        'tenant' => $this->empresa->id,
        'record' => $baja->id,
    ]);

    $this->actingAs($panelUser)->get($url)->assertOk();
});
