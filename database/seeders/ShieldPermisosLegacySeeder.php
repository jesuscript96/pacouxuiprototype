<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class ShieldPermisosLegacySeeder extends Seeder
{
    protected const GUARD = 'web';

    /**
     * Permisos adicionales por módulo (nombre legacy snake_case).
     * Se crean en BD con PascalCase para coincidir con Shield (config permissions.case = pascal).
     */
    protected function permisosPorModulo(): array
    {
        return [
            'Colaboradores' => [
                'upload_archivo_colaborador',
                'cargar_autorizadores',
                'view_historial_laboral',
                'view_documento_poliza',
                'view_baja_colaborador',
            ],
            'Encuestas' => [
                'duplicate_encuesta',
                'send_encuesta',
                'close_encuesta',
                'update_envio_encuesta',
                'delete_envio_encuesta',
                'view_encuestas_empresas',
            ],
            'Voz' => [
                'segmentar_voz',
                'view_tema_voz',
                'create_tema_voz',
                'update_tema_voz',
                'delete_tema_voz',
            ],
            'Reconocimientos' => [
                'view_reconocimiento_enviado',
            ],
            'Nómina / Cobranza' => [
                'process_cuenta_por_cobrar',
                'generate_reporte_interno',
                'delete_penalizacion',
                'view_cobranzas',
                'delete_recibo_nomina',
            ],
            'Reclutamiento' => [
                'ViewAny:Vacante',
                'View:Vacante',
                'Create:Vacante',
                'Update:Vacante',
                'Delete:Vacante',
                'ViewAny:CandidatoReclutamiento',
                'View:CandidatoReclutamiento',
                'Update:CandidatoReclutamiento',
                'Delete:CandidatoReclutamiento',
                'Delete:MensajeCandidato',
            ],
            'Documentos' => [
                'view_archivo_empresa',
                'create_archivo_empresa',
                'update_archivo_empresa',
                'delete_archivo_empresa',
                'view_contrato_laboral',
                'create_contrato_laboral',
                'update_contrato_laboral',
                'delete_contrato_laboral',
                'sign_contrato_laboral',
            ],
            'Capacitación' => [
                'view_capacitacion',
                'create_capacitacion',
                'delete_capacitacion',
            ],
            'Notificaciones' => [
                'view_notificaciones_empresas',
            ],
            'Seguros' => [
                'view_membresia_seguro',
            ],
            'Reportes' => [
                'view_tablero_salud_mental',
            ],
            'Empresa' => [
                'view_carrusel_empresa',
                'update_carrusel_empresa',
            ],
            'Catálogos generales' => [
                'view_area_general',
                'create_area_general',
                'update_area_general',
                'delete_area_general',
                'view_departamento_general',
                'create_departamento_general',
                'update_departamento_general',
                'delete_departamento_general',
                'view_puesto_general',
                'create_puesto_general',
                'update_puesto_general',
                'delete_puesto_general',
            ],
            'Gestión productos' => [
                'view_gestion_producto_empresa',
                'update_gestion_producto_empresa',
            ],
            'Solicitudes' => [
                'view_categoria_solicitud',
                'create_categoria_solicitud',
                'update_categoria_solicitud',
                'delete_categoria_solicitud',
                'view_tipo_solicitud',
                'create_tipo_solicitud',
                'update_tipo_solicitud',
                'delete_tipo_solicitud',
            ],
            'Estado ánimo' => [
                'view_estado_animo',
                'create_estado_animo',
                'update_estado_animo',
                'delete_estado_animo',
            ],
            'Cartas SUA' => [
                'ViewAny:CartaSua',
                'View:CartaSua',
                'Create:CartaSua',
                'Delete:CartaSua',
            ],
            'Sistema' => [
                'load_codigo_ios',
            ],
        ];
    }

    /**
     * Convierte nombre snake_case a PascalCase (Shield custom_permissions usa permissions.case = pascal).
     * Si prefieres mantener snake_case en BD, cambia a: return $snake;
     */
    protected function toPascalName(string $snake): string
    {
        return (string) Str::of($snake)->studly();
    }

    public function run(): void
    {
        $created = 0;

        foreach ($this->permisosPorModulo() as $modulo => $permisos) {
            foreach ($permisos as $name) {
                $pascalName = $this->toPascalName($name);
                $p = Permission::firstOrCreate(
                    [
                        'name' => $pascalName,
                        'guard_name' => self::GUARD,
                    ],
                    ['name' => $pascalName, 'guard_name' => self::GUARD]
                );
                if ($p->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        $this->command->info('Permisos legacy: '.$created.' creados. Total en BD: '.Permission::count());
    }
}
