<?php

namespace App\Jobs;

use App\Models\ErrorImportacion;
use App\Models\Importacion;
use App\Notifications\ImportacionCompletadaNotification;
use App\Services\ColaboradorService;
use App\Support\ColaboradorImportSpreadsheet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProcesarImportacionColaboradores implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    public int $backoff = 60;

    public function __construct(
        protected Importacion $importacion
    ) {}

    public function handle(ColaboradorService $colaboradorService): void
    {
        // BL: En Windows/local con max_execution_time=60, QUEUE_CONNECTION=sync ejecuta el job en la misma petición HTTP.
        set_time_limit(0);

        $this->importacion->update([
            'estado' => Importacion::ESTADO_PROCESANDO,
            'iniciado_en' => now(),
        ]);

        $path = Storage::path($this->importacion->archivo_original);
        if (! is_readable($path)) {
            $this->importacion->update([
                'estado' => Importacion::ESTADO_FALLIDA,
                'completado_en' => now(),
            ]);
            $this->notificarUsuario('Importación fallida: archivo no encontrado.');

            return;
        }

        try {
            // BL: En entornos con open_basedir (ej. RunCloud), ZipArchive debe extraer dentro de rutas permitidas
            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            putenv('TMPDIR='.$tempDir);

            $spreadsheet = IOFactory::load($path);
            $sheet = ColaboradorImportSpreadsheet::resolveDataSheet($spreadsheet);
            $highestRow = (int) $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $this->importacion->update(['total_filas' => max(0, $highestRow - 1)]);

            $headers = $this->getRowValues($sheet, 1, $colIndex);

            $chunkSize = 100;
            for ($startRow = 2; $startRow <= $highestRow; $startRow += $chunkSize) {
                $endRow = min($startRow + $chunkSize - 1, $highestRow);
                DB::beginTransaction();
                try {
                    for ($row = $startRow; $row <= $endRow; $row++) {
                        $rowValues = $this->getRowValues($sheet, $row, $colIndex);
                        $data = array_combine($headers, $rowValues);
                        if ($data === false) {
                            $data = [];
                        }
                        $data = $this->normalizeRowData($data);
                        $filaNumero = $row;

                        $validator = \Illuminate\Support\Facades\Validator::make(
                            $data,
                            \App\Http\Requests\ColaboradorRequest::rulesForImport()
                        );

                        if ($validator->fails()) {
                            $mensaje = $validator->errors()->first();
                            ErrorImportacion::create([
                                'importacion_id' => $this->importacion->id,
                                'fila' => $filaNumero,
                                'columna' => null,
                                'valor_enviado' => json_encode($data),
                                'mensaje_error' => $mensaje,
                            ]);
                            $this->importacion->increment('filas_con_error');
                        } else {
                            try {
                                $colaboradorService->crearColaborador($data, $this->importacion->empresa);
                                $this->importacion->increment('filas_exitosas');
                            } catch (\Throwable $e) {
                                ErrorImportacion::create([
                                    'importacion_id' => $this->importacion->id,
                                    'fila' => $filaNumero,
                                    'columna' => null,
                                    'valor_enviado' => json_encode($data),
                                    'mensaje_error' => $e->getMessage(),
                                ]);
                                $this->importacion->increment('filas_con_error');
                            }
                        }
                        $this->importacion->increment('filas_procesadas');
                    }
                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            $this->finalizarImportacion();
        } catch (\Throwable $e) {
            $this->importacion->update([
                'estado' => Importacion::ESTADO_FALLIDA,
                'completado_en' => now(),
            ]);
            $this->notificarUsuario('Importación fallida: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array<int, mixed>
     */
    private function getRowValues(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row, int $colCount): array
    {
        $values = [];
        for ($col = 1; $col <= $colCount; $col++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $val = $cell->getValue();
            $values[] = $val !== null ? trim((string) $val) : '';
        }

        return $values;
    }

    /**
     * @param  array<int|string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeRowData(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && $value !== null && $value !== '') {
                $out[$key] = $value;
            }
        }
        $dateFields = ['fecha_nacimiento', 'fecha_ingreso'];
        foreach ($dateFields as $field) {
            if (isset($out[$field]) && is_numeric($out[$field])) {
                try {
                    $out[$field] = \Carbon\Carbon::instance(ExcelDate::excelToDateTimeObject((float) $out[$field]))->format('Y-m-d');
                } catch (\Throwable) {
                    // leave as is
                }
            }
        }
        if (isset($out['telefono_movil'])) {
            $out['telefono_movil'] = preg_replace('/\D/', '', (string) $out['telefono_movil']);
        }
        // BL: plantillas usan "nombre" (colaboradores) o "name" (legacy); el servicio normaliza a `name` en User.
        if (isset($out['nombre']) && ! isset($out['name'])) {
            $out['name'] = $out['nombre'];
        }
        if (isset($out['name']) && ! isset($out['nombre'])) {
            $out['nombre'] = $out['name'];
        }
        if (array_key_exists('user_id', $out) && $out['user_id'] === '') {
            unset($out['user_id']);
        }

        return $out;
    }

    private function finalizarImportacion(): void
    {
        $importacion = $this->importacion->fresh();
        if ($importacion->filas_con_error > 0) {
            $archivoErrores = $this->generarArchivoErrores();
            $this->importacion->update([
                'estado' => Importacion::ESTADO_CON_ERRORES,
                'archivo_errores' => $archivoErrores,
            ]);
        } else {
            $this->importacion->update(['estado' => Importacion::ESTADO_COMPLETADA]);
        }
        $this->importacion->update(['completado_en' => now()]);
        $this->notificarUsuario(
            $importacion->filas_con_error > 0
                ? "Importación completada con {$importacion->filas_con_error} error(es). Puede descargar el archivo de errores."
                : 'Importación completada correctamente.'
        );
    }

    private function generarArchivoErrores(): string
    {
        $errores = $this->importacion->errores()->orderBy('fila')->get();
        $path = 'importaciones/errores/'.$this->importacion->id.'_errores_'.now()->format('Y-m-d_His').'.xlsx';
        $fullPath = storage_path('app/public/'.$path);
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Errores');
        $sheet->fromArray(['Fila', 'Columna', 'Valor', 'Mensaje'], null, 'A1');
        $row = 2;
        foreach ($errores as $err) {
            $sheet->fromArray([
                $err->fila,
                $err->columna,
                $err->valor_enviado,
                $err->mensaje_error,
            ], null, 'A'.$row);
            $row++;
        }
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($fullPath);

        return 'public/'.$path;
    }

    private function notificarUsuario(string $mensaje): void
    {
        $user = $this->importacion->usuario;
        if ($user) {
            $user->notify(new ImportacionCompletadaNotification(
                'Importación de colaboradores',
                $mensaje,
                $this->importacion->filas_con_error > 0 ? 'warning' : 'success'
            ));
        }
    }
}
