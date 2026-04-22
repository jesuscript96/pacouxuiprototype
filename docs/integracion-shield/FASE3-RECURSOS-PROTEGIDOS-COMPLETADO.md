# FASE 3 COMPLETADA - RECURSOS PROTEGIDOS

## 1. CAMBIOS REALIZADOS

- **Permisos generados:** `php artisan shield:generate --all --panel=admin --option=policies_and_permissions`. 99 permisos y 9 políticas (recursos) procesados.

- **Trait de políticas:** `App\Policies\Concerns\HasShieldPolicyHelpers`
  - `isSuperAdmin($user)`: usuario con rol super_admin.
  - `usuario($user)`: casteo a `Usuario` si aplica.
  - `canAccessEmpresa($user, $empresaId)`: acceso solo a la propia empresa (o super_admin).

- **Políticas con scope por empresa:**
  - **EmpresaPolicy:** view/update/delete/restore/forceDelete/replicate solo si el usuario es super_admin o (tiene permiso y `empresa->id === user->empresa_id`). Listado filtrado en `ListEmpresas::getTableQuery()` por `empresa_id` del usuario cuando no es super_admin.
  - **CentroCostoPolicy:** view/update/delete/restore/forceDelete/replicate exigen que el centro esté vinculado a la empresa del usuario (relación `empresas()`). Listado filtrado en `ListCentroCostos::getTableQuery()` con `whereHas('empresas', ... user->empresa_id)`.

- **Políticas con bypass super_admin (catálogos globales):** Industria, Subindustria, NotificacionesIncluidas, Producto, Reconocmiento, TemaVozColaborador. Todas usan `HasShieldPolicyHelpers` y `$this->isSuperAdmin($authUser) || $authUser->can(...)`.

- **Acciones condicionales en tablas:**
  - **EmpresasTable:** EditAction y DeleteAction con `->visible(fn ($record) => auth()->user()?->can('update', $record))` y `can('delete', $record)`. Bulk actions con `can('deleteAny', ...)`, `can('forceDeleteAny', ...)`, `can('restoreAny', ...)`.
  - **CentroCostosTable:** Misma pauta para acciones por fila y bulk.

- **Seeder de permisos por rol:** `ShieldPermisosRolesSeeder`
  - Asigna permisos a `admin_empresa` (empresa_id = 1): ViewAny/View/Create/Update/Delete para Empresa, CentroCosto, Industria, Subindustria, Producto, NotificacionesIncluidas, Reconocmiento, TemaVozColaborador.
  - Asigna permisos a `rh_empresa` (empresa_id = 1): solo ViewAny y View para esos mismos recursos.
  - Solo corre si existe empresa con id = 1.

## 2. PRUEBAS REALIZADAS

- `shield:generate --all --panel=admin --option=policies_and_permissions` ejecutado correctamente.
- `SpatieRolesSeeder` y `ShieldPermisosRolesSeeder` ejecutados (el segundo avisa si no existe empresa id 1).
- Pint aplicado a archivos tocados.

## 3. PROBLEMAS DETECTADOS

- **Empresa id 1:** Si no existe, `ShieldPermisosRolesSeeder` no asigna permisos a admin_empresa ni rh_empresa. Crear la empresa o cambiar `$empresaId` en el seeder.
- **Recursos Usuario/Empleado:** No hay `UsuarioResource` ni `EmpleadoResource` en el panel; solo se protegieron los recursos existentes (Empresa, CentroCosto, Industria, Subindustria, Producto, NotificacionesIncluidas, Reconocmiento, TemaVozColaborador). Al añadirlos, generar permisos con `shield:generate` y crear políticas con scope por empresa si aplica.

## 4. PRÓXIMOS PASOS SUGERIDOS

- Crear empresa con id 1 (o la que use el seeder) y volver a ejecutar `ShieldPermisosRolesSeeder` para asignar permisos a admin_empresa y rh_empresa.
- Probar con usuario super_admin: debe ver todos los registros y todas las acciones.
- Probar con usuario con rol admin_empresa y empresa_id = 1: solo su empresa y centros de su empresa; botones según permisos.
- Probar con usuario con rol rh_empresa y empresa_id = 1: solo ver listados, sin botones de crear/editar/eliminar.
- Implementar selector de “rol actual” en la interfaz si se desea cambiar de rol en sesión.
- Documentar matriz de permisos por rol para negocio.

## 5. CÓMO PROBAR

```bash
# Crear empresa y roles (si aún no existen)
php artisan db:seed --class=SpatieRolesSeeder
php artisan db:seed --class=ShieldPermisosRolesSeeder

# Usuario admin@paco.com con super_admin: ver todo y todas las acciones.
# Usuario con empresa_id = 1 y rol admin_empresa: solo empresa 1 y centros de empresa 1; puede crear/editar/eliminar según permisos.
# Usuario con empresa_id = 1 y rol rh_empresa: solo ver; sin botones crear/editar/eliminar.
```

En la UI: listados de Empresas y Centro de costos deben filtrarse por empresa para usuarios no super_admin; los botones Editar/Eliminar solo se muestran si la política lo permite para ese registro.
