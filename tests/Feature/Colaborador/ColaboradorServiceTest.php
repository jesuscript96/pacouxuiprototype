<?php

/**
 * Asignación de productos a colaboradores (User con tipo colaborador).
 */
use App\Models\Producto;
use App\Models\User;
use App\Services\AsignacionProductosService;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
});

it('asigna productos activos de la empresa al colaborador', function (): void {
    $producto1 = Producto::query()->first() ?? Producto::create(['nombre' => 'Prod 1', 'descripcion' => '']);
    $producto2 = Producto::query()->skip(1)->first() ?? Producto::create(['nombre' => 'Prod 2', 'descripcion' => '']);
    $this->empresa->productos()->sync([$producto1->id => ['desde' => 1], $producto2->id => ['desde' => 1]]);
    $colaborador = crearUserColaborador($this->empresa, ['email' => 'prod@test.com']);

    app(AsignacionProductosService::class)->asignarProductosEmpresa($colaborador, $this->empresa);

    $colaborador->refresh();
    expect($colaborador->productos)->toHaveCount(2);
    foreach ($colaborador->productos as $pivot) {
        expect($pivot->pivot->estado)->toBe('ACTIVO')
            ->and($pivot->pivot->razon)->toBeNull();
    }
});

it('marca producto INACTIVO si colaborador tiene deuda', function (): void {
    $producto = Producto::query()->first() ?? Producto::create(['nombre' => 'Prod', 'descripcion' => '']);
    $this->empresa->productos()->sync([$producto->id => ['desde' => 1]]);
    $colaborador = crearUserColaborador($this->empresa, ['email' => 'deuda@test.com']);

    $serviceConDeuda = new class extends AsignacionProductosService
    {
        protected function colaboradorTieneDeudas(User $colaborador): bool
        {
            return true;
        }
    };
    $serviceConDeuda->asignarProductosEmpresa($colaborador, $this->empresa);

    $colaborador->refresh();
    expect($colaborador->productos)->toHaveCount(1)
        ->and($colaborador->productos->first()->pivot->estado)->toBe('INACTIVO')
        ->and($colaborador->productos->first()->pivot->razon)->toBe('DEUDA_PENDIENTE');
});

it('no asigna producto si colaborador no cumple filtros', function (): void {
    $productoAsignable = Producto::query()->first() ?? Producto::create(['nombre' => 'Asignable', 'descripcion' => '']);
    $productoNoCumple = Producto::query()->skip(1)->first() ?? Producto::create(['nombre' => 'No cumple', 'descripcion' => '']);
    $this->empresa->productos()->sync([
        $productoAsignable->id => ['desde' => 1],
        $productoNoCumple->id => ['desde' => 1],
    ]);
    $colaborador = crearUserColaborador($this->empresa, ['email' => 'filtro@test.com']);

    $serviceFiltro = new class($productoNoCumple->id) extends AsignacionProductosService
    {
        public function __construct(
            private int $productoIdExcluido
        ) {}

        protected function colaboradorCumpleFiltrosProducto(User $colaborador, Producto $producto): bool
        {
            return $producto->id !== $this->productoIdExcluido;
        }
    };
    // crearColaborador ya asignó ambos productos con el servicio por defecto; hay que vaciar y reevaluar con filtros.
    $serviceFiltro->reevaluarProductos($colaborador->refresh());

    $colaborador->refresh();
    expect($colaborador->productos)->toHaveCount(1)
        ->and($colaborador->productos->first()->id)->toBe($productoAsignable->id);
});
