<?php

declare(strict_types=1);

use App\Filament\Resources\NotificacionesPush\NotificacionPushResource;
use App\Models\NotificacionPush;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();

    $permisos = [
        'ViewAny:NotificacionPush',
        'View:NotificacionPush',
        'Create:NotificacionPush',
        'Update:NotificacionPush',
        'Delete:NotificacionPush',
        'DeleteAny:NotificacionPush',
    ];
    foreach ($permisos as $nombre) {
        Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->administrador()->create([
        'email' => fake()->unique()->safeEmail(),
    ]);
    $this->admin->givePermissionTo($permisos);
});

test('admin puede ver listado de notificaciones push', function (): void {
    NotificacionPush::factory()->count(3)->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $url = NotificacionPushResource::getUrl('index', panel: 'admin');

    $this->actingAs($this->admin)->get($url)->assertOk();
});

test('admin puede ver formulario de creación', function (): void {
    $url = NotificacionPushResource::getUrl('create', panel: 'admin');

    $this->actingAs($this->admin)
        ->get($url)
        ->assertOk()
        ->assertSee('Empresa', false);
});

test('admin no puede editar notificación ya enviada', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    expect(NotificacionPushResource::canEdit($notificacion))->toBeFalse();
});

test('admin puede ver detalle de notificación push', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $url = NotificacionPushResource::getUrl('view', ['record' => $notificacion], panel: 'admin');

    $this->actingAs($this->admin)->get($url)->assertOk();
});

test('listado muestra columna de empresa', function (): void {
    NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $url = NotificacionPushResource::getUrl('index', panel: 'admin');

    $this->actingAs($this->admin)
        ->get($url)
        ->assertOk()
        ->assertSee($this->empresa->nombre, false);
});
