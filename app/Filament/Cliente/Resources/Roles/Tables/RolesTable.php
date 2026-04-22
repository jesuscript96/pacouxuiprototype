<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Roles\Tables;

use App\Models\SpatieRole;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ])
            ->columns([
                TextColumn::make('display_name')
                    ->label('Nombre')
                    ->formatStateUsing(function (?string $state, SpatieRole $record): string {
                        return ($state !== null && $state !== '') ? $state : $record->name;
                    })
                    ->searchable(['name', 'display_name'])
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Identificador')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->badge()
                    ->color('info'),
                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (SpatieRole $record, DeleteAction $action): void {
                            if ($record->users()->exists()) {
                                Notification::make()
                                    ->title('No se puede eliminar el rol')
                                    ->body('Reasigna primero a los usuarios que lo tienen.')
                                    ->danger()
                                    ->send();
                                $action->halt();
                            }
                        }),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->before(function (\Illuminate\Support\Collection $records, DeleteBulkAction $action): void {
                        foreach ($records as $record) {
                            if ($record instanceof SpatieRole && $record->users()->exists()) {
                                Notification::make()
                                    ->title('No se pueden eliminar roles con usuarios')
                                    ->danger()
                                    ->send();
                                $action->halt();

                                return;
                            }
                        }
                    }),
            ])
            ->paginated(true);
    }
}
