<?php

namespace App\Filament\Cliente\Resources\Candidatos\Pages;

use App\Filament\Cliente\Resources\Candidatos\CandidatoReclutamientoResource;
use App\Filament\Cliente\Resources\Vacantes\VacanteResource;
use App\Models\CandidatoReclutamiento;
use App\Services\ArchivoService;
use App\Services\CandidatoEstatusService;
use App\Services\CandidatoPdfService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCandidato extends ViewRecord
{
    protected static string $resource = CandidatoReclutamientoResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var CandidatoReclutamiento $record */
        $record = $this->record;

        return $record->nombre_completo;
    }

    protected function getHeaderActions(): array
    {
        /** @var CandidatoReclutamiento $record */
        $record = $this->record;

        return [
            Action::make('cambiarEstatus')
                ->label('Cambiar estatus')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Select::make('nuevo_estatus')
                        ->label('Nuevo estatus')
                        ->options(function () use ($record): array {
                            $usados = $record->historialEstatus->pluck('estatus')->toArray();

                            return collect(CandidatoReclutamiento::estatusDisponibles())
                                ->reject(fn (string $estatus): bool => in_array($estatus, $usados, true))
                                ->mapWithKeys(fn (string $e): array => [$e => $e])
                                ->all();
                        })
                        ->required(),
                    Textarea::make('comentario')
                        ->label('Comentario')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    try {
                        app(CandidatoEstatusService::class)->cambiarEstatus(
                            $record,
                            $data['nuevo_estatus'],
                            auth()->user(),
                            $data['comentario'],
                        );

                        Notification::make()
                            ->title('Estatus actualizado')
                            ->success()
                            ->send();

                        $this->redirect(CandidatoReclutamientoResource::getUrl('view', ['record' => $record]));
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn (): bool => (bool) auth()->user()?->can('Update:CandidatoReclutamiento')),

            Action::make('agregarComentario')
                ->label('Agregar comentario')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('info')
                ->form([
                    Textarea::make('comentario')
                        ->label('Comentario')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    app(CandidatoEstatusService::class)->agregarComentario(
                        $record,
                        auth()->user(),
                        $data['comentario'],
                    );

                    Notification::make()
                        ->title('Comentario agregado')
                        ->success()
                        ->send();

                    $this->redirect(CandidatoReclutamientoResource::getUrl('view', ['record' => $record]));
                })
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:CandidatoReclutamiento')),

            Action::make('descargarArchivo')
                ->label('Descargar archivo')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->form([
                    Select::make('campo')
                        ->label('Archivo')
                        ->options(function () use ($record): array {
                            $archivos = $record->archivos ?? [];
                            $campos = $record->vacante?->camposFormulario
                                ?->where('tipo', 'file')
                                ->keyBy('nombre') ?? collect();

                            $opciones = [];
                            foreach ($archivos as $nombre => $info) {
                                if (! empty($info['path'])) {
                                    $etiqueta = $campos->get($nombre)?->etiqueta ?? $nombre;
                                    $original = $info['nombre_original'] ?? basename($info['path']);
                                    $opciones[$nombre] = "{$etiqueta} ({$original})";
                                }
                            }

                            return $opciones;
                        })
                        ->required(),
                ])
                ->action(function (array $data) use ($record): mixed {
                    $archivos = $record->archivos ?? [];
                    $info = $archivos[$data['campo']] ?? null;

                    if (! $info || empty($info['path'])) {
                        Notification::make()
                            ->title('Archivo no encontrado')
                            ->danger()
                            ->send();

                        return null;
                    }

                    return app(ArchivoService::class)->descargar(
                        $info['path'],
                        $info['nombre_original'] ?? null,
                    );
                })
                ->visible(fn (): bool => ! empty($record->archivos) && (bool) auth()->user()?->can('View:CandidatoReclutamiento')),

            Action::make('descargarPdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn (): mixed => app(CandidatoPdfService::class)->generarReporte($record))
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:CandidatoReclutamiento')),

            Action::make('verVacante')
                ->label('Ver vacante')
                ->icon('heroicon-o-briefcase')
                ->color('gray')
                ->url(fn (): string => VacanteResource::getUrl('view', ['record' => $record->vacante_id])),
        ];
    }
}
