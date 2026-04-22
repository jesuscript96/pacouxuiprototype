<?php

namespace App\Filament\Resources\Reconocimientos\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Relation manager para el pivot empresas_reconocimientos.
 *
 * Permite adjuntar (attach) y separar (detach) empresas
 * de un reconocimiento directamente desde la vista de edición.
 */
class EmpresasRelationManager extends RelationManager
{
    protected static string $relationship = 'empresas';

    protected static ?string $title = 'Empresas';

    /**
     * Configura la tabla del relation manager con columnas de empresa
     * y acciones de attach/detach.
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email_contacto')
                    ->label('Email contacto')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
