<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens;
    use HasFactory;
    use HasPanelShield;
    use HasRoles;
    use LogsActivity;

    /**
     * Bypass global de autorización para la rama prototipo.
     * Todos los permisos devuelven true sin consultar BD.
     */
    public function can($abilities, $arguments = []): bool
    {
        return true;
    }

    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'workos_id',
        'avatar',
        'telefono',
        'celular',
        'tipo',
        'empresa_id',
        'colaborador_id',
        'imagen',
        'ver_reportes',
        'usuario_tableau',
        'recibir_boletin',
        'google2fa_secret',
        'enable_2fa',
        'numero_colaborador',
        'fecha_nacimiento',
        'genero',
        'curp',
        'rfc',
        'nss',
        'estado_civil',
        'nacionalidad',
        'direccion',
        'telefono_movil',
        'fecha_ingreso',
        'fecha_registro_imss',
        'salario_bruto',
        'salario_neto',
        'salario_diario',
        'salario_diario_integrado',
        'monto_maximo',
        'periodicidad_pago',
        'dia_periodicidad',
        'dias_vacaciones_legales',
        'dias_vacaciones_empresa',
        'hora_entrada',
        'hora_salida',
        'hora_entrada_comida',
        'hora_salida_comida',
        'hora_inicio_horas_extra',
        'hora_fin_horas_extra',
        'comentario_adicional',
        'codigo_jefe',
        'nombre_empresa_pago',
        'verificado',
        'verificacion_carga_masiva',
        'tiene_identificacion',
        'fecha_verificacion_movil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_2fa_at' => 'datetime',
            'password' => 'hashed',
            'enable_2fa' => 'boolean',
            'ver_reportes' => 'boolean',
            'recibir_boletin' => 'boolean',
            'tipo' => 'array',
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'fecha_registro_imss' => 'date',
            'fecha_verificacion_movil' => 'datetime',
            'salario_bruto' => 'decimal:2',
            'salario_neto' => 'decimal:2',
            'salario_diario' => 'decimal:2',
            'salario_diario_integrado' => 'decimal:2',
            'monto_maximo' => 'decimal:2',
            'verificado' => 'boolean',
            'verificacion_carga_masiva' => 'boolean',
            'tiene_identificacion' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept([
                'password',
                'remember_token',
                'google2fa_secret',
                'created_at',
                'updated_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Tipos de usuario almacenados en users.tipo (JSON). Distinto de roles Spatie.
     */
    public function tieneRol(string $rol): bool
    {
        return in_array($rol, $this->tipo ?? [], true);
    }

    public function agregarRol(string $rol): void
    {
        $roles = $this->tipo ?? [];
        if (! in_array($rol, $roles, true)) {
            $roles[] = $rol;
            $this->tipo = $roles;
            $this->save();
        }
    }

    public function quitarRol(string $rol): void
    {
        $this->tipo = array_values(array_diff($this->tipo ?? [], [$rol]));
        $this->save();
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeConRol(Builder $query, string $rol): Builder
    {
        return $query->whereJsonContains('tipo', $rol);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeColaboradores(Builder $query): Builder
    {
        return $query->whereJsonContains('tipo', 'colaborador');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeClientes(Builder $query): Builder
    {
        return $query->whereJsonContains('tipo', 'cliente');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAdministradores(Builder $query): Builder
    {
        return $query->whereJsonContains('tipo', 'administrador');
    }

    /**
     * Usuarios con ficha en `colaboradores` para la empresa (no se usa pivot empresa_user para colaborador).
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeColaboradoresDeEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->whereHas('colaborador', fn (Builder $q) => $q->where('empresa_id', $empresaId));
    }

    /**
     * Usuarios vinculados a la empresa por empresa_id o pivot empresa_user (panel admin / legado HighEmployee users).
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopePertenecenAEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where(function (Builder $q) use ($empresaId): void {
            $q->where('empresa_id', $empresaId)
                ->orWhereHas('empresas', fn (Builder $q2) => $q2->where('empresas.id', $empresaId));
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_user', 'user_id', 'empresa_id')
            ->withTimestamps();
    }

    public function hasEmpresasAsignadas(): bool
    {
        if ($this->empresa_id !== null) {
            return true;
        }

        return $this->empresas()->exists();
    }

    public function perteneceAEmpresa(int $empresaId): bool
    {
        if ((int) $this->empresa_id === $empresaId) {
            return true;
        }

        return $this->empresas()->where('empresa_id', $empresaId)->exists();
    }

    /**
     * @return array<int, int>
     */
    public function getEmpresaIdsAttribute(): array
    {
        $ids = $this->empresa_id ? [$this->empresa_id] : [];
        $pivotIds = $this->empresas()->get()->pluck('id')->toArray();

        return array_values(array_unique(array_merge($ids, $pivotIds)));
    }

    public function beneficiarios(): HasMany
    {
        return $this->hasMany(BeneficiarioColaborador::class, 'user_id');
    }

    /**
     * Filtros de colaboradores guardados en admin (gestión de empleados).
     *
     * @return HasMany<FiltroColaborador, $this>
     */
    public function filtrosColaborador(): HasMany
    {
        return $this->hasMany(FiltroColaborador::class, 'user_id');
    }

    public function cuentasNomina(): HasMany
    {
        return $this->hasMany(CuentaNomina::class, 'user_id');
    }

    public function historialUbicaciones(): HasMany
    {
        return $this->hasMany(HistorialUbicacion::class, 'user_id');
    }

    public function historialDepartamentos(): HasMany
    {
        return $this->hasMany(HistorialDepartamento::class, 'user_id');
    }

    public function historialAreas(): HasMany
    {
        return $this->hasMany(HistorialArea::class, 'user_id');
    }

    public function historialPuestos(): HasMany
    {
        return $this->hasMany(HistorialPuesto::class, 'user_id');
    }

    public function historialRegiones(): HasMany
    {
        return $this->hasMany(HistorialRegion::class, 'user_id');
    }

    public function historialRazonesSociales(): HasMany
    {
        return $this->hasMany(HistorialRazonSocial::class, 'user_id');
    }

    public function historialPeriodicidadesPago(): HasMany
    {
        return $this->hasMany(HistorialPeriodicidadPago::class, 'user_id');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'colaborador_producto', 'user_id', 'producto_id')
            ->withPivot(['estado', 'razon', 'tipo_cambio', 'colaborador_id'])
            ->withTimestamps();
    }

    public function legacyRoles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'user_id', 'rol_id')->withTimestamps();
    }

    /**
     * Roles de Shield disponibles para este usuario (por empresa + globales).
     */
    public function rolesDisponibles(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->hasRole('super_admin')) {
            return SpatieRole::withoutGlobalScopes()->get();
        }

        return SpatieRole::forCompany($this->empresa_id)->get();
    }

    public function getCurrentRolAttribute(): ?SpatieRole
    {
        if (session()->has('current_role_id')) {
            return SpatieRole::find(session('current_role_id'));
        }

        $rol = $this->roles()->first();
        if ($rol) {
            session(['current_role_id' => $rol->id]);
        }

        return $rol;
    }

    public function setCurrentRol(SpatieRole $rol): bool
    {
        if ($this->roles->contains($rol)) {
            session(['current_role_id' => $rol->id]);

            return true;
        }

        return false;
    }

    public function getTenants(Panel $panel): Collection
    {
        if ($panel->getId() !== 'cliente') {
            return collect();
        }

        $tenants = $this->empresas()->get();

        if ($this->empresa_id !== null) {
            $principal = Empresa::query()->find($this->empresa_id);
            if ($principal instanceof Empresa && ! $tenants->contains(fn (Empresa $e): bool => (int) $e->getKey() === (int) $principal->getKey())) {
                $tenants = $tenants->prepend($principal);
            }
        }

        return $tenants->unique('id')->values();
    }

    /**
     * Acceso al panel Filament «admin» y a analíticos Tableau sin tenant (legacy v2: hasRoles('admin')).
     */
    public function puedeAccederAlPanelAdminPaco(): bool
    {
        return $this->tieneRol('administrador') || $this->tieneRol('user') || $this->hasRole('super_admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // BL: Demo enseñable — mismo usuario puede entrar por /admin/login o /cliente/login y caer en panel cliente.
        if ($this->email === 'cliente@tecben.com') {
            return match ($panel->getId()) {
                'admin', 'cliente' => true,
                default => false,
            };
        }

        return match ($panel->getId()) {
            'cliente' => $this->tieneRol('cliente') && $this->hasEmpresasAsignadas(),
            // BL: `user` = cuenta WorkOS del panel Admin (super admins); `administrador` = catálogo legacy.
            'admin' => $this->tieneRol('administrador') || $this->tieneRol('user') || $this->hasRole('super_admin'),
            default => false,
        };
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Empresa) {
            return false;
        }

        return $this->perteneceAEmpresa((int) $tenant->getKey());
    }

    public function getNombreCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->name ?? '',
            $this->apellido_paterno ?? '',
            $this->apellido_materno ?? '',
        ]);

        return implode(' ', $partes) ?: (string) ($this->numero_colaborador ?? '');
    }

    /**
     * @return BelongsToMany<NotificacionPush, $this>
     */
    public function notificacionesPushRecibidas(): BelongsToMany
    {
        return $this->belongsToMany(
            NotificacionPush::class,
            'notificacion_push_destinatarios',
            'user_id',
            'notificacion_push_id'
        )->withPivot(['estado_lectura', 'leida_at', 'enviado', 'onesignal_player_id', 'enviado_at'])
            ->withTimestamps();
    }
}
