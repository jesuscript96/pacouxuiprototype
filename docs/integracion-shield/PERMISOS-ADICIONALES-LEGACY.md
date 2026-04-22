# Permisos adicionales creados en Shield (paridad legacy)

## 1. Resumen

- **Permisos existentes antes:** 99 (generados por `shield:generate` para recursos/páginas/widgets).
- **Permisos creados ahora:** 70 (custom por módulo legacy).
- **Total permisos:** 169.

Los nuevos permisos se crean en PascalCase para coincidir con la config de Shield (`permissions.case` = `pascal`). Ejemplo: `upload_archivo_empleado` → nombre en BD `UploadArchivoEmpleado`.

## 2. Permisos creados por módulo

| Módulo | Permisos (nombre en BD PascalCase) |
|--------|-------------------------------------|
| **Empleados** | UploadArchivoEmpleado, CargarAutorizadores, ViewHistorialLaboral, ViewDocumentoPoliza, ViewBajaEmpleado |
| **Encuestas** | DuplicateEncuesta, SendEncuesta, CloseEncuesta, UpdateEnvioEncuesta, DeleteEnvioEncuesta, ViewEncuestasEmpresas |
| **Voz** | SegmentarVoz, ViewTemaVoz, CreateTemaVoz, UpdateTemaVoz, DeleteTemaVoz |
| **Reconocimientos** | ViewReconocimientoEnviado |
| **Nómina / Cobranza** | ProcessCuentaPorCobrar, GenerateReporteInterno, DeletePenalizacion, ViewCobranzas, DeleteReciboNomina |
| **Reclutamiento** | ViewCandidato, UpdateCandidato, DeleteCandidato, DeleteComentarioCandidato |
| **Documentos** | ViewArchivoEmpresa, CreateArchivoEmpresa, UpdateArchivoEmpresa, DeleteArchivoEmpresa, ViewContratoLaboral, CreateContratoLaboral, UpdateContratoLaboral, DeleteContratoLaboral, SignContratoLaboral |
| **Capacitación** | ViewCapacitacion, CreateCapacitacion, DeleteCapacitacion |
| **Notificaciones** | ViewNotificacionesEmpresas |
| **Seguros** | ViewMembresiaSeguro |
| **Reportes** | ViewTableroSaludMental |
| **Empresa** | ViewCarruselEmpresa, UpdateCarruselEmpresa |
| **Catálogos generales** | ViewAreaGeneral, CreateAreaGeneral, UpdateAreaGeneral, DeleteAreaGeneral, ViewDepartamentoGeneral, CreateDepartamentoGeneral, UpdateDepartamentoGeneral, DeleteDepartamentoGeneral, ViewPuestoGeneral, CreatePuestoGeneral, UpdatePuestoGeneral, DeletePuestoGeneral |
| **Gestión productos** | ViewGestionProductoEmpresa, UpdateGestionProductoEmpresa |
| **Solicitudes** | ViewCategoriaSolicitud, CreateCategoriaSolicitud, UpdateCategoriaSolicitud, DeleteCategoriaSolicitud, ViewTipoSolicitud, CreateTipoSolicitud, UpdateTipoSolicitud, DeleteTipoSolicitud |
| **Estado ánimo** | ViewEstadoAnimo, CreateEstadoAnimo, UpdateEstadoAnimo, DeleteEstadoAnimo |
| **Sistema** | LoadCodigoIos |

## 3. Cambios realizados

- **Seeder:** `database/seeders/ShieldPermisosLegacySeeder.php` — crea los 70 permisos con `Permission::firstOrCreate` (nombre PascalCase, `guard_name` = `web`).
- **Config:** `config/filament-shield.php`:
  - `shield_resource.tabs.custom_permissions` = `true` para mostrar la pestaña en el recurso de roles.
  - `custom_permissions` rellenado con clave snake_case => etiqueta en español (Shield formatea la clave a PascalCase para coincidir con la BD).
- **Rol super_admin:** Sincronizado con los 169 permisos (99 + 70).

## 4. Verificaciones

- Seeder ejecutado sin errores: `php artisan db:seed --class=ShieldPermisosLegacySeeder`
- Total permisos en BD: 169
- Permisos visibles en la UI de Shield al editar un rol (pestaña "Custom Permissions" si está habilitada)
- super_admin tiene todos los permisos asignados

## 5. Uso en código

Para comprobar un permiso custom:

```php
// Nombre en BD (PascalCase)
auth()->user()->can('ViewCobranzas');
auth()->user()->can('UploadArchivoEmpleado');

// O con Gate
Gate::allows('ViewTableroSaludMental');
```

Para asignar a un rol desde tinker o seeder:

```php
$rol->givePermissionTo(['ViewCobranzas', 'ProcessCuentaPorCobrar']);
```

## 6. Próximos pasos sugeridos

- Asignar permisos a roles (admin_empresa, rh_empresa, etc.) según la matriz de negocio del legacy (ver `docs/contexto-legacy/ANALISIS_ROLES_Y_PERMISOS_LEGACY.md`).
- Proteger rutas/recursos con `$this->authorize('UploadArchivoEmpleado')` o políticas que usen estos permisos.
- Documentar la matriz rol → permisos para el equipo.
