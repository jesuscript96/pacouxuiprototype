<?php

/**
 * Alta de colaboradores vía ColaboradorService → User + ficha `colaboradores`; pivot empresa_user solo si acceso al panel.
 */
use App\Models\Banco;
use App\Models\Departamento;
use App\Models\User;
use App\Services\ColaboradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->service = app(ColaboradorService::class);
});

function datosMinimosColaborador(?int $empresaId = null): array
{
    $base = [
        'nombre' => 'Juan',
        'apellido_paterno' => 'Pérez',
        'apellido_materno' => 'García',
        'email' => 'juan.perez.'.uniqid().'@test.com',
        'fecha_nacimiento' => '1990-05-15',
        'fecha_ingreso' => '2024-01-01',
        'periodicidad_pago' => 'QUINCENAL',
    ];
    if ($empresaId !== null) {
        $base['empresa_id'] = $empresaId;
    }

    return $base;
}

it('crea colaborador con datos mínimos (nombre, apellidos, email, fecha_nacimiento, fecha_ingreso, periodicidad_pago)', function (): void {
    $data = datosMinimosColaborador();

    $colaborador = $this->service->crearColaborador($data, $this->empresa);

    expect($colaborador)->toBeInstanceOf(User::class)
        ->and($colaborador->id)->not->toBeNull()
        ->and($colaborador->empresa_id)->toBe($this->empresa->id)
        ->and($colaborador->tieneRol('colaborador'))->toBeTrue()
        ->and($colaborador->name)->toBe('Juan')
        ->and($colaborador->apellido_paterno)->toBe('Pérez')
        ->and($colaborador->apellido_materno)->toBe('García')
        ->and($colaborador->email)->toBe($data['email'])
        ->and($colaborador->fecha_nacimiento->format('Y-m-d'))->toBe('1990-05-15')
        ->and($colaborador->fecha_ingreso->format('Y-m-d'))->toBe('2024-01-01')
        ->and($colaborador->periodicidad_pago)->toBe('QUINCENAL');
});

it('crea colaborador con todos los campos', function (): void {
    $ubicacion = crearUbicacion($this->empresa, ['nombre' => 'Oficina Central']);
    $departamento = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'TI']);
    $area = crearArea($this->empresa, ['nombre' => 'Desarrollo']);
    $puesto = crearPuesto($this->empresa, ['nombre' => 'Desarrollador']);
    $data = array_merge(datosMinimosColaborador(), [
        'telefono_movil' => '5511223344',
        'numero_colaborador' => 'EMP001',
        'genero' => 'M',
        'curp' => 'PEGJ900515HDFRRN01',
        'rfc' => 'PEGJ900515XXX',
        'nss' => '12345678901',
        'ubicacion_id' => $ubicacion->id,
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
        'salario_bruto' => 25000.50,
    ]);

    $user = $this->service->crearColaborador($data, $this->empresa);
    $ficha = $user->colaborador;

    expect($user->telefono_movil)->toBe('5511223344')
        ->and($user->numero_colaborador)->toBe('EMP001')
        ->and($ficha)->not->toBeNull()
        ->and($ficha->ubicacion_id)->toBe($ubicacion->id)
        ->and($ficha->departamento_id)->toBe($departamento->id)
        ->and($ficha->area_id)->toBe($area->id)
        ->and($ficha->puesto_id)->toBe($puesto->id)
        ->and((float) $user->salario_bruto)->toBe(25000.50);
});

it('falla sin email ni teléfono móvil', function (): void {
    $data = datosMinimosColaborador();
    unset($data['email']);
    $data['telefono_movil'] = null;

    $this->service->crearColaborador($data, $this->empresa);
})->throws(ValidationException::class, 'Debe proporcionar al menos email o teléfono móvil');

it('falla con email duplicado en la misma empresa', function (): void {
    $data = datosMinimosColaborador();
    $data['email'] = 'duplicado@test.com';
    $this->service->crearColaborador($data, $this->empresa);

    $this->service->crearColaborador(array_merge($data, ['telefono_movil' => '5599887766']), $this->empresa);
})->throws(ValidationException::class, 'El email ya está registrado');

it('no permite el mismo email en otra empresa por unicidad global en users', function (): void {
    $data = datosMinimosColaborador();
    $data['email'] = 'mismo@test.com';
    $this->service->crearColaborador($data, $this->empresa);

    $empresa2 = crearEmpresaMinima();
    $empresa2->update(['nombre' => 'Otra Empresa S.A.']);
    $this->service->crearColaborador(array_merge($data, ['email' => 'mismo@test.com', 'telefono_movil' => '5511223344']), $empresa2);
})->throws(ValidationException::class, 'El email ya está registrado');

