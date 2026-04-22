<?php

declare(strict_types=1);

use App\Services\VerificacionCuentas\ImportadorResultadosVerificacion;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function (): void {
    $this->importador = app(ImportadorResultadosVerificacion::class);
    $this->tempDir = storage_path('app/test-imports');

    if (! is_dir($this->tempDir)) {
        mkdir($this->tempDir, 0755, true);
    }
});

afterEach(function (): void {
    foreach (glob($this->tempDir.'/*.xlsx') as $archivo) {
        @unlink($archivo);
    }
});

function crearExcelTemporal(array $filas, string $tempDir): string
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray($filas);

    $path = $tempDir.'/test_'.uniqid().'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

describe('Importación de Excel', function (): void {

    test('importa archivo con columnas estándar', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado'],
            ['1234567890', 'Valida'],
            ['0987654321', 'No valida'],
        ], $this->tempDir);

        $resultados = $this->importador->importar($path);

        expect($resultados)->toHaveCount(2);
        expect($resultados[0]['numero'])->toBe('1234567890');
        expect($resultados[0]['resultado'])->toBe('Valida');
        expect($resultados[1]['numero'])->toBe('0987654321');
        expect($resultados[1]['resultado'])->toBe('No valida');
    });

    test('importa archivo con columnas alternativas', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta Clabe / Tarjeta', 'Status'],
            ['1234567890', 'valida'],
            ['0987654321', 'no valida'],
        ], $this->tempDir);

        $resultados = $this->importador->importar($path);

        expect($resultados)->toHaveCount(2);
        expect($resultados[0]['resultado'])->toBe('Valida');
        expect($resultados[1]['resultado'])->toBe('No valida');
    });

    test('procesa columna de reenvío', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado', 'Reenviar'],
            ['1234567890', 'No valida', 'Si'],
            ['0987654321', 'No valida', ''],
        ], $this->tempDir);

        $resultados = $this->importador->importar($path);

        expect($resultados[0]['reenviar'])->toBeTrue();
        expect($resultados[1]['reenviar'])->toBeFalse();
    });

    test('normaliza diferentes valores de resultado', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado'],
            ['1111111111', 'VALIDA'],
            ['2222222222', 'válida'],
            ['3333333333', 'SI'],
            ['4444444444', 'aprobada'],
            ['5555555555', 'rechazada'],
            ['6666666666', 'NO'],
        ], $this->tempDir);

        $resultados = $this->importador->importar($path);

        expect($resultados[0]['resultado'])->toBe('Valida');
        expect($resultados[1]['resultado'])->toBe('Valida');
        expect($resultados[2]['resultado'])->toBe('Valida');
        expect($resultados[3]['resultado'])->toBe('Valida');
        expect($resultados[4]['resultado'])->toBe('No valida');
        expect($resultados[5]['resultado'])->toBe('No valida');
    });

    test('ignora filas vacías', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado'],
            ['1234567890', 'Valida'],
            ['', ''],
            ['0987654321', 'No valida'],
            [null, null],
        ], $this->tempDir);

        $resultados = $this->importador->importar($path);

        expect($resultados)->toHaveCount(2);
    });

    test('limpia espacios del número de cuenta', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado'],
            [' 1234 5678 90 ', 'Valida'],
        ], $this->tempDir);

        $resultados = $this->importador->importar($path);

        expect($resultados[0]['numero'])->toBe('1234567890');
    });

    test('reenviar es false por defecto cuando no hay columna', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado'],
            ['1234567890', 'No valida'],
        ], $this->tempDir);

        $resultados = $this->importador->importar($path);

        expect($resultados[0]['reenviar'])->toBeFalse();
    });

});

describe('Validaciones', function (): void {

    test('falla si faltan columnas requeridas', function (): void {
        $path = crearExcelTemporal([
            ['Nombre', 'Banco'],
            ['Juan', 'BBVA'],
        ], $this->tempDir);

        expect(fn () => $this->importador->importar($path))
            ->toThrow(ValidationException::class);
    });

    test('falla si el archivo solo tiene encabezados', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado'],
        ], $this->tempDir);

        expect(fn () => $this->importador->importar($path))
            ->toThrow(ValidationException::class);
    });

    test('falla si el archivo no existe', function (): void {
        expect(fn () => $this->importador->importar('/ruta/inexistente.xlsx'))
            ->toThrow(ValidationException::class);
    });

    test('falla si todas las filas de datos están vacías', function (): void {
        $path = crearExcelTemporal([
            ['Cuenta', 'Resultado'],
            ['', ''],
            ['', ''],
        ], $this->tempDir);

        expect(fn () => $this->importador->importar($path))
            ->toThrow(ValidationException::class);
    });

});
