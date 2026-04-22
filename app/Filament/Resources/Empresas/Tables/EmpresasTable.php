<?php

namespace App\Filament\Resources\Empresas\Tables;

use App\Models\Empresa;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmpresasTable
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
                TextColumn::make('nombre_contacto')
                    ->label('Contacto')
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email_contacto')
                    ->label('Email contacto')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('industria.nombre')
                    ->label('Industria')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subindustria.nombre')
                    ->label('Subindustria')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_facturacion')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_inicio_contrato')
                    ->label('Inicio contrato')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_fin_contrato')
                    ->label('Fin contrato')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('activo')
                    ->boolean()
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
            ->defaultSort('nombre', 'desc')
            ->emptyStateHeading('Sin empresas registradas')
            ->emptyStateDescription('Agrega la primera empresa para comenzar a gestionar clientes.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->tooltip('Ver detalle')
                        ->visible(fn ($record) => auth()->user()?->can('view', $record)),
                    EditAction::make()
                        ->tooltip('Editar')
                        ->visible(fn ($record) => auth()->user()?->can('update', $record)),
                    DeleteAction::make()
                        ->tooltip('Eliminar')
                        ->visible(fn (Empresa $record) => auth()->user()?->can('delete', $record)),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('deleteAny', \App\Models\Empresa::class)),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('forceDeleteAny', \App\Models\Empresa::class)),
                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('restoreAny', \App\Models\Empresa::class)),
                ]),
            ]);
    }
}
