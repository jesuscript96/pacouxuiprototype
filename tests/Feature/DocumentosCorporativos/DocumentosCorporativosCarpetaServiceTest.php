<?php

declare(strict_types=1);

use App\Models\Area;
use App\Models\Carpeta;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Puesto;
use App\Models\Ubicacion;
use App\Services\DocumentosCorporativosCarpetaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('uploads');
    Storage::fake('local');
});

it('crea carpeta con pivotes, subcarpeta y directorio en disco uploads', function (): void {
    $empresa = Empresa::factory()->create();
    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ubicación prueba', 'empresa_id' => $empresa->id]);
    $departamento = Departamento::query()->create(['nombre' => 'Departamento prueba', 'empresa_id' => $empresa->id]);
    $area = Area::query()->create(['nombre' => 'Área prueba', 'empresa_id' => $empresa->id]);
    $puesto = Puesto::query()->create(['nombre' => 'Puesto prueba', 'empresa_id' => $empresa->id]);

    $service = app(DocumentosCorporativosCarpetaService::class);

    $carpeta = $service->crearDesdeWizard($empresa, null, [
        'ubicacion_ids' => [$ubicacion->id],
        'departamento_ids' => [$departamento->id],
        'area_ids' => [$area->id],
        'puesto_ids' => [$puesto->id],
        'nombre' => 'Manual interno',
        'subcarpetas' => [
            ['nombre' => 'Legales'],
        ],
        'staging_id' => '',
    ]);

    expect($carpeta)->toBeInstanceOf(Carpeta::class)
        ->and($carpeta->ubicaciones()->count())->toBe(1)
        ->and($carpeta->empresasPivot()->count())->toBe(1)
        ->and($carpeta->subcarpetas()->count())->toBe(1);

    Storage::disk('uploads')->assertExists($carpeta->url);
    $sub = $carpeta->subcarpetas->first();
    expect($sub)->not->toBeNull();
    Storage::disk('uploads')->assertExists($sub->url);
});

it('normaliza nombres de archivos al mover desde staging a la carpeta', function (): void {
    $empresa = Empresa::factory()->create();
    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ubicación prueba', 'empresa_id' => $empresa->id]);
    $departamento = Departamento::query()->create(['nombre' => 'Departamento prueba', 'empresa_id' => $empresa->id]);
    $area = Area::query()->create(['nombre' => 'Área prueba', 'empresa_id' => $empresa->id]);
    $puesto = Puesto::query()->create(['nombre' => 'Puesto prueba', 'empresa_id' => $empresa->id]);

    $stagingId = 'stg-test-1';
    Storage::disk('local')->put(
        'tmp/carpetas-staging/'.$stagingId.'/raiz/mi foto niño.JPG',
        'fake'
    );

    $service = app(DocumentosCorporativosCarpetaService::class);
    $carpeta = $service->crearDesdeWizard($empresa, null, [
        'ubicacion_ids' => [$ubicacion->id],
        'departamento_ids' => [$departamento->id],
        'area_ids' => [$area->id],
        'puesto_ids' => [$puesto->id],
        'nombre' => 'Docs legales',
        'subcarpetas' => [],
        'staging_id' => $stagingId,
    ]);

    $archivos = Storage::disk('uploads')->files($carpeta->url);
    expect($archivos)->toHaveCount(1)
        ->and(basename($archivos[0]))->toBe('mi_foto_nino.jpg');
});
