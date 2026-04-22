<?php

namespace App\Filament\Resources\SegmentacionVozColaboradores\Tables;

use App\Filament\Support\CatalogSlideOver;
use App\Filament\Support\SegmentacionVozColaboradorFormActions;
use App\Models\TemaVozColaborador;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SegmentacionVozColaboradoresTable
{
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
                    ->toggleable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('empresaExclusiva.nombre')
                    ->label('Exclusivo para empresa')
                    ->placeholder('General')
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->searchable()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('exclusivo_para_empresa', $direction);
                    }),
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
            ->defaultSort('id', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    CatalogSlideOver::editAction()
                        ->visible(fn ($record) => auth()->user()?->can('update', $record))
                        ->mutateRecordDataUsing(fn (array $data, TemaVozColaborador $record): array => SegmentacionVozColaboradorFormActions::mutateFill($record, $data))
                        ->using(function (
                            array $data,
                            \Filament\Actions\Contracts\HasActions&\Filament\Schemas\Contracts\HasSchemas $livewire,
                            TemaVozColaborador $record,
                            ?Table $table
                        ): void {
                            [$clean, $ids] = SegmentacionVozColaboradorFormActions::splitPayloadForSave($data);
                            $record->update($clean);
                            $record->refresh();
                            SegmentacionVozColaboradorFormActions::afterPersist($record, $ids);
                        }),
                    DeleteAction::make()
                        ->visible(fn ($record) => auth()->user()?->can('delete', $record)),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('deleteAny', TemaVozColaborador::class)),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('forceDeleteAny', TemaVozColaborador::class)),
                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('restoreAny', TemaVozColaborador::class)),
                ]),
            ]);
    }
}
