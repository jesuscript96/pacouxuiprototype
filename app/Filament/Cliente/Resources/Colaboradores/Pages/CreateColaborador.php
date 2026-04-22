<?php

namespace App\Filament\Cliente\Resources\Colaboradores\Pages;

use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Http\Requests\ColaboradorRequest;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Services\ColaboradorService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateColaborador extends CreateRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected static string $request = ColaboradorRequest::class;

    public function getTitle(): string|Htmlable
    {
        return 'Alta de colaborador';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', ['tenant' => Filament::getTenant()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $empresa = Filament::getTenant() instanceof Empresa ? Filament::getTenant() : null;
        if ($empresa) {
            $data['empresa_id'] = $empresa->id;
        }

        // Usar estado completo del formulario para no perder cuenta_nomina ni otros campos
        $formState = $this->form->getState();
        $data = array_merge($formState, $data);

        return $this->buildDataForService($data);
    }

    protected function handleRecordCreation(array $data): Colaborador
    {
        $empresa = Filament::getTenant() instanceof Empresa ? Filament::getTenant() : null;
        if (! $empresa) {
            throw new \RuntimeException('No hay empresa (tenant) seleccionada.');
        }

        $user = app(ColaboradorService::class)->crearColaborador($data, $empresa);
        $ficha = $user->colaborador;
        if ($ficha === null) {
            throw new \RuntimeException('No se creó la ficha de colaborador (colaboradores).');
        }

        return $ficha;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildDataForService(array $data): array
    {
        $beneficiarios = $data['beneficiarios'] ?? [];
        unset($data['beneficiarios']);

        $cuentaNomina = null;
        if (isset($data['cuenta_nomina']) && is_array($data['cuenta_nomina'])) {
            $cn = $data['cuenta_nomina'];
            if (! empty($cn['banco_id']) || ! empty($cn['numero_cuenta'])) {
                $cuentaNomina = [
                    'banco_id' => $cn['banco_id'] ?? null,
                    'numero_cuenta' => $cn['numero_cuenta'] ?? '',
                    'tipo_cuenta' => $cn['tipo_cuenta'] ?? 'CLABE',
                    'estado' => 'ACTIVA',
                ];
            }
            unset($data['cuenta_nomina']);
        }

        $data['beneficiarios'] = $beneficiarios;
        if ($cuentaNomina !== null) {
            $data['cuenta_nomina'] = $cuentaNomina;
        }

        return $data;
    }
}
