<?php

namespace App\Filament\Cliente\Resources\Regiones\Tables;

use App\Filament\Cliente\Actions\ExportarCatalogoColaboradorExcelAction;
use App\Models\Region;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegionesTable
{
    public static function accionExportarExcel(): Action
    {
        return ExportarCatalogoColaboradorExcelAction::make(
            'ViewAny:Region',
            'regiones',
            'Regiones',
            ['ID', 'Nombre', 'Fecha de creación', 'Fecha de actualización'],
            fn (Builder $query): array => $query->get()->map(
                fn (Region $region): array => [
                    $region->id,
                    $region->nombre,
                    $region->created_at?->format('d/m/Y H:i'),
                    $region->updated_at?->format('d/m/Y H:i'),
                ],
            )->all(),
        );
    }

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
            ->emptyStateHeading('Sin regiones aún')
            ->emptyStateDescription('Crea la primera región para segmentar geográficamente a tus colaboradores.')
            ->emptyStateIcon('heroicon-o-map')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->tooltip('Ver detalle'),
                    EditAction::make()
                        ->tooltip('Editar'),
                    DeleteAction::make()
                        ->hidden(fn ($record): bool => $record->tieneColaboradoresAsociados())
                        ->tooltip('Eliminar'),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
