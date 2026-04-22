# FASE 2 COMPLETADA - SHIELD MULTITENANT

## 1. CAMBIOS REALIZADOS

- **Migración:** `2026_03_03_171432_add_company_id_to_spatie_roles_table.php`  
  Añade `company_id` nullable a `spatie_roles` con FK a `empresas` y `onDelete('cascade')`.

- **Modelo:** `App\Models\SpatieRole`  
  Extiende `Spatie\Permission\Models\Role` con:
  - `$table = 'spatie_roles'`, `$fillable` incluye `company_id`
  - `company()` → BelongsTo Empresa
  - `usuarios()` → BelongsToMany Usuario vía `model_has_roles`
  - `scopeForCompany($query, $companyId)` → roles de esa empresa + roles globales (`company_id` null)
  - Global scope `company`: en peticiones web, si el middleware setea `shield.company_id`, solo se listan roles de esa empresa + globales
  - `findByParam` usa `withoutGlobalScopes()` para unicidad y búsquedas
  - `create()` incluye `company_id` en la comprobación de rol existente (mismo nombre por empresa)

- **Config:** `config/permission.php`  
  `'role' => \App\Models\SpatieRole::class`

- **Roles base:** Seeder `SpatieRolesSeeder`  
  Crea rol global `super_admin` (sin `company_id`) y le asigna todos los permisos existentes.  
  Si existe empresa con `id = 1`, crea `admin_empresa`, `rh_empresa`, `empleado` con `company_id = 1`.  
  Ejecución: `php artisan db:seed --class=SpatieRolesSeeder`

- **Middleware:** `App\Http\Middleware\ScopeByCompany`  
  Si el usuario está autenticado, tiene `empresa_id` y no es `super_admin`, setea `request()->attributes->set('shield.company_id', empresa_id)` para que el global scope de `SpatieRole` filtre por empresa.  
  Registrado en `bootstrap/app.php` en el grupo `web`.

- **Usuario:**  
  - `rolesDisponibles()`: super_admin ve todos los roles; el resto solo roles de su empresa + globales (`SpatieRole::forCompany($this->empresa_id)`).
  - `getCurrentRolAttribute()` / acceso `current_rol`: devuelve el rol actual en sesión (`current_role_id`); si no hay, usa el primer rol del usuario y lo guarda en sesión.
  - `setCurrentRol(SpatieRole $rol)`: pone el rol actual en sesión si el usuario tiene ese rol.

## 2. PRUEBAS REALIZADAS

- Migración ejecutada correctamente: `php artisan migrate --path=...add_company_id_to_spatie_roles_table.php`
- Seeder ejecutado: `php artisan db:seed --class=SpatieRolesSeeder` (solo creó/actualizó `super_admin` al no existir empresa id 1 en BD)
- Linter sin errores en `SpatieRole`, `Usuario`, `SpatieRolesSeeder`
- Pint aplicado a archivos modificados

## 3. PROBLEMAS DETECTADOS

- **FK empresa:** Si no existe ninguna fila en `empresas`, el seeder no crea roles con `company_id` (evita violación de FK). Para crear roles por empresa, asegurar que exista al menos la empresa deseada o ejecutar el seeder tras crear empresas.
- **Permisos por rol empresa:** El seeder no asigna permisos a `admin_empresa`/`rh_empresa`/`empleado`; se pueden asignar desde la UI de Shield (`/admin/shield/roles`) o vía tinker una vez existan permisos (p. ej. tras `php artisan shield:generate`).

## 4. PRÓXIMOS PASOS SUGERIDOS

- Migrar datos de roles legacy a Shield (mapeo `roles`/`permiso_rol` → `spatie_roles`/`permissions`/`role_has_permissions` y `model_has_roles`) si se quiere unificar.
- Adaptar el recurso de roles de Shield (o políticas) para usar `SpatieRole::forCompany()` al listar/crear roles según la empresa del usuario.
- Probar permisos en recursos reales (Filament resources) con usuarios con rol por empresa.
- Opcional: en recursos Filament, usar `$usuario->current_rol` y `setCurrentRol()` para selector de “rol activo” en la cabecera o en un dropdown.

## 5. VERIFICACIONES MANUALES SUGERIDAS

```bash
# Migración
php artisan migrate

# Tinker
php artisan tinker
>>> $rol = \App\Models\SpatieRole::withoutGlobalScopes()->first();
>>> $rol->company;   // debe funcionar si tiene company_id
>>> \App\Models\SpatieRole::forCompany(1)->get();  // roles empresa 1 + globales
>>> $u = \App\Models\Usuario::find(1);
>>> $u->assignRole('super_admin');
>>> $u->roles;
>>> $u->rolesDisponibles();
>>> $u->current_rol;
```

En la UI: ir a `/admin/shield/roles` y comprobar que se listan los roles (según scope por empresa si aplica).
