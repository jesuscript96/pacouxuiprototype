<?php

declare(strict_types=1);

use App\Filament\Cliente\Resources\Roles\RolResource;
use App\Filament\Cliente\Resources\UsuariosEmpresa\UsuarioEmpresaResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    foreach (['ViewAny:SpatieRole', 'ViewAny:User', 'Create:SpatieRole'] as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }
});

it('permite listar roles del tenant con permiso ViewAny:SpatieRole', function (): void {
    $user = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->empresas()->attach($this->empresa->id);
    $user->givePermissionTo('ViewAny:SpatieRole');

    $url = RolResource::getUrl(
        name: 'index',
        parameters: ['tenant' => $this->empresa],
        panel: 'cliente',
    );

    $this->actingAs($user)->get($url)->assertOk();
});

it('muestra enlace a crear rol con permisos ViewAny y Create:SpatieRole', function (): void {
    $user = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->empresas()->attach($this->empresa->id);
    $user->givePermissionTo(['ViewAny:SpatieRole', 'Create:SpatieRole']);

    $indexUrl = RolResource::getUrl(
        name: 'index',
        parameters: ['tenant' => $this->empresa],
        panel: 'cliente',
    );
    $createUrl = RolResource::getUrl(
        name: 'create',
        parameters: ['tenant' => $this->empresa],
        panel: 'cliente',
    );

    $this->actingAs($user)->get($indexUrl)
        ->assertOk()
        ->assertSee($createUrl, escape: false);
});

it('permite listar usuarios de empresa con permiso ViewAny:User', function (): void {
    $user = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->empresas()->attach($this->empresa->id);
    $user->givePermissionTo('ViewAny:User');

    $url = UsuarioEmpresaResource::getUrl(
        name: 'index',
        parameters: ['tenant' => $this->empresa],
        panel: 'cliente',
    );

    $this->actingAs($user)->get($url)->assertOk();
});
