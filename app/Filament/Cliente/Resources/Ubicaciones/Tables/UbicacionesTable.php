<?php

namespace App\Filament\Cliente\Resources\Ubicaciones\Tables;

use App\Filament\Cliente\Actions\ExportarCatalogoColaboradorExcelAction;
use App\Models\Ubicacion;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UbicacionesTable
{
    public static function accionExportarExcel(): Action
    {
        return ExportarCatalogoColaboradorExcelAction::make(
            'ViewAny:Ubicacion',
            'ubicaciones',
            'Ubicaciones',
            [
                'ID',
                'Nombre',
                'Empresa',
                'Código postal',
                'Agendar cita',
                'Registro patronal sucursal',
                'Dirección IMSS',
                'Razones sociales',
                'Fecha de creación',
                'Fecha de actualización',
            ],
            fn (Builder $query): array => $query->with(['empresa', 'razonesSociales'])->get()->map(
                fn (Ubicacion $ubicacion): array => [
                    $ubicacion->id,
                    $ubicacion->nombre,
                    $ubicacion->empresa?->nombre,
                    $ubicacion->cp,
                    $ubicacion->mostrar_modal_calendly ? 'Sí' : 'No',
                    $ubicacion->registro_patronal_sucursal,
                    $ubicacion->direccion_imss,
                    $ubicacion->razonesSociales->pluck('nombre')->implode(', '),
                    $ubicacion->created_at?->format('d/m/Y H:i'),
                    $ubicacion->updated_at?->format('d/m/Y H:i'),
                ],
            )->all(),
        );
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cp')
                    ->label('Código postal')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('mostrar_modal_calendly')
                    ->label('Agendar cita')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('registro_patronal_sucursal')
                    ->label('Registro patronal sucursal')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('direccion_imss')
                    ->label('Dirección IMSS')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('razonesSociales.nombre')
                    ->label('Razones sociales')
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->emptyStateHeading('Sin ubicaciones aún')
            ->emptyStateDescription('Crea la primera ubicación para asignar centros de trabajo a tus colaboradores.')
            ->emptyStateIcon('heroicon-o-map-pin')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->tooltip('Ver detalle'),
                    EditAction::make()
                        ->tooltip('Editar'),
                    DeleteAction::make()
                        ->hidden(fn ($record): bool => $record->tieneColaboradoresAsociados())
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
            ]);
    }
}
