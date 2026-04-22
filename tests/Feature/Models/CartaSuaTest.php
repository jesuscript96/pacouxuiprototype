<?php

declare(strict_types=1);

use App\Models\CartaSua;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Subindustria;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);
});

// =========================================================================
// Factory y creación
// =========================================================================

test('puede crear una carta SUA con factory', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    expect($carta)
        ->toBeInstanceOf(CartaSua::class)
        ->empresa_id->toBe($this->empresa->id)
        ->colaborador_id->toBe($this->colaborador->id)
        ->firmado->toBeFalse();
});

test('campos financieros se almacenan como decimal', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'retiro' => 1234.56,
        'cesantia_vejez' => 2345.67,
        'infonavit' => 987.65,
        'total' => 4567.88,
    ]);

    $carta->refresh();

    expect($carta->retiro)->toBe('1234.56')
        ->and($carta->cesantia_vejez)->toBe('2345.67')
        ->and($carta->infonavit)->toBe('987.65')
        ->and($carta->total)->toBe('4567.88');
});

test('datos_origen se castea como array', function (): void {
    $datos = [
        'numero_empleado' => '1234',
        'rfc' => 'ABCD123456XYZ',
        'curp' => 'ABCD123456HABCDE01',
    ];

    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'datos_origen' => $datos,
    ]);

    $carta->refresh();

    expect($carta->datos_origen)
        ->toBeArray()
        ->toHaveKey('numero_empleado', '1234')
        ->toHaveKey('rfc', 'ABCD123456XYZ');
});

// =========================================================================
// Relaciones
// =========================================================================

test('pertenece a empresa', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    expect($carta->empresa)
        ->toBeInstanceOf(Empresa::class)
        ->id->toBe($this->empresa->id);
});

test('pertenece a colaborador', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    expect($carta->colaborador)
        ->toBeInstanceOf(Colaborador::class)
        ->id->toBe($this->colaborador->id);
});

test('colaborador tiene muchas cartas SUA', function (): void {
    CartaSua::factory()->paraColaborador($this->colaborador)->count(3)->create([
        'bimestre' => fake()->unique()->randomElement([
            'Enero-Febrero 2024',
            'Marzo-Abril 2024',
            'Mayo-Junio 2024',
        ]),
    ]);

    expect($this->colaborador->cartasSua)->toHaveCount(3);
});

// =========================================================================
// Unicidad (RN-01)
// =========================================================================

test('no permite duplicar carta con misma combinación colaborador + bimestre + razón social', function (): void {
    CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'ACME S.A. de C.V.',
    ]);

    CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'ACME S.A. de C.V.',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

test('permite misma combinación bimestre + razón social para otro colaborador', function (): void {
    $otroColaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'ACME S.A. de C.V.',
    ]);

    $carta2 = CartaSua::factory()->paraColaborador($otroColaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'ACME S.A. de C.V.',
    ]);

    expect($carta2)->toBeInstanceOf(CartaSua::class);
});

test('permite mismo colaborador + bimestre con diferente razón social', function (): void {
    CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'ACME S.A. de C.V.',
    ]);

    $carta2 = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'OTRA EMPRESA S.A. de C.V.',
    ]);

    expect($carta2)->toBeInstanceOf(CartaSua::class);
});

test('existeDuplicado detecta registros existentes', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'ACME S.A. de C.V.',
    ]);

    expect(CartaSua::existeDuplicado(
        $this->colaborador->id,
        'Enero-Febrero 2024',
        'ACME S.A. de C.V.'
    ))->toBeTrue();

    expect(CartaSua::existeDuplicado(
        $this->colaborador->id,
        'Marzo-Abril 2024',
        'ACME S.A. de C.V.'
    ))->toBeFalse();
});

// =========================================================================
// Estado calculado
// =========================================================================

test('estado es pendiente cuando no ha sido vista', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    expect($carta->estado)->toBe(CartaSua::ESTADO_PENDIENTE)
        ->and($carta->estado_color)->toBe('warning')
        ->and($carta->estado_label)->toBe('Pendiente');
});

