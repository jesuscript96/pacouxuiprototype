# Shield – Panel Cliente: permisos y roles

## Paso 1: Auditoría de permisos existentes (resultados)

Ejecutando `php scripts/audit_shield_permissions.php` (o las consultas equivalentes en tinker):

### Permisos existentes antes del seeder

- **Departamento:** no existían permisos con formato `*Departamento*` (sí existían solo de DepartamentoGeneral con otro formato).
- **DepartamentoGeneral:** existían `CreateDepartamentoGeneral`, `DeleteDepartamentoGeneral`, `UpdateDepartamentoGeneral`, `ViewDepartamentoGeneral` (sin dos puntos; las policies usan `ViewAny:DepartamentoGeneral`, etc.).
- **Role:** existían permisos `ViewAny:SpatieRole`, `View:Role`, etc. Las **policies** comprueban `ViewAny:Role`, `View:Role`, etc. (modelo `Role`), por lo que era necesario crear permisos con nombre exacto `ViewAny:Role`, `View:Role`, etc.

### Roles existentes antes del seeder

- `super_admin` (company_id null)
- `panel_user` (company_id = 1)
- `departamento_rol` (company_id null)
- No existían `admin_empresa` ni `rh_empresa` con company_id = 1.

---

## Paso 2: Cómo cada recurso define/espera permisos

### 1. `app/Filament/Cliente/Resources/Departamentos/DepartamentoResource.php`

- No usa `HasShieldPermissions`.
- Usa la policy por convención de Laravel: modelo `Departamento` → `App\Policies\DepartamentoPolicy`.
- **Permisos esperados (nombre exacto):** `ViewAny:Departamento`, `View:Departamento`, `Create:Departamento`, `Update:Departamento`, `Delete:Departamento` (y opcionales Restore, ForceDelete, etc.).

### 2. `app/Filament/Cliente/Resources/DepartamentosGenerales/DepartamentoGeneralResource.php`

- No usa `HasShieldPermissions`.
- Modelo `DepartamentoGeneral` → `App\Policies\DepartamentoGeneralPolicy`.
- **Permisos esperados:** `ViewAny:DepartamentoGeneral`, `View:DepartamentoGeneral`, `Create:DepartamentoGeneral`, `Update:DepartamentoGeneral`, `Delete:DepartamentoGeneral`.

### 3. `App\Filament\Resources\Shield\RoleResource` (registrado en ClientePanelProvider)

- Extiende `ShieldRoleResource`; modelo Spatie es `Role` (contract; implementado por `App\Models\SpatieRole`).
- Policy: `App\Policies\RolePolicy` comprueba `$user->can('ViewAny:Role')`, `View:Role`, `Create:Role`, `Update:Role`, `Delete:Role`.
- **Permisos esperados:** `ViewAny:Role`, `View:Role`, `Create:Role`, `Update:Role`, `Delete:Role`.

Formato en el proyecto: **`Acción:Modelo`** (PascalCase), definido en `config/filament-shield.php` (`separator` `:`, `case` `pascal`).

---

## Paso 3: Creación de permisos

No se usó `php artisan shield:generate` para el panel Cliente (ese comando descubre recursos del panel por defecto). Los permisos se crean en el seeder **ShieldPanelClienteSeeder** con `Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])` para los 15 nombres: 5 por Departamento, 5 por DepartamentoGeneral, 5 por Role.

---

## Paso 4 y 5: Roles y company_id

- **gestor_catalogos:** CRUD Departamento + DepartamentoGeneral (sin Role). `company_id = 1`.
- **consultor_catalogos:** solo ViewAny + View para Departamento y DepartamentoGeneral. `company_id = 1`.
- **admin_empresa:** todos los permisos del panel Cliente (Departamento, DepartamentoGeneral, Role). `company_id = 1`.
- **rh_empresa:** solo lectura (ViewAny, View) para Departamento y DepartamentoGeneral. `company_id = 1`.

Misma convención que en `SpatieRolesSeeder`: roles por empresa con `company_id` del tenant; no se clonan por empresa, se crean con `company_id = 1` para desarrollo.

---

## Paso 6: Seeder

- **Clase:** `Database\Seeders\ShieldPanelClienteSeeder`.
- **Ejecución:** `php artisan db:seed --class=ShieldPanelClienteSeeder`.
- Idempotente: `firstOrCreate` para permisos y roles; `syncPermissions` para asignar; usuario `cliente@tecben.com` con `firstOrCreate` y `syncWithoutDetaching` para empresa.
- Dependencias: requiere que exista una empresa con `id = 1` (por ejemplo vía `EmpresaEjemploSeeder`). En `DatabaseSeeder` está comentada la llamada a `ShieldPanelClienteSeeder` para activarla cuando se use empresa de ejemplo.

---

## Paso 7: Verificación

Después del seeder:

```bash
php scripts/verificar_shield_panel_cliente.php
```

Comprueba para `cliente@tecben.com`: roles, `getAllPermissions()`, `hasEmpresasAsignadas()`, `can('ViewAny:Departamento')`, `can('ViewAny:DepartamentoGeneral')`, `can('ViewAny:Role')`. Login en `/cliente` debe mostrar Dashboard, Departamentos, Departamentos generales y Roles (con rol admin_empresa).
