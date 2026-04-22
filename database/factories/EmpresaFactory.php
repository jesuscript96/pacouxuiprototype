<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    /**
     * Requiere catálogo Inicial (industrias / sub_industrias id 2).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaInicio = now()->format('Y-m-d');
        $fechaFin = now()->addYear()->format('Y-m-d');

        return [
            'nombre' => fake()->company(),
            'nombre_contacto' => fake()->name(),
            'email_contacto' => fake()->unique()->safeEmail(),
            'telefono_contacto' => fake()->numerify('55########'),
            'movil_contacto' => fake()->numerify('55########'),
            'industria_id' => 2,
            'sub_industria_id' => 2,
            'email_facturacion' => fake()->unique()->safeEmail(),
            'fecha_inicio_contrato' => $fechaInicio,
            'fecha_fin_contrato' => $fechaFin,
            'num_usuarios_reportes' => 10,
            'activo' => true,
            'fecha_activacion' => now(),
            'nombre_app' => 'App Test',
            'link_descarga_app' => null,
            'app_android_id' => null,
            'app_ios_id' => null,
            'app_huawei_id' => null,
            'color_primario' => null,
            'color_secundario' => null,
            'color_terciario' => null,
            'color_cuarto' => null,
            'logo_url' => null,
            'tipo_comision' => 'PERCENTAGE',
            'comision_bisemanal' => 2.00,
            'comision_mensual' => 3.00,
            'comision_quincenal' => 2.50,
            'comision_semanal' => 1.50,
            'tiene_pagos_catorcenales' => true,
            'fecha_proximo_pago_catorcenal' => now()->addDays(14)->format('Y-m-d'),
            'tiene_sub_empresas' => false,
            'comision_gateway' => 0.50,
            'transacciones_con_imss' => true,
            'validar_cuentas_automaticamente' => true,
            'tiene_analiticas_por_ubicacion' => true,
            'version_android' => null,
            'version_ios' => null,
            'tiene_limite_de_sesiones' => false,
            'tiene_firma_nubarium' => false,
            'enviar_boletin' => true,
            'permitir_encuesta_salida' => false,
            'configuracion_app_id' => null,
            'activar_finiquito' => false,
            'url_finiquito' => null,
            'domiciliación_via_api' => false,
            'ha_firmado_nuevo_contrato' => false,
            'vigencia_mensajes_urgentes' => null,
            'permitir_notificaciones_felicitaciones' => false,
            'segmento_notificaciones_felicitaciones' => null,
            'permitir_retenciones' => false,
            'dias_vencidos_retencion' => 30,
            'pertenece_pepeferia' => null,
            'tipo_registro' => null,
            'descargar_cursos' => false,
        ];
    }
}
