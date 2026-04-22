<?php

use App\Exports\BajasColaboradores\PlantillaBajasExport;
use App\Imports\BajasColaboradores\BajasMasivasImport;
use App\Models\BajaColaborador;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__.'/Helpers.php';

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
});

it('exporta plantilla con encabezados correctos', function (): void {
    $binary = ExcelFacade::raw(new PlantillaBajasExport, Excel::XLSX);
    $path = tempnam(sys_get_temp_dir(), 'plantilla_bajas_');
    expect($path)->not->toBeFalse();
    file_put_contents($path, $binary);
    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    expect($sheet->getCell('A1')->getValue())->toBe('email')
        ->and($sheet->getCell('B1')->getValue())->toBe('numero_colaborador')
        ->and($sheet->getCell('C1')->getValue())->toBe('fecha_baja')
        ->and($sheet->getCell('D1')->getValue())->toBe('motivo')
        ->and($sheet->getCell('E1')->getValue())->toBe('comentarios');
    @unlink($path);
});

it('importa bajas correctamente', function (): void {
    $user = crearUserColaborador($this->empresa);
    $colaborador = $user->refresh()->colaborador;
    expect($colaborador)->not->toBeNull();

    $import = new BajasMasivasImport($this->empresa->id);

    $import->collection(collect([
        collect([
            'email' => $colaborador->email,
            'numero_colaborador' => $colaborador->numero_colaborador,
            'fecha_baja' => now()->addWeek()->format('Y-m-d'),
            'motivo' => 'RENUNCIA',
            'comentarios' => 'Baja de prueba',
        ]),
    ]));

    expect($import->getProcesadas())->toBe(1)
        ->and($import->getErrores())->toBe(0);
    expect(BajaColaborador::query()->where('colaborador_id', $colaborador->id)->exists())->toBeTrue();
});

it('reporta error si colaborador no existe', function (): void {
    $import = new BajasMasivasImport($this->empresa->id);

    $import->collection(collect([
        collect([
            'email' => 'noexiste-nunca@test.com',
            'numero_colaborador' => '',
            'fecha_baja' => now()->addDay()->format('Y-m-d'),
            'motivo' => 'RENUNCIA',
            'comentarios' => '',
        ]),
    ]));

    expect($import->getProcesadas())->toBe(0)
        ->and($import->getErrores())->toBe(1)
        ->and($import->getResultados()[0]['status'])->toBe('error');
});

it('valida motivo inválido', function (): void {
    $user = crearUserColaborador($this->empresa);
    $colaborador = $user->refresh()->colaborador;
    expect($colaborador)->not->toBeNull();

    $import = new BajasMasivasImport($this->empresa->id);

    $import->collection(collect([
        collect([
            'email' => $colaborador->email,
            'numero_colaborador' => '',
            'fecha_baja' => now()->addWeek()->format('Y-m-d'),
            'motivo' => 'MOTIVO_INVALIDO',
            'comentarios' => '',
        ]),
    ]));

    expect($import->getErrores())->toBe(1)
        ->and($import->getResultados()[0]['mensaje'])->toContain('Motivo inválido');
});

it('salta fila de ejemplo', function (): void {
    $import = new BajasMasivasImport($this->empresa->id);

    $import->collection(collect([
        collect([
            'email' => 'colaborador@empresa.com',
            'numero_colaborador' => 'EMP-001',
            'fecha_baja' => now()->format('Y-m-d'),
            'motivo' => 'RENUNCIA',
            'comentarios' => '',
        ]),
    ]));

    expect($import->getProcesadas())->toBe(0)
        ->and($import->getErrores())->toBe(0);
});

it('solo procesa colaboradores de la empresa del tenant', function (): void {
    $otraEmpresa = crearEmpresaMinima();
    $userOtro = crearUserColaborador($otraEmpresa);
    $colaboradorOtro = $userOtro->refresh()->colaborador;
    expect($colaboradorOtro)->not->toBeNull();

    $import = new BajasMasivasImport($this->empresa->id);

    $import->collection(collect([
        collect([
            'email' => $colaboradorOtro->email,
            'numero_colaborador' => '',
            'fecha_baja' => now()->addDay()->format('Y-m-d'),
            'motivo' => 'RENUNCIA',
            'comentarios' => '',
        ]),
    ]));

    expect($import->getErrores())->toBe(1)
        ->and($import->getResultados()[0]['mensaje'])->toContain('no encontrado');
});
