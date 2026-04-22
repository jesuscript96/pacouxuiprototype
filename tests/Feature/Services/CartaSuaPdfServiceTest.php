<?php

declare(strict_types=1);

use App\Models\CartaSua;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Subindustria;
use App\Services\CartaSuaPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('uploads');

    // BL: Mock DomPDF para evitar memory exhaustion en tests
    $fakePdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
    $fakePdf->shouldReceive('setPaper')->andReturnSelf();
    $fakePdf->shouldReceive('output')->andReturn('%PDF-1.4 fake content');

    Pdf::shouldReceive('loadView')
        ->andReturn($fakePdf);

    $industria = Industria::withoutEvents(fn (): Industria => Industria::query()->create([
        'nombre' => 'Industria Test',
    ]));

    $subindustria = Subindustria::withoutEvents(fn (): Subindustria => Subindustria::query()->create([
        'nombre' => 'Subindustria Test',
        'industria_id' => $industria->id,
    ]));

    $this->empresa = Empresa::withoutEvents(fn (): Empresa => Empresa::factory()->create([
        'nombre' => 'Acme Corp',
        'industria_id' => $industria->id,
        'sub_industria_id' => $subindustria->id,
    ]));

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $this->service = app(CartaSuaPdfService::class);
});

// =========================================================================
// Generación de PDF
// =========================================================================

test('genera PDF y guarda en storage', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'Acme Corp S.A. de C.V.',
        'retiro' => 1500.00,
        'cesantia_vejez' => 3000.00,
        'infonavit' => 1200.00,
        'total' => 5700.00,
    ]);

    $path = $this->service->generar($carta);

    expect($path)
        ->toContain('cartas-sua')
        ->toEndWith('carta.pdf');

    Storage::disk('uploads')->assertExists($path);

    $carta->refresh();
    expect($carta->pdf_path)->toBe($path);
});

test('genera PDF con datos del Excel', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    $datosExcel = [
        'nombre' => 'María López García',
        'rfc' => 'LOGM900515XXX',
        'curp' => 'LOGM900515MDFPRL01',
    ];

    $path = $this->service->generar($carta, $datosExcel);

    expect($path)->not->toBeEmpty();
    Storage::disk('uploads')->assertExists($path);
});

test('genera PDF usando datos_origen si existen', function (): void {
    $carta = CartaSua::factory()
        ->paraColaborador($this->colaborador)
        ->conDatosOrigen()
        ->create();

    $path = $this->service->generar($carta);

    expect($path)->not->toBeEmpty();
    Storage::disk('uploads')->assertExists($path);
});

test('path sigue estructura companies/{slug}/cartas-sua/{id}/carta.pdf', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    $path = $this->service->generar($carta);

    expect($path)
        ->toContain('companies/')
        ->toContain('/cartas-sua/')
        ->toContain("/{$carta->id}/")
        ->toEndWith('carta.pdf');
});

test('actualiza pdf_path en el modelo después de generar', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'pdf_path' => null,
    ]);

    expect($carta->pdf_path)->toBeNull();

    $path = $this->service->generar($carta);
    $carta->refresh();

    expect($carta->pdf_path)->toBe($path);
});

// =========================================================================
// Regeneración
// =========================================================================

test('regenerar elimina PDF anterior y genera uno nuevo', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();

    $pathOriginal = $this->service->generar($carta);
    Storage::disk('uploads')->assertExists($pathOriginal);

    $nuevoPath = $this->service->regenerar($carta);

    expect($nuevoPath)->not->toBeEmpty();
    Storage::disk('uploads')->assertExists($nuevoPath);

    $carta->refresh();
    expect($carta->pdf_path)->toBe($nuevoPath);
});

// =========================================================================
// URL
// =========================================================================

test('obtenerUrl retorna cadena vacía si no hay pdf_path', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create([
        'pdf_path' => null,
    ]);

    $url = $this->service->obtenerUrl($carta);

    expect($url)->toBe('');
});

test('obtenerUrl retorna URL si existe pdf_path', function (): void {
    $carta = CartaSua::factory()->paraColaborador($this->colaborador)->create();
    $this->service->generar($carta);
    $carta->refresh();

    $url = $this->service->obtenerUrl($carta);

    expect($url)->not->toBeEmpty();
});
