<?php

namespace App\Filament\Cliente\Resources\Vacantes\Tables;

use App\Models\Vacante;
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

class VacantesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('puesto')
                    ->label('Puesto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('candidatos_count')
                    ->label('Candidatos')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('creador.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('copiarUrl')
                        ->label('Copiar URL')
                        ->icon('heroicon-o-link')
                        ->color('gray')
                        ->alpineClickHandler(fn (Vacante $record): string => "navigator.clipboard.writeText('".$record->urlPublica()."').then(() => { \$tooltip('URL copiada', { timeout: 1500 }) })"),
                    DeleteAction::make()
                        ->hidden(fn (Vacante $record): bool => $record->tieneRegistrosAsociados()),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
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
