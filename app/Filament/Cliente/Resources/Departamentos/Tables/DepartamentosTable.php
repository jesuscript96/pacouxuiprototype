<?php

namespace App\Filament\Cliente\Resources\Departamentos\Tables;

use App\Filament\Cliente\Actions\ExportarCatalogoColaboradorExcelAction;
use App\Models\Departamento;
use Filament\Actions\Action;
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
use Illuminate\Database\Eloquent\Builder;

class DepartamentosTable
{
    public static function accionExportarExcel(): Action
    {
        return ExportarCatalogoColaboradorExcelAction::make(
            'ViewAny:Departamento',
            'departamentos',
            'Departamentos',
            ['ID', 'Nombre', 'Departamento general', 'Empresa', 'Fecha de creación', 'Fecha de actualización'],
            fn (Builder $query): array => $query->with(['departamentoGeneral', 'empresa'])->get()->map(
                fn (Departamento $departamento): array => [
                    $departamento->id,
                    $departamento->nombre,
                    $departamento->departamentoGeneral?->nombre,
                    $departamento->empresa?->nombre,
                    $departamento->created_at?->format('d/m/Y H:i'),
                    $departamento->updated_at?->format('d/m/Y H:i'),
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

                TextColumn::make('departamentoGeneral.nombre')
                    ->label('Departamento general')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->toggleable()
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
            ->emptyStateHeading('Sin departamentos aún')
            ->emptyStateDescription('Crea el primer departamento para comenzar a organizar a tus colaboradores.')
            ->emptyStateIcon('heroicon-o-building-office')
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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
