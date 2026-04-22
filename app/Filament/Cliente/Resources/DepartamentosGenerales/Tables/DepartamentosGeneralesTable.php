<?php

namespace App\Filament\Cliente\Resources\DepartamentosGenerales\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DepartamentosGeneralesTable
{
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
                TextColumn::make('departamentos_count')
                    ->label('Departamentos')
                    ->counts('departamentos')
                    ->badge()
                    ->alignCenter()
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
            ->emptyStateHeading('Sin departamentos generales')
            ->emptyStateDescription('Los departamentos generales sirven como plantilla para las empresas.')
            ->emptyStateIcon('heroicon-o-building-library')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->tooltip('Ver detalle'),
                    EditAction::make()
                        ->tooltip('Editar'),
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
            ]);
    }
}
