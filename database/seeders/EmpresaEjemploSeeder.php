<?php

namespace Database\Seeders;

use App\Models\AliasTipoTransaccion;
use App\Models\CentroCosto;
use App\Models\ComisionRango;
use App\Models\ConfiguracionApp;
use App\Models\ConfiguracionRetencionNomina;
use App\Models\Empresa;
use App\Models\FrecuenciaNotificaciones;
use App\Models\Industria;
use App\Models\NotificacionesIncluidas;
use App\Models\Producto;
use App\Models\QuincenasPersonalizadas;
use App\Models\RazonEncuestaSalida;
use App\Models\Razonsocial;
use App\Models\Reconocmiento;
use App\Models\Subindustria;
use App\Models\TemaVozColaborador;
use Illuminate\Database\Seeder;

class EmpresaEjemploSeeder extends Seeder
{
    /**
     * Crea una Empresa de ejemplo con todas sus relaciones (tablas alternas/adicionales).
     * Debe ejecutarse después de Inicial (Industria, Subindustria, Producto, NotificacionesIncluidas, CentroCosto).
     */
    public function run(): void
    {
        $industria = Industria::query()->first();
        $subindustria = Subindustria::query()->first();
        if (! $industria || ! $subindustria) {
            $this->command->warn('Ejecuta primero el seeder Inicial (Industria y Subindustria).');

            return;
        }

        $fechaInicio = now()->format('Y-m-d');
        $fechaFin = now()->addYear()->format('Y-m-d');

        $empresa = Empresa::withoutEvents(function () use ($industria, $subindustria, $fechaInicio, $fechaFin) {
            return Empresa::create([
                'nombre' => 'Empresa Ejemplo S.A. de C.V.',
                'nombre_contacto' => 'Juan Pérez',
                'email_contacto' => 'contacto@empresaejemplo.com',
                'telefono_contacto' => '5512345678',
                'movil_contacto' => '5518765432',
                'industria_id' => $industria->id,
                'sub_industria_id' => $subindustria->id,
                'email_facturacion' => 'facturacion@empresaejemplo.com',
                'fecha_inicio_contrato' => $fechaInicio,
                'fecha_fin_contrato' => $fechaFin,
                'num_usuarios_reportes' => 50,
                'activo' => true,
                'fecha_activacion' => now(),
                'nombre_app' => 'App Empresa Ejemplo',
                'link_descarga_app' => 'https://ejemplo.com/app',
                'app_android_id' => 'com.ejemplo.app',
                'app_ios_id' => '123456789',
                'app_huawei_id' => null,
                'color_primario' => '#1976D2',
                'color_secundario' => '#388E3C',
                'color_terciario' => '#F57C00',
                'color_cuarto' => '#7B1FA2',
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
                'version_android' => '1.0.0',
                'version_ios' => '1.0.0',
                'tiene_limite_de_sesiones' => false,
                'tiene_firma_nubarium' => false,
                'enviar_boletin' => true,
                'permitir_encuesta_salida' => true,
                'configuracion_app_id' => null,
                'activar_finiquito' => false,
                'url_finiquito' => null,
                'domiciliación_via_api' => false,
                'ha_firmado_nuevo_contrato' => true,
                'vigencia_mensajes_urgentes' => 30,
                'permitir_notificaciones_felicitaciones' => true,
                'segmento_notificaciones_felicitaciones' => 'COMPANY',
                'permitir_retenciones' => true,
                'dias_vencidos_retencion' => 30,
                'pertenece_pepeferia' => false,
                'tipo_registro' => null,
                'descargar_cursos' => true,
            ]);
        });

        $this->crearConfiguracionApp($empresa);
        $this->crearRazonesSociales($empresa);
        $this->asignarProductos($empresa);
        $this->asignarNotificacionesIncluidas($empresa);
        $this->crearComisionesRangos($empresa);
        $this->crearConfiguracionRetencionNominas($empresa);
        $this->asignarCentrosCostos($empresa);
        $this->asignarReconocimientos($empresa);
        $this->asignarTemasVozColaboradores($empresa);
        $this->crearAliasTipoTransacciones($empresa);
        $this->crearFrecuenciaNotificaciones($empresa);
        $this->crearQuincenaPersonalizada($empresa);
        $this->crearRazonesEncuestaSalida($empresa);

        $this->command->info('Empresa de ejemplo creada: '.$empresa->nombre.' (ID: '.$empresa->id.').');
    }

    private function crearConfiguracionApp(Empresa $empresa): void
    {
        $config = ConfiguracionApp::create([
            'nombre_app' => $empresa->nombre_app ?? 'App Ejemplo',
            'android_app_id' => $empresa->app_android_id ?? 'com.ejemplo.app',
            'ios_app_id' => $empresa->app_ios_id ?? '123456789',
            'one_signal_app_id' => 'one-signal-ejemplo',
            'one_signal_rest_api_key' => 'rest-api-key-ejemplo',
            'link_descarga' => $empresa->link_descarga_app,
            'android_channel_id' => null,
            'version_ios' => '1.0.0',
            'version_android' => '1.0.0',
            'requiere_validacion' => true,
        ]);
        Empresa::withoutEvents(fn () => $empresa->update(['configuracion_app_id' => $config->id]));
    }

