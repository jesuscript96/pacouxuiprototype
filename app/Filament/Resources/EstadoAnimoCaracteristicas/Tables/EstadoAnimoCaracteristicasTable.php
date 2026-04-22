<?php

namespace App\Filament\Resources\EstadoAnimoCaracteristicas\Tables;

use App\Filament\Support\CatalogSlideOver;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EstadoAnimoCaracteristicasTable
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
                TextColumn::make('lista_inicial')
                    ->label('Lista inicial')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'normal' => 'Normal',
                        'bad' => 'Mal',
                        'very_bad' => 'Muy mal',
                        'well' => 'Bien',
                        'very_well' => 'Muy bien',
                        default => $state ?? '—',
                    })
                    ->badge()
                    ->placeholder('Sin asignar')
                    ->searchable()
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
            ->recordActions([
                ActionGroup::make([
                    CatalogSlideOver::viewAction(),
                    CatalogSlideOver::editAction(),
                    DeleteAction::make(),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
