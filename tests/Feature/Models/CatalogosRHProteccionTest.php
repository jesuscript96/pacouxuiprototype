<?php

declare(strict_types=1);

use App\Models\Area;
use App\Models\AreaGeneral;
use App\Models\CentroPago;
use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\DepartamentoGeneral;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Ocupacion;
use App\Models\Puesto;
use App\Models\PuestoGeneral;
use App\Models\Region;
use App\Models\Subindustria;
use App\Models\Ubicacion;
use App\Models\User;
use App\Policies\AreaGeneralPolicy;
use App\Policies\DepartamentoGeneralPolicy;
use App\Policies\PuestoGeneralPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $industria = Industria::withoutEvents(fn (): Industria => Industria::query()->create([
        'nombre' => 'Industria Test',
    ]));

    $subindustria = Subindustria::withoutEvents(fn (): Subindustria => Subindustria::query()->create([
        'nombre' => 'Subindustria Test',
        'industria_id' => $industria->id,
    ]));

    $this->empresa = Empresa::withoutEvents(fn (): Empresa => Empresa::factory()->create([
        'industria_id' => $industria->id,
        'sub_industria_id' => $subindustria->id,
    ]));
});

function crearColaboradorConCatalogo(Empresa $empresa, array $overrides): Colaborador
{
    return Colaborador::factory()->create(array_merge([
        'empresa_id' => $empresa->id,
        'departamento_id' => null,
        'area_id' => null,
        'puesto_id' => null,
        'region_id' => null,
        'centro_pago_id' => null,
        'ubicacion_id' => null,
    ], $overrides));
}

