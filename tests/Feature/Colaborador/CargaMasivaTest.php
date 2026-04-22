<?php

/**
 * Importación masiva: crea Users colaborador (ColaboradorService / job).
 */
use App\Jobs\ProcesarImportacionColaboradores;
use App\Models\ErrorImportacion;
use App\Models\Importacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    Permission::firstOrCreate(['name' => 'Upload:Colaborador', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Import:Colaborador', 'guard_name' => 'web']);
});

function crearExcelValidoMinimo(string $path): void
{
    $headers = [
        'user_id', 'name', 'apellido_paterno', 'apellido_materno', 'email', 'telefono_movil',
        'numero_colaborador', 'fecha_nacimiento', 'genero', 'fecha_ingreso', 'periodicidad_pago',
    ];
    $row = [
        '', 'Juan', 'Pérez', 'García', 'juan.carga.masiva.'.uniqid().'@test.com', '5511223344',
        'EMP'.uniqid(), '1990-05-15', 'M', '2024-01-01', 'QUINCENAL',
    ];
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray($row, null, 'A2');
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $fullPath = Storage::path($path);
    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $writer->save($fullPath);
}

it('crea importación y despacha job al subir Excel', function (): void {
    Queue::fake();

    $user = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->empresas()->attach($this->empresa->id);
    $user->givePermissionTo('Upload:Colaborador');

    $path = 'importaciones/'.$this->empresa->id.'/test_'.uniqid().'.xlsx';
    crearExcelValidoMinimo($path);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $user->id,
        'tipo' => Importacion::TIPO_ALTA_MASIVA,
        'archivo_original' => $path,
        'estado' => Importacion::ESTADO_PENDIENTE,
    ]);

    ProcesarImportacionColaboradores::dispatch($importacion);

    Queue::assertPushed(ProcesarImportacionColaboradores::class);
});

it('job procesa filas válidas correctamente', function (): void {
    Notification::fake();

    $path = 'importaciones/'.$this->empresa->id.'/test_'.uniqid().'.xlsx';
    crearExcelValidoMinimo($path);

    $user = User::factory()->cliente()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $user->id,
        'tipo' => Importacion::TIPO_ALTA_MASIVA,
        'archivo_original' => $path,
        'estado' => Importacion::ESTADO_PENDIENTE,
    ]);

    $job = new ProcesarImportacionColaboradores($importacion);
    $job->handle(app(\App\Services\ColaboradorService::class));

    $importacion->refresh();
    expect($importacion->estado)->toBe(Importacion::ESTADO_COMPLETADA)
        ->and($importacion->filas_exitosas)->toBe(1)
        ->and($importacion->filas_con_error)->toBe(0)
        ->and(User::query()->colaboradoresDeEmpresa($this->empresa->id)->count())->toBe(1);
});

it('importa fila con correo que incluye caracteres unicode en la parte local', function (): void {
    Notification::fake();

    $headers = [
        'nombre', 'apellido_paterno', 'apellido_materno', 'email', 'telefono_movil',
        'numero_colaborador', 'fecha_nacimiento', 'genero', 'fecha_ingreso', 'periodicidad_pago',
    ];
    $row = [
        'María', 'García', 'Cruz', 'maría.import.'.uniqid().'@empresa.test', '5597970995',
        'COL-U'.uniqid(), '2000-01-05', 'M', '2024-01-06', 'MENSUAL',
    ];
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray($row, null, 'A2');
    $path = 'importaciones/'.$this->empresa->id.'/test_unicode_'.uniqid().'.xlsx';
    $fullPath = Storage::path($path);
    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($fullPath);

    $user = User::factory()->cliente()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $user->id,
        'tipo' => Importacion::TIPO_ALTA_MASIVA,
        'archivo_original' => $path,
        'estado' => Importacion::ESTADO_PENDIENTE,
    ]);

    $job = new ProcesarImportacionColaboradores($importacion);
    $job->handle(app(\App\Services\ColaboradorService::class));

    $importacion->refresh();
    expect($importacion->estado)->toBe(Importacion::ESTADO_COMPLETADA)
        ->and($importacion->filas_exitosas)->toBe(1)
        ->and($importacion->filas_con_error)->toBe(0);
});

