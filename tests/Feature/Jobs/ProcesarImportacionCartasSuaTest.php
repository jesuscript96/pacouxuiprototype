<?php

declare(strict_types=1);

use App\Exports\PlantillaCartasSuaExport;
use App\Exports\PlantillaCartasSuaSheet;
use App\Jobs\ProcesarImportacionCartasSua;
use App\Models\CartaSua;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\Importacion;
use App\Models\User;
use App\Services\CartaSuaPdfService;
use App\Support\CartaSuaImportSpreadsheet;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);

    $this->empresa = Empresa::factory()->create();

    $this->usuario = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
});

// =========================================================================
// PlantillaCartasSuaSheet
// =========================================================================

test('plantilla tiene las 10 cabeceras correctas', function (): void {
    $sheet = new PlantillaCartasSuaSheet;

    expect($sheet->headings())
        ->toHaveCount(10)
        ->and($sheet->headings()[0])->toBe('Número de empleado')
        ->and($sheet->headings()[9])->toBe('Bimestre');
});

test('plantilla tiene fila de ejemplo', function (): void {
    $sheet = new PlantillaCartasSuaSheet;

    expect($sheet->array())
        ->toHaveCount(1)
        ->and($sheet->array()[0][0])->toBe('1234');
});

test('export tiene dos hojas', function (): void {
    $export = new PlantillaCartasSuaExport;

    expect($export->sheets())->toHaveCount(2);
});

// =========================================================================
// CartaSuaImportSpreadsheet — Validación
// =========================================================================

test('validateRow acepta fila válida', function (): void {
    $data = [
        'numero_empleado' => '1234',
        'razon_social' => 'Test Corp S.A.',
        'rfc' => 'XAXX010101XXX',
        'curp' => 'XAXX010101HDFRRL09',
        'nombre_completo' => 'Juan Pérez',
        'retiro' => 1500.00,
        'cv' => 3000.00,
        'infonavit' => 1200.00,
        'total' => 5700.00,
        'bimestre' => 'Enero-Febrero 2024',
    ];

    $errores = CartaSuaImportSpreadsheet::validateRow($data);

    expect($errores)->toBeEmpty();
});

test('validateRow rechaza fila con datos faltantes', function (): void {
    $data = [
        'numero_empleado' => '',
        'razon_social' => '',
        'rfc' => 'XX',
        'curp' => 'XXX',
        'nombre_completo' => '',
        'total' => 0,
        'bimestre' => '',
    ];

    $errores = CartaSuaImportSpreadsheet::validateRow($data);

    expect($errores)->not->toBeEmpty()
        ->and(count($errores))->toBeGreaterThanOrEqual(5);
});

test('validateRow rechaza total cero', function (): void {
    $data = [
        'numero_empleado' => '1234',
        'razon_social' => 'Test Corp',
        'rfc' => 'XAXX010101XXX',
        'curp' => 'XAXX010101HDFRRL09',
        'nombre_completo' => 'Juan Pérez',
        'total' => 0,
        'bimestre' => 'Enero-Febrero 2024',
    ];

    $errores = CartaSuaImportSpreadsheet::validateRow($data);

    expect($errores)->toContain('Total debe ser mayor a 0');
});

// =========================================================================
// CartaSuaImportSpreadsheet — normalizeHeaders
// =========================================================================

test('normalizeHeaders mapea encabezados legacy a nombres internos', function (): void {
    $legacy = ['Número de empleado', 'Razón social', 'RFC', 'CURP', 'Nombre', 'Retiro', 'C.V.', 'Infonavit', 'Tot RCV_INF', 'Bimestre'];

    $result = CartaSuaImportSpreadsheet::normalizeHeaders($legacy);

    expect($result)->toBe([
        'numero_empleado', 'razon_social', 'rfc', 'curp', 'nombre_completo',
        'retiro', 'cv', 'infonavit', 'total', 'bimestre',
    ]);
});

test('normalizeHeaders acepta encabezados snake_case', function (): void {
    $snake = ['numero_empleado', 'razon_social', 'rfc', 'curp', 'nombre_completo', 'retiro', 'cv', 'infonavit', 'total', 'bimestre'];

    $result = CartaSuaImportSpreadsheet::normalizeHeaders($snake);

    expect($result)->toBe($snake);
});

// =========================================================================
// CartaSuaImportSpreadsheet — normalizeRow
// =========================================================================

test('normalizeRow limpia valores numéricos con formato moneda', function (): void {
    $data = [
        'numero_empleado' => '1234',
        'retiro' => '$1,500.00',
        'cv' => '3000.50',
        'infonavit' => '1200',
        'total' => '$5,700.50',
    ];

    $resultado = CartaSuaImportSpreadsheet::normalizeRow($data);

    expect($resultado['retiro'])->toBe(1500.00)
        ->and($resultado['cv'])->toBe(3000.50)
        ->and($resultado['infonavit'])->toBe(1200.00)
        ->and($resultado['total'])->toBe(5700.50);
});

