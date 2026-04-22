<?php

namespace App\Filament\Resources\Reconocimientos\Tables;

use App\Filament\Support\CatalogSlideOver;
use App\Filament\Support\ReconocimientoFormActions;
use App\Models\Reconocmiento;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de reconocimientos.
 *
 * Muestra: id, nombre, descripción, estado de envío/exclusividad,
 * menciones necesarias y fechas. Incluye filtro de soft deletes
 * y acciones de ver/editar/eliminar.
 */
class ReconocimientosTable
{
    /**
     * Configura columnas, filtros y acciones de la tabla.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(40),
                IconColumn::make('es_enviable')
                    ->label('Activo')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable()
                    ->sortable(),
                IconColumn::make('es_exclusivo')
                    ->label('Exclusivo')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('menciones_necesarias')
                    ->label('Menciones necesarias')
                    ->badge()
                    ->alignCenter()
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
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
            ->recordActions([
                ActionGroup::make([
                    CatalogSlideOver::viewAction(),
                    CatalogSlideOver::editAction()
                        ->mutateRecordDataUsing(fn (array $data, Reconocmiento $record): array => ReconocimientoFormActions::mutateFillWithEmpresas($record, $data))
                        ->after(function (EditAction $action): void {
                            $record = $action->getRecord();
                            if (! $record instanceof Reconocmiento) {
                                return;
                            }
                            ReconocimientoFormActions::syncEmpresasPivot($record, $action->getData());
                        }),
                    DeleteAction::make()
                        ->visible(fn (Reconocmiento $record) => auth()->user()?->can('delete', $record)),
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
            ->defaultSort('created_at', 'desc')
            ->paginated(true);
    }
}
