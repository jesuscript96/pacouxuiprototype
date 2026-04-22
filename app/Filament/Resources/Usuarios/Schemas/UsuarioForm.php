<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Schemas;

use App\Models\Empresa;
use App\Models\SpatieRole;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Spatie\Permission\Guard;

class UsuarioForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user instanceof User && $user->hasRole(Utils::getSuperAdminName());

        return $schema
            ->components([
                Section::make('Información personal')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('apellido_paterno')
                            ->label('Apellido paterno')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('apellido_materno')
                            ->label('Apellido materno')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->label('Contraseña')
                            ->required(fn (?User $record): bool => $record === null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText(fn (?User $record): string => $record
                                ? 'Dejar en blanco para mantener la actual. Mínimo 8 caracteres.'
                                : 'Mínimo 8 caracteres.'),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->label('Confirmar contraseña')
                            ->required(fn (?User $record): bool => $record === null)
                            ->same('password')
                            ->dehydrated(false),
                        TextInput::make('telefono')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('celular')
                            ->tel()
                            ->maxLength(20),
                        FileUpload::make('imagen')
                            ->label('Foto')
                            ->image()
                            ->maxSize(2048)
                            ->directory('usuarios')
                            ->visibility('public'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Tipo de usuario')
                    ->schema([
                        Grid::make(1)
                            ->columnSpanFull()
                            ->extraAttributes([
                                // Centra el wrapper del campo (label + checkboxes): mx-auto + ancho máximo.
                                'class' => 'w-full py-6 sm:py-8 [&>div]:mx-auto [&>div]:w-full [&>div]:max-w-xl sm:[&>div]:max-w-2xl',
                            ])
                            ->schema([
                                CheckboxList::make('tipo')
                                    ->label('Tipo de usuario')
                                    ->validationAttribute('tipo')
                                    ->options([
                                        'administrador' => 'Administrador',
                                        'cliente' => 'Cliente',
                                        'colaborador' => 'Colaborador',
                                    ])
                                    ->columns(3)
                                    ->gridDirection('row')
                                    // Contenedor raíz del componente (envuelve label + opciones).
                                    ->extraAlpineAttributes([
                                        'class' => 'mx-auto w-full max-w-xl sm:max-w-2xl',
                                    ])
                                    ->extraAttributes([
                                        'class' => 'justify-items-center gap-x-6 gap-y-3 sm:gap-x-10',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                                        $rolesTipo = is_array($state) ? $state : [];
                                        if (! self::formStateIncluyeTipo($rolesTipo, 'cliente') && ! self::formStateIncluyeTipo($rolesTipo, 'colaborador')) {
                                            $set('empresas', []);
                                            $set('empresa_id', null);
                                            $set('roles', self::filterRolesToValidOptions($get('roles'), $get));
                                        } else {
                                            $set('roles', self::filterRolesToValidOptions($get('roles'), $get));
                                        }
                                    }),
                            ]),
                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('empresa_id')
                                    ->label('Empresa principal')
                                    ->options(fn (): array => self::empresasOptionsForUsuarioForm($isSuperAdmin, $user))
                                    ->default(function ($record) use ($user, $isSuperAdmin): ?int {
                                        if ($record !== null && isset($record->empresa_id) && $record->empresa_id !== null) {
                                            return (int) $record->empresa_id;
                                        }
                                        if (! $isSuperAdmin && $user instanceof User && $user->empresa_id) {
                                            return (int) $user->empresa_id;
                                        }

                                        return null;
                                    })
                                    ->required(fn (Get $get): bool => self::formStateIncluyeTipo($get('tipo'), 'cliente') || self::formStateIncluyeTipo($get('tipo'), 'colaborador'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        $set('roles', self::filterRolesToValidOptions($get('roles'), $get));
                                    })
                                    ->helperText('Empresa principal del usuario. Define el acceso a la App móvil.'),
                                Select::make('empresas')
                                    ->label('Empresas asignadas')
                                    // Sin ->relationship(): el sync del pivote empresa_user lo aplica UsuarioService al guardar.
                                    ->options(fn (): array => self::empresasOptionsForUsuarioForm($isSuperAdmin, $user))
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                                        $set('roles', self::filterRolesToValidOptions($get('roles'), $get));
                                    })
                                    ->helperText('Empresas adicionales con acceso al panel Cliente. La empresa principal se incluye sola en el acceso al guardar.'),
                            ])
                            ->visible(fn (Get $get): bool => self::formStateIncluyeTipo($get('tipo'), 'cliente') || self::formStateIncluyeTipo($get('tipo'), 'colaborador')),
                    ])
                    ->columnSpanFull(),

                Section::make('Datos laborales')
                    ->description('Solo aplica para usuarios tipo colaborador.')
                    ->schema([
                        DatePicker::make('fecha_ingreso')
                            ->label('Fecha de ingreso')
                            ->native(false),
                        Select::make('periodicidad_pago')
                            ->label('Periodicidad de pago')
                            ->options([
                                'SEMANAL' => 'Semanal',
                                'CATORCENAL' => 'Catorcenal',
                                'QUINCENAL' => 'Quincenal',
                                'MENSUAL' => 'Mensual',
                            ])
                            ->nullable(),
                        TextInput::make('dia_periodicidad')
                            ->label('Día de periodicidad')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->nullable(),
                        TextInput::make('salario_bruto')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->nullable(),
                        TextInput::make('salario_neto')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->nullable(),
                        TextInput::make('salario_diario')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->nullable(),
                        TextInput::make('salario_diario_integrado')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->nullable(),
                        TextInput::make('monto_maximo')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->nullable(),
                        TextInput::make('dias_vacaciones_legales')
                            ->label('Días vacaciones legales')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365)
                            ->default(0)
                            ->dehydrateStateUsing(fn ($state): int => (int) ($state ?? 0)),
                        TextInput::make('dias_vacaciones_empresa')
                            ->label('Días vacaciones empresa')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365)
                            ->default(0)
                            ->dehydrateStateUsing(fn ($state): int => (int) ($state ?? 0)),
                        DatePicker::make('fecha_registro_imss')
                            ->label('Fecha registro IMSS')
                            ->native(false)
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => self::formStateIncluyeTipo($get('tipo'), 'colaborador'))
                    ->columnSpanFull(),

                Section::make('Configuración de administrador')
                    ->schema([
                        Toggle::make('ver_reportes')
                            ->label('Ver reportes')
                            ->helperText(function (Get $get, ?User $record): string {
                                $empresaId = self::firstEmpresaIdForAdmin($get);
                                if (! $empresaId) {
                                    return 'Selecciona una empresa para ver el límite de reportes.';
                                }
                                $empresa = Empresa::find($empresaId);
                                if (! $empresa) {
                                    return '';
                                }
                                $limite = (int) $empresa->num_usuarios_reportes;
                                $usados = User::query()
                                    ->whereJsonContains('tipo', 'cliente')
                                    ->whereHas('empresas', fn ($q) => $q->where('empresas.id', $empresaId))
                                    ->where('ver_reportes', true)
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->count();

                                return "Reportes usados: {$usados}/{$limite}";
                            })
                            ->live(),
                        TextInput::make('usuario_tableau')
                            ->label('Usuario Tableau')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => self::formStateIncluyeTipo($get('tipo'), 'cliente') && self::empresaTieneAnaliticas(self::firstEmpresaIdForAdmin($get))),
                        Toggle::make('recibir_boletin')
                            ->label('Recibir newsletter')
                            ->visible(fn (Get $get): bool => self::formStateIncluyeTipo($get('tipo'), 'cliente') && self::empresaEnviaBoletin(self::firstEmpresaIdForAdmin($get))),
                    ])
                    ->visible(fn (Get $get): bool => self::formStateIncluyeTipo($get('tipo'), 'cliente'))
                    ->columnSpanFull(),

                Section::make('Roles')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->options(fn (Get $get): array => self::rolesOptionsForEmpresaIds(self::empresaIdsForRolFiltro($get)))
                            ->searchable()
                            ->preload()
                            ->helperText('Los roles disponibles dependen de las empresas seleccionadas (más roles globales sin empresa).')
                            ->visible(fn (Get $get): bool => self::formStateIncluyeAlguno($get('tipo'), ['administrador', 'cliente'])),
                    ])
                    ->columnSpanFull(),

                Section::make('Inicio de sesión empresarial')
                    ->schema([
                        TextInput::make('workos_id')->disabled()->dehydrated(false),
                        TextInput::make('avatar')->disabled()->dehydrated(false)->url(),
                        DateTimePicker::make('email_verified_at')->disabled()->dehydrated(false),
                    ])
                    ->visible(fn ($record): bool => filled($record?->workos_id))
                    ->columnSpanFull(),

                Section::make('Autenticación de dos factores')
                    ->schema([
                        Toggle::make('enable_2fa')->label('Verificación en dos pasos')->disabled()->dehydrated(false),
                        DateTimePicker::make('verified_2fa_at')->label('Verificado el')->disabled()->dehydrated(false),
                    ])
                    ->visible(fn ($record): bool => filled($record?->google2fa_secret) || (bool) $record?->enable_2fa)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Opciones de empresa para el panel Admin (misma fuente que multiselect y empresa principal).
     *
     * @return array<int|string, string>
     */
    protected static function empresasOptionsForUsuarioForm(bool $isSuperAdmin, mixed $user): array
    {
        $query = Empresa::query()->orderBy('nombre');
        if (! $isSuperAdmin && $user instanceof User && $user->empresa_id) {
            $query->where('empresas.id', $user->empresa_id);
        }

        return $query->pluck('nombre', 'id')->all();
    }

    protected static function formStateIncluyeTipo(mixed $tipoState, string $rol): bool
    {
        return is_array($tipoState) && in_array($rol, $tipoState, true);
    }

    /**
     * @param  list<string>  $roles
     */
    protected static function formStateIncluyeAlguno(mixed $tipoState, array $roles): bool
    {
        if (! is_array($tipoState)) {
            return false;
        }

        return count(array_intersect($tipoState, $roles)) > 0;
    }

    /**
     * Primera empresa del formulario (config Tableau / boletín).
     */
    protected static function firstEmpresaIdForAdmin(Get $get): ?int
    {
        $principal = $get('empresa_id');
        if ($principal !== null && $principal !== '') {
            return (int) $principal;
        }

        $empresas = $get('empresas');
        if (is_array($empresas) && count($empresas) > 0) {
            return (int) $empresas[0];
        }

        return null;
    }

    /**
     * Empresa principal + multiselect (para filtrar roles Spatie coherentes con el pivot resultante).
     *
     * @return list<int>
     */
    protected static function empresaIdsForRolFiltro(Get $get): array
    {
        $tipo = $get('tipo') ?? [];
        $ids = self::normalizeEmpresaIdsFromState($get('empresas'));
        $requiereEmpresas = is_array($tipo) && (
            in_array('cliente', $tipo, true) || in_array('colaborador', $tipo, true)
        );
        if (! $requiereEmpresas) {
            return [];
        }

        $principal = $get('empresa_id');
        if ($principal !== null && $principal !== '') {
            return array_values(array_unique(array_merge([(int) $principal], $ids)));
        }

        return $ids;
    }

    /**
     * Opciones del selector de roles según empresas seleccionadas en el formulario (más globales).
     * No depende del rol del usuario logueado: super_admin ve el mismo filtro según multiselect empresas.
     *
     * @return array<int|string, string>
     */
    /**
     * @param  list<int>  $empresaIds
     * @return array<int|string, string>
     */
    protected static function rolesOptionsForEmpresaIds(array $empresaIds): array
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return [];
        }

        $guard = Guard::getDefaultName(SpatieRole::class);

        $query = SpatieRole::withoutGlobalScopes()
            ->where('guard_name', $guard)
            ->orderBy('name');

        if ($empresaIds === []) {
            $query->whereNull('company_id');
        } else {
            $query->where(function ($q) use ($empresaIds): void {
                $q->whereIn('company_id', $empresaIds)
                    ->orWhereNull('company_id');
            });
        }

        return $query->get()->mapWithKeys(fn (SpatieRole $r): array => [$r->id => $r->display_name ?? $r->name])->all();
    }

    /**
     * @return list<int>
     */
    protected static function normalizeEmpresaIdsFromState(mixed $empresasState): array
    {
        if (is_array($empresasState)) {
            return array_values(array_map('intval', array_filter($empresasState, fn ($v): bool => $v !== null && $v !== '')));
        }
        if (is_numeric($empresasState)) {
            return [(int) $empresasState];
        }

        return [];
    }

    /**
     * Mantiene solo roles que siguen siendo opciones válidas (super_admin no filtra).
     *
     * @return list<int>
     */
    protected static function filterRolesToValidOptions(mixed $rolesState, Get $get): array
    {
        if (! is_array($rolesState)) {
            $rolesState = [];
        }
        $current = array_values(array_map('intval', array_filter($rolesState, fn ($v): bool => $v !== null && $v !== '')));

        $user = auth()->user();
        if ($user instanceof User && $user->hasRole(Utils::getSuperAdminName())) {
            return $current;
        }

        $validIds = array_map('intval', array_keys(self::rolesOptionsForEmpresaIds(self::empresaIdsForRolFiltro($get))));

        return array_values(array_intersect($current, $validIds));
    }

    protected static function empresaTieneAnaliticas(?int $empresaId): bool
    {
        if (! $empresaId) {
            return false;
        }
        $e = Empresa::find($empresaId);

        return $e && (bool) ($e->tiene_analiticas_por_ubicacion ?? false);
    }

    protected static function empresaEnviaBoletin(?int $empresaId): bool
    {
        if (! $empresaId) {
            return false;
        }
        $e = Empresa::find($empresaId);

        return $e && (bool) ($e->enviar_boletin ?? false);
    }
}
