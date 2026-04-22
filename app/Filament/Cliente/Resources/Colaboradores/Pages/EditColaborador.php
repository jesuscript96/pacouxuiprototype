<?php

namespace App\Filament\Cliente\Resources\Colaboradores\Pages;

use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Services\ColaboradorService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class EditColaborador extends EditRecord
{
    protected static string $resource = ColaboradorResource::class;

    /**
     * No usar Form Request en edición: Filament valida con el schema del form.
     * Con Request, el flujo puede no pasar el estado completo y el guardado falla sin aviso.
     */
    protected static ?string $request = null;

    public function getTitle(): string|Htmlable
    {
        return 'Editar colaborador';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cambios guardados';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Colaborador $colaborador */
        $colaborador = $this->record;
        $colaborador->load(['beneficiarios', 'cuentasNomina']);

        $data['beneficiarios'] = $colaborador->beneficiarios
            ->map(fn ($b) => [
                'nombre_completo' => $b->nombre_completo,
                'parentesco' => $b->parentesco,
                'porcentaje' => $b->porcentaje !== null ? (float) $b->porcentaje : null,
            ])
            ->toArray();

        $cuenta = $colaborador->cuentasNomina->first();
        if ($cuenta) {
            $data['cuenta_nomina'] = [
                'banco_id' => $cuenta->banco_id,
                'numero_cuenta' => $cuenta->numero_cuenta,
                'tipo_cuenta' => $cuenta->tipo_cuenta,
                'estado' => $cuenta->estado,
            ];
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->buildDataForService($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var \App\Models\Colaborador $colaborador */
        $colaborador = $record;
        $user = $colaborador->user;
        if ($user === null) {
            throw new \RuntimeException('Colaborador sin cuenta de usuario vinculada.');
        }

        app(ColaboradorService::class)->actualizarColaborador($user->fresh(), $data);

        return $colaborador->refresh();
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
                    'estado' => $cn['estado'] ?? 'ACTIVA',
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
