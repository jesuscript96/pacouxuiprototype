<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\Roles\Schemas;

use App\Models\SpatieRole;
use App\Support\ClientePanelAssignablePermissions;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Permission;

class RolForm
{
    /**
     * Orden de acciones dentro de cada grupo (PascalCase, formato Shield).
     *
     * @var list<string>
     */
    protected const ACTION_ORDER = [
        'ViewAny',
        'View',
        'Create',
        'Update',
        'Delete',
        'DeleteAny',
        'Restore',
        'RestoreAny',
        'ForceDelete',
        'ForceDeleteAny',
        'Replicate',
        'Reorder',
    ];

    public static function configure(Schema $schema): Schema
    {
        $permissionSections = self::buildPermissionSections();

        return $schema
            ->components([
                Section::make('Información del rol')
                    ->schema([
                        Hidden::make('company_id')
                            ->default(fn (): ?int => Filament::getTenant()?->id),
                        Hidden::make('guard_name')
                            ->default('web'),
                        TextInput::make('name')
                            ->label('Identificador')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(
                                table: 'spatie_roles',
                                column: 'name',
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule): Unique {
                                    $tenant = Filament::getTenant();

                                    return $rule
                                        ->where('guard_name', 'web')
                                        ->where('company_id', $tenant?->id);
                                }
                            )
                            ->helperText('Nombre único para esta empresa. Solo letras, números y guiones.'),
                        TextInput::make('display_name')
                            ->label('Nombre para mostrar')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->columnSpanFull(),

                Section::make('Permisos')
                    ->description('Marca los permisos que tendrá este rol. Expande cada sección para ver las acciones disponibles.')
                    ->schema($permissionSections)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public static function buildPermissionSections(): array
    {
        $grouped = self::getPermisosAgrupadosPorModelo();
        $sections = [];

        foreach ($grouped as $modelo => $permisos) {
            $options = $permisos->mapWithKeys(fn (array $p): array => [
                $p['id'] => self::formatActionLabel($p['action']),
            ])->all();

            $sections[] = Section::make(self::sectionLabelForModel($modelo))
                ->description("Acciones permitidas en «{$modelo}».")
                ->collapsible()
                ->collapsed()
                ->schema([
                    CheckboxList::make('permisos_'.$modelo)
                        ->label('')
                        ->options($options)
                        ->default([])
                        ->columns(4)
                        ->bulkToggleable()
                        ->searchable(),
                ])
                ->columnSpanFull();
        }

        return $sections;
    }

    /**
     * @return array<string, Collection<int, array{id: int, name: string, action: string, modelo: string}>>
     */
    public static function getPermisosAgrupadosPorModelo(): array
    {
        $suffixes = ClientePanelAssignablePermissions::MODEL_SUFFIXES;
        $suffixSet = array_flip($suffixes);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $grouped = [];

        foreach ($permissions as $permiso) {
            if (! preg_match('/^([^:]+):(.+)$/', $permiso->name, $matches)) {
                continue;
            }

            $action = $matches[1];
            $modelo = $matches[2];

            if (! isset($suffixSet[$modelo])) {
                continue;
            }

            if (! isset($grouped[$modelo])) {
                $grouped[$modelo] = collect();
            }

            $grouped[$modelo]->push([
                'id' => $permiso->id,
                'name' => $permiso->name,
                'action' => $action,
                'modelo' => $modelo,
            ]);
        }

        foreach ($grouped as $modelo => $items) {
            $grouped[$modelo] = $items->sortBy(function (array $p): int {
                $idx = array_search($p['action'], self::ACTION_ORDER, true);

                return $idx !== false ? $idx : 1000;
            })->values();
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @return array<string, list<int|string>>
     */
    public static function hydrateGroupedPermissionFields(?SpatieRole $record): array
    {
        if ($record === null) {
            return [];
        }

        $record->loadMissing('permissions');
        $assignedIds = $record->permissions->pluck('id')->all();
        $assignedSet = array_flip($assignedIds);

        $data = [];
        foreach (self::getPermisosAgrupadosPorModelo() as $modelo => $permisos) {
            $selected = $permisos
                ->filter(fn (array $p): bool => isset($assignedSet[$p['id']]))
                ->pluck('id')
                ->values()
                ->all();
            $data['permisos_'.$modelo] = $selected;
        }

        return $data;
    }

    public static function formatActionLabel(string $action): string
    {
        $actions = [
            'ViewAny' => 'Ver listado',
            'View' => 'Ver detalle',
            'Create' => 'Crear',
            'Update' => 'Editar',
            'Delete' => 'Eliminar',
            'DeleteAny' => 'Eliminar masivo',
            'Restore' => 'Restaurar',
            'RestoreAny' => 'Restaurar masivo',
            'ForceDelete' => 'Eliminar permanente',
            'ForceDeleteAny' => 'Eliminar permanente masivo',
            'Replicate' => 'Duplicar',
            'Reorder' => 'Reordenar',
            'view_any' => 'Ver listado',
            'view' => 'Ver detalle',
            'create' => 'Crear',
            'update' => 'Editar',
            'delete' => 'Eliminar',
            'delete_any' => 'Eliminar masivo',
            'restore' => 'Restaurar',
            'restore_any' => 'Restaurar masivo',
            'force_delete' => 'Eliminar permanente',
            'force_delete_any' => 'Eliminar permanente masivo',
            'replicate' => 'Duplicar',
            'reorder' => 'Reordenar',
        ];

        return $actions[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    /**
     * Etiqueta legible para el encabezado de sección.
     */
    public static function sectionLabelForModel(string $modelo): string
    {
        $labels = [
            'Colaborador' => 'Colaboradores',
            'BajaColaborador' => 'Bajas de colaborador',
            'Departamento' => 'Departamentos',
            'DepartamentoGeneral' => 'Departamentos generales',
            'Area' => 'Áreas',
            'AreaGeneral' => 'Áreas generales',
            'Puesto' => 'Puestos',
            'PuestoGeneral' => 'Puestos generales',
            'Region' => 'Regiones',
            'Ubicacion' => 'Ubicaciones',
            'CentroPago' => 'Centros de pago',
            'SpatieRole' => 'Roles',
            'User' => 'Usuarios',
            'DocumentoCorporativo' => 'Documentos corporativos (destinatarios)',
        ];

        return $labels[$modelo] ?? $modelo;
    }

    /**
     * Ej.: "ViewAny:Colaborador" → "Ver listado: Colaborador"
     */
    public static function formatPermissionLabel(string $permission): string
    {
        if (! str_contains($permission, ':')) {
            return $permission;
        }

        [$action, $resource] = explode(':', $permission, 2);

        return self::formatActionLabel($action).' ('.$resource.')';
    }
}
