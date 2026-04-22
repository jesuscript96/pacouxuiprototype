<?php

namespace App\Jobs;

use App\Models\CartaSua;
use App\Models\Colaborador;
use App\Models\ErrorImportacion;
use App\Models\Importacion;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Notifications\ImportacionCompletadaNotification;
use App\Services\CartaSuaPdfService;
use App\Support\CartaSuaImportSpreadsheet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcesarImportacionCartasSua implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    public int $backoff = 60;

    public function __construct(
        protected Importacion $importacion,
    ) {}

    public function handle(CartaSuaPdfService $pdfService): void
    {
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
            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            putenv('TMPDIR='.$tempDir);

            $spreadsheet = IOFactory::load($path);
            $sheet = CartaSuaImportSpreadsheet::resolveDataSheet($spreadsheet);
            $highestRow = (int) $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $this->importacion->update(['total_filas' => max(0, $highestRow - 1)]);

            $headersRaw = $this->getRowValues($sheet, 1, $colIndex);
            $headers = CartaSuaImportSpreadsheet::normalizeHeaders($headersRaw);
            $empresa = $this->importacion->empresa;

            $chunkSize = 50;
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
                        $data = CartaSuaImportSpreadsheet::normalizeRow($data);
                        $filaNumero = $row;

                        if ($this->filaVacia($data)) {
                            continue;
                        }

                        $erroresValidacion = CartaSuaImportSpreadsheet::validateRow($data);

                        if (! empty($erroresValidacion)) {
                            ErrorImportacion::create([
                                'importacion_id' => $this->importacion->id,
                                'fila' => $filaNumero,
                                'columna' => null,
                                'valor_enviado' => json_encode($data),
                                'mensaje_error' => implode('; ', $erroresValidacion),
                            ]);
                            $this->importacion->increment('filas_con_error');
                        } else {
                            try {
                                $this->procesarFila($data, $empresa, $pdfService, $filaNumero);
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
            $this->notificarUsuario('Importación de Cartas SUA fallida: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function procesarFila(
        array $data,
        \App\Models\Empresa $empresa,
        CartaSuaPdfService $pdfService,
        int $filaNumero,
    ): void {
        $colaborador = Colaborador::query()
            ->where('empresa_id', $empresa->id)
            ->where('numero_colaborador', $data['numero_empleado'])
            ->first();

        if (! $colaborador) {
            throw new \RuntimeException(
                "Colaborador con número '{$data['numero_empleado']}' no encontrado en la empresa."
            );
        }

        // BL: RN-01 — no duplicar carta con misma combinación
        if (CartaSua::existeDuplicado($colaborador->id, $data['bimestre'], $data['razon_social'])) {
            Log::info('Carta SUA duplicada, se omite', [
                'colaborador_id' => $colaborador->id,
                'bimestre' => $data['bimestre'],
                'fila' => $filaNumero,
            ]);

            return;
        }

        $carta = CartaSua::create([
            'empresa_id' => $empresa->id,
            'colaborador_id' => $colaborador->id,
            'bimestre' => $data['bimestre'],
            'razon_social' => $data['razon_social'],
            'retiro' => $data['retiro'],
            'cesantia_vejez' => $data['cv'],
            'infonavit' => $data['infonavit'],
            'total' => $data['total'],
            'datos_origen' => $data,
        ]);

        $pdfService->generar($carta, $data);

        $this->enviarNotificacionPush($carta, $colaborador);
    }

    private function enviarNotificacionPush(CartaSua $carta, Colaborador $colaborador): void
    {
        $user = $colaborador->user;

        if (! $user) {
            Log::info('Carta SUA: Colaborador sin usuario, no se envía push', [
                'carta_id' => $carta->id,
                'colaborador_id' => $colaborador->id,
            ]);

            return;
        }

        try {
            $notificacion = NotificacionPush::create([
                'empresa_id' => $carta->empresa_id,
                'titulo' => 'Nueva Carta SUA disponible',
                'mensaje' => "Tienes una nueva carta de aportaciones del bimestre {$carta->bimestre}.",
                'data' => [
                    'type' => 'CARTA_SUA',
                    'carta_sua_id' => $carta->id,
                ],
            ]);

            NotificacionPushDestinatario::create([
                'notificacion_push_id' => $notificacion->id,
                'user_id' => $user->id,
            ]);

            EnviarNotificacionPushJob::dispatch($notificacion);
        } catch (\Throwable $e) {
            Log::warning('Error enviando notificación de Carta SUA', [
                'carta_id' => $carta->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function filaVacia(array $data): bool
    {
        return empty($data['numero_empleado'] ?? '') && empty($data['bimestre'] ?? '');
    }

    /**
     * @return array<int, mixed>
     */
    private function getRowValues(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $row,
        int $colCount,
    ): array {
        $values = [];
        for ($col = 1; $col <= $colCount; $col++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $val = $cell->getValue();
            $values[] = $val !== null ? trim((string) $val) : '';
        }

        return $values;
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
                ? "Importación de Cartas SUA completada con {$importacion->filas_con_error} error(es). Puede descargar el archivo de errores."
                : 'Importación de Cartas SUA completada correctamente.'
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
                'Importación de Cartas SUA',
                $mensaje,
                $this->importacion->filas_con_error > 0 ? 'warning' : 'success'
            ));
        }
    }
}
