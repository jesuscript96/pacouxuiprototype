# FORMULARIO DE ROLES EXTENDIDO (SHIELD)

Documentación de la extensión del recurso de roles de Filament Shield para incluir campos y comportamiento alineados con el legacy.

## 1. CAMBIOS REALIZADOS

### Modelo y base de datos

- **Migración** `add_display_name_and_description_to_spatie_roles_table`:
  - Columnas en `spatie_roles`: `display_name` (string, nullable), `description` (string, nullable).

- **Modelo** `App\Models\SpatieRole`:
  - `$fillable`: añadidos `display_name` y `description`.
  - Accessor `getIsGlobalAttribute()`: `true` cuando `company_id` es null (rol global).

### Recurso de roles (Shield)

- **Recurso propio** `App\Filament\Resources\Shield\RoleResource` (extiende el de Shield):
  - El panel descubre este recurso; Shield no registra el suyo (`isResourcePublished` detecta `\RoleResource` en el panel).
  - Misma ruta y slug: `shield/roles` (config `filament-shield.shield_resource.slug`).

### Formulario

- **Campos:**
  - **Nombre** (técnico): `name`, requerido, único, helper.
  - **Nombre para mostrar:** `display_name`, requerido.
  - **Descripción:** `description`, requerido.
  - **Guard:** `guard_name` oculto, valor por defecto del panel (p. ej. `web`).

- **Solo super_admin:**
  - Sección “Asignación a empresa”:
    - Toggle “Asignar a empresa” (`is_asignable`).
    - Select **Empresa** (`company_id`), requerido si el toggle está activo, regla `nullable|exists:empresas,id`.

- **No super_admin:**
  - No se muestra la sección de empresa.
  - Campo oculto `company_id` con valor por defecto `auth()->user()->empresa_id`.

- Se mantienen las pestañas de permisos de Shield (Recursos, Páginas, Widgets, Permisos personalizados) y el Toggle “Select all”.

### Listado

- Columnas: nombre, nombre para mostrar, descripción, empresa (o “Global”), tipo (icono global vs empresa), guard_name, número de permisos, updated_at.
- Filtro por empresa: `SelectFilter` sobre `company_id`.

### Scope por empresa

- **Listado:** `RoleResource::getEloquentQuery()`: usuarios que no son `super_admin` y tienen `empresa_id` solo ven roles con `company_id` igual a su empresa o `company_id` null (globales).

### Páginas

- Páginas propias en `App\Filament\Resources\Shield\Pages\`:
  - `CreateRole`, `EditRole`, `ListRoles`, `ViewRole`.
  - Heredan el layout del panel desde las páginas base de Shield/Filament (no es necesario forzar `$layout`).

### Guardado

- **CreateRole** y **EditRole:** en `mutateFormDataBeforeCreate` / `mutateFormDataBeforeSave` se excluyen de “permisos” los keys: `name`, `guard_name`, `select_all`, `display_name`, `description`, `company_id`, `is_asignable`, y se guardan en el rol: `name`, `guard_name`, `display_name`, `description`, `company_id` (y tenant key si aplica).

## 2. ARCHIVOS PRINCIPALES

| Archivo | Descripción |
|--------|-------------|
| `database/migrations/..._add_display_name_and_description_to_spatie_roles_table.php` | Columnas `display_name`, `description` en `spatie_roles`. |
| `app/Models/SpatieRole.php` | Fillable y accessor `is_global`. |
| `app/Filament/Resources/Shield/RoleResource.php` | Form, table, getEloquentQuery, getPages. |
| `app/Filament/Resources/Shield/Pages/CreateRole.php` | Mutación de datos antes de crear + layout. |
| `app/Filament/Resources/Shield/Pages/EditRole.php` | Mutación de datos antes de guardar + layout. |
| `app/Filament/Resources/Shield/Pages/ListRoles.php` | Listado + layout. |
| `app/Filament/Resources/Shield/Pages/ViewRole.php` | Vista + layout. |

## 3. COMPORTAMIENTO

- **Super_admin:** ve todos los roles; puede crear roles globales (sin empresa) o por empresa; ve la sección “Asignación a empresa” con toggle y selector.
- **Usuario con empresa:** no ve esa sección; al crear/editar un rol se usa su `empresa_id`; en el listado solo ve roles de su empresa y globales.

## 4. PÁGINA DE EDICIÓN SIN DISEÑO

Si al abrir **Editar** (p. ej. `http://127.0.0.1:8000/admin/shield/roles/1/edit`) la página se ve sin estilos de Filament, suele deberse a que **los assets de Vite no se están cargando**:

- En desarrollo, el navegador pide JS/CSS a `http://[::1]:5173` (servidor de Vite). Si ese servidor no está en marcha, verás `ERR_CONNECTION_REFUSED` y la página sin diseño.
- **Solución:** ejecutar `npm run dev` (y dejar la terminal abierta) o, si no usas el dev server, `npm run build` y recargar.

Si tras tener Vite en marcha (o tras hacer build) sigue sin verse bien, revisar consola del navegador y `storage/logs/laravel.log` por excepciones (p. ej. roles con `display_name` o `description` null).

## 5. PRUEBAS SUGERIDAS

1. **Super_admin:** crear rol global (toggle “Asignar a empresa” desactivado); crear rol con empresa; editar y cambiar empresa; comprobar que la edición se ve con el layout de Filament.
2. **Usuario con empresa:** crear rol (sin bloque empresa, `company_id` automático); comprobar que el listado solo muestra sus roles y globales.
3. Listado: comprobar columnas y filtro por empresa.

## 6. ROLES EXISTENTES SIN display_name / description

Los roles creados antes de esta extensión pueden tener `display_name` y `description` en null. En la próxima edición el formulario los pedirá obligatorios. Para rellenarlos por defecto sin pasar por la UI:

```php
// En tinker o un seeder
\App\Models\SpatieRole::query()
    ->whereNull('display_name')
    ->update([
        'display_name' => \DB::raw('name'),
        'description' => '',
    ]);
```
