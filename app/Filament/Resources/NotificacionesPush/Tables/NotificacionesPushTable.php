<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Tables;

use App\Actions\NotificacionesPush\EnviarNotificacionPushAction;
use App\Enums\EstadoNotificacionPush;
use App\Models\NotificacionPush;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificacionesPushTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ])
            ->columns([
                TextColumn::make('empresa.nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (NotificacionPush $record): string => $record->titulo),
                TextColumn::make('mensaje')
                    ->label('Mensaje')
                    ->limit(60)
                    ->tooltip(fn (NotificacionPush $record): string => $record->mensaje)
                    ->toggleable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?EstadoNotificacionPush $state): string => $state?->getLabel() ?? '')
                    ->color(fn (?EstadoNotificacionPush $state): string|array|null => $state?->getColor())
                    ->sortable(),
                TextColumn::make('total_destinatarios')
                    ->label('Destinatarios')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_enviados')
                    ->label('Enviados')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn (NotificacionPush $record): ?string => $record->total_enviados > 0 ? 'success' : null),
                TextColumn::make('programada_para')
                    ->label('Programada para')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('enviada_at')
                    ->label('Enviada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creadoPor.name')
                    ->label('Creado por')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(collect(EstadoNotificacionPush::cases())->mapWithKeys(
                        fn (EstadoNotificacionPush $case): array => [$case->value => $case->getLabel()]
                    )->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (NotificacionPush $record): bool => $record->esEditable()),
                    Action::make('enviar')
                        ->label('Enviar ahora')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Enviar notificación')
                        ->modalDescription('¿Estás seguro de que deseas enviar esta notificación ahora? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, enviar ahora')
                        ->visible(fn (NotificacionPush $record): bool => $record->puedeEnviarse())
                        ->action(function (NotificacionPush $record): void {
                            $enviar = app(EnviarNotificacionPushAction::class);
                            $resultado = $enviar->enviarAhora($record);

                            if ($resultado['success']) {
                                Notification::make()
                                    ->title('Notificación enviada')
                                    ->body($resultado['message'])
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error')
                                    ->body($resultado['message'])
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('cancelar')
                        ->label('Cancelar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar notificación')
                        ->modalDescription('¿Estás seguro de que deseas cancelar esta notificación?')
                        ->visible(fn (NotificacionPush $record): bool => $record->esCancelable())
                        ->action(function (NotificacionPush $record): void {
                            $enviar = app(EnviarNotificacionPushAction::class);
                            $resultado = $enviar->cancelar($record);

                            Notification::make()
                                ->title($resultado['success'] ? 'Cancelada' : 'Error')
                                ->body($resultado['message'])
                                ->color($resultado['success'] ? 'success' : 'danger')
                                ->send();
                        }),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->can('DeleteAny:NotificacionPush')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
