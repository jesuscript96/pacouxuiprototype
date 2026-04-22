<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush\Pages;

use App\Actions\NotificacionesPush\EnviarNotificacionPushAction;
use App\Enums\EstadoNotificacionPush;
use App\Filament\Resources\NotificacionesPush\Concerns\SanitizesNotificacionPushFiltros;
use App\Filament\Resources\NotificacionesPush\Concerns\SincronizaDestinatariosNotificacionPushForm;
use App\Filament\Resources\NotificacionesPush\NotificacionPushResource;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNotificacionPush extends CreateRecord
{
    use SanitizesNotificacionPushFiltros;
    use SincronizaDestinatariosNotificacionPushForm;

    protected static string $resource = NotificacionPushResource::class;

    protected ?string $tipoEnvioPendiente = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->tipoEnvioPendiente = $data['tipo_envio'] ?? 'borrador';

        $data['creado_por'] = auth()->id();

        $tipoEnvio = $this->tipoEnvioPendiente;
        unset($data['tipo_envio']);

        if ($tipoEnvio === 'inmediato') {
            $data['estado'] = EstadoNotificacionPush::BORRADOR;
        } elseif ($tipoEnvio === 'programado') {
            $data['estado'] = EstadoNotificacionPush::PROGRAMADA;
        } else {
            $data['estado'] = EstadoNotificacionPush::BORRADOR;
            $data['programada_para'] = null;
        }

        $data['filtros'] = $this->sanitizarFiltros($data['filtros'] ?? null);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $resolverService = app(ResolverDestinatariosService::class);
        $totalDestinatarios = $resolverService->persistirDestinatarios($record);

        if ($totalDestinatarios === 0) {
            Notification::make()
                ->title('Sin destinatarios')
                ->body('No se encontraron colaboradores que coincidan con los filtros seleccionados.')
                ->warning()
                ->send();

            return;
        }

        if ($this->tipoEnvioPendiente === 'inmediato') {
            $action = app(EnviarNotificacionPushAction::class);
            $resultado = $action->enviarAhora($record);

            if ($resultado['success']) {
                Notification::make()
                    ->title('Notificación enviada')
                    ->body("Se envió a {$totalDestinatarios} destinatarios.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al enviar')
                    ->body($resultado['message'])
                    ->danger()
                    ->send();
            }

            return;
        }

        Notification::make()
            ->title('Notificación guardada')
            ->body("Se guardó con {$totalDestinatarios} destinatarios.")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