test('departamento no se elimina si tiene colaboradores activos o dados de baja', function (): void {
    $departamento = Departamento::withoutEvents(fn (): Departamento => Departamento::query()->create([
        'nombre' => 'Departamento Test',
        'empresa_id' => $this->empresa->id,
    ]));

    crearColaboradorConCatalogo($this->empresa, [
        'departamento_id' => $departamento->id,
    ]);

    expect(fn () => $departamento->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene colaboradores asociados.');

    $departamento = Departamento::withoutEvents(fn (): Departamento => Departamento::query()->create([
        'nombre' => 'Departamento Test Baja',
        'empresa_id' => $this->empresa->id,
    ]));

    $colaborador = crearColaboradorConCatalogo($this->empresa, [
        'departamento_id' => $departamento->id,
    ]);
    $colaborador->delete();

    expect(fn () => $departamento->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene colaboradores asociados.');
});

test('area no se elimina si tiene colaboradores asociados', function (): void {
    $areaGeneral = AreaGeneral::withoutEvents(fn (): AreaGeneral => AreaGeneral::query()->create([
        'nombre' => 'Area General Test',
        'empresa_id' => $this->empresa->id,
    ]));

    $area = Area::withoutEvents(fn (): Area => Area::query()->create([
        'nombre' => 'Area Test',
        'empresa_id' => $this->empresa->id,
        'area_general_id' => $areaGeneral->id,
    ]));

    crearColaboradorConCatalogo($this->empresa, [
        'area_id' => $area->id,
    ]);

    expect(fn () => $area->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene colaboradores asociados.');
});

test('puesto no se elimina si tiene colaboradores asociados', function (): void {
    $areaGeneral = AreaGeneral::withoutEvents(fn (): AreaGeneral => AreaGeneral::query()->create([
        'nombre' => 'Area General Puesto',
        'empresa_id' => $this->empresa->id,
    ]));

    $puestoGeneral = PuestoGeneral::withoutEvents(fn (): PuestoGeneral => PuestoGeneral::query()->create([
        'nombre' => 'Puesto General Test',
        'empresa_id' => $this->empresa->id,
    ]));

    $ocupacion = Ocupacion::query()->create([
        'descripcion' => 'Ocupacion Test',
    ]);

    $puesto = Puesto::withoutEvents(fn (): Puesto => Puesto::query()->create([
        'nombre' => 'Puesto Test',
        'empresa_id' => $this->empresa->id,
        'area_general_id' => $areaGeneral->id,
        'puesto_general_id' => $puestoGeneral->id,
        'ocupacion_id' => $ocupacion->id,
    ]));

    crearColaboradorConCatalogo($this->empresa, [
        'puesto_id' => $puesto->id,
    ]);

    expect(fn () => $puesto->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene colaboradores asociados.');
});

test('region no se elimina si tiene colaboradores asociados', function (): void {
    $region = Region::withoutEvents(fn (): Region => Region::query()->create([
        'nombre' => 'Region Test',
        'empresa_id' => $this->empresa->id,
    ]));

    crearColaboradorConCatalogo($this->empresa, [
        'region_id' => $region->id,
    ]);

    expect(fn () => $region->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene colaboradores asociados.');
});

test('centro pago no se elimina si tiene colaboradores asociados', function (): void {
    $centroPago = CentroPago::withoutEvents(fn (): CentroPago => CentroPago::query()->create([
        'nombre' => 'Centro Pago Test',
        'empresa_id' => $this->empresa->id,
    ]));

    crearColaboradorConCatalogo($this->empresa, [
        'centro_pago_id' => $centroPago->id,
    ]);

    expect(fn () => $centroPago->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene colaboradores asociados.');
});

test('ubicacion no se elimina si tiene colaboradores asociados', function (): void {
    $ubicacion = Ubicacion::withoutEvents(fn (): Ubicacion => Ubicacion::query()->create([
        'nombre' => 'Ubicacion Test',
        'empresa_id' => $this->empresa->id,
        'cp' => '01001',
    ]));

    crearColaboradorConCatalogo($this->empresa, [
        'ubicacion_id' => $ubicacion->id,
    ]);

    expect(fn () => $ubicacion->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene colaboradores asociados.');
});

test('policies de homologación deniegan delete si hay catálogos dependientes', function (): void {
    Permission::query()->firstOrCreate(
        ['name' => 'Delete:AreaGeneral', 'guard_name' => 'web'],
        ['name' => 'Delete:AreaGeneral', 'guard_name' => 'web']
    );
    Permission::query()->firstOrCreate(
        ['name' => 'Delete:DepartamentoGeneral', 'guard_name' => 'web'],
        ['name' => 'Delete:DepartamentoGeneral', 'guard_name' => 'web']
    );
    Permission::query()->firstOrCreate(
        ['name' => 'Delete:PuestoGeneral', 'guard_name' => 'web'],
        ['name' => 'Delete:PuestoGeneral', 'guard_name' => 'web']
    );

    $user = User::factory()->cliente()->create();
    $user->givePermissionTo(['Delete:AreaGeneral', 'Delete:DepartamentoGeneral', 'Delete:PuestoGeneral']);

    $areaGeneral = AreaGeneral::withoutEvents(fn (): AreaGeneral => AreaGeneral::query()->create([
        'nombre' => 'AG policy',
        'empresa_id' => $this->empresa->id,
    ]));

    expect((new AreaGeneralPolicy)->delete($user, $areaGeneral))->toBeTrue();

    Area::withoutEvents(fn (): Area => Area::query()->create([
        'nombre' => 'Área hija policy',
        'empresa_id' => $this->empresa->id,
        'area_general_id' => $areaGeneral->id,
    ]));

    expect((new AreaGeneralPolicy)->delete($user, $areaGeneral))->toBeFalse();

    $dg = DepartamentoGeneral::withoutEvents(fn (): DepartamentoGeneral => DepartamentoGeneral::query()->create([
        'nombre' => 'DG policy',
    ]));

    expect((new DepartamentoGeneralPolicy)->delete($user, $dg))->toBeTrue();

    Departamento::withoutEvents(fn (): Departamento => Departamento::query()->create([
        'nombre' => 'Depto policy',
        'empresa_id' => $this->empresa->id,
        'departamento_general_id' => $dg->id,
    ]));

    expect((new DepartamentoGeneralPolicy)->delete($user, $dg))->toBeFalse();

    $pg = PuestoGeneral::withoutEvents(fn (): PuestoGeneral => PuestoGeneral::query()->create([
        'nombre' => 'PG policy',
        'empresa_id' => $this->empresa->id,
    ]));

    expect((new PuestoGeneralPolicy)->delete($user, $pg))->toBeTrue();

    $ocupacion = Ocupacion::query()->create(['descripcion' => 'Ocup policy']);
    Puesto::withoutEvents(fn (): Puesto => Puesto::query()->create([
        'nombre' => 'Puesto policy',
        'empresa_id' => $this->empresa->id,
        'puesto_general_id' => $pg->id,
        'ocupacion_id' => $ocupacion->id,
        'area_general_id' => $areaGeneral->id,
    ]));

    expect((new PuestoGeneralPolicy)->delete($user, $pg))->toBeFalse();
});

test('area general no se elimina en modelo si tiene áreas o puestos asociados', function (): void {
    $areaGeneral = AreaGeneral::withoutEvents(fn (): AreaGeneral => AreaGeneral::query()->create([
        'nombre' => 'AG modelo',
        'empresa_id' => $this->empresa->id,
    ]));

    Area::withoutEvents(fn (): Area => Area::query()->create([
        'nombre' => 'Área hija modelo',
        'empresa_id' => $this->empresa->id,
        'area_general_id' => $areaGeneral->id,
    ]));

    expect(fn () => $areaGeneral->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque está asignada en áreas o puestos de la empresa.');
});

test('departamento general no se elimina en modelo si tiene departamentos asociados', function (): void {
    $dg = DepartamentoGeneral::withoutEvents(fn (): DepartamentoGeneral => DepartamentoGeneral::query()->create([
        'nombre' => 'DG modelo',
    ]));

    Departamento::withoutEvents(fn (): Departamento => Departamento::query()->create([
        'nombre' => 'Depto hijo modelo',
        'empresa_id' => $this->empresa->id,
        'departamento_general_id' => $dg->id,
    ]));

    expect(fn () => $dg->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene departamentos asociados.');
});

test('puesto general no se elimina en modelo si tiene puestos asociados', function (): void {
    $areaGeneral = AreaGeneral::withoutEvents(fn (): AreaGeneral => AreaGeneral::query()->create([
        'nombre' => 'AG para PG modelo',
        'empresa_id' => $this->empresa->id,
    ]));

    $puestoGeneral = PuestoGeneral::withoutEvents(fn (): PuestoGeneral => PuestoGeneral::query()->create([
        'nombre' => 'PG modelo',
        'empresa_id' => $this->empresa->id,
    ]));

    $ocupacion = Ocupacion::query()->create(['descripcion' => 'Ocup modelo']);
    Puesto::withoutEvents(fn (): Puesto => Puesto::query()->create([
        'nombre' => 'Puesto hijo modelo',
        'empresa_id' => $this->empresa->id,
        'puesto_general_id' => $puestoGeneral->id,
        'ocupacion_id' => $ocupacion->id,
        'area_general_id' => $areaGeneral->id,
    ]));

    expect(fn () => $puestoGeneral->delete())
        ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene puestos asociados.');
});
