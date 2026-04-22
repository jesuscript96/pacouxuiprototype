<?php

namespace App\Filament\Cliente\Resources\Puestos\Tables;

use App\Filament\Cliente\Actions\ExportarCatalogoColaboradorExcelAction;
use App\Models\Puesto;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PuestosTable
{
    public static function accionExportarExcel(): Action
    {
        return ExportarCatalogoColaboradorExcelAction::make(
            'ViewAny:Puesto',
            'puestos',
            'Puestos',
            ['ID', 'Nombre', 'Puesto general', 'Área general', 'Ocupación', 'Fecha de creación', 'Fecha de actualización'],
            fn (Builder $query): array => $query->with(['puestoGeneral', 'areaGeneral', 'ocupacion'])->get()->map(
                fn (Puesto $puesto): array => [
                    $puesto->id,
                    $puesto->nombre,
                    $puesto->puestoGeneral?->nombre,
                    $puesto->areaGeneral?->nombre,
                    $puesto->ocupacion?->descripcion,
                    $puesto->created_at?->format('d/m/Y H:i'),
                    $puesto->updated_at?->format('d/m/Y H:i'),
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
                TextColumn::make('puestoGeneral.nombre')
                    ->label('Puesto general')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('areaGeneral.nombre')
                    ->label('Área general')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ocupacion.descripcion')
                    ->label('Ocupación')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(30),
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
            ->emptyStateHeading('Sin puestos aún')
            ->emptyStateDescription('Crea el primer puesto para asignarlo a tus colaboradores.')
            ->emptyStateIcon('heroicon-o-briefcase')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->tooltip('Ver detalle'),
                    EditAction::make()
                        ->tooltip('Editar'),
                    DeleteAction::make()
                        ->hidden(fn ($record): bool => $record->tieneColaboradoresAsociados())
                        ->tooltip('Eliminar'),
                    ForceDeleteAction::make()
                        ->tooltip('Eliminar permanentemente'),
                    RestoreAction::make()
                        ->tooltip('Restaurar'),
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
