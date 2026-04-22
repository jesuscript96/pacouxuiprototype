# Agregar nuevo módulo al panel Cliente

Procedimiento estándar cuando alguien agrega un nuevo recurso/módulo al panel Cliente.

## 1. Crear el recurso

```bash
php artisan make:filament-resource NuevoModulo --panel=cliente
```

Ajusta nombres (modelo, tablas) según el asistente. El recurso quedará en `app/Filament/Cliente/Resources/`.

## 2. Crear la policy

```bash
php artisan make:policy NuevoModuloPolicy --model=NuevoModulo
```

Implementa los métodos que necesites (`viewAny`, `view`, `create`, `update`, `delete`, etc.) usando el formato de permisos del proyecto: `$user->can('ViewAny:NuevoModulo')`, `View:NuevoModulo`, etc. (PascalCase, separador `:`). Puedes basarte en `DepartamentoPolicy` o `DepartamentoGeneralPolicy`.

## 3. Generar permisos Shield para el panel Cliente

```bash
php artisan shield:generate-cliente
```

Este comando:

- Descubre los recursos del panel Cliente (incluido el que acabas de crear).
- Crea en BD solo los **permisos** con formato `Acción:Modelo` (guard `web`).
- Es **idempotente**: ejecutarlo varias veces no duplica permisos.
- **No** crea roles ni toca permisos del panel Admin.

## 4. Verificar en el Admin

1. Entra a **Admin** → **Roles**.
2. Edita un rol con empresa (o crea uno con el toggle "Asignar a empresa" activado).
3. En la sección **Permisos (panel Cliente)** debe aparecer el nuevo recurso con sus permisos (ViewAny, View, Create, Update, Delete, etc.).

## 5. Asignar permisos a los roles que correspondan

Desde el mismo formulario de Roles en Admin, asigna los permisos del nuevo módulo a los roles de empresa que deban usarlo (p. ej. `gestor_catalogos`, `admin_empresa`).

---

**Nota:** No hace falta crear seeders manuales para estos permisos. El comando `shield:generate-cliente` y el seeder `ShieldPanelClienteSeeder` (que asigna permisos a roles de ejemplo) cubren el flujo. Si quieres que un rol de desarrollo tenga por defecto permisos del nuevo módulo, actualiza `ShieldPanelClienteSeeder` para incluirlos en el array de permisos del rol correspondiente.

## Flujo completo de ejemplo (setup inicial)

```bash
php artisan migrate
php artisan shield:generate-cliente   # permisos del panel Cliente
php artisan db:seed                    # o --class=ShieldPanelClienteSeeder si solo quieres roles/permisos Cliente
```

## Alternativa con shield:generate estándar

El comando `shield:generate` del paquete tiene la opción `--panel=cliente`. Si quisieras usarlo en lugar de `shield:generate-cliente`:

```bash
php artisan shield:generate --panel=cliente --option=permissions --all
```

Cuidado: con `--all` también se generan permisos de **custom_permissions** del config (orientados al panel Admin). Para solo recursos del panel Cliente, usar `shield:generate-cliente` es más seguro.
