<?php

namespace App\Filament\Cliente\Resources\Ubicaciones\Pages;

use App\Filament\Cliente\Resources\Ubicaciones\UbicacionResource;
use App\Models\Razonsocial;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUbicacion extends EditRecord
{
    protected static string $resource = UbicacionResource::class;

    /** @var array<int, array<string, mixed>> */
    protected array $razonesSocialesPayload = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        $data['razones_sociales'] = $this->record->razonesSociales()
            ->get()
            ->map(function (Razonsocial $rs): array {
                $colonias = [];
                if (filled($rs->cp) && preg_match("/^(?:0?[1-9]|[1-9]\d|5[0-2])\d{3}$/", $rs->cp)) {
                    $url = config('app.sepomex').'/'.$rs->cp;
                    $request = @file_get_contents($url);
                    $response = $request ? json_decode($request) : null;
                    if ($response && isset($response->asentamientos)) {
                        foreach ($response->asentamientos as $c) {
                            $colonias[$c] = $c;
                        }
                    }
                }

                return [
                    'id' => $rs->id,
                    'nombre' => $rs->nombre,
                    'rfc' => $rs->rfc,
                    'cp' => $rs->cp,
                    'calle' => $rs->calle,
                    'numero_exterior' => $rs->numero_exterior,
                    'numero_interior' => $rs->numero_interior,
                    'colonia' => $rs->colonia,
                    'alcaldia' => $rs->alcaldia,
                    'estado' => $rs->estado,
                    'registro_patronal' => $rs->registro_patronal,
                    'api_options_storage' => $colonias ? json_encode($colonias) : null,
                ];
            })
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->razonesSocialesPayload = $data['razones_sociales'] ?? [];

        unset($data['razones_sociales']);

        return $data;
    }

    protected function afterSave(): void
    {
        $empresaId = $this->record->empresa_id;
        $ids = [];

        foreach ($this->razonesSocialesPayload as $item) {
            $attrs = CreateUbicacion::razonSocialAttributes($item);

            if (empty($attrs['nombre']) && empty($attrs['rfc'])) {
                continue;
            }

            if (! empty($item['id'])) {
                $razonSocial = Razonsocial::find($item['id']);
                if ($razonSocial) {
                    $razonSocial->update($attrs);
                    $ids[] = $razonSocial->id;
                }

                continue;
            }

            $razonSocial = Razonsocial::create($attrs);
            $razonSocial->empresas()->syncWithoutDetaching([$empresaId]);
            $ids[] = $razonSocial->id;
        }

        $this->record->razonesSociales()->sync($ids);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