// =========================================================================
// CartaSuaImportSpreadsheet — resolveDataSheet
// =========================================================================

test('resolveDataSheet encuentra hoja por título "Cartas SUA"', function (): void {
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->setTitle('Instrucciones');
    $hojaData = $spreadsheet->createSheet();
    $hojaData->setTitle('Cartas SUA');
    $hojaData->setCellValue('A1', 'numero_empleado');

    $result = CartaSuaImportSpreadsheet::resolveDataSheet($spreadsheet);

    expect($result->getTitle())->toBe('Cartas SUA');
});

test('resolveDataSheet encuentra hoja legacy "Carga de registros"', function (): void {
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->setTitle('Otra hoja');
    $hojaData = $spreadsheet->createSheet();
    $hojaData->setTitle('Carga de registros');
    $hojaData->setCellValue('A1', 'Número de empleado');

    $result = CartaSuaImportSpreadsheet::resolveDataSheet($spreadsheet);

    expect($result->getTitle())->toBe('Carga de registros');
});

test('resolveDataSheet encuentra hoja por encabezado A1 legacy', function (): void {
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->setTitle('Sheet1');
    $spreadsheet->getActiveSheet()->setCellValue('A1', 'Número de empleado');

    $result = CartaSuaImportSpreadsheet::resolveDataSheet($spreadsheet);

    expect($result->getCell('A1')->getValue())->toBe('Número de empleado');
});

// =========================================================================
// Excel — lectura y escritura
// =========================================================================

test('helper crea Excel legible con datos correctos', function (): void {
    $archivoPath = crearExcelCartaSuaTest([
        ['5678', 'ACME S.A.', 'XAXX010101XXX', 'XAXX010101HDFRRL09', 'Juan Pérez', '1500.00', '3000.00', '1200.00', '5700.00', 'Enero-Febrero 2024'],
    ]);

    $fullPath = Storage::path($archivoPath);
    expect(file_exists($fullPath))->toBeTrue();

    $spreadsheet = IOFactory::load($fullPath);
    $sheet = CartaSuaImportSpreadsheet::resolveDataSheet($spreadsheet);

    expect((int) $sheet->getHighestRow())->toBe(2);

    $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());

    $headers = [];
    for ($col = 1; $col <= $colIndex; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, 1);
        $headers[] = $cell->getValue() !== null ? trim((string) $cell->getValue()) : '';
    }

    expect($headers[0])->toBe('Número de empleado')
        ->and($headers[9])->toBe('Bimestre');

    $normalizedHeaders = CartaSuaImportSpreadsheet::normalizeHeaders($headers);
    expect($normalizedHeaders[0])->toBe('numero_empleado')
        ->and($normalizedHeaders[9])->toBe('bimestre');

    $values = [];
    for ($col = 1; $col <= $colIndex; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, 2);
        $values[] = $cell->getValue() !== null ? trim((string) $cell->getValue()) : '';
    }

    $data = array_combine($normalizedHeaders, $values);
    $data = CartaSuaImportSpreadsheet::normalizeRow($data);

    expect($data['numero_empleado'])->toBe('5678')
        ->and($data['bimestre'])->toBe('Enero-Febrero 2024')
        ->and($data['total'])->toBe(5700.00);

    @unlink($fullPath);
});

// =========================================================================
// Job — procesamiento
// =========================================================================

test('job crea cartas para colaboradores existentes', function (): void {
    Queue::fake([\App\Jobs\EnviarNotificacionPushJob::class]);

    $colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
        'numero_colaborador' => '5678',
    ]);

    $archivoPath = crearExcelCartaSuaTest([
        ['5678', 'ACME S.A.', 'XAXX010101XXX', 'XAXX010101HDFRRL09', 'Juan Pérez', '1500.00', '3000.00', '1200.00', '5700.00', 'Enero-Febrero 2024'],
    ]);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $this->usuario->id,
        'tipo' => Importacion::TIPO_CARGA_SUA,
        'archivo_original' => $archivoPath,
        'estado' => Importacion::ESTADO_PENDIENTE,
        'total_filas' => 0,
        'filas_procesadas' => 0,
        'filas_exitosas' => 0,
        'filas_con_error' => 0,
    ]);

    $mockPdf = Mockery::mock(CartaSuaPdfService::class);
    $mockPdf->shouldReceive('generar')->once()->andReturn('test/path.pdf');

    $job = new ProcesarImportacionCartasSua($importacion);
    $job->handle($mockPdf);

    $importacion->refresh();

    expect($importacion->estado)->not->toBe(Importacion::ESTADO_PENDIENTE)
        ->and($importacion->estado)->not->toBe(Importacion::ESTADO_FALLIDA)
        ->and($importacion->total_filas)->toBeGreaterThan(0)
        ->and($importacion->filas_procesadas)->toBeGreaterThan(0)
        ->and($importacion->filas_con_error)->toBe(0)
        ->and($importacion->filas_exitosas)->toBe(1)
        ->and(CartaSua::count())->toBe(1);

    $carta = CartaSua::first();
    expect($carta->empresa_id)->toBe($this->empresa->id)
        ->and($carta->colaborador_id)->toBe($colaborador->id)
        ->and($carta->bimestre)->toBe('Enero-Febrero 2024')
        ->and($carta->razon_social)->toBe('ACME S.A.');
});

