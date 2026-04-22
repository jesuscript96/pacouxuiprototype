<?php

declare(strict_types=1);

namespace App\Filament\Resources\VerificacionCuentas\Pages;

use App\Filament\Resources\VerificacionCuentas\VerificacionCuentaResource;
use App\Filament\Resources\VerificacionCuentas\Widgets\ContadorCuentasPendientesWidget;
use App\Models\CuentaBancaria;
use App\Services\VerificacionCuentas\ImportadorResultadosVerificacion;
use App\Services\VerificacionCuentas\VerificacionCuentaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ListVerificacionCuentas extends ListRecords
{
    protected static string $resource = VerificacionCuentaResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ContadorCuentasPendientesWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportarCsv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): mixed {
                    $cuentas = CuentaBancaria::query()
                        ->sinVerificar()
                        ->with(['colaborador', 'banco'])
                        ->get();

                    if ($cuentas->isEmpty()) {
                        Notification::make()
                            ->title('No hay cuentas pendientes')
                            ->body('No hay cuentas pendientes por verificar.')
                            ->warning()
                            ->send();

                        return null;
                    }

                    return response()->streamDownload(function () use ($cuentas): void {
                        echo "numero,colaborador,banco,estado\n";
                        foreach ($cuentas as $cuenta) {
                            echo "{$cuenta->numero},{$cuenta->colaborador?->nombre_completo},{$cuenta->banco?->nombre},{$cuenta->estado->getLabel()}\n";
                        }
                    }, 'cuentas-por-verificar-'.now()->format('Y-m-d').'.csv');
                }),

            Action::make('exportarTxt')
                ->label('Generar TXT')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(function (): mixed {
                    $service = app(VerificacionCuentaService::class);
                    $cuentas = $service->obtenerCuentasPendientesDeEnvio();

                    if ($cuentas->isEmpty()) {
                        Notification::make()
                            ->title('No hay cuentas para enviar')
                            ->body('No hay cuentas pendientes o el horario de envío ya pasó (después de las 18:00 hrs).')
                            ->warning()
                            ->send();

                        return null;
                    }

                    $payload = $service->prepararPayloadSTP($cuentas);

                    return response()->streamDownload(function () use ($payload): void {
                        foreach ($payload as $linea) {
                            echo implode(',', [
                                $linea['date'],
                                $linea['transferId'],
                                $linea['institucionContraparte'],
                                $linea['bank_code'],
                                $linea['account'],
                                $linea['amount'],
                            ])."\r\n";
                        }
                    }, 'Cuentas-Por-Verificar-'.now()->format('Y-m-d').'.txt', [
                        'Content-Type' => 'text/plain',
                    ]);
                }),

            Action::make('cargarResultados')
                ->label('Importar resultados')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Cargar resultados de verificación')
                ->modalDescription('Selecciona el archivo Excel con los resultados de verificación de STP.')
                ->modalSubmitActionLabel('Procesar')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('archivo')
                        ->label('Archivo de resultados (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required()
                        ->disk('public')
                        ->directory('verificacion-cuentas')
                        ->visibility('private')
                        ->maxSize(10240)
                        ->helperText('El archivo debe incluir las columnas: Cuenta y Resultado (Válida / No válida). La columna Reenviar es opcional.'),
                ])
                ->action(fn (array $data) => $this->cargarResultados($data)),
        ];
    }

    protected function cargarResultados(array $data): void
    {
        $archivoPath = Storage::disk('public')->path($data['archivo']);

        if (! file_exists($archivoPath)) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo encontrar el archivo subido.')
                ->danger()
                ->send();

            return;
        }

        try {
            $importador = app(ImportadorResultadosVerificacion::class);
            $resultados = $importador->importar($archivoPath);

            $service = app(VerificacionCuentaService::class);
            $resumen = $service->procesarResultadosMasivos($resultados->toArray());

            Storage::disk('public')->delete($data['archivo']);

            $mensaje = "Validadas: {$resumen['validadas']}, Rechazadas: {$resumen['rechazadas']}, Reenviadas: {$resumen['reenviadas']}";

            if ($resumen['errores'] !== []) {
                $erroresVisibles = array_slice($resumen['errores'], 0, 5);
                $mensaje .= "\n\nErrores (".count($resumen['errores'])."):\n".implode("\n", $erroresVisibles);

                if (count($resumen['errores']) > 5) {
                    $mensaje .= "\n... y ".(count($resumen['errores']) - 5).' más';
                }
            }

            Notification::make()
                ->title('Procesamiento completado')
                ->body($mensaje)
                ->success()
                ->persistent()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Error de validación')
                ->body(implode("\n", $e->errors()['archivo'] ?? ['Error desconocido']))
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error al procesar el archivo: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