it('crea historiales iniciales al crear colaborador', function (): void {
    $departamento = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Ventas']);
    $area = crearArea($this->empresa, ['nombre' => 'Área 1']);
    $puesto = crearPuesto($this->empresa, ['nombre' => 'Vendedor']);
    $data = array_merge(datosMinimosColaborador(), [
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
    ]);

    $colaborador = $this->service->crearColaborador($data, $this->empresa);

    expect($colaborador->historialDepartamentos()->count())->toBe(1)
        ->and($colaborador->historialAreas()->count())->toBe(1)
        ->and($colaborador->historialPuestos()->count())->toBe(1)
        ->and($colaborador->historialDepartamentos()->first()->departamento_id)->toBe($departamento->id);
});

it('genera codigo_jefe correctamente como concatenación de IDs', function (): void {
    $ubicacion = crearUbicacion($this->empresa, ['nombre' => 'U1']);
    $departamento = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'D1']);
    $area = crearArea($this->empresa, ['nombre' => 'A1']);
    $puesto = crearPuesto($this->empresa, ['nombre' => 'P1']);
    $data = array_merge(datosMinimosColaborador(), [
        'ubicacion_id' => $ubicacion->id,
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
    ]);

    $colaborador = $this->service->crearColaborador($data, $this->empresa);

    $esperado = implode('.', [$ubicacion->id, $departamento->id, $area->id, $puesto->id]);
    expect($colaborador->codigo_jefe)->toBe($esperado);
});

it('crea beneficiarios y valida que porcentajes sumen 100', function (): void {
    $data = array_merge(datosMinimosColaborador(), [
        'beneficiarios' => [
            ['nombre_completo' => 'María Pérez', 'parentesco' => 'Cónyuge', 'porcentaje' => 50],
            ['nombre_completo' => 'Pedro Pérez', 'parentesco' => 'Hijo', 'porcentaje' => 50],
        ],
    ]);

    $colaborador = $this->service->crearColaborador($data, $this->empresa);

    expect($colaborador->beneficiarios)->toHaveCount(2)
        ->and((float) $colaborador->beneficiarios->sum('porcentaje'))->toBe(100.0);
});

it('falla si beneficiarios suman menos de 100', function (): void {
    $data = array_merge(datosMinimosColaborador(), [
        'beneficiarios' => [
            ['nombre_completo' => 'María Pérez', 'parentesco' => 'Cónyuge', 'porcentaje' => 30],
            ['nombre_completo' => 'Pedro Pérez', 'parentesco' => 'Hijo', 'porcentaje' => 50],
        ],
    ]);

    $this->service->crearColaborador($data, $this->empresa);
})->throws(ValidationException::class, 'suma de porcentajes');

it('crea cuenta de nómina asociada', function (): void {
    $banco = Banco::create(['nombre' => 'Banco Test', 'codigo' => 1, 'comision' => 0]);
    $data = array_merge(datosMinimosColaborador(), [
        'cuenta_nomina' => [
            'banco_id' => $banco->id,
            'numero_cuenta' => '012345678901234567',
            'tipo_cuenta' => 'CLABE',
            'estado' => 'ACTIVA',
        ],
    ]);

    $colaborador = $this->service->crearColaborador($data, $this->empresa);

    expect($colaborador->cuentasNomina)->toHaveCount(1)
        ->and($colaborador->cuentasNomina->first()->numero_cuenta)->toBe('012345678901234567')
        ->and($colaborador->cuentasNomina->first()->banco_id)->toBe($banco->id);
});

it('falla con cuenta de nómina duplicada en colaborador activo', function (): void {
    $banco = Banco::create(['nombre' => 'Banco Test', 'codigo' => 1, 'comision' => 0]);
    $numeroCuenta = '012345678901234567';
    $data1 = array_merge(datosMinimosColaborador(), [
        'cuenta_nomina' => [
            'banco_id' => $banco->id,
            'numero_cuenta' => $numeroCuenta,
            'tipo_cuenta' => 'CLABE',
            'estado' => 'ACTIVA',
        ],
    ]);
    $this->service->crearColaborador($data1, $this->empresa);

    $data2 = array_merge(datosMinimosColaborador(), [
        'cuenta_nomina' => [
            'banco_id' => $banco->id,
            'numero_cuenta' => $numeroCuenta,
            'tipo_cuenta' => 'CLABE',
            'estado' => 'ACTIVA',
        ],
    ]);
    $this->service->crearColaborador($data2, $this->empresa);
})->throws(ValidationException::class, 'número de cuenta ya está en uso');