test('job omite duplicados sin error', function (): void {
    Queue::fake([\App\Jobs\EnviarNotificacionPushJob::class]);

    $colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
        'numero_colaborador' => '5678',
    ]);

    CartaSua::factory()->paraColaborador($colaborador)->create([
        'bimestre' => 'Enero-Febrero 2024',
        'razon_social' => 'ACME S.A.',
    ]);

    $archivoPath = crearExcelCartaSuaTest([
        ['5678', 'ACME S.A.', 'XAXX010101XXX', 'XAXX010101HDFRRL09', 'Juan Pérez', '1500.00', '3000.00', '1200.00', '5700.00', 'Enero-Febrero 2024'],
    ]);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $this->usuario->id,
        'tipo' => Importacion::TIPO_CARGA_SUA,
        'archivo_original' => $archivoPath,
        'estado' => Importacion::ESTADO_PENDIENTE,
        'total_filas' => 0,
        'filas_procesadas' => 0,
        'filas_exitosas' => 0,
        'filas_con_error' => 0,
    ]);

    $mockPdf = Mockery::mock(CartaSuaPdfService::class);
    $mockPdf->shouldReceive('generar')->never();

    $job = new ProcesarImportacionCartasSua($importacion);
    $job->handle($mockPdf);

    expect(CartaSua::count())->toBe(1);
});

test('job registra error cuando colaborador no existe', function (): void {
    Queue::fake([\App\Jobs\EnviarNotificacionPushJob::class]);

    $archivoPath = crearExcelCartaSuaTest([
        ['9999', 'ACME S.A.', 'XAXX010101XXX', 'XAXX010101HDFRRL09', 'Juan Pérez', '1500.00', '3000.00', '1200.00', '5700.00', 'Enero-Febrero 2024'],
    ]);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $this->usuario->id,
        'tipo' => Importacion::TIPO_CARGA_SUA,
        'archivo_original' => $archivoPath,
        'estado' => Importacion::ESTADO_PENDIENTE,
        'total_filas' => 0,
        'filas_procesadas' => 0,
        'filas_exitosas' => 0,
        'filas_con_error' => 0,
    ]);

    $mockPdf = Mockery::mock(CartaSuaPdfService::class);
    $mockPdf->shouldReceive('generar')->never();

    $job = new ProcesarImportacionCartasSua($importacion);
    $job->handle($mockPdf);

    expect(CartaSua::count())->toBe(0);

    $importacion->refresh();
    expect($importacion->filas_con_error)->toBe(1)
        ->and($importacion->estado)->toBe(Importacion::ESTADO_CON_ERRORES);
});

test('job marca importación como fallida si archivo no existe', function (): void {
    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $this->usuario->id,
        'tipo' => Importacion::TIPO_CARGA_SUA,
        'archivo_original' => 'no-existe/archivo.xlsx',
        'estado' => Importacion::ESTADO_PENDIENTE,
        'total_filas' => 0,
        'filas_procesadas' => 0,
        'filas_exitosas' => 0,
        'filas_con_error' => 0,
    ]);

    $mockPdf = Mockery::mock(CartaSuaPdfService::class);

    $job = new ProcesarImportacionCartasSua($importacion);
    $job->handle($mockPdf);

    $importacion->refresh();
    expect($importacion->estado)->toBe(Importacion::ESTADO_FALLIDA);
});

// =========================================================================
// Helper
// =========================================================================

/**
 * Crea un archivo Excel real en storage para tests.
 *
 * @param  array<int, array<int, string>>  $filas  Cada fila es un array con los 10 valores en orden
 */
function crearExcelCartaSuaTest(array $filas): string
{
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Carga de registros');

    $headers = ['Número de empleado', 'Razón social', 'RFC', 'CURP', 'Nombre', 'Retiro', 'C.V.', 'Infonavit', 'Tot RCV_INF', 'Bimestre'];

    $allData = array_merge([$headers], $filas);
    $sheet->fromArray($allData, null, 'A1');

    $path = 'importaciones/test_cartas_sua_'.uniqid('', true).'.xlsx';
    $fullPath = Storage::path($path);
    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($fullPath);

    return $path;
}
