<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Pages;

use App\Actions\NotificacionesPush\EnviarNotificacionPushAction;
use App\Enums\EstadoNotificacionPush;
use App\Filament\Resources\NotificacionesPush\Concerns\SanitizesNotificacionPushFiltros;
use App\Filament\Resources\NotificacionesPush\Concerns\SincronizaDestinatariosNotificacionPushForm;
use App\Filament\Resources\NotificacionesPush\NotificacionPushResource;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNotificacionPush extends EditRecord
{
    use SanitizesNotificacionPushFiltros;
    use SincronizaDestinatariosNotificacionPushForm;

    protected static string $resource = NotificacionPushResource::class;

    protected bool $enviarInmediatoDespuesDeGuardar = false;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('enviar')
                ->label('Enviar ahora')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Enviar notificación')
                ->modalDescription('¿Estás seguro de que deseas enviar esta notificación ahora?')
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
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->esCancelable()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['tipo_envio'] = $this->record->estado === EstadoNotificacionPush::PROGRAMADA
            ? 'programado'
            : 'borrador';

        if (isset($data['filtros']['con_adeudos']) && is_bool($data['filtros']['con_adeudos'])) {
            $data['filtros']['con_adeudos'] = $data['filtros']['con_adeudos'] ? '1' : '0';
        }

        $data['filtros'] ??= [];
        $data['filtros']['destinatarios'] = array_merge(
            [
                'select_all' => true,
                'manual_activation' => [],
                'manual_deactivation' => [],
            ],
            $data['filtros']['destinatarios'] ?? []
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->enviarInmediatoDespuesDeGuardar = false;

        if (isset($data['tipo_envio'])) {
            $tipoEnvio = $data['tipo_envio'];
            unset($data['tipo_envio']);

            if ($tipoEnvio === 'programado' && ! empty($data['programada_para'])) {
                $data['estado'] = EstadoNotificacionPush::PROGRAMADA;
            } elseif ($tipoEnvio === 'borrador') {
                $data['estado'] = EstadoNotificacionPush::BORRADOR;
                $data['programada_para'] = null;
            } elseif ($tipoEnvio === 'inmediato') {
                $data['estado'] = EstadoNotificacionPush::BORRADOR;
                $this->enviarInmediatoDespuesDeGuardar = true;
            }
        }

        $data['filtros'] = $this->sanitizarFiltros($data['filtros'] ?? null);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        $resolverService = app(ResolverDestinatariosService::class);
        $resolverService->persistirDestinatarios($record);

        if (! $this->enviarInmediatoDespuesDeGuardar) {
            return;
        }

        $record->refresh();

        if (! $record->puedeEnviarse()) {
            return;
        }

        $enviar = app(EnviarNotificacionPushAction::class);
        $resultado = $enviar->enviarAhora($record);

        if ($resultado['success']) {
            Notification::make()
                ->title('Notificación enviada')
                ->body($resultado['message'])
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
        } else {
            Notification::make()
                ->title('No se pudo enviar')
                ->body($resultado['message'])
                ->warning()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
