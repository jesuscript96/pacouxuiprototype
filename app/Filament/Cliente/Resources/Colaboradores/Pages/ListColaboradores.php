<?php

namespace App\Filament\Cliente\Resources\Colaboradores\Pages;

use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Filament\Cliente\Widgets\UxPrototype\ColaboradoresHeroWidget;
use App\Jobs\ProcesarEdicionMasivaColaboradores;
use App\Jobs\ProcesarImportacionColaboradores;
use App\Models\Empresa;
use App\Models\Importacion;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListColaboradores extends ListRecords
{
    protected static string $resource = ColaboradorResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ColaboradoresHeroWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        $empresa = Filament::getTenant() instanceof Empresa ? Filament::getTenant() : null;

        return [
            CreateAction::make(),
            Action::make('carga_masiva')
                ->label('Carga masiva')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn (): bool => $empresa !== null && auth()->user()?->can('Upload:Colaborador'))
                ->form([
                    \Filament\Forms\Components\FileUpload::make('archivo')
                        ->label('Archivo Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(10240)
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data): void {
                    $empresa = Filament::getTenant() instanceof Empresa ? Filament::getTenant() : null;
                    if (! $empresa || ! auth()->user()?->can('Upload:Colaborador')) {
                        return;
                    }
                    $file = $data['archivo'];
                    $path = $file->store('importaciones/'.$empresa->id, 'local');
                    $importacion = Importacion::create([
                        'empresa_id' => $empresa->id,
                        'usuario_id' => auth()->id(),
                        'tipo' => Importacion::TIPO_ALTA_MASIVA,
                        'archivo_original' => $path,
                        'estado' => Importacion::ESTADO_PENDIENTE,
                    ]);
                    ProcesarImportacionColaboradores::dispatch($importacion);
                    Notification::make()
                        ->title('Importación iniciada')
                        ->body('Te notificaremos cuando termine el proceso.')
                        ->success()
                        ->send();
                }),
            Action::make('edicion_masiva')
                ->label('Edición masiva')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn (): bool => $empresa !== null && auth()->user()?->can('BulkUpdate:Colaborador'))
                ->form([
                    \Filament\Forms\Components\FileUpload::make('archivo')
                        ->label('Archivo Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(10240)
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data): void {
                    $empresa = Filament::getTenant() instanceof Empresa ? Filament::getTenant() : null;
                    if (! $empresa || ! auth()->user()?->can('BulkUpdate:Colaborador')) {
                        return;
                    }
                    $file = $data['archivo'];
                    $path = $file->store('importaciones/'.$empresa->id, 'local');
                    $importacion = Importacion::create([
                        'empresa_id' => $empresa->id,
                        'usuario_id' => auth()->id(),
                        'tipo' => Importacion::TIPO_EDICION_MASIVA,
                        'archivo_original' => $path,
                        'estado' => Importacion::ESTADO_PENDIENTE,
                    ]);
                    ProcesarEdicionMasivaColaboradores::dispatch($importacion);
                    Notification::make()
                        ->title('Edición masiva iniciada')
                        ->body('Te notificaremos cuando termine el proceso.')
                        ->success()
                        ->send();
                }),
            Action::make('descargar_plantilla')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => $empresa !== null && auth()->user()?->can('Import:Colaborador'))
                ->url(fn (): string => route('cliente.plantilla.colaboradores', ['empresa' => $empresa?->id]))
                ->openUrlInNewTab(),
            Action::make('ver_importaciones')
                ->label('Ver importaciones')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(fn (): string => ColaboradorResource::getUrl('importaciones', ['tenant' => Filament::getTenant()]))
                ->visible(fn (): bool => $empresa !== null),
        ];
    }
}
