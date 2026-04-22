<?php

namespace App\Filament\Cliente\Resources\Ubicaciones\Pages;

use App\Filament\Cliente\Resources\Ubicaciones\UbicacionResource;
use App\Models\Razonsocial;
use Filament\Resources\Pages\CreateRecord;

class CreateUbicacion extends CreateRecord
{
    protected static string $resource = UbicacionResource::class;

    /** @var array<int, array<string, mixed>> */
    protected array $razonesSocialesPayload = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->razonesSocialesPayload = $data['razones_sociales'] ?? [];

        unset($data['razones_sociales']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $empresaId = $this->record->empresa_id;

        foreach ($this->razonesSocialesPayload as $item) {
            $attrs = self::razonSocialAttributes($item);
            if (empty($attrs['nombre']) || empty($attrs['rfc'])) {
                continue;
            }
            $razonSocial = Razonsocial::create($attrs);
            $razonSocial->empresas()->syncWithoutDetaching([$empresaId]);
            $this->record->razonesSociales()->attach($razonSocial->id);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function razonSocialAttributes(array $item): array
    {
        $keys = [
            'nombre', 'rfc', 'cp', 'calle', 'numero_exterior', 'numero_interior',
            'colonia', 'alcaldia', 'estado', 'registro_patronal',
        ];

        $attrs = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $item) && $item[$key] !== null && $item[$key] !== '') {
                $attrs[$key] = $item[$key];
            }
        }

        $attrs['numero_interior'] = $attrs['numero_interior'] ?? null;
        $attrs['registro_patronal'] = $attrs['registro_patronal'] ?? null;

        return $attrs;
    }
}