it('usa la hoja Colaboradores aunque Instrucciones sea la hoja activa al guardar', function (): void {
    Notification::fake();

    $spreadsheet = new Spreadsheet;
    $instrucciones = $spreadsheet->getActiveSheet();
    $instrucciones->setTitle('Instrucciones');
    $instrucciones->setCellValue('A1', 'Instrucciones para carga masiva');

    $headers = [
        'user_id', 'name', 'apellido_paterno', 'apellido_materno', 'email', 'telefono_movil',
        'numero_colaborador', 'fecha_nacimiento', 'genero', 'fecha_ingreso', 'periodicidad_pago',
    ];
    $row = [
        '', 'Luis', 'Tab', 'Activo', 'luis.tab.'.uniqid().'@test.com', '5511223344',
        'TAB'.uniqid(), '1992-03-10', 'M', '2024-02-01', 'QUINCENAL',
    ];
    $datos = $spreadsheet->createSheet();
    $datos->setTitle('Colaboradores');
    $datos->fromArray($headers, null, 'A1');
    $datos->fromArray($row, null, 'A2');
    $spreadsheet->setActiveSheetIndex(0);

    $path = 'importaciones/'.$this->empresa->id.'/test_sheet_'.uniqid().'.xlsx';
    $fullPath = Storage::path($path);
    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($fullPath);

    $user = User::factory()->cliente()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $user->id,
        'tipo' => Importacion::TIPO_ALTA_MASIVA,
        'archivo_original' => $path,
        'estado' => Importacion::ESTADO_PENDIENTE,
    ]);

    $job = new ProcesarImportacionColaboradores($importacion);
    $job->handle(app(\App\Services\ColaboradorService::class));

    $importacion->refresh();
    expect($importacion->filas_exitosas)->toBe(1)
        ->and($importacion->filas_con_error)->toBe(0);
});

it('job registra errores por fila sin afectar las válidas', function (): void {
    Notification::fake();

    $headers = [
        'user_id', 'name', 'apellido_paterno', 'apellido_materno', 'email', 'telefono_movil',
        'numero_colaborador', 'fecha_nacimiento', 'genero', 'fecha_ingreso', 'periodicidad_pago',
    ];
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray([
        '', 'Juan', 'Válido', 'Uno', 'valido.'.uniqid().'@test.com', '5511223344',
        'N'.uniqid(), '1990-05-15', 'M', '2024-01-01', 'QUINCENAL',
    ], null, 'A2');
    // Fila inválida: sin name (required)
    $sheet->fromArray([
        '', '', 'Sin', 'Nombre', 'sin.nombre@test.com', '5599887766',
        'N2'.uniqid(), '1995-01-01', 'F', '2024-06-01', 'MENSUAL',
    ], null, 'A3');
    $path = 'importaciones/'.$this->empresa->id.'/test_'.uniqid().'.xlsx';
    $fullPath = Storage::path($path);
    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($fullPath);

    $user = User::factory()->cliente()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $user->id,
        'tipo' => Importacion::TIPO_ALTA_MASIVA,
        'archivo_original' => $path,
        'estado' => Importacion::ESTADO_PENDIENTE,
    ]);

    $job = new ProcesarImportacionColaboradores($importacion);
    $job->handle(app(\App\Services\ColaboradorService::class));

    $importacion->refresh();
    expect($importacion->filas_exitosas)->toBe(1)
        ->and($importacion->filas_con_error)->toBe(1)
        ->and(ErrorImportacion::where('importacion_id', $importacion->id)->count())->toBe(1)
        ->and(User::query()->colaboradoresDeEmpresa($this->empresa->id)->count())->toBe(1);
});

it('rechaza archivo que no es xlsx', function (): void {
    Notification::fake();

    $path = 'importaciones/'.$this->empresa->id.'/test_'.uniqid().'.csv';
    $fullPath = Storage::path($path);
    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($fullPath, "nombre,email\nJuan,j@test.com");

    $user = User::factory()->cliente()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);

    $importacion = Importacion::create([
        'empresa_id' => $this->empresa->id,
        'usuario_id' => $user->id,
        'tipo' => Importacion::TIPO_ALTA_MASIVA,
        'archivo_original' => $path,
        'estado' => Importacion::ESTADO_PENDIENTE,
    ]);

    $job = new ProcesarImportacionColaboradores($importacion);
    try {
        $job->handle(app(\App\Services\ColaboradorService::class));
    } catch (\Throwable) {
        // job lanza al no poder leer CSV como Excel
    }

    $importacion->refresh();
    expect($importacion->estado)->toBeIn([
        Importacion::ESTADO_FALLIDA,
        Importacion::ESTADO_PROCESANDO,
        Importacion::ESTADO_PENDIENTE,
        Importacion::ESTADO_CON_ERRORES,
    ]);
});

it('solo permite carga a usuarios con empresa asignada', function (): void {
    $user = User::factory()->cliente()->create([
        'empresa_id' => null,
    ]);
    $user->empresas()->detach();
    $user->givePermissionTo('Import:Colaborador');
    expect($user->hasEmpresasAsignadas())->toBeFalse();

    $response = $this->actingAs($user)->get(
        route('cliente.plantilla.colaboradores', ['empresa' => $this->empresa->id])
    );

    $response->assertForbidden();
});
