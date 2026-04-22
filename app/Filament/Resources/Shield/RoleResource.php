<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shield;

use App\Helpers\RoleHelper;
use App\Models\Empresa;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as ShieldRoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;

class RoleResource extends ShieldRoleResource
{
    /**
     * Relación en SpatieRole que vincula al tenant (Empresa).
     * Necesario cuando el recurso se usa en el panel Cliente con tenant(Empresa::class).
     */
    protected static ?string $tenantOwnershipRelationshipName = 'company';

    public static function getNavigationGroup(): ?string
    {
        return 'Catálogos Admin';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationIcon(): \BackedEnum|string|null
    {
        return null;
    }

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->hasRole(Utils::getSuperAdminName());

        $mainSchema = [
            Section::make()
                ->schema([
                    Hidden::make('name')
                        ->required()
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule): Unique => $rule
                        ),

                    TextInput::make('role_name_edit')
                        ->label(__('filament-shield::filament-shield.field.name'))
                        ->required()
                        ->maxLength(fn (Get $get): int => static::maxLengthForRoleNameInput($get('company_id')))
                        ->prefix(fn (Get $get): ?string => static::namePrefixForForm($get('company_id')))
                        ->helperText(fn (Get $get): string => static::roleNameHelperText($get('company_id')))
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            static::syncRoleNameFromEditor($set, $get);
                        }),

                    TextInput::make('display_name')
                        ->label('Nombre para mostrar')
                        ->required()
                        ->maxLength(fn (Get $get): int => static::maxLengthForDisplayNameSuffix($get('company_id')))
                        ->prefix(fn (Get $get): ?string => static::displayNamePrefixForForm($get('company_id')))
                        ->helperText(fn (Get $get): string => static::displayNameHelperText($get('company_id')))
                        ->live(debounce: '500ms'),

                    TextInput::make('description')
                        ->label('Descripción')
                        ->required()
                        ->maxLength(255),

