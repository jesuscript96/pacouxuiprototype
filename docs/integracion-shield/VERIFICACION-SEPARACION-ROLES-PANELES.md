# Verificación de separación de roles entre paneles

## 1. Resumen

- **En gran parte correcto:** La separación por tipo de usuario (user → Admin, admin → Cliente), el filtrado de permisos por Shield y el scope de roles por empresa están implementados.
- **Problema detectado y corregido:** En el panel Cliente, el recurso Roles mostraba también el rol **super_admin** (company_id null) a usuarios tipo admin. En `RoleResource::getEloquentQuery()` se añadió un filtro para el panel Cliente que excluye el rol super_admin cuando el usuario no es super_admin. Así, en Cliente solo se listan roles de empresa (admin_empresa, rh_empresa, etc.).

## 2. Panel Admin

| Aspecto | Estado | Detalle |
|---------|--------|---------|
| Plugin Shield | ✅ | `FilamentShieldPlugin::make()` en `AdminPanelProvider`. |
| Tenant | ❌ | Sin `tenant()`; panel no multitenant. |
| Roles visibles | ✅ | Recurso Roles en `App\Filament\Resources\Shield\RoleResource`; descubierto desde `Filament/Resources`. Super_admin ve todos los roles (`getEloquentQuery` sin filtro por empresa cuando es super_admin). |
| Recursos protegidos | ✅ | Recursos bajo `Filament/Resources` con políticas Shield; menú y acciones filtrados por permisos. |
| Acceso por tipo | ✅ | `EnsurePanelAccessByUserType`: solo tipo `user` o rol `super_admin` pueden entrar en `/admin`. |
| Middleware | ✅ | `module:admin`, `EnsurePanelAccessByUserType`; sin `ScopeByCompany` (no aplica tenant). |

## 3. Panel Cliente

| Aspecto | Estado | Detalle |
|---------|--------|---------|
| Plugin Shield | ✅ | `FilamentShieldPlugin::make()` en `ClientePanelProvider`. |
| Tenant | ✅ | `tenant(Empresa::class)`; multitenant por empresa. |
| Recurso Roles | ✅ | Mismo `App\Filament\Resources\Shield\RoleResource` registrado explícitamente con `tenantOwnershipRelationshipName = 'company'`. |
| Roles visibles | ⚠️ | `RoleResource::getEloquentQuery()`: usuario no super_admin con `empresa_id` ve roles con `company_id = su empresa` **o** `company_id null`. Tras corrección, en panel Cliente se excluye el rol super_admin para usuarios no super_admin. |
| Recursos filtrados por permisos | ✅ | Departamento y DepartamentoGeneral con políticas; menú y acciones según permisos del rol. |
| Scope por empresa | ✅ | `ScopeByCompany` establece `shield.company_id`; `SpatieRole` aplica global scope y solo muestra roles de esa empresa + globales. |
| Acceso por tipo | ✅ | `EnsurePanelAccessByUserType`: solo tipo `admin` con `hasEmpresasAsignadas()` pueden entrar en `/cliente`. |

## 4. Asignación de roles

| Aspecto | Estado | Detalle |
|---------|--------|---------|
| CRUD Usuarios | ✅ | Solo en panel Admin (`UsuarioResource` en `Filament/Resources`). |
| Selector de roles | ✅ | `UsuarioForm::rolesOptions()`: super_admin ve todos los roles (`SpatieRole::withoutGlobalScopes()`); resto ve `SpatieRole::forCompany($companyId)` (su empresa + globales). |
| Tipos y roles | ✅ | Sección Roles visible para tipo `user` y `admin`; tipo `employee` no tiene selector de roles. |
| super_admin en Cliente | ✅ | En `/cliente/shield/roles` un admin de empresa ya no ve el rol super_admin; se excluye en `RoleResource::getEloquentQuery()`. |

## 5. Configuración de permisos compartida

- **Tabla única:** `spatie_roles` y `permissions` / `role_has_permissions` son compartidas entre ambos paneles. No hay tablas de roles/permisos distintas por panel.
- **Separación lógica:** Se hace por 1) **tipo de usuario** y **canAccessPanel** (quién entra en cada panel) y 2) **company_id** en roles y **ScopeByCompany** (qué roles ve cada usuario en cada contexto). Los permisos son los mismos; lo que cambia es qué recursos existen en cada panel (Admin vs Cliente) y qué roles tiene cada usuario.

## 6. Confirmaciones esperadas vs actual

| Aspecto | Esperado | Actual |
|---------|----------|--------|
| Plugin Shield en ambos paneles | ✅ | ✅ |
| Roles visibles en Admin | super_admin, admin_empresa, rh_empresa, etc. | ✅ Correcto |
| Roles visibles en Cliente | Solo roles con company_id (admin_empresa, rh_empresa, etc.) | ✅ Tras corrección: se excluye super_admin en `RoleResource::getEloquentQuery()` cuando panel es Cliente y usuario no es super_admin. |
| Recursos Admin protegidos por Shield | ✅ | ✅ |
| Recursos Cliente filtrados por permisos | ✅ | ✅ (Departamento, DepartamentoGeneral) |
| Usuario tipo 'user' solo en Admin | ✅ | ✅ (middleware + canAccessPanel) |
| Usuario tipo 'admin' solo en Cliente | ✅ | ✅ (middleware + canAccessPanel) |
| Admin solo asigna roles de su empresa | ✅ (en CRUD desde Admin) | ✅ `rolesOptions()` usa `forCompany`. En Cliente, lista de roles excluye super_admin. |

## 7. Acciones correctivas recomendadas

1. **Excluir super_admin de la lista de roles en el panel Cliente** — **Aplicado.** En `RoleResource::getEloquentQuery()` se añadió: si el panel actual es `cliente` y el usuario no es super_admin, se filtra `where('name', '!=', Utils::getSuperAdminName())`, de modo que en Cliente solo se listen roles de empresa (admin_empresa, rh_empresa, empleado, etc.).

2. **Opcional:** En el CRUD de usuarios (panel Admin), al asignar roles a un usuario tipo `admin`, restringir las opciones a roles con `company_id` no null (excluir super_admin) para evitar asignar por error el rol de panel Admin. Actualmente super_admin puede asignar cualquier rol; si se desea mayor rigor, filtrar en `rolesOptions()` cuando el usuario editado es tipo `admin` para no mostrar super_admin.

## 8. Comandos de verificación

```bash
# Permisos de un rol en BD
php artisan shield:verificar-rol rh_empresa --email=usuario@ejemplo.com

# Roles existentes (tinker)
php artisan tinker
>>> \App\Models\SpatieRole::withoutGlobalScopes()->get(['id','name','company_id']);
```
