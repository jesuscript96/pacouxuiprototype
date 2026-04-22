<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Helpers\SeederRoleNaming;
use App\Models\AliasTipoTransaccion;
use App\Models\CentroCosto;
use App\Models\Colaborador;
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
use App\Models\SpatieRole;
use App\Models\Subindustria;
use App\Models\TemaVozColaborador;
use App\Models\User;
use App\Services\ColaboradorService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Segunda empresa demo para pruebas multitenant (datos distintos a EmpresaEjemploSeeder).
 * Requiere: Inicial, EmpresaEjemploSeeder (o catálogos equivalentes), shield:generate + seeders de roles base.
 */
class Empresa2Seeder extends Seeder
{
    public const EMPRESA_NOMBRE = 'Empresa 2 (Multitenant)';

    protected const GUARD = 'web';

    public function run(): void
    {
        $industria = Industria::query()->first();
        $subindustria = Subindustria::query()->first();
        if (! $industria || ! $subindustria) {
            $this->command->warn('Ejecuta primero Inicial (Industria y Subindustria).');

            return;
        }

        $existente = Empresa::query()->where('nombre', self::EMPRESA_NOMBRE)->first();
        if ($existente !== null) {
            $this->command->info(self::EMPRESA_NOMBRE.' ya existe (ID '.$existente->id.'). Sincronizando roles y usuarios.');
            $this->seedRolesParaEmpresa($existente->id);
            $this->seedUsuariosPrueba($existente);
            $this->seedColaboradoresPrueba($existente);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $fechaInicio = now()->format('Y-m-d');
        $fechaFin = now()->addYear()->format('Y-m-d');

        $empresa = Empresa::withoutEvents(function () use ($industria, $subindustria, $fechaInicio, $fechaFin) {
            return Empresa::create([
                'nombre' => self::EMPRESA_NOMBRE,
                'nombre_contacto' => 'María Gómez',
                'email_contacto' => 'contacto@empresa2-demo.test',
                'telefono_contacto' => '5587654321',
                'movil_contacto' => '5581234567',
                'industria_id' => $industria->id,
                'sub_industria_id' => $subindustria->id,
                'email_facturacion' => 'facturacion@empresa2-demo.test',
                'fecha_inicio_contrato' => $fechaInicio,
                'fecha_fin_contrato' => $fechaFin,
                'num_usuarios_reportes' => 25,
                'activo' => true,
                'fecha_activacion' => now(),
                'nombre_app' => 'App Empresa 2',
                'link_descarga_app' => 'https://empresa2-demo.test/app',
                'app_android_id' => 'com.empresa2.demo',
                'app_ios_id' => '987654321',
                'app_huawei_id' => null,
                'color_primario' => '#0D47A1',
                'color_secundario' => '#00695C',
                'color_terciario' => '#E65100',
                'color_cuarto' => '#4A148C',
                'logo_url' => null,
                'tipo_comision' => 'PERCENTAGE',
                'comision_bisemanal' => 1.80,
                'comision_mensual' => 2.80,
                'comision_quincenal' => 2.20,
                'comision_semanal' => 1.20,
                'tiene_pagos_catorcenales' => false,
                'fecha_proximo_pago_catorcenal' => null,
                'tiene_sub_empresas' => false,
                'comision_gateway' => 0.45,
                'transacciones_con_imss' => true,
                'validar_cuentas_automaticamente' => true,
                'tiene_analiticas_por_ubicacion' => false,
                'version_android' => '2.0.0',
                'version_ios' => '2.0.0',
                'tiene_limite_de_sesiones' => false,
                'tiene_firma_nubarium' => false,
                'enviar_boletin' => false,
                'permitir_encuesta_salida' => true,
                'configuracion_app_id' => null,
                'activar_finiquito' => false,
                'url_finiquito' => null,
                'domiciliación_via_api' => false,
                'ha_firmado_nuevo_contrato' => true,
                'vigencia_mensajes_urgentes' => 15,
                'permitir_notificaciones_felicitaciones' => true,
                'segmento_notificaciones_felicitaciones' => 'COMPANY',
                'permitir_retenciones' => true,
                'dias_vencidos_retencion' => 20,
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

        $this->seedRolesParaEmpresa($empresa->id);
        $this->seedUsuariosPrueba($empresa);
        $this->seedColaboradoresPrueba($empresa);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info(self::EMPRESA_NOMBRE.' creada (ID: '.$empresa->id.'). Usuarios: admin@empresa2.test / rh@empresa2.test — contraseña: password123');
    }

    private function crearConfiguracionApp(Empresa $empresa): void
    {
        $config = ConfiguracionApp::create([
            'nombre_app' => $empresa->nombre_app ?? 'App Empresa 2',
            'android_app_id' => $empresa->app_android_id ?? 'com.empresa2.demo',
            'ios_app_id' => $empresa->app_ios_id ?? '987654321',
            'one_signal_app_id' => 'one-signal-empresa2',
            'one_signal_rest_api_key' => 'rest-api-key-empresa2',
            'link_descarga' => $empresa->link_descarga_app,
            'android_channel_id' => null,
            'version_ios' => '2.0.0',
            'version_android' => '2.0.0',
            'requiere_validacion' => true,
        ]);
        Empresa::withoutEvents(fn () => $empresa->update(['configuracion_app_id' => $config->id]));
    }

    private function crearRazonesSociales(Empresa $empresa): void
    {
        $razon = Razonsocial::create([
            'nombre' => 'Empresa 2 Multitenant S.A. de C.V.',
            'rfc' => 'E2M850101XXX',
            'cp' => '03100',
            'calle' => 'Av. Multitenant',
            'numero_exterior' => '200',
            'numero_interior' => 'Piso 2',
            'colonia' => 'Del Valle',
            'alcaldia' => 'Benito Juárez',
            'estado' => 'Ciudad de México',
            'registro_patronal' => 'E29876543',
        ]);
        if (! $empresa->razonesSociales()->where('razon_social_id', $razon->id)->exists()) {
            $empresa->razonesSociales()->attach($razon->id);
        }
    }

    private function asignarProductos(Empresa $empresa): void
    {
        $productos = Producto::query()->take(3)->get();
        foreach ($productos as $producto) {
            if (! $empresa->productos()->where('productos.id', $producto->id)->exists()) {
                $empresa->productos()->attach($producto->id, ['desde' => 1]);
            }
        }
    }

    private function asignarNotificacionesIncluidas(Empresa $empresa): void
    {
        $ids = NotificacionesIncluidas::query()->pluck('id');
        foreach ($ids as $id) {
            if (! $empresa->notificacionesIncluidas()->where('notificacion_incluida_id', $id)->exists()) {
                $empresa->notificacionesIncluidas()->attach($id);
            }
        }
    }

    private function crearComisionesRangos(Empresa $empresa): void
    {
        if (ComisionRango::query()->where('empresa_id', $empresa->id)->exists()) {
            return;
        }
        ComisionRango::create([
            'empresa_id' => $empresa->id,
            'tipo_comision' => 'PERCENTAGE',
            'precio_desde' => 0,
            'precio_hasta' => 4000,
            'cantidad_fija' => null,
            'porcentaje' => 1.25,
        ]);
        ComisionRango::create([
            'empresa_id' => $empresa->id,
            'tipo_comision' => 'PERCENTAGE',
            'precio_desde' => 4001,
            'precio_hasta' => 15000,
            'cantidad_fija' => null,
            'porcentaje' => 1.75,
        ]);
    }

    private function crearConfiguracionRetencionNominas(Empresa $empresa): void
    {
        if (ConfiguracionRetencionNomina::query()->where('empresa_id', $empresa->id)->exists()) {
            return;
        }
        ConfiguracionRetencionNomina::create([
            'empresa_id' => $empresa->id,
            'fecha' => now()->format('Y-m-d'),
            'dias' => 3,
            'dia_semana' => 3,
            'emails' => ['retenciones@empresa2-demo.test'],
            'periodicidad_pago' => 'QUINCENAL',
        ]);
    }

    private function asignarCentrosCostos(Empresa $empresa): void
    {
        $centros = CentroCosto::query()->take(3)->pluck('id');
        foreach ($centros as $cid) {
            if (! $empresa->centrosCostos()->where('centro_costo_id', $cid)->exists()) {
                $empresa->centrosCostos()->attach($cid);
            }
        }
    }

    private function asignarReconocimientos(Empresa $empresa): void
    {
        $reconocimiento = Reconocmiento::query()->first();
        if (! $reconocimiento) {
            $reconocimiento = Reconocmiento::create([
                'nombre' => 'Reconocimiento Empresa 2',
                'descripcion' => 'Reconocimiento demo multitenant',
                'es_enviable' => true,
                'es_exclusivo' => false,
                'menciones_necesarias' => 1,
            ]);
        }
        if (! $empresa->reconocimientos()->where('reconocimiento_id', $reconocimiento->id)->exists()) {
            $empresa->reconocimientos()->attach($reconocimiento->id, [
                'es_enviable' => true,
                'menciones_necesarias' => 1,
            ]);
        }
    }

    private function asignarTemasVozColaboradores(Empresa $empresa): void
    {
        $tema = TemaVozColaborador::query()->first();
        if (! $tema) {
            $tema = TemaVozColaborador::create([
                'nombre' => 'Tema Voz Empresa 2',
                'descripcion' => 'Tema demo multitenant',
                'exclusivo_para_empresa' => null,
            ]);
        }
        if (! $empresa->temasVozColaboradores()->where('temas_voz_colaboradores.id', $tema->id)->exists()) {
            $empresa->temasVozColaboradores()->attach($tema->id);
        }
    }

    private function crearAliasTipoTransacciones(Empresa $empresa): void
    {
        $alias = [
            ['tipo_transaccion' => 'NOMINA', 'alias' => 'Nómina E2'],
            ['tipo_transaccion' => 'SERVICIO', 'alias' => 'Servicio E2'],
        ];
        foreach ($alias as $a) {
            $exists = AliasTipoTransaccion::query()
                ->where('empresa_id', $empresa->id)
                ->where('tipo_transaccion', $a['tipo_transaccion'])
                ->exists();
            if (! $exists) {
                AliasTipoTransaccion::create([
                    'empresa_id' => $empresa->id,
                    'tipo_transaccion' => $a['tipo_transaccion'],
                    'alias' => $a['alias'],
                ]);
            }
        }
    }

    private function crearFrecuenciaNotificaciones(Empresa $empresa): void
    {
        if (FrecuenciaNotificaciones::query()->where('empresa_id', $empresa->id)->exists()) {
            return;
        }
        FrecuenciaNotificaciones::create([
            'empresa_id' => $empresa->id,
            'dias' => 14,
            'tipo' => 'estado_animo',
            'siguiente_fecha' => now()->addDays(14),
        ]);
    }

    private function crearQuincenaPersonalizada(Empresa $empresa): void
    {
        if (QuincenasPersonalizadas::query()->where('empresa_id', $empresa->id)->exists()) {
            return;
        }
        QuincenasPersonalizadas::create([
            'empresa_id' => $empresa->id,
            'dia_inicio' => 16,
            'dia_fin' => 30,
        ]);
    }

    private function crearRazonesEncuestaSalida(Empresa $empresa): void
    {
        $razones = ['Cambio de residencia', 'Mejor oferta laboral'];
        foreach ($razones as $razon) {
            $exists = RazonEncuestaSalida::query()
                ->where('empresa_id', $empresa->id)
                ->where('razon', $razon)
                ->exists();
            if (! $exists) {
                RazonEncuestaSalida::create([
                    'empresa_id' => $empresa->id,
                    'razon' => $razon,
                ]);
            }
        }
    }

    /**
     * Roles panel Cliente + permisos admin/rh alineados a RolesClienteSeeder y ShieldPermisosRolesSeeder.
     */
    private function seedRolesParaEmpresa(int $empresaId): void
    {
        $empresa = Empresa::query()->find($empresaId);
        if ($empresa === null) {
            return;
        }

        $permisosDepartamento = Permission::where('guard_name', self::GUARD)
            ->where('name', 'like', '%Departamento')
            ->where('name', 'not like', '%DepartamentoGeneral')
            ->pluck('name')
            ->toArray();

        $permisosDepartamentoGeneral = Permission::where('guard_name', self::GUARD)
            ->where('name', 'like', '%DepartamentoGeneral')
            ->pluck('name')
            ->toArray();

        $permisosCompletos = array_values(array_unique(array_merge($permisosDepartamento, $permisosDepartamentoGeneral)));
        $permisosSoloLectura = array_values(array_filter($permisosCompletos, static fn (string $name): bool => str_starts_with($name, 'ViewAny:') || str_starts_with($name, 'View:')));

        $gestor = SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'gestor_catalogos'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'Gestor de catálogos'),
                'description' => 'CRUD Departamentos (panel Cliente)',
            ]
        );
        $gestor->syncPermissions($permisosCompletos);

        $consultor = SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'consultor_catalogos'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'Consultor de catálogos'),
                'description' => 'Solo lectura catálogos (panel Cliente)',
            ]
        );
        $consultor->syncPermissions($permisosSoloLectura);

        SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'admin_empresa'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'Administrador de empresa'),
                'description' => 'Acceso completo panel Cliente (catálogos)',
            ]
        );

        SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'rh_empresa'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'RH Empresa'),
                'description' => 'RH lectura catálogos (panel Cliente)',
            ]
        );

        SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'colaborador'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'Colaborador'),
                'description' => 'Rol colaborador de la empresa',
            ]
        );

        $adminEmpresa = SpatieRole::withoutGlobalScopes()
            ->where('name', SeederRoleNaming::technical($empresa, 'admin_empresa'))
            ->where('company_id', $empresaId)
            ->first();
        if ($adminEmpresa) {
            $adminPermisos = Permission::whereIn('name', [
                'ViewAny:Empresa', 'View:Empresa', 'Create:Empresa', 'Update:Empresa', 'Delete:Empresa',
                'ViewAny:CentroCosto', 'View:CentroCosto', 'Create:CentroCosto', 'Update:CentroCosto', 'Delete:CentroCosto',
                'ViewAny:Industria', 'View:Industria', 'Create:Industria', 'Update:Industria',
                'ViewAny:Subindustria', 'View:Subindustria', 'Create:Subindustria', 'Update:Subindustria',
                'ViewAny:Producto', 'View:Producto', 'Create:Producto', 'Update:Producto',
                'ViewAny:NotificacionesIncluidas', 'View:NotificacionesIncluidas', 'Create:NotificacionesIncluidas', 'Update:NotificacionesIncluidas',
                'ViewAny:Reconocmiento', 'View:Reconocmiento', 'Create:Reconocmiento', 'Update:Reconocmiento',
                'ViewAny:TemaVozColaborador', 'View:TemaVozColaborador', 'Create:TemaVozColaborador', 'Update:TemaVozColaborador',
                'ViewAny:Departamento', 'View:Departamento', 'Create:Departamento', 'Update:Departamento', 'Delete:Departamento',
                'ViewAny:DepartamentoGeneral', 'View:DepartamentoGeneral', 'Create:DepartamentoGeneral', 'Update:DepartamentoGeneral', 'Delete:DepartamentoGeneral',
                'ViewAny:NotificacionPush', 'View:NotificacionPush', 'Create:NotificacionPush', 'Update:NotificacionPush', 'Delete:NotificacionPush',
            ])->pluck('name');
            $adminEmpresa->syncPermissions($adminPermisos);
        }

        $rhEmpresa = SpatieRole::withoutGlobalScopes()
            ->where('name', SeederRoleNaming::technical($empresa, 'rh_empresa'))
            ->where('company_id', $empresaId)
            ->first();
        if ($rhEmpresa) {
            $rhPermisos = Permission::whereIn('name', [
                'ViewAny:Empresa', 'View:Empresa',
                'ViewAny:CentroCosto', 'View:CentroCosto',
                'ViewAny:Industria', 'View:Industria',
                'ViewAny:Subindustria', 'View:Subindustria',
                'ViewAny:Producto', 'View:Producto',
                'ViewAny:NotificacionesIncluidas', 'View:NotificacionesIncluidas',
                'ViewAny:Reconocmiento', 'View:Reconocmiento',
                'ViewAny:TemaVozColaborador', 'View:TemaVozColaborador',
                'ViewAny:Departamento', 'View:Departamento',
                'ViewAny:DepartamentoGeneral', 'View:DepartamentoGeneral',
                'ViewAny:NotificacionPush', 'View:NotificacionPush',
            ])->pluck('name');
            $rhEmpresa->syncPermissions($rhPermisos);
        }

        $this->command->info('Roles Spatie creados/actualizados para empresa_id '.$empresaId.'.');
    }

    private function seedUsuariosPrueba(Empresa $empresa): void
    {
        $empresaId = $empresa->id;

        $usuariosCliente = [
            [
                'email' => 'admin@empresa2.test',
                'name' => 'Admin',
                'apellido_paterno' => 'Empresa2',
                'apellido_materno' => 'Demo',
                'numero_colaborador' => 'E2-FCH-ADMIN-DEMO',
                'rol' => 'admin_empresa',
            ],
            [
                'email' => 'rh@empresa2.test',
                'name' => 'RH',
                'apellido_paterno' => 'Empresa2',
                'apellido_materno' => 'Demo',
                'numero_colaborador' => 'E2-FCH-RH-DEMO',
                'rol' => 'rh_empresa',
            ],
        ];

        foreach ($usuariosCliente as $datos) {
            $colaborador = Colaborador::withoutEvents(function () use ($datos, $empresaId): Colaborador {
                return Colaborador::query()->firstOrCreate(
                    [
                        'email' => $datos['email'],
                        'empresa_id' => $empresaId,
                    ],
                    [
                        'nombre' => $datos['name'],
                        'apellido_paterno' => $datos['apellido_paterno'],
                        'apellido_materno' => $datos['apellido_materno'],
                        'numero_colaborador' => $datos['numero_colaborador'],
                        'fecha_nacimiento' => '1990-01-15',
                        'fecha_ingreso' => now()->format('Y-m-d'),
                        'periodicidad_pago' => 'QUINCENAL',
                        'verificado' => false,
                        'verificacion_carga_masiva' => false,
                    ]
                );
            });

            $user = User::query()->firstOrCreate(
                ['email' => $datos['email']],
                [
                    'name' => $datos['name'],
                    'apellido_paterno' => $datos['apellido_paterno'],
                    'apellido_materno' => $datos['apellido_materno'],
                    'password' => 'password123',
                    'tipo' => ['cliente'],
                    'empresa_id' => $empresaId,
                    'colaborador_id' => $colaborador->id,
                    'numero_colaborador' => $colaborador->numero_colaborador,
                ]
            );

            if ((int) $user->colaborador_id !== (int) $colaborador->id || (int) $user->empresa_id !== $empresaId) {
                $user->update([
                    'colaborador_id' => $colaborador->id,
                    'empresa_id' => $empresaId,
                    'numero_colaborador' => $colaborador->numero_colaborador,
                ]);
            }

            $user->empresas()->syncWithoutDetaching([$empresaId]);

            $rol = SeederRoleNaming::findForCompany($empresaId, $datos['rol']);
            if ($rol && ! $user->hasRole($rol->name)) {
                $user->assignRole($rol);
            }
        }

        $this->command->info('Usuarios admin@empresa2.test y rh@empresa2.test listos (ficha colaborador + password123).');
    }

    private function seedColaboradoresPrueba(Empresa $empresa): void
    {
        $empresaId = $empresa->id;
        $colaboradorService = app(ColaboradorService::class);

        foreach (['colaborador.uno@empresa2.test', 'colaborador.dos@empresa2.test'] as $email) {
            $u = User::query()->where('email', $email)->first();
            if ($u !== null && $u->colaborador_id === null) {
                $u->forceDelete();
                $u = null;
            }
            if ($u === null) {
                $u = $colaboradorService->crearColaborador([
                    'name' => str_contains($email, 'uno') ? 'Colaborador' : 'Colaboradora',
                    'apellido_paterno' => 'Prueba',
                    'apellido_materno' => 'E2',
                    'email' => $email,
                    'password' => 'password123',
                    'fecha_nacimiento' => '1992-05-10',
                    'fecha_ingreso' => now()->subMonths(6)->format('Y-m-d'),
                    'periodicidad_pago' => 'QUINCENAL',
                    'numero_colaborador' => 'E2-'.substr(md5($email), 0, 6),
                ], $empresa);
            }
            $u->forceFill(['empresa_id' => $empresaId])->save();
            $u->empresas()->syncWithoutDetaching([$empresaId]);

            $rolColab = SeederRoleNaming::findForCompany($empresaId, 'colaborador');
            if ($rolColab && ! $u->hasRole($rolColab->name)) {
                $u->assignRole($rolColab);
            }
        }

        $this->command->info('Colaboradores de prueba (colaborador.*@empresa2.test) listos.');
    }
}