                    Hidden::make('guard_name')
                        ->default(Utils::getFilamentAuthGuard()),
                ])
                ->columns(['sm' => 2, 'lg' => 3])
                ->columnSpanFull(),
        ];

        if ($isSuperAdmin) {
            $mainSchema[] = Section::make('Asignación a empresa')
                ->schema([
                    Toggle::make('is_asignable')
                        ->label('Asignar a empresa')
                        ->helperText('Define si el rol pertenece a una empresa o no')
                        ->default(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                            if (! $state) {
                                // BL: El identificador guardado es el nombre completo; al pasar a rol global
                                // el editor debe mostrar todo el string, no solo el sufijo.
                                $set('role_name_edit', (string) ($get('name') ?? ''));
                                $set('company_id', null);
                            }
                            static::syncRoleNameFromEditor($set, $get);
                        }),

                    Select::make('company_id')
                        ->label('Empresa')
                        ->options(fn (): array => Empresa::query()->pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(fn (callable $get): bool => (bool) $get('is_asignable'))
                        ->disabled(fn (callable $get): bool => ! (bool) $get('is_asignable'))
                        ->helperText('Selecciona la empresa a la que pertenece este rol')
                        ->rules(['nullable', 'exists:empresas,id'])
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            static::syncRoleNameFromEditor($set, $get);
                        }),
                ])
                ->visible(fn (): bool => $isSuperAdmin)
                ->columnSpanFull();
        } else {
            $mainSchema[] = Hidden::make('company_id')
                ->default(fn (): ?int => $user?->empresa_id);
        }

        $mainSchema[] = static::getSelectAllFormComponent();

        $isSuperAdmin = $user && $user->hasRole(Utils::getSuperAdminName());

        // Permisos panel Admin (roles globales): recurso actual de Shield (panel Admin).
        $permisosAdmin = static::getShieldFormComponents()
            ->visible(fn (callable $get): bool => $isSuperAdmin && ! (bool) $get('is_asignable'));

        // Permisos panel Cliente (roles por empresa): solo recursos del panel Cliente, sin Role.
        $permisosCliente = static::getClientePanelPermissionsTabs()
            ->visible(fn (callable $get): bool => $isSuperAdmin && (bool) $get('is_asignable'));

        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema($mainSchema)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                $permisosAdmin,
                $permisosCliente,
            ]);
    }

    public static function companyIdToInt(mixed $companyId): ?int
    {
        if (! filled($companyId)) {
            return null;
        }

        return (int) $companyId;
    }

    /**
     * Roles con empresa: el nombre técnico se guarda como slug(empresa.nombre) + sufijo editable.
     * BL: Solo depende de company_id, no de is_asignable, para que el guardado no falle si el toggle
     * no llega en el estado del formulario.
     */
    public static function shouldUsePrefixedRoleName(mixed $companyId): bool
    {
        return filled($companyId);
    }

    public static function namePrefixForEmpresaId(?int $empresaId): string
    {
        if ($empresaId === null) {
            return '';
        }

        $empresa = Empresa::query()->find($empresaId);
        if ($empresa === null) {
            return '';
        }

        return Str::slug($empresa->nombre, '_').'_';
    }

    public static function fullRoleName(?int $empresaId, string $suffix): string
    {
        return static::namePrefixForEmpresaId($empresaId).trim($suffix);
    }

    public static function suffixFromStoredName(?int $empresaId, string $fullName): string
    {
        $prefix = static::namePrefixForEmpresaId($empresaId);
        if ($prefix !== '' && str_starts_with($fullName, $prefix)) {
            return substr($fullName, strlen($prefix));
        }

        return $fullName;
    }

    public static function roleNameInputForFill(array $data): string
    {
        $fullName = (string) ($data['name'] ?? '');
        $companyId = $data['company_id'] ?? null;

        if (static::shouldUsePrefixedRoleName($companyId)) {
            return static::suffixFromStoredName(static::companyIdToInt($companyId), $fullName);
        }

        return $fullName;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mergeRoleNameFromFormData(array $data): array
    {
        $input = trim((string) ($data['role_name_edit'] ?? ''));
        if (static::shouldUsePrefixedRoleName($data['company_id'] ?? null)) {
            $data['name'] = static::fullRoleName(static::companyIdToInt($data['company_id'] ?? null), $input);
        } else {
            $data['name'] = $input;
        }
        unset($data['role_name_edit']);

        return $data;
    }

    /**
     * BL: Sin trim() en cada pulsación para evitar saltos al escribir; el trim del sufijo aplica al guardar.
     */
    public static function syncRoleNameFromEditor(Set $set, Get $get): void
    {
        $input = (string) ($get('role_name_edit') ?? '');
        if (static::shouldUsePrefixedRoleName($get('company_id'))) {
            $set('name', static::namePrefixForEmpresaId(static::companyIdToInt($get('company_id'))).$input);

            return;
        }
        $set('name', $input);
    }

    public static function namePrefixForForm(mixed $companyId): ?string
    {
        if (! static::shouldUsePrefixedRoleName($companyId)) {
            return null;
        }

        $prefix = static::namePrefixForEmpresaId(static::companyIdToInt($companyId));

        return $prefix === '' ? null : $prefix;
    }

    public static function maxLengthForRoleNameInput(mixed $companyId): int
    {
        if (! static::shouldUsePrefixedRoleName($companyId)) {
            return 255;
        }

        $prefixLen = strlen(static::namePrefixForEmpresaId(static::companyIdToInt($companyId)));

        return max(1, 255 - $prefixLen);
    }

    public static function roleNameHelperText(mixed $companyId): string
    {
        if (static::shouldUsePrefixedRoleName($companyId)) {
            return 'Escribe solo la parte final. El prefijo de la empresa se agrega automáticamente.';
        }

        return 'Nombre técnico del rol (ej: gerente_ventas)';
    }

    public static function displayNamePrefixForForm(mixed $companyId): ?string
    {
        if (! static::shouldUsePrefixedRoleName($companyId)) {
            return null;
        }

        $cid = static::companyIdToInt($companyId);
        if ($cid === null) {
            return null;
        }

        $empresa = Empresa::query()->find($cid);
        if ($empresa === null) {
            return null;
        }

        $p = RoleHelper::displayNamePrefixForEmpresa($empresa);

        return $p === '' ? null : $p;
    }

    public static function displayNameHelperText(mixed $companyId): string
    {
        if (static::shouldUsePrefixedRoleName($companyId)) {
            return 'Escribe solo el nombre del rol. El prefijo de la empresa se agrega automáticamente.';
        }

        return 'Texto que verán los usuarios (ej: Gerente de Paco)';
    }

    public static function maxLengthForDisplayNameSuffix(mixed $companyId): int
    {
        $prefix = static::displayNamePrefixForForm($companyId);

        if ($prefix === null) {
            return 255;
        }

        return max(1, 255 - strlen($prefix));
    }

    /**
     * Al editar: el formulario muestra solo el sufijo; en BD puede estar el valor completo con prefijo.
     */
    public static function displayNameSuffixForEdit(?int $empresaId, string $storedDisplayName): string
    {
        if ($empresaId === null) {
            return $storedDisplayName;
        }

        $empresa = Empresa::query()->find($empresaId);
        if ($empresa === null) {
            return $storedDisplayName;
        }

        $prefix = RoleHelper::displayNamePrefixForEmpresa($empresa);
        if ($prefix !== '' && str_starts_with($storedDisplayName, $prefix)) {
            return substr($storedDisplayName, strlen($prefix));
        }

        return $storedDisplayName;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mergeDisplayNameForStorage(array $data): array
    {
        if (! static::shouldUsePrefixedRoleName($data['company_id'] ?? null)) {
            return $data;
        }

        $cid = static::companyIdToInt($data['company_id'] ?? null);
        if ($cid === null) {
            return $data;
        }

        $empresa = Empresa::query()->find($cid);
        if ($empresa === null) {
            return $data;
        }

        $prefix = RoleHelper::displayNamePrefixForEmpresa($empresa);
        $suffix = (string) ($data['display_name'] ?? '');
        if (str_starts_with($suffix, $prefix)) {
            $suffix = substr($suffix, strlen($prefix));
        }
        $suffix = trim($suffix);
        $data['display_name'] = $prefix.$suffix;

        return $data;
    }

    /**
     * Recursos del panel Cliente (para toggle "Asignar a empresa").
     * Excluye RoleResource. Escalable: nuevos recursos en Cliente aparecen solos.
     */
    public static function getClientePanelPermissionNames(): array
    {
        $entities = static::getClientePanelResourcesTransformed();

        return collect($entities)
            ->pluck('permissions')
            ->flatMap(fn (array $p): array => array_keys($p))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Recursos y páginas del panel Cliente (para toggle "Asignar a empresa").
     * Excluye RoleResource. Escalable: nuevos recursos/páginas aparecen solos.
     * Las Pages que declaren getPermissionModel() se incluyen automáticamente.
     */
    protected static function getClientePanelResourcesTransformed(): array
    {
        $panel = Filament::getPanel('cliente');
        if (! $panel) {
            return [];
        }

        $config = Utils::getConfig();
        $metodosPorDefecto = $config->policies->methods ?? ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'forceDeleteAny', 'restoreAny', 'replicate', 'reorder'];
        $separator = $config->permissions->separator ?? ':';
        /** @var array<class-string, array<int, string>> $gestionPorRecurso */
        $gestionPorRecurso = config('filament-shield.resources.manage', []);

        $out = [];
        $processedModels = [];

        $resources = collect($panel->getResources())
            ->filter(fn (string $resource): bool => $resource !== self::class && $resource !== \BezhanSalleh\FilamentShield\Resources\Roles\RoleResource::class)
            ->values();
        foreach ($resources as $resource) {
            if (! method_exists($resource, 'getModel')) {
                continue;
            }
            $modelFqcn = $resource::getModel();
            $model = class_basename($modelFqcn);
            $subject = Str::of($model)->studly()->toString();
            $processedModels[] = $subject;

            $permissions = [];
            $metodosRecurso = $gestionPorRecurso[$resource] ?? $metodosPorDefecto;
            foreach ($metodosRecurso as $method) {
                $affix = Str::of($method)->studly()->toString();
                $key = $affix.$separator.$subject;
                $label = FilamentShield::getAffixLabel($method) ?? $affix;
                $permissions[$key] = $label;
            }
            $out[$resource] = [
                'resourceFqcn' => $resource,
                'model' => $model,
                'modelFqcn' => $modelFqcn,
                'permissions' => $permissions,
            ];
        }

        $pages = collect($panel->getPages())
            ->filter(fn (string $page): bool => method_exists($page, 'getPermissionModel'));

        foreach ($pages as $page) {
            $modelFqcn = $page::getPermissionModel();
            if (! $modelFqcn) {
                continue;
            }
            $model = class_basename($modelFqcn);
            $subject = Str::of($model)->studly()->toString();

            if (in_array($subject, $processedModels, true)) {
                continue;
            }
            $processedModels[] = $subject;

            $permissions = [];
            foreach ($metodosPorDefecto as $method) {
                $affix = Str::of($method)->studly()->toString();
                $key = $affix.$separator.$subject;
                $label = FilamentShield::getAffixLabel($method) ?? $affix;
                $permissions[$key] = $label;
            }
            $out[$page] = [
                'resourceFqcn' => $page,
                'model' => $model,
                'modelFqcn' => $modelFqcn,
                'permissions' => $permissions,
            ];
        }

        return $out;
    }

    /**
     * Pestaña de permisos solo con recursos del panel Cliente (sin Role, sin pages/widgets/custom).
     */
    protected static function getClientePanelPermissionsTabs(): Tabs
    {
        $entities = static::getClientePanelResourcesTransformed();
        if ($entities === []) {
            return Tabs::make('PermissionsCliente')
                ->contained()
                ->tabs([])
                ->columnSpan('full');
        }

        $sections = [];
        foreach ($entities as $entity) {
            $sectionLabel = $entity['model'];
            $modelFqcn = $entity['modelFqcn'];
            $options = $entity['permissions'];

            $sections[] = Section::make($sectionLabel)
                ->description(fn (): HtmlString => new HtmlString('<span style="word-break: break-word;">'.class_basename($modelFqcn).'</span>'))
                ->compact()
                ->schema([
                    CheckboxList::make($entity['resourceFqcn'])
                        ->hiddenLabel()
                        ->options(fn (): array => $options)
                        ->searchable(false)
                        ->live()
                        ->afterStateHydrated(function (\Filament\Schemas\Components\Component $component, string $operation, ?Model $record, Set $set) use ($options): void {
                            static::setPermissionStateForRecordPermissions(
                                component: $component,
                                operation: $operation,
                                permissions: $options,
                                record: $record
                            );
                            static::toggleSelectAllViaEntities($component->getLivewire(), $set);
                        })
                        ->afterStateUpdated(function (Livewire $livewire, Set $set): void {
                            static::toggleSelectAllViaEntities($livewire, $set);
                        })
                        ->dehydrated(fn ($state): bool => ! blank($state))
                        ->bulkToggleable()
                        ->gridDirection('row')
                        ->columns(static::shield()->getResourceCheckboxListColumns())
                        ->columnSpan(static::shield()->getResourceCheckboxListColumnSpan()),
                ])
                ->columnSpan(static::shield()->getSectionColumnSpan())
                ->collapsible();
        }

        $badgeCount = (int) collect($entities)->sum(fn (array $e): int => count($e['permissions']));

        return Tabs::make('PermissionsCliente')
            ->contained()
            ->tabs([
                Tab::make('resources_cliente')
                    ->label(__('filament-shield::filament-shield.resources').' (panel Cliente)')
                    ->badge($badgeCount)
                    ->schema([
                        Grid::make()
                            ->schema($sections)
                            ->columns(static::shield()->getGridColumns()),
                    ]),
            ])
            ->columnSpan('full');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Nombre para mostrar')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->placeholder('—'),
                TextColumn::make('company.nombre')
                    ->label('Empresa')
                    ->default('Global')
                    ->placeholder('Global'),
                IconColumn::make('is_global')
                    ->label('Tipo')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-building-office')
                    ->getStateUsing(fn ($record): bool => is_null($record->company_id)),
                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label('Panel'),
                TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->color('primary'),
                TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'nombre')
                    ->placeholder('Todas las empresas'),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\DeleteAction::make()
                        ->hidden(fn (\App\Models\SpatieRole $record): bool => $record->tieneUsuariosAsignados()),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->toolbarActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ])
            ->paginated(true);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user && ! $user->hasRole(Utils::getSuperAdminName()) && $user->empresa_id) {
            $query->where(function (Builder $q) use ($user): void {
                $q->where('company_id', $user->empresa_id)
                    ->orWhereNull('company_id');
            });
        }

        return $query;
    }
}
