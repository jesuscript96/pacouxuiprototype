<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Permisos\Pages;

use App\Filament\Cliente\Resources\Permisos\PermisoResource;
use App\Services\TipoSolicitudPersistService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Alta de permiso ({@see \App\Models\TipoSolicitud}): delega en {@see TipoSolicitudPersistService}.
 */
class CreatePermiso extends CreateRecord
{
    protected static string $resource = PermisoResource::class;

    /**
     * @var array{etapas: mixed, preguntas: mixed}|null
     */
    public ?array $payloadFlujoYPreguntas = null;

    protected function getRedirectUrl(): string
    {
        return PermisoResource::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->payloadFlujoYPreguntas = [
            'etapas' => $data['etapas'] ?? [],
            'preguntas' => $data['preguntas'] ?? [],
        ];
        unset($data['etapas'], $data['preguntas']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $tenant = Filament::getTenant();
        abort_if($tenant === null, 403);

        $merged = array_merge($data, $this->payloadFlujoYPreguntas ?? []);

        return app(TipoSolicitudPersistService::class)->crear($merged, (int) $tenant->id);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Permiso creado correctamente.';
    }
}
