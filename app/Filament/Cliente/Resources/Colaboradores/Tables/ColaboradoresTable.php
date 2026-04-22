<?php

namespace App\Filament\Cliente\Resources\Colaboradores\Tables;

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Services\ColaboradorBajaService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ColaboradoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estado_baja')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(function (Colaborador $record): string {
                        if ($record->trashed()) {
                            return 'Dado de baja';
                        }
                        if ($record->bajaProgramada !== null) {
                            return 'Baja programada';
                        }

                        return 'Activo';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Activo' => 'success',
                        'Baja programada' => 'warning',
                        'Dado de baja' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('nombre_completo')
                    ->label('Nombre completo')
                    ->getStateUsing(fn (Colaborador $record): string => $record->nombre_completo)
                    ->searchable(query: function ($query, string $search): Builder {
                        return $query->where(function ($q) use ($search): void {
                            $q->where('nombre', 'like', "%{$search}%")
                                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                ->orWhere('apellido_materno', 'like', "%{$search}%")
                                ->orWhere('numero_colaborador', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, string $direction): Builder {
                        return $query->orderBy('nombre', $direction);
                    }),
                TextColumn::make('numero_colaborador')
                    ->label('Nº empleado')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('departamento.nombre')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('puesto.nombre')
                    ->label('Puesto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha_ingreso')
                    ->label('Fecha ingreso')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('verificado')
                    ->label('Cuenta activa')
                    ->tooltip('Usuario con acceso activo al sistema')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Estado del colaborador')
                    ->placeholder('Solo activos')
                    ->trueLabel('Incluir dados de baja')
                    ->falseLabel('Solo dados de baja'),
                SelectFilter::make('ubicacion_id')
                    ->label('Ubicación')
                    ->relationship('ubicacion', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('departamento_id')
                    ->label('Departamento')
                    ->relationship('departamento', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('puesto_id')
                    ->label('Puesto')
                    ->relationship('puesto', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('region_id')
                    ->label('Región')
                    ->relationship('region', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('Sin colaboradores aún')
            ->emptyStateDescription('Da de alta el primer colaborador para comenzar a gestionar tu equipo.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->tooltip('Ver detalle'),
                    EditAction::make()
                        ->tooltip('Editar'),
                    Action::make('darDeBaja')
                        ->label('Dar de baja')
                        ->icon('heroicon-o-user-minus')
                        ->color('danger')
                        ->visible(fn (Colaborador $record): bool => (bool) auth()->user()?->can('Create:BajaColaborador')
                            && ! $record->trashed()
                            && $record->bajaProgramada === null)
                        ->modalHeading('Dar de baja colaborador')
                        ->modalDescription(fn (Colaborador $record): string => '¿Dar de baja a '.$record->nombre_completo.'? Las fechas futuras programan la baja; hoy o pasado ejecutan la baja al confirmar.')
                        ->modalSubmitActionLabel('Confirmar baja')
                        ->fillForm(fn (): array => [
                            'fecha_baja' => now()->format('Y-m-d'),
                            'motivo' => null,
                            'comentarios' => null,
                        ])
                        ->schema([
                            Section::make()
                                ->schema([
                                    DatePicker::make('fecha_baja')
                                        ->label('Fecha de baja')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->minDate(function ($record): ?\Carbon\Carbon {
                                            return $record instanceof Colaborador
                                                ? $record->fecha_ingreso?->copy()->addDay()
                                                : null;
                                        })
                                        ->helperText('Debe ser posterior a la fecha de ingreso. Fechas futuras programan la baja.'),
                                    Select::make('motivo')
                                        ->label('Motivo')
                                        ->required()
                                        ->options(BajaColaborador::motivosDisponibles()),
                                    Textarea::make('comentarios')
                                        ->label('Comentarios')
                                        ->rows(3)
                                        ->nullable(),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data, Colaborador $record): void {
                            try {
                                $baja = app(ColaboradorBajaService::class)->registrarBaja($record, $data);

                                $mensaje = $baja->esProgramada()
                                    ? 'Baja programada para el '.$baja->fecha_baja->format('d/m/Y')
                                    : 'Colaborador '.$record->nombre_completo.' dado de baja correctamente';

                                Notification::make()
                                    ->title('Éxito')
                                    ->body($mensaje)
                                    ->success()
                                    ->send();
                            } catch (ValidationException $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body(collect($e->errors())->flatten()->first() ?? 'No se pudo registrar la baja.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('verBajaProgramada')
                        ->label('Baja programada')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->visible(fn (Colaborador $record): bool => ! $record->trashed()
                            && $record->bajaProgramada !== null
                            && (bool) auth()->user()?->can('Update:BajaColaborador'))
                        ->url(fn (Colaborador $record): string => BajaColaboradorResource::getUrl('edit', [
                            'tenant' => Filament::getTenant(),
                            'record' => $record->bajaProgramada,
                        ])),
                    Action::make('verBaja')
                        ->label('Ver baja')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->visible(fn (Colaborador $record): bool => $record->trashed()
                            && $record->ultimaBaja !== null
                            && (bool) auth()->user()?->can('View:BajaColaborador'))
                        ->url(fn (Colaborador $record): string => BajaColaboradorResource::getUrl('view', [
                            'tenant' => Filament::getTenant(),
                            'record' => $record->ultimaBaja,
                        ])),
                    DeleteAction::make()
                        ->tooltip('Eliminar'),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->paginated(true);
    }
}
