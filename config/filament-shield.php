<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Shield Resource
    |--------------------------------------------------------------------------
    |
    | Here you may configure the built-in role management resource. You can
    | customize the URL, choose whether to show model paths, group it under
    | a cluster, and decide which permission tabs to display.
    |
    */

    'shield_resource' => [
        'slug' => 'shield/roles',
        'show_model_path' => true,
        'cluster' => null,
        'tabs' => [
            'pages' => true,
            'widgets' => true,
            'resources' => true,
            'custom_permissions' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | When your application supports teams, Shield will automatically detect
    | and configure the tenant model during setup. This enables tenant-scoped
    | roles and permissions throughout your application.
    |
    */

    'tenant_model' => null,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This value contains the class name of your user model. This model will
    | be used for role assignments and must implement the HasRoles trait
    | provided by the Spatie\Permission package.
    |
    */

    'auth_provider_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    |
    | Here you may define a super admin that has unrestricted access to your
    | application. You can choose to implement this via Laravel's gate system
    | or as a traditional role with all permissions explicitly assigned.
    |
    */

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => false,
        'intercept_gate' => 'before',
    ],

    /*
    |--------------------------------------------------------------------------
    | Panel User
    |--------------------------------------------------------------------------
    |
    | When enabled, Shield will create a basic panel user role that can be
    | assigned to users who should have access to your Filament panels but
    | don't need any specific permissions beyond basic authentication.
    |
    */

    'panel_user' => [
        'enabled' => true,
        'name' => 'panel_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Builder
    |--------------------------------------------------------------------------
    |
    | You can customize how permission keys are generated to match your
    | preferred naming convention and organizational standards. Shield uses
    | these settings when creating permission names from your resources.
    |
    | Supported formats: snake, kebab, pascal, camel, upper_snake, lower_snake
    |
    */

    'permissions' => [
        'separator' => ':',
        'case' => 'pascal',
        'generate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Policies
    |--------------------------------------------------------------------------
    |
    | Shield can automatically generate Laravel policies for your resources.
    | When merge is enabled, the methods below will be combined with any
    | resource-specific methods you define in the resources section.
    |
    */

    'policies' => [
        'path' => app_path('Policies'),
        'merge' => true,
        'generate' => true,
        'methods' => [
            'viewAny', 'view', 'create', 'update', 'delete', 'deleteAny', 'restore',
            'forceDelete', 'forceDeleteAny', 'restoreAny', 'replicate', 'reorder',
        ],
        'single_parameter_methods' => [
            'viewAny',
            'create',
            'deleteAny',
            'forceDeleteAny',
            'restoreAny',
            'reorder',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Shield supports multiple languages out of the box. When enabled, you
    | can provide translated labels for permissions to create a more
    | localized experience for your international users.
    |
    */

    'localization' => [
        'enabled' => false,
        'key' => 'filament-shield::filament-shield.resource_permission_prefixes_labels',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Here you can fine-tune permissions for specific Filament resources.
    | Use the 'manage' array to override the default policy methods for
    | individual resources, giving you granular control over permissions.
    |
    */

    'resources' => [
        'subject' => 'model',
        'manage' => [
            \BezhanSalleh\FilamentShield\Resources\Roles\RoleResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
            ],
            \App\Filament\Resources\Usuarios\UsuarioResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
            ],
            \App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource::class => [
                'viewAny',
                'view',
                'create',
                'update',
                'delete',
                'restore',
                'forceDelete',
                'forceDeleteAny',
                'restoreAny',
                'replicate',
                'reorder',
                'upload',
                'import',
                'bulkUpdate',
            ],
            \App\Filament\Cliente\Resources\VerDestinatariosDocumentos\VerDestinatariosDocumentosResource::class => [
                'viewAny',
                'view',
            ],
        ],
        'exclude' => [
            \App\Filament\Resources\ActivityLogs\ActivityLogResource::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Most Filament pages only require view permissions. Pages listed in the
    | exclude array will be skipped during permission generation and won't
    | appear in your role management interface.
    |
    */

    'pages' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            \Filament\Pages\Dashboard::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    |
    | Like pages, widgets typically only need view permissions. Add widgets
    | to the exclude array if you don't want them to appear in your role
    | management interface.
    |
    */

    'widgets' => [
        'subject' => 'class',
        'prefix' => 'view',
        'exclude' => [
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Permissions
    |--------------------------------------------------------------------------
    |
    | Sometimes you need permissions that don't map to resources, pages, or
    | widgets. Define any custom permissions here and they'll be available
    | when editing roles in your application.
    |
    */

    'custom_permissions' => [
        // Colaboradores (panel Cliente)
        'Upload:Colaborador' => 'Subir colaboradores',
        'Import:Colaborador' => 'Importar colaboradores',
        'BulkUpdate:Colaborador' => 'Actualización masiva colaboradores',
        // Colaboradores (legacy / app)
        'upload_archivo_colaborador' => 'Subir archivo colaborador',
        'cargar_autorizadores' => 'Cargar autorizadores',
        'view_historial_laboral' => 'Ver historial laboral',
        'view_documento_poliza' => 'Ver documento póliza',
        'view_baja_colaborador' => 'Ver bajas colaboradores',
        // Encuestas
        'duplicate_encuesta' => 'Duplicar encuesta',
        'send_encuesta' => 'Enviar encuesta',
        'close_encuesta' => 'Cerrar encuesta',
        'update_envio_encuesta' => 'Editar envío encuesta',
        'delete_envio_encuesta' => 'Eliminar envío encuesta',
        'view_encuestas_empresas' => 'Ver encuestas empresas',
        // Voz
        'segmentar_voz' => 'Segmentar voz colaborador',
        'view_tema_voz' => 'Ver temas voz',
        'create_tema_voz' => 'Crear tema voz',
        'update_tema_voz' => 'Editar tema voz',
        'delete_tema_voz' => 'Eliminar tema voz',
        // Reconocimientos
        'view_reconocimiento_enviado' => 'Ver reconocimientos enviados',
        // Nómina / Cobranza
        'process_cuenta_por_cobrar' => 'Procesar cuentas por cobrar',
        'generate_reporte_interno' => 'Generar reportes internos',
        'delete_penalizacion' => 'Borrar penalizaciones',
        'view_cobranzas' => 'Ver cobranzas',
        'delete_recibo_nomina' => 'Eliminar recibos nómina',
        // Reclutamiento — Vacantes
        'ViewAny:Vacante' => 'Ver listado de vacantes',
        'View:Vacante' => 'Ver detalle de vacante',
        'Create:Vacante' => 'Crear vacante',
        'Update:Vacante' => 'Editar vacante',
        'Delete:Vacante' => 'Eliminar vacante',
        // Reclutamiento — Candidatos
        'ViewAny:CandidatoReclutamiento' => 'Ver listado de candidatos',
        'View:CandidatoReclutamiento' => 'Ver detalle de candidato',
        'Update:CandidatoReclutamiento' => 'Editar candidato',
        'Delete:CandidatoReclutamiento' => 'Eliminar candidato',
        // Reclutamiento — Mensajes
        'Delete:MensajeCandidato' => 'Eliminar comentario de candidato',
        // Documentos
        'view_archivo_empresa' => 'Ver archivos empresa',
        'create_archivo_empresa' => 'Crear archivos empresa',
        'update_archivo_empresa' => 'Editar archivos empresa',
        'delete_archivo_empresa' => 'Eliminar archivos empresa',
        'view_contrato_laboral' => 'Ver contratos laborales',
        'create_contrato_laboral' => 'Crear contratos laborales',
        'update_contrato_laboral' => 'Editar contratos laborales',
        'delete_contrato_laboral' => 'Eliminar contratos laborales',
        'sign_contrato_laboral' => 'Firmar contratos',
        // Capacitación
        'view_capacitacion' => 'Ver capacitaciones',
        'create_capacitacion' => 'Crear capacitaciones',
        'delete_capacitacion' => 'Eliminar capacitaciones',
        // Notificaciones
        'view_notificaciones_empresas' => 'Ver notificaciones empresas',
        // Seguros
        'view_membresia_seguro' => 'Ver membresías seguros',
        // Reportes
        'view_tablero_salud_mental' => 'Ver tablero salud mental',
        // Empresa
        'view_carrusel_empresa' => 'Ver carrusel empresa',
        'update_carrusel_empresa' => 'Editar carrusel empresa',
        // Catálogos generales
        'view_area_general' => 'Ver áreas generales',
        'create_area_general' => 'Crear áreas generales',
        'update_area_general' => 'Editar áreas generales',
        'delete_area_general' => 'Eliminar áreas generales',
        'view_departamento_general' => 'Ver departamentos generales',
        'create_departamento_general' => 'Crear departamentos generales',
        'update_departamento_general' => 'Editar departamentos generales',
        'delete_departamento_general' => 'Eliminar departamentos generales',
        'view_puesto_general' => 'Ver puestos generales',
        'create_puesto_general' => 'Crear puestos generales',
        'update_puesto_general' => 'Editar puestos generales',
        'delete_puesto_general' => 'Eliminar puestos generales',
        // Gestión productos
        'view_gestion_producto_empresa' => 'Ver gestión productos empresa',
        'update_gestion_producto_empresa' => 'Editar gestión productos empresa',
        // Solicitudes
        'view_categoria_solicitud' => 'Ver categorías solicitud',
        'create_categoria_solicitud' => 'Crear categorías solicitud',
        'update_categoria_solicitud' => 'Editar categorías solicitud',
        'delete_categoria_solicitud' => 'Eliminar categorías solicitud',
        'view_tipo_solicitud' => 'Ver tipos solicitud',
        'create_tipo_solicitud' => 'Crear tipos solicitud',
        'update_tipo_solicitud' => 'Editar tipos solicitud',
        'delete_tipo_solicitud' => 'Eliminar tipos solicitud',
        // Estado ánimo
        'view_estado_animo' => 'Ver estados ánimo',
        'create_estado_animo' => 'Crear estados ánimo',
        'update_estado_animo' => 'Editar estados ánimo',
        'delete_estado_animo' => 'Eliminar estados ánimo',
        // Sistema
        'load_codigo_ios' => 'Cargar códigos iOS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Entity Discovery
    |--------------------------------------------------------------------------
    |
    | By default, Shield only looks for entities in your default Filament
    | panel. Enable these options if you're using multiple panels and want
    | Shield to discover entities across all of them.
    |
    */

    'discovery' => [
        'discover_all_resources' => false,
        'discover_all_widgets' => false,
        'discover_all_pages' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Policy
    |--------------------------------------------------------------------------
    |
    | Shield can automatically register a policy for role management itself.
    | This lets you control who can manage roles using Laravel's built-in
    | authorization system. Requires a RolePolicy class in your app.
    |
    */

    'register_role_policy' => true,

];
