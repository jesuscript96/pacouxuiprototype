<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @use HasFactory<\Database\Factories\ColaboradorFactory>
 */
class Colaborador extends Model
{
    use HasFactory;
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'colaboradores';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'telefono_movil',
        'numero_colaborador',
        'fecha_nacimiento',
        'genero',
        'curp',
        'rfc',
        'nss',
        'fecha_ingreso',
        'fecha_registro_imss',
        'estado_civil',
        'nacionalidad',
        'direccion',
        'salario_bruto',
        'salario_neto',
        'salario_diario',
        'salario_diario_integrado',
        'salario_variable',
        'monto_maximo',
        'periodicidad_pago',
        'dia_periodicidad',
        'dias_vacaciones_anuales',
        'dias_vacaciones_restantes',
        'hora_entrada',
        'hora_salida',
        'hora_entrada_comida',
        'hora_salida_comida',
        'hora_entrada_extra',
        'hora_salida_extra',
        'comentario_adicional',
        'codigo_jefe',
        'verificado',
        'verificacion_carga_masiva',
        'tiene_identificacion',
        'fecha_verificacion_movil',
        'ubicacion_id',
        'departamento_id',
        'area_id',
        'puesto_id',
        'region_id',
        'centro_pago_id',
        'razon_social_id',
        'nombre_empresa_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'fecha_registro_imss' => 'date',
            'fecha_verificacion_movil' => 'datetime',
            'salario_bruto' => 'decimal:2',
            'salario_neto' => 'decimal:2',
            'salario_diario' => 'decimal:2',
            'salario_diario_integrado' => 'decimal:2',
            'salario_variable' => 'decimal:2',
            'monto_maximo' => 'decimal:2',
            'verificado' => 'boolean',
            'verificacion_carga_masiva' => 'boolean',
            'tiene_identificacion' => 'boolean',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'colaborador_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    /**
     * Códigos de autorización por nivel (equivalente legacy `bosses` ligado a `high_employee`).
     */
    public function jefe(): HasOne
    {
        return $this->hasOne(Jefe::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function centroPago(): BelongsTo
    {
        return $this->belongsTo(CentroPago::class, 'centro_pago_id');
    }

    public function razonSocial(): BelongsTo
    {
        return $this->belongsTo(Razonsocial::class, 'razon_social_id');
    }

    public function cartasSua(): HasMany
    {
        return $this->hasMany(CartaSua::class);
    }

    public function beneficiarios(): HasMany
    {
        return $this->hasMany(BeneficiarioColaborador::class);
    }

    public function cuentasNomina(): HasMany
    {
        return $this->hasMany(CuentaNomina::class);
    }

    public function historialUbicaciones(): HasMany
    {
        return $this->hasMany(HistorialUbicacion::class);
    }

    public function historialDepartamentos(): HasMany
    {
        return $this->hasMany(HistorialDepartamento::class);
    }

    public function historialAreas(): HasMany
    {
        return $this->hasMany(HistorialArea::class);
    }

    public function historialPuestos(): HasMany
    {
        return $this->hasMany(HistorialPuesto::class);
    }

    public function historialRegiones(): HasMany
    {
        return $this->hasMany(HistorialRegion::class);
    }

    public function historialRazonesSociales(): HasMany
    {
        return $this->hasMany(HistorialRazonSocial::class);
    }

    public function historialPeriodicidadesPago(): HasMany
    {
        return $this->hasMany(HistorialPeriodicidadPago::class);
    }

    public function bajas(): HasMany
    {
        return $this->hasMany(BajaColaborador::class);
    }

    public function bajaProgramada(): HasOne
    {
        return $this->hasOne(BajaColaborador::class)
            ->where('estado', BajaColaborador::ESTADO_PROGRAMADA);
    }

    public function ultimaBaja(): HasOne
    {
        return $this->hasOne(BajaColaborador::class)->latestOfMany();
    }

    public function tieneBajaProgramada(): bool
    {
        return $this->bajaProgramada()->exists();
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'colaborador_producto')
            ->withPivot(['estado', 'razon', 'tipo_cambio'])
            ->withTimestamps();
    }

    /**
     * @param  Builder<Colaborador>  $query
     * @return Builder<Colaborador>
     */
    public function scopePorEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * @param  Builder<Colaborador>  $query
     * @return Builder<Colaborador>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * @param  Builder<Colaborador>  $query
     * @return Builder<Colaborador>
     */
    public function scopeCumpleanosMes(Builder $query): Builder
    {
        return $query->whereMonth('fecha_nacimiento', now()->month);
    }

    /**
     * @param  Builder<Colaborador>  $query
     * @return Builder<Colaborador>
     */
    public function scopeAniversariosMes(Builder $query): Builder
    {
        return $query->whereMonth('fecha_ingreso', now()->month);
    }

    public function getNombreCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->nombre ?? '',
            $this->apellido_paterno ?? '',
            $this->apellido_materno ?? '',
        ]);

        return implode(' ', $partes) ?: (string) ($this->numero_colaborador ?? '');
    }

    /**
     * BL: equivalente legacy `getCodeBossAttribute` (concatenación de ids ubicación + departamento + área + puesto, sin separadores).
     * No confundir con el atributo persistido `codigo_jefe` (formato con puntos vía {@see ColaboradorService::generarCodigoJefe}).
     */
    public function getCodigoBossAttribute(): string
    {
        return (string) ($this->ubicacion_id ?? '')
            .(string) ($this->departamento_id ?? '')
            .(string) ($this->area_id ?? '')
            .(string) ($this->puesto_id ?? '');
    }
}
