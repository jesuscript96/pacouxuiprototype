<?php

namespace App\Filament\Cliente\Resources\Areas\Tables;

use App\Filament\Cliente\Actions\ExportarCatalogoColaboradorExcelAction;
use App\Models\Area;
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
use Illuminate\Database\Eloquent\Builder;

class AreasTable
{
    public static function accionExportarExcel(): Action
    {
        return ExportarCatalogoColaboradorExcelAction::make(
            'ViewAny:Area',
            'areas',
            'Áreas',
            ['ID', 'Nombre', 'Área general', 'Fecha de creación', 'Fecha de actualización'],
            fn (Builder $query): array => $query->with(['areaGeneral'])->get()->map(
                fn (Area $area): array => [
                    $area->id,
                    $area->nombre,
                    $area->areaGeneral?->nombre,
                    $area->created_at?->format('d/m/Y H:i'),
                    $area->updated_at?->format('d/m/Y H:i'),
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
                TextColumn::make('areaGeneral.nombre')
                    ->label('Área general')
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
            ->filters([
                TrashedFilter::make(),
            ])
            ->emptyStateHeading('Sin áreas aún')
            ->emptyStateDescription('Crea la primera área para estructurar los departamentos de tu empresa.')
            ->emptyStateIcon('heroicon-o-squares-2x2')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->tooltip('Ver detalle'),
                    EditAction::make()
                        ->tooltip('Editar'),
                    DeleteAction::make()
                        ->hidden(fn ($record): bool => $record->tieneColaboradoresAsociados())
                        ->tooltip('Eliminar'),
                    ForceDeleteAction::make()
                        ->tooltip('Eliminar permanentemente'),
                    RestoreAction::make()
                        ->tooltip('Restaurar'),
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
