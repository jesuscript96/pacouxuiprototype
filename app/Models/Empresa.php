<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Empresa extends Model implements HasName
{
    use HasFactory;
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'nombre_contacto',
        'email_contacto',
        'telefono_contacto',
        'movil_contacto',
        'industria_id',
        'sub_industria_id',
        'email_facturacion',
        'fecha_inicio_contrato',
        'fecha_fin_contrato',
        'num_usuarios_reportes',
        'activo',
        'fecha_activacion',
        'nombre_app',
        'link_descarga_app',
        'app_android_id',
        'app_ios_id',
        'app_huawei_id',
        'color_primario',
        'color_secundario',
        'color_terciario',
        'color_cuarto',
        'logo_url',
        'foto',
        'tipo_comision',
        'comision_bisemanal',
        'comision_mensual',
        'comision_quincenal',
        'comision_semanal',
        'tiene_pagos_catorcenales',
        'fecha_proximo_pago_catorcenal',
        'tiene_sub_empresas',
        'comision_gateway',
        'transacciones_con_imss',
        'validar_cuentas_automaticamente',
        'tiene_analiticas_por_ubicacion',
        'version_android',
        'version_ios',
        'tiene_limite_de_sesiones',
        'tiene_firma_nubarium',
        'enviar_boletin',
        'permitir_encuesta_salida',
        'configuracion_app_id',
        'activar_finiquito',
        'url_finiquito',
        'domiciliación_via_api',
        'ha_firmado_nuevo_contrato',
        'vigencia_mensajes_urgentes',
        'permitir_notificaciones_felicitaciones',
        'segmento_notificaciones_felicitaciones',
        'permitir_retenciones',
        'dias_vencidos_retencion',
        'pertenece_pepeferia',
        'tipo_registro',
        'descargar_cursos',
    ];

    public function getFilamentName(): string
    {
        return "{$this->nombre}";
    }

    public function industria(): BelongsTo
    {
        return $this->belongsTo(Industria::class);
    }

    public function subindustria(): BelongsTo
    {
        return $this->belongsTo(Subindustria::class, 'sub_industria_id');
    }

    /**
     * Configuración de la app móvil (credenciales OneSignal, versiones, etc.)
     */
    public function configuracionApp(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionApp::class, 'configuracion_app_id');
    }

    /**
     * Credenciales OneSignal para esta empresa (vía configuracion_app).
     *
     * @return array{app_id: string, rest_api_key: string, android_channel_id: string|null}|null
     */
    public function getOneSignalCredentials(): ?array
    {
        $config = $this->configuracionApp;

        if (! $config || ! $config->tieneOneSignalConfigurado()) {
            return null;
        }

        return [
            'app_id' => $config->one_signal_app_id,
            'rest_api_key' => $config->one_signal_rest_api_key,
            'android_channel_id' => $config->android_channel_id,
        ];
    }

    public function razonesSociales(): BelongsToMany
    {
        return $this->belongsToMany(Razonsocial::class, 'empresas_razones_sociales', 'empresa_id', 'razon_social_id');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'empresas_productos', 'empresa_id', 'producto_id')->withPivot('desde');
    }

    public function notificacionesIncluidas()
    {
        return $this->belongsToMany(NotificacionesIncluidas::class, 'empresas_notificaciones_incluidas', 'empresa_id', 'notificacion_incluida_id');
    }

    public function comisionesRangos()
    {
        return $this->hasMany(ComisionRango::class, 'empresa_id');
    }

    public function configuracionRetencionNominas()
    {
        return $this->hasMany(ConfiguracionRetencionNomina::class, 'empresa_id');
    }

    public function centrosCostos()
    {
        return $this->belongsToMany(CentroCosto::class, 'empresas_centros_costos', 'empresa_id', 'centro_costo_id');
    }

    public function reconocimientos()
    {
        return $this->belongsToMany(Reconocmiento::class, 'empresas_reconocimientos', 'empresa_id', 'reconocimiento_id');
    }

    public function temasVozColaboradores()
    {
        return $this->belongsToMany(TemaVozColaborador::class, 'empresas_temas_voz_colaboradores', 'empresa_id', 'tema_voz_colaborador_id');
    }

    public function aliasTipoTransacciones()
    {
        return $this->hasMany(AliasTipoTransaccion::class, 'empresa_id');
    }

    public function notificacionesEstadoAnimo()
    {
        return $this->hasMany(FrecuenciaNotificaciones::class, 'empresa_id');
    }

    public function usuarios(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    /**
     * Usuarios vinculados vía pivot `empresa_user` (además de `users.empresa_id`).
     *
     * @return BelongsToMany<User, $this>
     */
    public function usuariosPivot(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empresa_user', 'empresa_id', 'user_id');
    }

    /**
     * BL: La empresa no debe eliminarse si hay cuentas que la tienen como principal o en el pivot multi-empresa.
     */
    public function tieneUsuariosAsignados(): bool
    {
        return $this->usuarios()->exists()
            || $this->usuariosPivot()->exists();
    }

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class, 'empresa_id');
    }

    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class, 'empresa_id');
    }

    public function centrosPagos(): HasMany
    {
        return $this->hasMany(CentroPago::class, 'empresa_id');
    }

    public function areasGenerales(): HasMany
    {
        return $this->hasMany(AreaGeneral::class, 'empresa_id');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'empresa_id');
    }

    public function puestosGenerales(): HasMany
    {
        return $this->hasMany(PuestoGeneral::class, 'empresa_id');
    }

    public function puestos(): HasMany
    {
        return $this->hasMany(Puesto::class, 'empresa_id');
    }

    public function regiones(): HasMany
    {
        return $this->hasMany(Region::class, 'empresa_id');
    }

    /**
     * Fichas RH en catálogo `colaboradores` para esta empresa.
     */
    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class, 'empresa_id');
    }

    /**
     * @return HasMany<Carpeta, $this>
     */
    public function carpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class, 'empresa_id');
    }

    /**
     * @return HasMany<FiltroColaborador, $this>
     */
    public function filtrosColaborador(): HasMany
    {
        return $this->hasMany(FiltroColaborador::class, 'empresa_id');
    }

    /**
     * @return HasMany<CategoriaSolicitud, $this>
     */
    public function categoriasSolicitud(): HasMany
    {
        return $this->hasMany(CategoriaSolicitud::class, 'empresa_id');
    }

    public function opcionesPortafolio(): HasMany
    {
        return $this->hasMany(OpcionesPortafolio::class, 'empresa_id');
    }

    /**
     * Documentos/contratos de la empresa almacenados en Wasabi/S3.
     *
     * @return HasMany<EmpresaDocumento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(EmpresaDocumento::class, 'empresa_id');
    }

    /**
     * @return HasMany<Carrusel, $this>
     */
    public function carruseles(): HasMany
    {
        return $this->hasMany(Carrusel::class, 'empresa_id');
    }

    // LOGS
    protected static function booted(): void
    {
        static::deleting(function (self $empresa): void {
            if ($empresa->tieneUsuariosAsignados()) {
                throw ValidationException::withMessages([
                    'empresa' => 'No se puede eliminar la empresa porque tiene usuarios asignados.',
                ]);
            }
        });

        static::deleted(function ($empresa) {
            $user = auth()->user();
            if ($user) {
                Log::create([
                    'accion' => 'Borrado de empresa: '.$empresa->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });

        static::created(function ($empresa) {
            $user = auth()->user();
            if ($user) {
                Log::create([
                    'accion' => 'Creacion de empresa: '.$empresa->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });

        static::updated(function ($empresa) {
            $user = auth()->user();
            if ($user) {
                Log::create([
                    'accion' => 'Actualizacion de empresa: '.$empresa->nombre.' por usuario: '.$user->name.' (ID: '.$user->id.')',
                    'fecha' => now(),
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
