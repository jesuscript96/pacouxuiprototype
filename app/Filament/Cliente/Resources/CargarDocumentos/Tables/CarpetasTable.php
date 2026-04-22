<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CargarDocumentos\Tables;

use App\Models\Carpeta;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarpetasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->hidden(fn (Carpeta $record): bool => $record->tieneRegistrosAsociados()),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
