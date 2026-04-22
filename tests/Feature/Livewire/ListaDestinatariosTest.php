<?php

declare(strict_types=1);

use App\Livewire\NotificacionesPush\ListaDestinatarios;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();
});

function crearColaboradorConUsuarioLista(Empresa $empresa, array $overrides = []): Colaborador
{
    $colaborador = Colaborador::factory()->create(array_merge(
        ['empresa_id' => $empresa->id],
        $overrides
    ));

    User::factory()->colaborador()->create([
        'colaborador_id' => $colaborador->id,
        'empresa_id' => $empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    return $colaborador;
}

test('componente renderiza correctamente', function (): void {
    Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->assertStatus(200)
        ->assertSee('Destinatarios');
});

test('muestra colaboradores de la empresa', function (): void {
    crearColaboradorConUsuarioLista($this->empresa, ['nombre' => 'JuanTestLista']);

    Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->assertSee('JuanTestLista');
});

test('toggle selectAll cambia el estado', function (): void {
    Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->assertSet('selectAll', true)
        ->call('toggleSelectAll')
        ->assertSet('selectAll', false)
        ->call('toggleSelectAll')
        ->assertSet('selectAll', true);
});

test('toggle colaborador individual en modo selectAll agrega a deactivation', function (): void {
    $colaborador = crearColaboradorConUsuarioLista($this->empresa);

    Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->assertSet('selectAll', true)
        ->assertSet('manualDeactivation', [])
        ->call('toggleColaborador', $colaborador->id)
        ->assertSet('manualDeactivation', [$colaborador->id]);
});

test('toggle colaborador individual en modo manual agrega a activation', function (): void {
    $colaborador = crearColaboradorConUsuarioLista($this->empresa);

    Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->call('toggleSelectAll')
        ->assertSet('selectAll', false)
        ->assertSet('manualActivation', [])
        ->call('toggleColaborador', $colaborador->id)
        ->assertSet('manualActivation', [$colaborador->id]);
});

test('búsqueda filtra colaboradores', function (): void {
    crearColaboradorConUsuarioLista($this->empresa, ['nombre' => 'JuanBusqXyz']);
    crearColaboradorConUsuarioLista($this->empresa, ['nombre' => 'MariaBusqAbc']);

    Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->set('busqueda', 'JuanBusqXyz')
        ->assertSee('JuanBusqXyz')
        ->assertDontSee('MariaBusqAbc');
});

test('conteo de seleccionados es correcto en modo selectAll', function (): void {
    crearColaboradorConUsuarioLista($this->empresa);
    crearColaboradorConUsuarioLista($this->empresa);
    $colaborador3 = crearColaboradorConUsuarioLista($this->empresa);

    $component = Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ]);

    expect($component->instance()->getSeleccionState()['total_seleccionados'])->toBe(3);

    $component->call('toggleColaborador', $colaborador3->id);

    expect($component->instance()->getSeleccionState()['total_seleccionados'])->toBe(2);
});

test('conteo de seleccionados es correcto en modo manual', function (): void {
    $colaborador1 = crearColaboradorConUsuarioLista($this->empresa);
    $colaborador2 = crearColaboradorConUsuarioLista($this->empresa);

    $component = Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->call('toggleSelectAll');

    expect($component->instance()->getSeleccionState()['total_seleccionados'])->toBe(0);

    $component->call('toggleColaborador', $colaborador1->id);
    expect($component->instance()->getSeleccionState()['total_seleccionados'])->toBe(1);

    $component->call('toggleColaborador', $colaborador2->id);
    expect($component->instance()->getSeleccionState()['total_seleccionados'])->toBe(2);
});

test('getSeleccionState retorna estado correcto', function (): void {
    $colaborador = crearColaboradorConUsuarioLista($this->empresa);

    $component = Livewire::test(ListaDestinatarios::class, [
        'empresaId' => $this->empresa->id,
        'filtros' => [],
    ])
        ->call('toggleColaborador', $colaborador->id);

    $state = $component->instance()->getSeleccionState();

    expect($state)->toMatchArray([
        'select_all' => true,
        'manual_deactivation' => [$colaborador->id],
    ]);
});
