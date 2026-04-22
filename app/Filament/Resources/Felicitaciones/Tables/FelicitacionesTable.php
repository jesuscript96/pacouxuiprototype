<?php

namespace App\Filament\Resources\Felicitaciones\Tables;

use App\Filament\Support\CatalogSlideOver;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FelicitacionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('departamento.nombre')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('es_urgente')
                    ->label('Urgente')
                    ->badge()
                    ->color(fn (bool $state): string => ($state == 'urgente') ? 'danger' : 'gray')
                    ->formatStateUsing(fn (string $state): string => ($state == '1') ? 'Urgente' : 'No urgente'
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Remitente')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('requiere_respuesta')
                    ->label('Requiere respuesta')
                    ->boolean()
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
            ->recordActions([
                ActionGroup::make([
                    CatalogSlideOver::viewAction(),
                    CatalogSlideOver::editAction(),
                    DeleteAction::make(),
                ])
                    ->tooltip(__('Acciones')),
            ]);
    }
}
