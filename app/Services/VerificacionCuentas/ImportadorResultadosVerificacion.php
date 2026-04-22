<?php

declare(strict_types=1);

namespace App\Services\VerificacionCuentas;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportadorResultadosVerificacion
{
    /**
     * BL: Columnas requeridas en el Excel de resultados de STP.
     */
    private const COLUMNAS_REQUERIDAS = ['cuenta', 'resultado'];

    /**
     * BL: Mapeo de nombres de columnas alternativos a nombres normalizados.
     * Soporta variantes comunes de Excel exportados por STP y el legacy.
     */
    private const MAPEO_COLUMNAS = [
        'cuenta' => 'cuenta',
        'cuenta clabe' => 'cuenta',
        'cuenta clabe / tarjeta' => 'cuenta',
        'account' => 'cuenta',
        'numero' => 'cuenta',
        'número' => 'cuenta',
        'numero de cuenta' => 'cuenta',
        'número de cuenta' => 'cuenta',
        'clabe' => 'cuenta',

        'resultado' => 'resultado',
        'status' => 'resultado',
        'estado' => 'resultado',
        'validacion' => 'resultado',
        'validación' => 'resultado',
        'check' => 'resultado',

        'reenviar' => 'reenviar',
        'resend' => 'reenviar',
        'reenvio' => 'reenviar',
        'reenvío' => 'reenviar',
    ];

    private const VALORES_VALIDA = ['valida', 'válida', 'valid', 'si', 'sí', 'yes', '1', 'true', 'aprobada', 'aprobado'];

    private const VALORES_REENVIAR = ['si', 'sí', 'yes', '1', 'true', 'x'];

    /**
     * Importa y parsea el archivo Excel de resultados.
     *
     * @return Collection<int, array{numero: string, resultado: string, reenviar: bool}>
     */
    public function importar(UploadedFile|string $archivo): Collection
    {
        $rutaArchivo = $archivo instanceof UploadedFile
            ? $archivo->getRealPath()
            : $archivo;

        if (! file_exists($rutaArchivo)) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo no existe o no se puede leer.',
            ]);
        }

        try {
            $spreadsheet = IOFactory::load($rutaArchivo);
            $filas = $spreadsheet->getActiveSheet()->toArray();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'archivo' => 'No se pudo leer el archivo Excel: '.$e->getMessage(),
            ]);
        }

        if (count($filas) < 2) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo está vacío o solo contiene encabezados.',
            ]);
        }

        $encabezados = $this->normalizarEncabezados($filas[0]);
        $this->validarEncabezados($encabezados);
        $indiceColumnas = $this->obtenerIndiceColumnas($encabezados);

        $resultados = collect();

        for ($i = 1; $i < count($filas); $i++) {
            $fila = $filas[$i];

            $cuenta = $this->obtenerValorCelda($fila, $indiceColumnas['cuenta']);

            if ($cuenta === '') {
                continue;
            }

            $resultado = $this->obtenerValorCelda($fila, $indiceColumnas['resultado']);
            $reenviar = isset($indiceColumnas['reenviar'])
                ? $this->obtenerValorCelda($fila, $indiceColumnas['reenviar'])
                : '';

            $resultados->push([
                'numero' => $this->limpiarNumeroCuenta($cuenta),
                'resultado' => $this->normalizarResultado($resultado),
                'reenviar' => $this->normalizarReenviar($reenviar),
            ]);
        }

        if ($resultados->isEmpty()) {
            throw ValidationException::withMessages([
                'archivo' => 'No se encontraron registros válidos en el archivo.',
            ]);
        }

        return $resultados;
    }

    /**
     * @return list<string>
     */
    private function normalizarEncabezados(array $encabezados): array
    {
        return array_map(
            fn ($encabezado): string => mb_strtolower(trim((string) $encabezado)),
            $encabezados
        );
    }

    private function validarEncabezados(array $encabezados): void
    {
        $columnasEncontradas = [];

        foreach ($encabezados as $encabezado) {
            if (isset(self::MAPEO_COLUMNAS[$encabezado])) {
                $columnasEncontradas[self::MAPEO_COLUMNAS[$encabezado]] = true;
            }
        }

        $faltantes = [];
        foreach (self::COLUMNAS_REQUERIDAS as $requerida) {
            if (! isset($columnasEncontradas[$requerida])) {
                $faltantes[] = $requerida;
            }
        }

        if ($faltantes !== []) {
            throw ValidationException::withMessages([
                'archivo' => 'Faltan columnas requeridas: '.implode(', ', $faltantes)
                    .'. Columnas encontradas: '.implode(', ', $encabezados),
            ]);
        }
    }

    /**
     * @return array<string, int>
     */
    private function obtenerIndiceColumnas(array $encabezados): array
    {
        $indices = [];

        foreach ($encabezados as $indice => $encabezado) {
            if (isset(self::MAPEO_COLUMNAS[$encabezado])) {
                $columnaNormalizada = self::MAPEO_COLUMNAS[$encabezado];
                if (! isset($indices[$columnaNormalizada])) {
                    $indices[$columnaNormalizada] = $indice;
                }
            }
        }

        return $indices;
    }

    private function obtenerValorCelda(array $fila, int $indice): string
    {
        return isset($fila[$indice]) ? trim((string) $fila[$indice]) : '';
    }

    private function limpiarNumeroCuenta(string $cuenta): string
    {
        return preg_replace('/\s+/', '', $cuenta);
    }

    private function normalizarResultado(string $resultado): string
    {
        $resultadoLower = mb_strtolower(trim($resultado));

        if (in_array($resultadoLower, self::VALORES_VALIDA, true)) {
            return 'Valida';
        }

        return 'No valida';
    }

    private function normalizarReenviar(string $valor): bool
    {
        $valorLower = mb_strtolower(trim($valor));

        return in_array($valorLower, self::VALORES_REENVIAR, true);
    }
}