    private function crearRazonesSociales(Empresa $empresa): void
    {
        $razon = Razonsocial::create([
            'nombre' => 'Empresa Ejemplo S.A. de C.V.',
            'rfc' => 'EEE850101XXX',
            'cp' => '01000',
            'calle' => 'Av. Ejemplo',
            'numero_exterior' => '100',
            'numero_interior' => 'Piso 1',
            'colonia' => 'Centro',
            'alcaldia' => 'Cuauhtémoc',
            'estado' => 'Ciudad de México',
            'registro_patronal' => 'E12345678',
        ]);
        $empresa->razonesSociales()->attach($razon->id);
    }

    private function asignarProductos(Empresa $empresa): void
    {
        $productos = Producto::query()->take(3)->get();
        foreach ($productos as $producto) {
            $empresa->productos()->attach($producto->id, ['desde' => 1]);
        }
    }

    private function asignarNotificacionesIncluidas(Empresa $empresa): void
    {
        $ids = NotificacionesIncluidas::query()->pluck('id');
        $empresa->notificacionesIncluidas()->attach($ids);
    }

    private function crearComisionesRangos(Empresa $empresa): void
    {
        ComisionRango::create([
            'empresa_id' => $empresa->id,
            'tipo_comision' => 'PERCENTAGE',
            'precio_desde' => 0,
            'precio_hasta' => 5000,
            'cantidad_fija' => null,
            'porcentaje' => 1.5,
        ]);
        ComisionRango::create([
            'empresa_id' => $empresa->id,
            'tipo_comision' => 'PERCENTAGE',
            'precio_desde' => 5001,
            'precio_hasta' => 20000,
            'cantidad_fija' => null,
            'porcentaje' => 2.0,
        ]);
    }

    private function crearConfiguracionRetencionNominas(Empresa $empresa): void
    {
        ConfiguracionRetencionNomina::create([
            'empresa_id' => $empresa->id,
            'fecha' => now()->format('Y-m-d'),
            'dias' => 5,
            'dia_semana' => 5,
            'emails' => ['retenciones@empresaejemplo.com'],
            'periodicidad_pago' => 'QUINCENAL',
        ]);
    }

    private function asignarCentrosCostos(Empresa $empresa): void
    {
        $centros = CentroCosto::query()->take(3)->pluck('id');
        $empresa->centrosCostos()->attach($centros);
    }

    private function asignarReconocimientos(Empresa $empresa): void
    {
        $reconocimiento = Reconocmiento::query()->first();
        if (! $reconocimiento) {
            $reconocimiento = Reconocmiento::create([
                'nombre' => 'Reconocimiento Ejemplo',
                'descripcion' => 'Reconocimiento de ejemplo para empresa demo',
                'es_enviable' => true,
                'es_exclusivo' => false,
                'menciones_necesarias' => 1,
            ]);
        }
        $empresa->reconocimientos()->attach($reconocimiento->id, [
            'es_enviable' => true,
            'menciones_necesarias' => 1,
        ]);
    }

    private function asignarTemasVozColaboradores(Empresa $empresa): void
    {
        $tema = TemaVozColaborador::query()->first();
        if (! $tema) {
            $tema = TemaVozColaborador::create([
                'nombre' => 'Tema Voz Ejemplo',
                'descripcion' => 'Tema de voz del colaborador de ejemplo',
                'exclusivo_para_empresa' => null,
            ]);
        }
        $empresa->temasVozColaboradores()->attach($tema->id);
    }

    private function crearAliasTipoTransacciones(Empresa $empresa): void
    {
        $alias = [
            ['tipo_transaccion' => 'NOMINA', 'alias' => 'Nómina'],
            ['tipo_transaccion' => 'SERVICIO', 'alias' => 'Servicio'],
            ['tipo_transaccion' => 'RECARGA', 'alias' => 'Recarga'],
        ];
        foreach ($alias as $a) {
            AliasTipoTransaccion::create([
                'empresa_id' => $empresa->id,
                'tipo_transaccion' => $a['tipo_transaccion'],
                'alias' => $a['alias'],
            ]);
        }
    }

    private function crearFrecuenciaNotificaciones(Empresa $empresa): void
    {
        FrecuenciaNotificaciones::create([
            'empresa_id' => $empresa->id,
            'dias' => 7,
            'tipo' => 'estado_animo',
            'siguiente_fecha' => now()->addDays(7),
        ]);
    }

    private function crearQuincenaPersonalizada(Empresa $empresa): void
    {
        QuincenasPersonalizadas::create([
            'empresa_id' => $empresa->id,
            'dia_inicio' => 1,
            'dia_fin' => 15,
        ]);
    }

    private function crearRazonesEncuestaSalida(Empresa $empresa): void
    {
        $razones = ['Abandono del puesto', 'Renuncia voluntaria'];
        foreach ($razones as $razon) {
            RazonEncuestaSalida::create([
                'empresa_id' => $empresa->id,
                'razon' => $razon,
            ]);
        }
    }
}
