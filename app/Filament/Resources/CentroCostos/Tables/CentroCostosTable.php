<?php

namespace App\Filament\Resources\CentroCostos\Tables;

use App\Filament\Support\CatalogSlideOver;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CentroCostosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('servicio')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

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

            ])
            ->emptyStateHeading('Sin centros de costo')
            ->emptyStateDescription('Agrega el primer centro de costo para asignarlo a las empresas.')
            ->emptyStateIcon('heroicon-o-calculator')
            ->recordActions([
                ActionGroup::make([
                    CatalogSlideOver::viewAction()
                        ->tooltip('Ver detalle')
                        ->visible(fn ($record) => auth()->user()?->can('view', $record)),
                    CatalogSlideOver::editAction()
                        ->tooltip('Editar')
                        ->visible(fn ($record) => auth()->user()?->can('update', $record)),
                    DeleteAction::make()
                        ->tooltip('Eliminar')
                        ->visible(fn ($record) => auth()->user()?->can('delete', $record)),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('deleteAny', \App\Models\CentroCosto::class)),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('forceDeleteAny', \App\Models\CentroCosto::class)),
                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('restoreAny', \App\Models\CentroCosto::class)),
                ]),
            ]);
    }
}
