<?php

namespace App\Filament\Cliente\Pages\CartasSua;

use App\Exports\PlantillaCartasSuaExport;
use App\Jobs\ProcesarImportacionCartasSua;
use App\Models\CartaSua;
use App\Models\Empresa;
use App\Models\Importacion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Action as TableAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class CargarCartasSua extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Cargar Registros';

    protected static ?string $title = 'Cargar Cartas SUA';

    protected static ?string $slug = 'cartas-sua/cargar';

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.cliente.pages.cartas-sua.cargar-cartas-sua';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function getPermissionModel(): string
    {
        return CartaSua::class;
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('Create:CartaSua');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    // =========================================================================
    // FORMULARIO DE CARGA
    // =========================================================================

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('archivo')
                    ->label('Archivo Excel')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(25 * 1024)
                    ->disk('local')
                    ->directory('imports/cartas-sua')
                    ->visibility('private')
                    ->required()
                    ->helperText('Formato .xlsx · Máximo 25 MB · Usa la plantilla oficial.')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    // =========================================================================
    // ACCIONES DE CABECERA
    // =========================================================================

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarPlantilla')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => Excel::download(
                    new PlantillaCartasSuaExport,
                    'plantilla-cartas-sua.xlsx',
                )),
        ];
    }

    // =========================================================================
    // PROCESAMIENTO
    // =========================================================================

    public function procesarCartas(): void
    {
        $data = $this->form->getState();

        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo determinar la empresa activa.')
                ->danger()
                ->send();

            return;
        }

        $archivoPath = $data['archivo'];

        $importacion = Importacion::create([
            'empresa_id' => $tenant->id,
            'usuario_id' => auth()->id(),
            'tipo' => Importacion::TIPO_CARGA_SUA,
            'archivo_original' => $archivoPath,
            'estado' => Importacion::ESTADO_PENDIENTE,
        ]);

        ProcesarImportacionCartasSua::dispatch($importacion);

        $this->form->fill();

        Notification::make()
            ->title('Procesamiento iniciado')
            ->body('El archivo se está procesando en segundo plano. El progreso aparecerá en la tabla inferior.')
            ->success()
            ->send();
    }

    // =========================================================================
    // TABLA DE IMPORTACIONES RECIENTES
    // =========================================================================

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();

        return $table
            ->query(
                Importacion::query()
                    ->where('tipo', Importacion::TIPO_CARGA_SUA)
                    ->when(
                        $tenant instanceof Empresa,
                        fn ($q) => $q->where('empresa_id', $tenant->id),
                    )
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('archivo_original')
                    ->label('Archivo')
                    ->icon('heroicon-o-document-text')
                    ->formatStateUsing(fn (?string $state): string => $state ? basename((string) $state) : '—')
                    ->limit(30)
                    ->tooltip(fn (Importacion $record): ?string => $record->archivo_original
                        ? basename($record->archivo_original)
                        : null),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Importacion::ESTADO_PENDIENTE => 'Pendiente',
                        Importacion::ESTADO_PROCESANDO => 'Procesando…',
                        Importacion::ESTADO_COMPLETADA => 'Completada',
                        Importacion::ESTADO_CON_ERRORES => 'Con errores',
                        Importacion::ESTADO_FALLIDA => 'Fallida',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Importacion::ESTADO_PENDIENTE => 'gray',
                        Importacion::ESTADO_PROCESANDO => 'warning',
                        Importacion::ESTADO_COMPLETADA => 'success',
                        Importacion::ESTADO_CON_ERRORES => 'warning',
                        Importacion::ESTADO_FALLIDA => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): ?string => match ($state) {
                        Importacion::ESTADO_PENDIENTE => 'heroicon-o-clock',
                        Importacion::ESTADO_PROCESANDO => 'heroicon-o-arrow-path',
                        Importacion::ESTADO_COMPLETADA => 'heroicon-o-check-circle',
                        Importacion::ESTADO_CON_ERRORES => 'heroicon-o-exclamation-triangle',
                        Importacion::ESTADO_FALLIDA => 'heroicon-o-x-circle',
                        default => null,
                    }),

                TextColumn::make('filas_exitosas')
                    ->label('Creadas')
                    ->numeric()
                    ->alignCenter()
                    ->default(0)
                    ->color('success'),

                TextColumn::make('filas_con_error')
                    ->label('Errores')
                    ->numeric()
                    ->alignCenter()
                    ->default(0)
                    ->color(fn ($state): string => ($state ?? 0) > 0 ? 'danger' : 'gray'),

                TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (Importacion $record): string {
                        if (in_array($record->estado, [
                            Importacion::ESTADO_COMPLETADA,
                            Importacion::ESTADO_CON_ERRORES,
                        ])) {
                            return '100 %';
                        }

                        if ($record->estado === Importacion::ESTADO_FALLIDA) {
                            return 'Error';
                        }

                        if ($record->estado === Importacion::ESTADO_PENDIENTE) {
                            return '—';
                        }

                        if ($record->total_filas > 0) {
                            $porcentaje = round(($record->filas_procesadas / $record->total_filas) * 100);

                            return "{$porcentaje} %";
                        }

                        return '—';
                    })
                    ->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('5s')
            ->emptyStateHeading('Sin importaciones')
            ->emptyStateDescription('Aún no has cargado ningún archivo de Cartas SUA.')
            ->emptyStateIcon('heroicon-o-document-arrow-up')
            ->recordActions([
                TableAction::make('verLog')
                    ->label('Ver detalle')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalle de importación')
                    ->modalWidth(Width::TwoExtraLarge)
                    ->modalContent(fn (Importacion $record) => view(
                        'filament.cliente.pages.cartas-sua.modal-log-importacion',
                        ['importacion' => $record->load('errores', 'usuario')],
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                TableAction::make('descargarErrores')
                    ->label('Descargar errores')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Importacion $record): bool => $record->filas_con_error > 0
                        && filled($record->archivo_errores))
                    ->action(fn (Importacion $record) => Storage::download(
                        $record->archivo_errores,
                        "errores-importacion-{$record->id}.xlsx",
                    )),
            ])
            ->paginated([10, 25, 50]);
    }

    public function getSubheading(): ?string
    {
        return 'Sube un archivo Excel para generar cartas SUA de manera masiva.';
    }
}
