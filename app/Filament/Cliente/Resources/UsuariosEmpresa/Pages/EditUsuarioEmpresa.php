<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\UsuariosEmpresa\Pages;

use App\Actions\User\SyncClientePanelAccesoForEmpresa;
use App\Filament\Cliente\Resources\UsuariosEmpresa\UsuarioEmpresaResource;
use App\Models\Empresa;
use App\Models\SpatieRole;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUsuarioEmpresa extends EditRecord
{
    protected static string $resource = UsuarioEmpresaResource::class;

    /** @var list<int>|null */
    protected ?array $pendingTenantRoleIds = null;

    protected ?bool $pendingAccesoPanelCliente = null;

    /**
     * BL: el toggle refleja pertenencia al pivote empresa_user para el tenant (y caso empresa principal + tipo cliente).
     */
    public static function accesoPanelClienteParaTenant(User $record, ?Empresa $tenant): bool
    {
        if (! $tenant instanceof Empresa) {
            return false;
        }

        $tieneAccesoEstaEmpresa = $record->empresas()
            ->where('empresas.id', $tenant->id)
            ->exists();

        if (! $tieneAccesoEstaEmpresa && (int) $record->empresa_id === (int) $tenant->id) {
            $tipos = $record->tipo ?? [];
            $tieneAccesoEstaEmpresa = is_array($tipos) && in_array('cliente', $tipos, true);
        }

        return $tieneAccesoEstaEmpresa;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $record */
        $record = $this->record;

        $data['colaborador_resumen'] = $record->colaborador
            ? "{$record->colaborador->nombre_completo} ({$record->colaborador->numero_colaborador})"
            : 'Sin ficha de colaborador';

        $tipo = $record->tipo ?? [];
        $data['tipo_display'] = is_array($tipo)
            ? implode(', ', array_map('ucfirst', $tipo))
            : (string) $tipo;

        $tenant = Filament::getTenant();
        $data['acceso_panel_cliente'] = self::accesoPanelClienteParaTenant(
            $record,
            $tenant instanceof Empresa ? $tenant : null
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['colaborador_resumen'], $data['tipo_display']);

        $tenantId = Filament::getTenant()?->id;
        if (isset($data['roles']) && is_array($data['roles']) && $tenantId !== null) {
            $validIds = SpatieRole::query()->withoutGlobalScopes()
                ->where('company_id', $tenantId)
                ->where('guard_name', 'web')
                ->where('name', '!=', 'super_admin')
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
            $this->pendingTenantRoleIds = array_values(array_intersect(
                array_map(fn ($v): int => (int) $v, $data['roles']),
                $validIds
            ));
            unset($data['roles']);
        }

        if (array_key_exists('acceso_panel_cliente', $data)) {
            $this->pendingAccesoPanelCliente = (bool) $data['acceso_panel_cliente'];
            unset($data['acceso_panel_cliente']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $tenant = Filament::getTenant();
        if ($this->pendingTenantRoleIds !== null && $tenant instanceof Empresa) {
            /** @var User $user */
            $user = $this->record->fresh();
            $tenantId = (int) $tenant->id;
            // Sin withoutGlobalScopes(), SpatieRole oculta roles de otras empresas y syncRoles los borraría.
            $keep = $user->roles()->withoutGlobalScopes()->get()->filter(
                fn (SpatieRole $r): bool => (int) ($r->company_id ?? 0) !== $tenantId
            );
            $new = SpatieRole::query()->withoutGlobalScopes()
                ->whereIn('id', $this->pendingTenantRoleIds)
                ->get();
            $user->syncRoles($keep->merge($new)->unique('id')->values()->all());
            $this->pendingTenantRoleIds = null;
        }

        if ($this->pendingAccesoPanelCliente !== null && $tenant instanceof Empresa) {
            app(SyncClientePanelAccesoForEmpresa::class)(
                $this->record->fresh(),
                $tenant,
                $this->pendingAccesoPanelCliente
            );
            $this->pendingAccesoPanelCliente = null;
        }

        Notification::make()
            ->title('Usuario actualizado')
            ->body('Roles y acceso al panel guardados.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
