# Shield activado en panel Cliente

## 1. Cambios realizados

- **FilamentShieldPlugin** agregado a `ClientePanelProvider`: los recursos del panel Cliente se filtran por permisos (menú y acciones según rol).
- **Permisos y políticas generados** para el panel Cliente con `php artisan shield:generate --panel=cliente --all` (Departamento, DepartamentoGeneral y entidades descubiertas).
- **Seeder `ShieldPermisosRolesSeeder` actualizado:**
  - **admin_empresa:** permisos completos (ViewAny/View/Create/Update/Delete) para Departamento y DepartamentoGeneral, además de los recursos ya existentes.
  - **rh_empresa:** solo ViewAny y View para Departamento y DepartamentoGeneral (solo lectura en Catálogo de Colaboradores).
- **Lógica existente mantenida:** multitenant (`tenant(Empresa::class)`), `ScopeByCompany`, roles por empresa (`company_id` en `spatie_roles`), sin cambios en panel Admin.

## 2. Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `app/Providers/Filament/ClientePanelProvider.php` | Añadido `FilamentShieldPlugin::make()` en `->plugins([...])`. |
| `database/seeders/ShieldPermisosRolesSeeder.php` | Permisos de Departamento y DepartamentoGeneral para admin_empresa (CRUD) y rh_empresa (solo ViewAny/View). |
| `app/Policies/DepartamentoPolicy.php` | Generado por `shield:generate --panel=cliente` (permisos ViewAny, View, Create, Update, Delete, etc.). |
| `app/Policies/DepartamentoGeneralPolicy.php` | Idem. |

## 3. Comandos ejecutados

```bash
php artisan shield:generate --panel=cliente --all --no-interaction
php artisan db:seed --class=ShieldPermisosRolesSeeder
```

## 4. Verificaciones post-implementación

- **Usuario con rol rh_empresa:** debe ver Departamentos y Departamentos generales en el menú; **no** debe ver botones Crear/Editar/Eliminar; al intentar acceder a `/cliente/departamentos/create` (o similar) → **403**.
- **Usuario con rol admin_empresa:** debe ver Departamentos y Departamentos generales con botones de crear/editar/eliminar y poder ejecutar CRUD.
- **Verificación en BD:** `php artisan shield:verificar-rol rh_empresa --email=usuario@ejemplo.com` debe listar los permisos asignados (incluidos ViewAny:Departamento, View:Departamento, ViewAny:DepartamentoGeneral, View:DepartamentoGeneral).
- **Scope por empresa:** el middleware `ScopeByCompany` sigue en el stack del panel Cliente; los roles se siguen filtrando por `company_id`.

## 5. Próximos pasos sugeridos

- Añadir más recursos al panel Cliente en el futuro: ejecutar `php artisan shield:generate --panel=cliente --resource=NombreRecurso` (o `--all`) y actualizar `ShieldPermisosRolesSeeder` con los permisos para admin_empresa y rh_empresa según corresponda.
- Documentar para el equipo que el panel Cliente ya usa Shield y que los roles admin_empresa/rh_empresa deben tener permisos asignados para ver y actuar sobre los recursos.
