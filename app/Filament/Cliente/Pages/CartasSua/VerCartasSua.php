<?php

namespace App\Filament\Cliente\Pages\CartasSua;

use App\Filament\Cliente\Widgets\CartasSuaStatsWidget;
use App\Models\CartaSua;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Services\CartaSuaPdfService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class VerCartasSua extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Ver Cartas SUA';

    protected static ?string $title = 'Cartas SUA';

    protected static ?string $slug = 'cartas-sua/ver';

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.cliente.pages.cartas-sua.ver-cartas-sua';

    public static function getPermissionModel(): string
    {
        return CartaSua::class;
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:CartaSua');
    }

    // =========================================================================
    // WIDGETS
    // =========================================================================

    protected function getHeaderWidgets(): array
    {
        return [
            CartasSuaStatsWidget::class,
        ];
    }

    /** @return int|array<string, int> */
    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    // =========================================================================
    // TABLA
    // =========================================================================

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();

        return $table
            ->query(
                CartaSua::query()
                    ->when(
                        $tenant instanceof Empresa,
                        fn (Builder $q) => $q->where('empresa_id', $tenant->id),
                    )
                    ->with(['colaborador'])
            )
            ->columns([
                TextColumn::make('colaborador.nombre_completo')
                    ->label('Colaborador')
                    ->searchable(['colaborador.nombre', 'colaborador.apellido_paterno', 'colaborador.apellido_materno'])
                    ->sortable(['colaborador.nombre'])
                    ->description(fn (CartaSua $record): string => $record->colaborador?->numero_colaborador
                        ? "Nº {$record->colaborador->numero_colaborador}"
                        : ''),

                TextColumn::make('bimestre')
                    ->label('Bimestre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('razon_social')
                    ->label('Razón social')
                    ->limit(30)
                    ->tooltip(fn (CartaSua $record): string => $record->razon_social)
                    ->toggleable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (CartaSua $record): string => $record->estado_label)
                    ->color(fn (CartaSua $record): string => $record->estado_color),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bimestre')
                    ->label('Bimestre')
                    ->options(fn (): array => $this->getBimestresDisponibles())
                    ->searchable(),

                SelectFilter::make('razon_social')
                    ->label('Razón social')
                    ->options(fn (): array => $this->getRazonesSocialesDisponibles())
                    ->searchable(),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'vista' => 'Vista',
                        'firmada' => 'Firmada',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'pendiente' => $query->whereNull('primera_visualizacion')->where('firmado', false),
                            'vista' => $query->whereNotNull('primera_visualizacion')->where('firmado', false),
                            'firmada' => $query->where('firmado', true),
                            default => $query,
                        };
                    }),

                SelectFilter::make('colaborador_id')
                    ->label('Colaborador')
                    ->options(fn (): array => $this->getColaboradoresDisponibles())
                    ->searchable(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    Action::make('ver')
                        ->label('Ver')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->visible(fn (): bool => (bool) auth()->user()?->can('View:CartaSua'))
                        ->modalHeading(fn (CartaSua $record): string => "Carta SUA — {$record->colaborador?->nombre_completo}")
                        ->modalDescription(fn (CartaSua $record): string => "Bimestre: {$record->bimestre}")
                        ->modalContent(fn (CartaSua $record) => view(
                            'filament.cliente.pages.cartas-sua.modal-preview-carta',
                            [
                                'carta' => $record->loadMissing('colaborador'),
                                'pdfUrl' => $this->obtenerUrlPdf($record),
                            ],
                        ))
                        ->modalWidth(Width::FiveExtraLarge)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar'),
                    Action::make('descargar')
                        ->label('Descargar')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->visible(fn (): bool => (bool) auth()->user()?->can('View:CartaSua'))
                        ->action(fn (CartaSua $record) => $this->descargarPdf($record)),
                    DeleteAction::make()
                        ->label('Borrar')
                        ->visible(fn (): bool => (bool) auth()->user()?->can('Delete:CartaSua'))
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar carta SUA')
                        ->modalDescription('¿Estás seguro de eliminar esta carta? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->emptyStateHeading('Sin cartas SUA')
            ->emptyStateDescription('Aún no hay cartas SUA registradas. Puedes cargarlas desde "Cargar Registros".')
            ->emptyStateIcon('heroicon-o-document-text')
            ->poll('30s')
            ->paginated([10, 25, 50, 100]);
    }

    // =========================================================================
    // MÉTODOS AUXILIARES
    // =========================================================================

    /** @return array<string, string> */
    private function getBimestresDisponibles(): array
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            return [];
        }

        return CartaSua::where('empresa_id', $tenant->id)
            ->distinct()
            ->orderBy('bimestre')
            ->pluck('bimestre', 'bimestre')
            ->toArray();
    }

    /** @return array<string, string> */
    private function getRazonesSocialesDisponibles(): array
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            return [];
        }

        return CartaSua::where('empresa_id', $tenant->id)
            ->distinct()
            ->orderBy('razon_social')
            ->pluck('razon_social', 'razon_social')
            ->toArray();
    }

    /** @return array<int, string> */
    private function getColaboradoresDisponibles(): array
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            return [];
        }

        return Colaborador::query()
            ->whereIn('id', CartaSua::where('empresa_id', $tenant->id)->distinct()->pluck('colaborador_id'))
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn (Colaborador $c): array => [$c->id => $c->nombre_completo])
            ->toArray();
    }

    private function obtenerUrlPdf(CartaSua $record): ?string
    {
        if (! $record->pdf_path) {
            return null;
        }

        try {
            return app(CartaSuaPdfService::class)->obtenerUrl($record, 30);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return \Symfony\Component\HttpFoundation\StreamedResponse|null */
    private function descargarPdf(CartaSua $record): mixed
    {
        if (! $record->pdf_path) {
            Notification::make()
                ->title('Sin PDF')
                ->body('Esta carta aún no tiene un PDF generado.')
                ->warning()
                ->send();

            return null;
        }

        try {
            return app(CartaSuaPdfService::class)->descargar($record);
        } catch (\Throwable) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo descargar el archivo.')
                ->danger()
                ->send();

            return null;
        }
    }

    public function getSubheading(): ?string
    {
        return 'Gestión y seguimiento de cartas SUA de tus colaboradores.';
    }
}
