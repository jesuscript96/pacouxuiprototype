<?php

namespace App\Services;

use App\Models\AliasTipoTransaccion;
use App\Models\ComisionRango;
use App\Models\ConfiguracionApp;
use App\Models\ConfiguracionRetencionNomina;
use App\Models\Empresa;
use App\Models\EmpresaDocumento;
use App\Models\FrecuenciaNotificaciones;
use App\Models\QuincenasPersonalizadas;
use App\Models\RazonEncuestaSalida;
use App\Models\Razonsocial;
use App\Models\Reconocmiento;
use App\Models\TemaVozColaborador;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class EmpresaService
{
    public function __construct(
        private ArchivoService $archivoService,
    ) {}

    /**
     * Crea una nueva empresa y cualquier registro relacionado.
     * Centraliza la lógica que involucra varias tablas/modelos.
     * Adaptado desde CompaniesController::create (Laravel) a Filament v4.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Empresa
    {
        DB::beginTransaction();

        try {
            $empresa = new Empresa;

            // // Mapeo: general_name -> nombre, contact_* -> nombre_contacto, email_contacto, etc.
            $empresa->nombre = $data['nombre'];
            $empresa->nombre_contacto = $data['nombre_contacto'];
            $empresa->email_contacto = $data['email_contacto'];
            $empresa->telefono_contacto = $data['telefono_contacto'];
            $empresa->movil_contacto = $data['movil_contacto'];
            $empresa->email_facturacion = $data['email_facturacion'];
            $empresa->fecha_inicio_contrato = $data['fecha_inicio_contrato'];
            $empresa->fecha_fin_contrato = $data['fecha_fin_contrato'];
            $empresa->industria_id = $data['industria_id'];
            $empresa->sub_industria_id = $data['sub_industria_id'];
            $empresa->tipo_comision = $data['tipo_comision'];
            $empresa->num_usuarios_reportes = (int) ($data['num_usuarios_reportes'] ?? 0);
            $empresa->nombre_app = $data['nombre_app'] ?? null;
            $empresa->link_descarga_app = $data['link_descarga_app'] ?? null;
            $empresa->vigencia_mensajes_urgentes = (isset($data['vigencia_mensajes_urgentes']) && $data['vigencia_mensajes_urgentes'] != 0)
                ? (int) $data['vigencia_mensajes_urgentes'] : null;

            if (($data['tipo_comision'] ?? '') !== 'MIXED') {
                $empresa->comision_quincenal = (float) ($data['comision_quincenal'] ?? 0);
                $empresa->comision_mensual = (float) ($data['comision_mensual'] ?? 0);
                $empresa->comision_bisemanal = (float) ($data['comision_bisemanal'] ?? 0);
                $empresa->comision_semanal = (float) ($data['comision_semanal'] ?? 0);
                $empresa->comision_gateway = (float) ($data['comision_gateway'] ?? 0);
            } else {
                $empresa->comision_quincenal = 0.0;
                $empresa->comision_mensual = 0.0;
                $empresa->comision_bisemanal = 0.0;
                $empresa->comision_semanal = 0.0;
                $empresa->comision_gateway = 0.0;
            }

            // has_fourteen_monthly_payments -> tiene_pagos_quincenales, fourteen_monthly_next_payment_date -> fecha_proximo_pago_quincenal
            $empresa->tiene_pagos_catorcenales = (bool) ($data['tiene_pagos_catorcenales'] ?? false);
            if ($empresa->tiene_pagos_catorcenales && ! empty($data['fecha_proximo_pago_catorcenal'])) {
                $empresa->fecha_proximo_pago_catorcenal = $data['fecha_proximo_pago_catorcenal'] instanceof \DateTimeInterface
                    ? $data['fecha_proximo_pago_catorcenal']->format('Y-m-d')
                    : \Carbon\Carbon::parse($data['fecha_proximo_pago_catorcenal'])->format('Y-m-d');
            } else {
                $empresa->fecha_proximo_pago_catorcenal = null;
            }

            $empresa->tiene_limite_de_sesiones = (bool) ($data['tiene_limite_de_sesiones'] ?? false);
            $empresa->activar_finiquito = (bool) ($data['activar_finiquito'] ?? false);
            $empresa->url_finiquito = $data['activar_finiquito'] ? ($data['url_finiquito'] ?? null) : null;
            $empresa->descargar_cursos = (bool) ($data['descargar_cursos'] ?? false);
            $empresa->permitir_encuesta_salida = (bool) ($data['permitir_encuesta_salida'] ?? false);
            $empresa->tiene_firma_nubarium = (bool) ($data['tiene_firma_nubarium'] ?? false);
            $empresa->ha_firmado_nuevo_contrato = (bool) ($data['ha_firmado_nuevo_contrato'] ?? false);

            $empresa->app_android_id = $data['app_android_id'] ?? null;
            $empresa->app_ios_id = $data['app_ios_id'] ?? null;
            $empresa->app_huawei_id = $data['app_huawei_id'] ?? null;

            $user = auth()->user();
            if ($user instanceof User && $user->tieneRol('administrador')) {
                $empresa->activo = (bool) ($data['activo'] ?? false);
                $empresa->fecha_activacion = $empresa->activo ? now() : null;
            } else {
                $empresa->activo = false;
                $empresa->fecha_activacion = null;
            }

            $empresa->tiene_sub_empresas = (bool) ($data['tiene_sub_empresas'] ?? false);
            $empresa->color_primario = $data['color_primario'] ?? null;
            $empresa->color_secundario = $data['color_secundario'] ?? null;
            $empresa->color_terciario = $data['color_terciario'] ?? null;
            $empresa->color_cuarto = $data['color_cuarto'] ?? null;
            // BL: logo_url se procesa después del save (necesita el ID para la ruta S3)
            $empresa->logo_url = null;
            $empresa->tiene_analiticas_por_ubicacion = (bool) ($data['tiene_analiticas_por_ubicacion'] ?? false);
            $empresa->transacciones_con_imss = (bool) ($data['transacciones_con_imss'] ?? false);
            $empresa->validar_cuentas_automaticamente = (bool) ($data['validar_cuentas_automaticamente'] ?? false);
            $empresa->enviar_boletin = (bool) ($data['enviar_boletin'] ?? false);
            $empresa->domiciliación_via_api = (bool) ($data['domiciliación_via_api'] ?? false);
            $empresa->permitir_notificaciones_felicitaciones = (bool) ($data['permitir_notificaciones_felicitaciones'] ?? false);
            $empresa->segmento_notificaciones_felicitaciones = $data['permitir_notificaciones_felicitaciones'] ? ($data['segmento_notificaciones_felicitaciones'] ?? null) : null;
            $empresa->permitir_retenciones = (bool) ($data['permitir_retenciones'] ?? false);
            $empresa->dias_vencidos_retencion = isset($data['dias_vencidos_retencion']) ? (int) $data['dias_vencidos_retencion'] : 30;

            //  Campos sin usar
            $empresa->pertenece_pepeferia = (bool) ($data['pertenece_pepeferia'] ?? false);
            $empresa->tipo_registro = $data['tipo_registro'] ?? null;
            $empresa->version_android = $data['version_android'] ?? null;
            $empresa->version_ios = $data['version_ios'] ?? null;

            $empresa->configuracion_app_id = $data['configuracion_app_id'] ?? null;
            $empresa->save();

            // Frecuencia notificaciones estado de ánimo (CompaniesController líneas 328-339: mood_notification_frequency -> FrecuenciaNotificaciones)
            if (! empty($data['frecuencia_notificaciones_estado_animo'])) {
                $dias = (int) $data['frecuencia_notificaciones_estado_animo'];
                $siguienteFecha = Carbon::now()->setTime(0, 0, 0)->addDays($dias);
                FrecuenciaNotificaciones::create([
                    'empresa_id' => $empresa->id,
                    'dias' => $dias,
                    'tipo' => 'ESTADOS DE ÁNIMO',
                    'siguiente_fecha' => $siguienteFecha,
                ]);
            }

            // Retenciones (CompaniesController líneas 481-553: allow_withholdings + PayrollWithholdingConfig -> ConfiguracionRetencionNomina)
            if (! empty($data['permitir_retenciones'])) {

                $emails = $data['emails_retenciones'] ?? [];
                $emails = is_array($emails)
                    ? array_values(array_filter(array_map(function ($item) {
                        return is_array($item) ? ($item['email_retencion'] ?? null) : $item;
                    }, $emails)))
                    : array_filter(explode(',', (string) $emails));

                if (! empty($data['dia_retencion_mensual'])) {
                    $fecha = $data['dia_retencion_mensual'] instanceof \DateTimeInterface
                        ? $data['dia_retencion_mensual']->format('Y-m-d')
                        : Carbon::parse($data['dia_retencion_mensual'])->format('Y-m-d');
                    ConfiguracionRetencionNomina::create([
                        'empresa_id' => $empresa->id,
                        'emails' => $emails,
                        'periodicidad_pago' => 'MENSUAL',
                        'fecha' => $fecha,
                        'dias' => null,
                        'dia_semana' => null,
                    ]);
                }
                if (! empty($data['dia_retencion_semanal'])) {
                    ConfiguracionRetencionNomina::create([
                        'empresa_id' => $empresa->id,
                        'emails' => $emails,
                        'periodicidad_pago' => 'SEMANAL',
                        'dia_semana' => (int) $data['dia_retencion_semanal'],
                        'fecha' => null,
                        'dias' => null,
                    ]);
                }
                if (! empty($data['dia_retencion_catorcenal'])) {
                    $diasAntes = (int) $data['dia_retencion_catorcenal'];
                    $fechaRef = $empresa->fecha_proximo_pago_catorcenal ? Carbon::parse($empresa->fecha_proximo_pago_catorcenal) : Carbon::today();
                    if ($fechaRef->lessThanOrEqualTo(Carbon::today())) {
                        do {
                            $fechaRef->addWeeks(2);
                        } while ($fechaRef->lessThan(Carbon::today()));
                    }
                    $fechaRef->subDays($diasAntes);
                    ConfiguracionRetencionNomina::create([
                        'empresa_id' => $empresa->id,
                        'emails' => $emails,
                        'periodicidad_pago' => 'CATORCENAL',
                        'dias' => $diasAntes,
                        'fecha' => $fechaRef->format('Y-m-d'),
                        'dia_semana' => null,
                    ]);
                }
                if (! empty($data['dia_retencion_quincenal'])) {
                    ConfiguracionRetencionNomina::create([
                        'empresa_id' => $empresa->id,
                        'emails' => $emails,
                        'periodicidad_pago' => 'QUINCENAL',
                        'dias' => (int) $data['dia_retencion_quincenal'],
                        'fecha' => null,
                        'dia_semana' => null,
                    ]);
                }
            }

            if (! empty($data['app_android_id']) || ! empty($data['app_ios_id'])) {
                $configuracionApp = ConfiguracionApp::query()
                    ->when(! empty($data['app_android_id']), fn ($q) => $q->where('android_app_id', $data['app_android_id']))
                    ->when(! empty($data['app_ios_id']), fn ($q) => $q->where('ios_app_id', $data['app_ios_id']))
                    ->first();
                if ($configuracionApp) {
                    $empresa->configuracion_app_id = $configuracionApp->id;
                    $empresa->save();
                }
            }

            // Comisión mixta (CompaniesController líneas 564-581: commission_rank -> ComisionRango)
            if (($data['tipo_comision'] ?? '') === 'MIXED' && ! empty($data['rango_comision'])) {
                foreach ($data['rango_comision'] as $rango) {
                    $precioDesde = $rango['rango_comision_precio_desde'] ?? null;
                    $precioHasta = $rango['rango_comision_precio_hasta'] ?? null;
                    $montoFijo = $rango['rango_comision_monto_fijo'] ?? null;
                    $porcentaje = $rango['rango_comision_porcentaje'] ?? null;
                    if ($precioDesde !== null && $precioHasta !== null) {
                        ComisionRango::create([
                            'empresa_id' => $empresa->id,
                            'tipo_comision' => 'MIXED',
                            'precio_desde' => (float) $precioDesde,
                            'precio_hasta' => (float) $precioHasta,
                            'cantidad_fija' => (float) ($montoFijo ?? 0),
                            'porcentaje' => (float) ($porcentaje ?? 0),
                        ]);
                    }
                }
            }

            // Quincena personalizada (CompaniesController líneas 583-591: PersonalizedFortnight -> QuincenasPersonalizadas)
            if (! empty($data['tiene_quincena_personalizada']) && isset($data['dia_inicio'], $data['dia_fin'])) {
                $diaInicio = (int) $data['dia_inicio'];
                $diaFin = (int) $data['dia_fin'];
                QuincenasPersonalizadas::create([
                    'empresa_id' => $empresa->id,
                    'dia_inicio' => $diaInicio,
                    'dia_fin' => $diaFin,
                ]);
            }

            // Notificaciones incluidas (inverso de CompaniesController líneas 598-619: excluded -> aquí se guardan las incluidas)
            // TODO: REVISAR
            $notifIncluidas = $data['notificaciones_incluidas'] ?? [];
            if (is_array($notifIncluidas)) {
                $ids = [];
                foreach ($notifIncluidas as $id => $activo) {
                    if ($activo && is_numeric($id)) {
                        $ids[] = (int) $id;
                    }
                }
                foreach (array_unique($ids) as $notifId) {
                    $empresa->notificacionesIncluidas()->attach($notifId);
                }
            }

            // Razones sociales (CompaniesController líneas 617-635: business_names -> razones_sociales)
            $rawRazones = $data['razones_sociales'] ?? [];
            $razonesSociales = array_values(array_map(function (array $item): array {
                return array_merge(['numero_interior' => '', 'registro_patronal' => ''], $item);
            }, array_filter($rawRazones, 'is_array')));
            $this->syncRazonesSociales($empresa, $razonesSociales);

            // Centros de costo (CompaniesController líneas 637-655: cost_centers -> centro_costo_belvo_id, centro_costo_emida_id, centro_costo_stp_id)
            $centroIds = array_values(array_filter([
                $data['centro_costo_belvo_id'] ?? null,
                $data['centro_costo_emida_id'] ?? null,
                $data['centro_costo_stp_id'] ?? null,
            ]));
            $empresa->centrosCostos()->sync($centroIds);

            // Productos (CompaniesController líneas 657-666: product + pivot base_price, unit_price, enable_from, variation_margin)
            // En el formulario nuevo solo existen producto_id y desde (meses). La tabla empresas_productos exige precio_unitario, precio_base, desde (date), margen_variacion.
            // CompaniesController no adaptado: unit_price por producto/mes (JSON), base_price, variation_margin; aquí se usan valores por defecto para los campos no presentes en el form.
            foreach ($data['productos'] ?? [] as $item) {
                if (empty($item['producto_id'])) {
                    continue;
                }
                $desdeMeses = (int) ($item['desde'] ?? 1);
                $empresa->productos()->attach((int) $item['producto_id'], [
                    'desde' => $desdeMeses,
                    'precio_unitario' => 0,
                    'precio_base' => 0,
                    'margen_variacion' => 0,
                ]);
            }

            /* Relacion en automatico con Reconocimientos NO exlusivos */
            $reconocimientos = Reconocmiento::where('es_exclusivo', false)->get();
            foreach ($reconocimientos as $reconocimiento) {
                $reconocimiento->empresas()->attach($empresa, ['es_enviable' => $reconocimiento->es_enviable, 'menciones_necesarias' => $reconocimiento->menciones_necesarias]);
            }

            /* Relacion en automatico con Temas de Voz del Colaborador NO exlusivos */
            $temasVozColaboradores = TemaVozColaborador::whereNull('exclusivo_para_empresa')->orWhere('exclusivo_para_empresa', $empresa->id)->get();
            $empresa->temasVozColaboradores()->attach($temasVozColaboradores);

            // Razones encuesta de salida (form: CheckboxList 'razones' devuelve array de valores seleccionados)
            if (! empty($data['permitir_encuesta_salida']) && ! empty($data['razones']) && is_array($data['razones'])) {
                $razonesActivas = RazonEncuestaSalida::soloRazonesDelCatalogo(
                    array_values(array_filter((array) $data['razones'], fn ($v) => is_string($v)))
                );
                foreach ($razonesActivas as $razonNombre) {
                    RazonEncuestaSalida::create([
                        'empresa_id' => $empresa->id,
                        'razon' => $razonNombre,
                    ]);
                }
            }

            // ALIAS DE TRANSACCIONES (CompaniesController líneas 699-718: alias_transaccion_nomina, alias_transaccion_servicio, alias_transaccion_recarga)
            if (! empty($data['alias_transaccion_nomina'])) {
                $aliasTipoTransaccion = new AliasTipoTransaccion;
                $aliasTipoTransaccion->empresa_id = $empresa->id;
                $aliasTipoTransaccion->tipo_transaccion = 'ADELANTO DE NOMINA';
                $aliasTipoTransaccion->alias = strtoupper($data['alias_transaccion_nomina']);
                $aliasTipoTransaccion->save();

                $empresa->aliasTipoTransacciones()->save($aliasTipoTransaccion);
            }
            if (! empty($data['alias_transaccion_servicio'])) {
                $aliasTipoTransaccion = new AliasTipoTransaccion;
                $aliasTipoTransaccion->empresa_id = $empresa->id;
                $aliasTipoTransaccion->tipo_transaccion = 'PAGO DE SERVICIO';
                $aliasTipoTransaccion->alias = strtoupper($data['alias_transaccion_servicio']);
                $aliasTipoTransaccion->save();

                $empresa->aliasTipoTransacciones()->save($aliasTipoTransaccion);
            }
            if (! empty($data['alias_transaccion_recarga'])) {
                $aliasTipoTransaccion = new AliasTipoTransaccion;
                $aliasTipoTransaccion->empresa_id = $empresa->id;
                $aliasTipoTransaccion->tipo_transaccion = 'RECARGA';
                $aliasTipoTransaccion->alias = strtoupper($data['alias_transaccion_recarga']);
                $aliasTipoTransaccion->save();

                $empresa->aliasTipoTransacciones()->save($aliasTipoTransaccion);
            }

            $this->persistirDocumentosAlCrear($data['documentos_contratos'] ?? [], $empresa);

            if (! empty($data['logo_url'])) {
                $rutaLogo = $this->persistirArchivo($data['logo_url'], $empresa->id, 'logo', 'logo');
                if ($rutaLogo !== '') {
                    $empresa->update(['logo_url' => $rutaLogo]);
                }
            }

            if (! empty($data['foto'])) {
                $rutaFoto = $this->persistirFoto($data['foto'], $empresa->id);
                if ($rutaFoto !== '') {
                    $empresa->update(['foto' => $rutaFoto]);
                }
            }

            DB::commit();

            return $empresa;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Actualiza una empresa y sus relaciones.
     * Centraliza la lógica que involucra varias tablas/modelos.
     * Migrado desde CompaniesController::update (líneas 941-1720 aprox.).
     *
     * Mapeo de variables (controller antiguo -> nuevo): general_name->nombre, contact_name->nombre_contacto,
     * contact_email->email_contacto, contact_phone->telefono_contacto, contact_mobile->movil_contacto,
     * billing_email->email_facturacion, contract_start->fecha_inicio_contrato, contract_end->fecha_fin_contrato,
     * report_users->num_usuarios_reportes, commission_type->tipo_comision, biweekly_commission->comision_quincenal,
     * monthly_commission->comision_mensual, fourteen_monthly_commission->comision_bisemanal,
     * weekly_commission->comision_semanal, payment_gateway_commission->comision_gateway,
     * valid_days_messages->vigencia_mensajes_urgentes, name_app->nombre_app, app_download_link->link_descarga_app,
     * industry->industria_id, sub_industry->sub_industria_id, has_fourteen_monthly_payments->tiene_pagos_catorcenales,
     * fourteen_monthly_next_payment_date->fecha_proximo_pago_catorcenal, has_session_limit->tiene_limite_de_sesiones,
     * has_settlement_date->activar_finiquito, url_settlement_date->url_finiquito,
     * has_download_capacitation->descargar_cursos, allow_exit_poll->permitir_encuesta_salida,
     * has_nubarium_sign->tiene_firma_nubarium, has_sign_new_contract->ha_firmado_nuevo_contrato,
     * android_app_id->app_android_id, ios_app_id->app_ios_id, huawei_app_id->app_huawei_id,
     * is_active->activo, activation_date->fecha_activacion, has_sub_companies->tiene_sub_empresas,
     * first_color->color_primario, second_color->color_secundario, third_color->color_terciario,
     * fourth_color->color_cuarto, has_analytics_by_location->tiene_analiticas_por_ubicacion,
     * transactions_with_imss->transacciones_con_imss, validate_accounts_automatically->validar_cuentas_automaticamente,
     * send_newsletter->enviar_boletin, direct_debit_via_api->domiciliación_via_api,
     * allow_congratulation_notifications->permitir_notificaciones_felicitaciones,
     * congratulation_notifications_type->segmento_notificaciones_felicitaciones,
     * allow_withholdings->permitir_retenciones, withholding_day->dias_vencidos_retencion.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Empresa $empresa, array $data): Empresa
    {
        DB::beginTransaction();

        try {
            // CompaniesController líneas 1082-1122: datos base de la empresa
            $fillableData = $this->onlyFillable($data);
            // BL: logo_url y foto se procesan aparte (necesitan mover archivo a ruta final en S3)
            unset($fillableData['logo_url'], $fillableData['foto']);

            // CompaniesController líneas 1124-1126: valid_days_messages solo si != 0 (ref. 1124)
            if (array_key_exists('vigencia_mensajes_urgentes', $data)) {
                $fillableData['vigencia_mensajes_urgentes'] = (isset($data['vigencia_mensajes_urgentes']) && $data['vigencia_mensajes_urgentes'] != 0)
                    ? (int) $data['vigencia_mensajes_urgentes'] : null;
            }

            // CompaniesController líneas 1102-1121: tipo comisión; si no es MIXED, rellenar comisiones y borrar rangos; si es MIXED, anular comisiones
            $tipoComision = $fillableData['tipo_comision'] ?? $empresa->tipo_comision;
            if ($tipoComision !== 'MIXED') {
                $fillableData['comision_quincenal'] = (float) ($data['comision_quincenal'] ?? 0);
                $fillableData['comision_mensual'] = (float) ($data['comision_mensual'] ?? 0);
                $fillableData['comision_bisemanal'] = (float) ($data['comision_bisemanal'] ?? 0);
                $fillableData['comision_semanal'] = (float) ($data['comision_semanal'] ?? 0);
                $fillableData['comision_gateway'] = (float) ($data['comision_gateway'] ?? 0);
            } else {
                $fillableData['comision_quincenal'] = 0.0;
                $fillableData['comision_mensual'] = 0.0;
                $fillableData['comision_bisemanal'] = 0.0;
                $fillableData['comision_semanal'] = 0.0;
                $fillableData['comision_gateway'] = 0.0;
            }

            $empresa->update($fillableData);

            // CompaniesController líneas 1128-1136: vincular AppSetting/ConfiguracionApp por android_app_id o ios_app_id (ref. 1128)
            if (! empty($data['app_android_id'] ?? null) || ! empty($data['app_ios_id'] ?? null)) {
                $configuracionApp = ConfiguracionApp::query()
                    ->when(! empty($data['app_android_id']), fn ($q) => $q->where('android_app_id', $data['app_android_id']))
                    ->when(! empty($data['app_ios_id']), fn ($q) => $q->where('ios_app_id', $data['app_ios_id']))
                    ->first();
                if ($configuracionApp) {
                    $empresa->configuracion_app_id = $configuracionApp->id;
                    $empresa->save();
                }
            }

            $this->syncRelationsAfterUpdate($empresa, $data);

            DB::commit();

            return $empresa;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Filtra solo los atributos fillable del modelo Empresa.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function onlyFillable(array $data): array
    {
        return array_intersect_key($data, array_flip((new Empresa)->getFillable()));
    }

    /**
     * Sincroniza relaciones después de crear (ej. pivotes, comision_rangos, etc.).
     * Extender aquí la lógica que involucre más tablas.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncRelationsAfterCreate(Empresa $empresa, array $data): void
    {
        $this->syncRazonesSociales($empresa, $data['razones_sociales'] ?? []);
    }

    /**
     * Sincroniza relaciones después de actualizar.
     * Ref. CompaniesController::update desde ~1140 (comisión mixta, notificaciones, retenciones, etc.).
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncRelationsAfterUpdate(Empresa $empresa, array $data): void
    {
        // CompaniesController líneas 1143-1162: comisión mixta (commission_rank -> rango_comision / ComisionRango)
        $tipoComision = $data['tipo_comision'] ?? $empresa->tipo_comision;
        if ($tipoComision === 'MIXED' && ! empty($data['rango_comision'])) {
            $empresa->comisionesRangos()->delete();
            foreach ($data['rango_comision'] as $rango) {
                $precioDesde = $rango['rango_comision_precio_desde'] ?? null;
                $precioHasta = $rango['rango_comision_precio_hasta'] ?? null;
                $montoFijo = $rango['rango_comision_monto_fijo'] ?? null;
                $porcentaje = $rango['rango_comision_porcentaje'] ?? null;
                if ($precioDesde !== null && $precioHasta !== null) {
                    ComisionRango::create([
                        'empresa_id' => $empresa->id,
                        'tipo_comision' => 'MIXED',
                        'precio_desde' => (float) $precioDesde,
                        'precio_hasta' => (float) $precioHasta,
                        'cantidad_fija' => (float) ($montoFijo ?? 0),
                        'porcentaje' => (float) ($porcentaje ?? 0),
                    ]);
                }
            }
        } elseif ($tipoComision !== 'MIXED') {
            $empresa->comisionesRangos()->delete();
        }

        // CompaniesController líneas 1164-1192: frecuencia notificaciones estado de ánimo (mood_notification_frequency -> FrecuenciaNotificaciones)
        $this->syncFrecuenciaNotificacionesEstadoAnimo($empresa, $data);

        // CompaniesController líneas 1194-1224, 1417-1520: pagos catorcenales, sesión, finiquito, descarga cursos, encuesta salida, firma nubarium
        // (esos campos ya se actualizan vía fillable en update(); aquí solo relaciones)

        // CompaniesController líneas 1226-1274: allow_exit_poll -> permitir_encuesta_salida + razones (RazonEncuestaSalida)
        $this->syncRazonesEncuestaSalida($empresa, $data);

        // CompaniesController líneas 1294-1312: quincena personalizada (PersonalizedFortnight -> QuincenasPersonalizadas)
        $this->syncQuincenaPersonalizada($empresa, $data);

        // CompaniesController líneas 1314-1324: app ids ya en fillable; no relación adicional

        // CompaniesController líneas 1417-1520: allow_withholdings -> permitir_retenciones + PayrollWithholdingConfig -> ConfiguracionRetencionNomina
        $this->syncConfiguracionRetencionNominas($empresa, $data);

        // Notificaciones incluidas (CompaniesController 1522-1552: excluded_notifications; aquí se guardan las incluidas)
        $notifIncluidas = $data['notificaciones_incluidas'] ?? [];
        if (is_array($notifIncluidas)) {
            $ids = [];
            foreach ($notifIncluidas as $id => $activo) {
                if ($activo && is_numeric($id)) {
                    $ids[] = (int) $id;
                }
            }
            $empresa->notificacionesIncluidas()->sync(array_unique($ids));
        }

        // Razones sociales (CompaniesController líneas 1554-1604: business_names -> razones_sociales)
        $rawRazones = $data['razones_sociales'] ?? [];
        $razonesSociales = array_values(array_map(function (array $item): array {
            return array_merge(['numero_interior' => '', 'registro_patronal' => ''], $item);
        }, array_filter($rawRazones, 'is_array')));
        $this->syncRazonesSociales($empresa, $razonesSociales);

        // CompaniesController líneas 1602-1620: cost_centers -> centrosCostos
        $centroIds = array_values(array_filter([
            $data['centro_costo_belvo_id'] ?? null,
            $data['centro_costo_emida_id'] ?? null,
            $data['centro_costo_stp_id'] ?? null,
        ]));
        $empresa->centrosCostos()->sync($centroIds);

        // CompaniesController líneas 1623-1634: products -> productos (pivot base_price, unit_price, enable_from, variation_margin; aquí solo desde)
        $empresa->productos()->detach();
        foreach ($data['productos'] ?? [] as $item) {
            if (empty($item['producto_id'] ?? null)) {
                continue;
            }
            $desdeMeses = (int) ($item['desde'] ?? 1);
            $empresa->productos()->attach((int) $item['producto_id'], [
                'desde' => $desdeMeses,
                'precio_unitario' => 0,
                'precio_base' => 0,
                'margen_variacion' => 0,
            ]);
        }

        // CompaniesController líneas 1366-1405: logo (crear/actualizar o borrar)
        $this->syncLogoUpdate($empresa, $data);

        // CompaniesController líneas 1796-1820: documentos/contratos (añadir nuevos al directorio existente)
        $this->syncDocumentosContratosUpdate($empresa, $data);

        // CompaniesController líneas 1823-1840: photo/foto (reemplazar imagen redimensionada)
        $this->syncFotoUpdate($empresa, $data);

        // Alias tipo transacción (CompaniesController líneas 1843-1891)
        $this->syncAliasTipoTransacciones($empresa, $data);

        // CompaniesController líneas 1905-1910: relación automática con Temas de Voz del Colaborador no exclusivos (o exclusivos de esta empresa); solo si la empresa no tiene ninguno
        if ($empresa->temasVozColaboradores()->doesntExist()) {
            $empresa->temasVozColaboradores()->detach();
            $temasVozColaboradores = TemaVozColaborador::whereNull('exclusivo_para_empresa')->orWhere('exclusivo_para_empresa', $empresa->id)->get();
            $empresa->temasVozColaboradores()->attach($temasVozColaboradores);
        }

    }

    /**
     * Sincroniza la frecuencia de notificaciones de estado de ánimo (una sola por empresa, tipo ESTADOS DE ÁNIMO).
     * Ref. CompaniesController líneas 1164-1192.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncFrecuenciaNotificacionesEstadoAnimo(Empresa $empresa, array $data): void
    {
        $dias = isset($data['frecuencia_notificaciones_estado_animo']) && $data['frecuencia_notificaciones_estado_animo'] !== ''
            ? (int) $data['frecuencia_notificaciones_estado_animo']
            : null;

        $existente = $empresa->notificacionesEstadoAnimo()->where('tipo', 'ESTADOS DE ÁNIMO')->first();

        if ($dias !== null && $dias > 0) {
            $siguienteFecha = Carbon::now()->setTime(0, 0, 0)->addDays($dias);
            if ($existente) {
                $existente->update(['dias' => $dias, 'siguiente_fecha' => $siguienteFecha]);
            } else {
                FrecuenciaNotificaciones::create([
                    'empresa_id' => $empresa->id,
                    'dias' => $dias,
                    'tipo' => 'ESTADOS DE ÁNIMO',
                    'siguiente_fecha' => $siguienteFecha,
                ]);
            }
        } elseif ($existente) {
            $existente->delete();
        }
    }

    /**
     * Sincroniza razones de encuesta de salida (allow_exit_poll -> permitir_encuesta_salida).
     * Ref. CompaniesController líneas 1226-1274.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncRazonesEncuestaSalida(Empresa $empresa, array $data): void
    {
        RazonEncuestaSalida::where('empresa_id', $empresa->id)->delete();

        if (empty($data['permitir_encuesta_salida']) || empty($data['razones']) || ! is_array($data['razones'])) {
            return;
        }

        $razonesActivas = RazonEncuestaSalida::soloRazonesDelCatalogo(
            array_values(array_filter((array) $data['razones'], fn ($v) => is_string($v)))
        );
        if (count($razonesActivas) === 0) {
            return;
        }

        foreach ($razonesActivas as $razonNombre) {
            RazonEncuestaSalida::create([
                'empresa_id' => $empresa->id,
                'razon' => $razonNombre,
            ]);
        }
    }

    /**
     * Sincroniza quincena personalizada (PersonalizedFortnight -> QuincenasPersonalizadas).
     * Ref. CompaniesController líneas 1294-1312.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncQuincenaPersonalizada(Empresa $empresa, array $data): void
    {
        $quincena = QuincenasPersonalizadas::where('empresa_id', $empresa->id)->first();

        if (! empty($data['tiene_quincena_personalizada']) && isset($data['dia_inicio'], $data['dia_fin'])) {
            $diaInicio = (int) $data['dia_inicio'];
            $diaFin = (int) $data['dia_fin'];
            if ($quincena) {
                $quincena->update(['dia_inicio' => $diaInicio, 'dia_fin' => $diaFin]);
            } else {
                QuincenasPersonalizadas::create([
                    'empresa_id' => $empresa->id,
                    'dia_inicio' => $diaInicio,
                    'dia_fin' => $diaFin,
                ]);
            }
        } elseif ($quincena) {
            $quincena->delete();
        }
    }

    /**
     * Sincroniza configuraciones de retención de nómina (PayrollWithholdingConfig -> ConfiguracionRetencionNomina).
     * Ref. CompaniesController líneas 1417-1520.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncConfiguracionRetencionNominas(Empresa $empresa, array $data): void
    {
        $empresa->configuracionRetencionNominas()->delete();

        if (empty($data['permitir_retenciones'])) {
            return;
        }

        $emails = $data['emails_retenciones'] ?? [];
        $emails = is_array($emails) ? array_values(array_filter(array_map(function ($item) {
            return is_array($item) ? ($item['email_retencion'] ?? null) : $item;
        }, $emails))) : array_filter(explode(',', (string) $emails));

        if (! empty($data['dia_retencion_mensual'])) {
            $fecha = $data['dia_retencion_mensual'] instanceof \DateTimeInterface
                ? $data['dia_retencion_mensual']->format('Y-m-d')
                : Carbon::parse($data['dia_retencion_mensual'])->format('Y-m-d');
            ConfiguracionRetencionNomina::create([
                'empresa_id' => $empresa->id,
                'emails' => $emails,
                'periodicidad_pago' => 'MENSUAL',
                'fecha' => $fecha,
                'dias' => null,
                'dia_semana' => null,
            ]);
        }
        if (isset($data['dia_retencion_semanal']) && $data['dia_retencion_semanal'] !== '') {
            ConfiguracionRetencionNomina::create([
                'empresa_id' => $empresa->id,
                'emails' => $emails,
                'periodicidad_pago' => 'SEMANAL',
                'dia_semana' => (int) $data['dia_retencion_semanal'],
                'fecha' => null,
                'dias' => null,
            ]);
        }
        if (! empty($data['dia_retencion_catorcenal'])) {
            $diasAntes = (int) $data['dia_retencion_catorcenal'];
            $fechaRef = $empresa->fecha_proximo_pago_catorcenal ? Carbon::parse($empresa->fecha_proximo_pago_catorcenal) : Carbon::today();
            if ($fechaRef->lessThanOrEqualTo(Carbon::today())) {
                do {
                    $fechaRef->addWeeks(2);
                } while ($fechaRef->lessThan(Carbon::today()));
            }
            $fechaRef->subDays($diasAntes);
            ConfiguracionRetencionNomina::create([
                'empresa_id' => $empresa->id,
                'emails' => $emails,
                'periodicidad_pago' => 'CATORCENAL',
                'dias' => $diasAntes,
                'fecha' => $fechaRef->format('Y-m-d'),
                'dia_semana' => null,
            ]);
        }
        if (! empty($data['dia_retencion_quincenal'])) {
            ConfiguracionRetencionNomina::create([
                'empresa_id' => $empresa->id,
                'emails' => $emails,
                'periodicidad_pago' => 'QUINCENAL',
                'dias' => (int) $data['dia_retencion_quincenal'],
                'fecha' => null,
                'dia_semana' => null,
            ]);
        }
    }

    /**
     * Sincroniza alias de tipo de transacción por tipo: crear/actualizar si hay valor, eliminar si se "quita".
     * Ref. CompaniesController líneas 1843-1891 (payroll_advances_alias, services_pay_alias, refill_alias):
     * cuando el campo no viene (isset) o está vacío, se elimina el alias de ese tipo de la BD.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncAliasTipoTransacciones(Empresa $empresa, array $data): void
    {
        $tipos = [
            'alias_transaccion_nomina' => 'ADELANTO DE NOMINA',
            'alias_transaccion_servicio' => 'PAGO DE SERVICIO',
            'alias_transaccion_recarga' => 'RECARGA',
        ];

        foreach ($tipos as $key => $tipoTransaccion) {
            $alias = isset($data[$key]) ? trim((string) $data[$key]) : '';

            if ($alias === '') {
                // CompaniesController líneas 1859-1860, 1879-1880, 1890-1891: si no hay valor, eliminar de BD
                $empresa->aliasTipoTransacciones()->where('tipo_transaccion', $tipoTransaccion)->delete();

                continue;
            }

            $aliasStr = strtoupper($alias);
            $modelo = $empresa->aliasTipoTransacciones()->where('tipo_transaccion', $tipoTransaccion)->first();

            if ($modelo) {
                $modelo->update(['alias' => $aliasStr]);
            } else {
                AliasTipoTransaccion::create([
                    'empresa_id' => $empresa->id,
                    'tipo_transaccion' => $tipoTransaccion,
                    'alias' => $aliasStr,
                ]);
            }
        }
    }

    /**
     * Sincroniza el logo de la empresa al actualizar.
     * Mueve el archivo temporal a su ruta final en S3/Wasabi o elimina el anterior si se quitó.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncLogoUpdate(Empresa $empresa, array $data): void
    {
        if (! array_key_exists('logo_url', $data)) {
            return;
        }

        if (empty($data['logo_url'])) {
            if ($empresa->logo_url) {
                $this->archivoService->eliminar($empresa->logo_url);
            }
            $empresa->update(['logo_url' => null]);

            return;
        }

        if ($empresa->logo_url === $data['logo_url']) {
            return;
        }

        $rutaFinal = $this->persistirArchivo($data['logo_url'], $empresa->id, 'logo', 'logo');
        if ($rutaFinal === '') {
            return;
        }

        if ($empresa->logo_url) {
            $this->archivoService->eliminar($empresa->logo_url);
        }

        $empresa->update(['logo_url' => $rutaFinal]);
    }

    /**
     * Sincroniza documentos/contratos de la empresa al actualizar.
     * Persiste archivos nuevos en S3/Wasabi y crea registros en empresas_documentos.
     * Elimina archivos y registros que el usuario quitó del formulario.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncDocumentosContratosUpdate(Empresa $empresa, array $data): void
    {
        if (! isset($data['documentos_contratos']) || ! is_array($data['documentos_contratos'])) {
            return;
        }

        $rutasActuales = $empresa->documentos()->pluck('ruta')->toArray();
        $rutasEnviadas = [];

        foreach ($data['documentos_contratos'] as $ruta) {
            if (empty($ruta)) {
                continue;
            }

            if (in_array($ruta, $rutasActuales, true)) {
                $rutasEnviadas[] = $ruta;

                continue;
            }

            $rutaFinal = $this->persistirDocumento($ruta, $empresa->id);
            if ($rutaFinal !== '') {
                EmpresaDocumento::create([
                    'empresa_id' => $empresa->id,
                    'ruta' => $rutaFinal,
                    'subido_por' => auth()->id(),
                ]);
                $rutasEnviadas[] = $rutaFinal;
            }
        }

        $rutasAEliminar = array_diff($rutasActuales, $rutasEnviadas);
        foreach ($rutasAEliminar as $ruta) {
            $this->archivoService->eliminar($ruta);
            $empresa->documentos()->where('ruta', $ruta)->delete();
        }
    }

    /**
     * Sincroniza la foto de la empresa al actualizar.
     * Redimensiona a 150x150 px y almacena en S3/Wasabi.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncFotoUpdate(Empresa $empresa, array $data): void
    {
        if (! array_key_exists('foto', $data)) {
            return;
        }

        if (empty($data['foto'])) {
            if ($empresa->foto) {
                $this->archivoService->eliminar($empresa->foto);
            }
            $empresa->update(['foto' => null]);

            return;
        }

        if ($empresa->foto === $data['foto']) {
            return;
        }

        $rutaFinal = $this->persistirFoto($data['foto'], $empresa->id);
        if ($rutaFinal === '') {
            return;
        }

        if ($empresa->foto) {
            $this->archivoService->eliminar($empresa->foto);
        }

        $empresa->update(['foto' => $rutaFinal]);
    }

    // ─── Métodos de persistencia de archivos (S3/Wasabi) ────────────────

    /**
     * Construye la ruta del directorio de una empresa para una subcarpeta.
     * Estructura: companies/{empresaId}/{subcarpeta}
     *
     * @param  int  $empresaId  ID de la empresa
     * @param  string  $subcarpeta  Subcarpeta dentro del directorio de la empresa (logo, foto, documentos)
     * @return string Ruta del directorio (sin barra final)
     */
    private function directorioEmpresa(int $empresaId, string $subcarpeta): string
    {
        return "companies/{$empresaId}/{$subcarpeta}";
    }

    /**
     * Resuelve la ruta temporal de un archivo subido por Livewire/Filament.
     * Filament puede enviar la ruta directa o solo el nombre; en ese caso busca en livewire-tmp/.
     *
     * @param  string  $ruta  Ruta o nombre del archivo temporal
     * @return string Ruta resuelta en el disco
     */
    private function resolverRutaTemporal(string $ruta): string
    {
        $ruta = ltrim(str_replace('\\', '/', $ruta), '/');
        $disco = $this->archivoService->disco();

        if ($disco->exists($ruta)) {
            return $ruta;
        }

        $conLivewire = 'livewire-tmp/'.$ruta;
        if ($disco->exists($conLivewire)) {
            return $conLivewire;
        }

        return $ruta;
    }

    /**
     * Copia un archivo temporal a su ubicación definitiva en S3/Wasabi y elimina el original.
     * Usa get+put+delete en lugar de move() porque Wasabi no soporta move nativo de forma confiable.
     * Ruta final: companies/{empresaId}/{subcarpeta}/{prefijo}_{ts}.{ext}
     *
     * @param  string  $rutaTemporal  Ruta del archivo temporal en el disco
     * @param  int  $empresaId  ID de la empresa
     * @param  string  $subcarpeta  Subcarpeta destino (logo, foto, documentos)
     * @param  string  $prefijo  Prefijo del nombre final del archivo
     * @return string Ruta final del archivo o cadena vacía si falla
     */
    private function persistirArchivo(string $rutaTemporal, int $empresaId, string $subcarpeta, string $prefijo): string
    {
        $rutaTemporal = $this->resolverRutaTemporal($rutaTemporal);
        $disco = $this->archivoService->disco();

        if (! $disco->exists($rutaTemporal)) {
            return '';
        }

        $contenido = $disco->get($rutaTemporal);
        if ($contenido === null || $contenido === false) {
            return '';
        }

        $extension = pathinfo($rutaTemporal, PATHINFO_EXTENSION) ?: 'png';
        $nombreFinal = $prefijo.'_'.time().'.'.$extension;
        $destino = $this->directorioEmpresa($empresaId, $subcarpeta).'/'.$nombreFinal;

        $disco->put($destino, $contenido);
        $disco->delete($rutaTemporal);

        return $disco->exists($destino) ? $destino : '';
    }

    /**
     * Persiste la foto de la empresa redimensionada a 150×150 px en S3/Wasabi.
     * Ruta final: companies/{empresaId}/foto/foto_{ts}.png
     *
     * @param  string  $rutaTemporal  Ruta del archivo temporal en el disco
     * @param  int  $empresaId  ID de la empresa
     * @return string Ruta final del archivo o cadena vacía si falla
     */
    private function persistirFoto(string $rutaTemporal, int $empresaId): string
    {
        $rutaTemporal = $this->resolverRutaTemporal($rutaTemporal);
        $disco = $this->archivoService->disco();

        if (! $disco->exists($rutaTemporal)) {
            return '';
        }

        $contenido = $disco->get($rutaTemporal);
        $img = Image::read($contenido)
            ->resize(150, 150, function ($constraint): void {
                $constraint->aspectRatio();
            });

        $nombreFinal = 'foto_'.time().'.png';
        $destino = $this->directorioEmpresa($empresaId, 'foto').'/'.$nombreFinal;

        $disco->put($destino, $img->toPng());
        $disco->delete($rutaTemporal);

        return $disco->exists($destino) ? $destino : '';
    }

    /**
     * Persiste un documento individual de la empresa en S3/Wasabi.
     * Usa get+put+delete en lugar de move() porque Wasabi no soporta move nativo de forma confiable.
     * Ruta final: companies/{empresaId}/documentos/{nombre_slug}_{ts}.{ext}
     *
     * @param  string  $rutaTemporal  Ruta del archivo temporal en el disco
     * @param  int  $empresaId  ID de la empresa
     * @return string Ruta final del archivo o cadena vacía si falla
     */
    private function persistirDocumento(string $rutaTemporal, int $empresaId): string
    {
        $rutaTemporal = $this->resolverRutaTemporal($rutaTemporal);
        $disco = $this->archivoService->disco();

        if (! $disco->exists($rutaTemporal)) {
            return '';
        }

        $contenido = $disco->get($rutaTemporal);
        if ($contenido === null || $contenido === false) {
            return '';
        }

        $nombreOriginal = pathinfo($rutaTemporal, PATHINFO_FILENAME);
        $extension = pathinfo($rutaTemporal, PATHINFO_EXTENSION) ?: 'pdf';
        $nombreSlug = Str::slug($nombreOriginal).'_'.time();
        $nombreFinal = $nombreSlug.'.'.$extension;
        $destino = $this->directorioEmpresa($empresaId, 'documentos').'/'.$nombreFinal;

        $disco->put($destino, $contenido);
        $disco->delete($rutaTemporal);

        return $disco->exists($destino) ? $destino : '';
    }

    /**
     * Persiste todos los documentos nuevos al crear una empresa.
     * Crea registros en empresas_documentos por cada archivo subido.
     *
     * @param  array<int, string>  $documentos  Rutas temporales de los archivos
     * @param  Empresa  $empresa  Empresa recién creada
     */
    private function persistirDocumentosAlCrear(array $documentos, Empresa $empresa): void
    {
        foreach ($documentos as $rutaTemporal) {
            if (empty($rutaTemporal)) {
                continue;
            }

            $rutaFinal = $this->persistirDocumento($rutaTemporal, $empresa->id);
            if ($rutaFinal !== '') {
                EmpresaDocumento::create([
                    'empresa_id' => $empresa->id,
                    'ruta' => $rutaFinal,
                    'subido_por' => auth()->id(),
                ]);
            }
        }
    }

    // ─── Razones sociales ────────────────────────────────────────────────

    /**
     * Crea o actualiza razones sociales y las asocia a la empresa.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function syncRazonesSociales(Empresa $empresa, array $items): void
    {
        $ids = [];

        foreach ($items as $item) {
            if (empty(array_filter($item))) {
                continue;
            }

            $razon = isset($item['id']) ? Razonsocial::find($item['id']) : new Razonsocial;

            if ($razon) {
                $razon->fill(array_intersect_key($item, array_flip((new Razonsocial)->getFillable())));
                $razon->save();
                $ids[] = $razon->id;
            }

            // TODO: ACTUALIZAR a NULL a los "empleados" que pertenecen a la razón social y se ELIMINó
        }

        $empresa->razonesSociales()->sync($ids);
    }
}

/*
 * ==================================================================================
 * DOCUMENTACIÓN: LO NO MIGRADO O ADAPTADO DESDE CompaniesController::update
 * ==================================================================================

 *
 * 5. LÓGICA POST-SAVE CON EMPLEADOS (CompaniesController líneas 1639-1720+)
 *    No migrada en EmpresaService:
 *    - Envío de notificación “Bienvenida a la Empresa” al activar la empresa a
 *      high_employees (líneas 1645-1682).
 *    - Sincronización de productos por high_employee según request->product,
 *      enable_from y fechas (líneas 1686-1708).
 *    - Envío de notificación “Adelanto de nómina disponible” (líneas 1710-1719).
 *    Esta lógica debería vivir en Observers, Jobs o en la capa que gestione
 *    “empleados/colaboradores” en el nuevo sistema.
 *
 * 6. BORRADO DE RAZONES SOCIALES Y EMPLEADOS (CompaniesController líneas 1565-1572)
 *    Al eliminar una business_name (razón social) del listado, el controller
 *    actualizaba high_employees asociados (business_name => null) y borraba la razón.
 *    En el nuevo flujo syncRazonesSociales hace sync por IDs: las razones que dejen
 *    de enviarse en el repeater ya no se asocian, pero no se ejecuta lógica sobre
 *    empleados/colaboradores ni borrado explícito de Razonsocial no usadas (las
 *    huérfanas quedarían en BD si no se eliminan en otro lugar).
 *
 * 7. ROL DE ADMINISTRADOR Y ACTIVACIÓN (CompaniesController líneas 1335-1340)
 *    Solo usuarios con tipo JSON `administrador` (User::tieneRol('administrador'))
 *    pueden persistir activo/fecha_activacion desde el payload; en otro caso la
 *    empresa queda inactiva. El formulario Filament puede además restringir el campo.
 */