test('estado es vista cuando tiene primera_visualizacion pero no firmada', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->vista()->create();

    expect($carta->estado)->toBe(CartaSua::ESTADO_VISTA)
        ->and($carta->estado_color)->toBe('info')
        ->and($carta->estado_label)->toBe('Vista');
});

test('estado es firmada cuando firmado es true', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->firmada()->create();

    expect($carta->estado)->toBe(CartaSua::ESTADO_FIRMADA)
        ->and($carta->estado_color)->toBe('success')
        ->and($carta->estado_label)->toBe('Firmada');
});

// =========================================================================
// Métodos
// =========================================================================

test('registrarVisualizacion setea primera_visualizacion la primera vez', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    expect($carta->primera_visualizacion)->toBeNull();

    $carta->registrarVisualizacion();

    expect($carta->primera_visualizacion)->not->toBeNull()
        ->and($carta->ultima_visualizacion)->not->toBeNull();
});

test('registrarVisualizacion no sobreescribe primera_visualizacion en llamadas posteriores', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'primera_visualizacion' => now()->subDays(5),
    ]);

    $primeraOriginal = $carta->primera_visualizacion->toDateTimeString();

    $carta->registrarVisualizacion();

    expect($carta->primera_visualizacion->toDateTimeString())->toBe($primeraOriginal)
        ->and($carta->ultima_visualizacion)->not->toBeNull();
});

test('marcarComoFirmada actualiza campos de firma', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    $carta->marcarComoFirmada(
        imagenFirma: 'base64firma',
        nom151: 'datos-nom151',
        hashNom151: 'hash123',
        codigoValidacion: 'ABC-123',
    );

    $carta->refresh();

    expect($carta->firmado)->toBeTrue()
        ->and($carta->fecha_firma)->not->toBeNull()
        ->and($carta->imagen_firma)->toBe('base64firma')
        ->and($carta->nom151)->toBe('datos-nom151')
        ->and($carta->hash_nom151)->toBe('hash123')
        ->and($carta->codigo_validacion)->toBe('ABC-123');
});

test('marcarComoFirmada funciona sin Nubarium', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    $carta->marcarComoFirmada(imagenFirma: 'base64firma');

    $carta->refresh();

    expect($carta->firmado)->toBeTrue()
        ->and($carta->fecha_firma)->not->toBeNull()
        ->and($carta->imagen_firma)->toBe('base64firma')
        ->and($carta->nom151)->toBeNull()
        ->and($carta->hash_nom151)->toBeNull()
        ->and($carta->codigo_validacion)->toBeNull();
});

test('getDatosParaPdf retorna datos_origen si existen', function (): void {
    $datosOrigen = [
        'nombre' => 'Juan Pérez',
        'rfc' => 'ABCD123456XYZ',
        'curp' => 'ABCD123456HABCDE01',
    ];

    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'datos_origen' => $datosOrigen,
    ]);

    expect($carta->getDatosParaPdf())->toBe($datosOrigen);
});

test('getDatosParaPdf hace fallback a datos del colaborador si no hay datos_origen', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'datos_origen' => null,
    ]);

    $datos = $carta->getDatosParaPdf();

    expect($datos)
        ->toHaveKey('nombre_empleado')
        ->toHaveKey('rfc')
        ->toHaveKey('curp')
        ->toHaveKey('razon_social')
        ->toHaveKey('bimestre')
        ->toHaveKey('retiro')
        ->toHaveKey('cesantia_vejez')
        ->toHaveKey('infonavit')
        ->toHaveKey('total');
});

// =========================================================================
// Accessor total formateado
// =========================================================================

test('totalFormateado retorna formato moneda', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'total' => 12345.67,
    ]);

    expect($carta->total_formateado)->toBe('$12,345.67');
});

// =========================================================================
// Factory states
// =========================================================================

test('factory state conPdf genera pdf_path', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->conPdf()->create();

    expect($carta->pdf_path)
        ->not->toBeNull()
        ->toContain('cartas-sua');
});

test('factory state conDatosOrigen genera JSON completo', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->conDatosOrigen()->create();

    expect($carta->datos_origen)
        ->toBeArray()
        ->toHaveKeys(['numero_empleado', 'razon_social', 'rfc', 'curp', 'nombre', 'retiro', 'cv', 'infonavit', 'total', 'bimestre']);
});
