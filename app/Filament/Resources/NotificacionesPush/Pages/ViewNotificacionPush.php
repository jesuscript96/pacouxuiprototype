<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Pages;

use App\Actions\NotificacionesPush\EnviarNotificacionPushAction;
use App\Filament\Resources\NotificacionesPush\NotificacionPushResource;
use App\Filament\Resources\NotificacionesPush\Widgets\DestinatariosWidget;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewNotificacionPush extends ViewRecord
{
    protected static string $resource = NotificacionPushResource::class;

    protected function getFooterWidgets(): array
    {
        if ($this->record->destinatarios()->count() === 0) {
            return [];
        }

        return [
            DestinatariosWidget::make(['record' => $this->record]),
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => $this->record->esEditable()),
            Action::make('enviar')
                ->label('Enviar ahora')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->puedeEnviarse())
                ->action(function (): void {
                    $enviar = app(EnviarNotificacionPushAction::class);
                    $resultado = $enviar->enviarAhora($this->record);

                    if ($resultado['success']) {
                        Notification::make()
                            ->title('Notificación enviada')
                            ->body($resultado['message'])
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } else {
                        Notification::make()
                            ->title('Error')
                            ->body($resultado['message'])
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('cancelar')
                ->label('Cancelar notificación')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->esCancelable())
                ->action(function (): void {
                    $enviar = app(EnviarNotificacionPushAction::class);
                    $resultado = $enviar->cancelar($this->record);

                    Notification::make()
                        ->title($resultado['success'] ? 'Cancelada' : 'Error')
                        ->body($resultado['message'])
                        ->color($resultado['success'] ? 'success' : 'danger')
                        ->send();

                    if ($resultado['success']) {
                        $this->redirect($this->getResource()::getUrl('index'));
                    }
                }),
        ];
    }
}
