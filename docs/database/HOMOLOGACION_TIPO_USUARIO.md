# Homologación de valores del campo `tipo` (user)

## Paso 1: Resumen de búsquedas

- **`tipo === 'user'` / `'tipo' => 'user'`**: Modelo User, middleware EnsurePanelAccessByUserType, WorkOsAuthController, UsuarioForm, UsuariosTable, CorregirTipoSuperAdmin, WorkOSTestUserSeeder. ✅ REFERENCIA A TIPO.
- **`tipo === 'admin'` / `'tipo' => 'admin'`**: Modelo User, middleware, WorkOsAuthController, UsuarioForm, CreateUsuario, EditUsuario, UsuariosTable, FixUsuarioAdmin, ClienteEjemploSeeder, ShieldPanelClienteSeeder. ✅ REFERENCIA A TIPO.
- **`'admin'` como panel/path** (EnsureModulePanel, WorkOsAuthController panel query, AdminPanelProvider ->id('admin')): ❌ NO TOCAR.
- **`tipo === 'employee'` / `'tipo' => 'employee'`**: UsuarioForm, UsuariosTable. ✅ REFERENCIA A TIPO.
- **Otras apariciones de "tipo"**: tipo_comision, tipo_transaccion, tipo_solicitud, tipo_respuesta, etc. ❌ NO TOCAR.

## Paso 2: Tabla de archivos afectados

| Archivo | Línea | Código actual | Cambio necesario |
|---------|-------|----------------|------------------|
| **1. Modelo User** | | | |
| app/Models/User.php | 165 | `$this->tipo !== 'admin'` | `$this->tipo !== 'cliente'` |
| app/Models/User.php | 177 | `$this->tipo === 'user'` | `$this->tipo === 'administrador'` |
| app/Models/User.php | 181 | `$this->tipo === 'admin'` | `$this->tipo === 'cliente'` |
| **2. Middlewares** | | | |
| app/Http/Middleware/EnsurePanelAccessByUserType.php | 14-15 | comentario 'user'/'admin' | 'administrador'/'cliente' |
| app/Http/Middleware/EnsurePanelAccessByUserType.php | 35 | `$user->tipo === 'user'` | `$user->tipo === 'administrador'` |
| app/Http/Middleware/EnsurePanelAccessByUserType.php | 47 | `$user->tipo !== 'admin'` | `$user->tipo !== 'cliente'` |
| **3. Controlador** | | | |
| app/Http/Controllers/Auth/WorkOsAuthController.php | 137 | `$user->tipo === 'user'` | `$user->tipo === 'administrador'` |
| app/Http/Controllers/Auth/WorkOsAuthController.php | 138 | `$user->tipo === 'admin'` | `$user->tipo === 'cliente'` |
| **4. Formularios Filament** | | | |
| app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php | 82-86 | options 'user','admin','employee' + labels | 'administrador'=>'Administrador','cliente'=>'Cliente','colaborador'=>'Colaborador' |
| app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php | 101-102,126,129,131 | `$get('tipo') === 'admin'` | `$get('tipo') === 'cliente'` |
| app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php | 111-113,177-178 | `$get('tipo') === 'employee'`, `where('tipo','employee')` | `'colaborador'` |
| app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php | 141 | `in_array($get('tipo'), ['user', 'admin'], true)` | `['administrador', 'cliente'], true` |
| app/Filament/Resources/Usuarios/Tables/UsuariosTable.php | 39-47 | formatStateUsing/color 'admin','employee', default | 'administrador','cliente','colaborador' |
| app/Filament/Resources/Usuarios/Tables/UsuariosTable.php | 72-76 | SelectFilter options | 'administrador','cliente','colaborador' + labels |
| **5. Pages** | | | |
| app/Filament/Resources/Usuarios/Pages/CreateUsuario.php | 26,44,75 | `'admin'` (tipo) | `'cliente'` |
| app/Filament/Resources/Usuarios/Pages/EditUsuario.php | 35,51,54,73,113 | `'admin'` (tipo) | `'cliente'` |
| **6. Comandos** | | | |
| app/Console/Commands/CorregirTipoSuperAdmin.php | 12,15,47,49,51,53 | "user" en descripción y lógica | "administrador" |
| app/Console/Commands/FixUsuarioAdmin.php | 27-28 | `!== 'admin'`, mensaje | `'cliente'` |
| **7. Seeders** | | | |
| database/seeders/ClienteEjemploSeeder.php | 32 | `'tipo' => 'admin'` | `'tipo' => 'cliente'` |
| database/seeders/WorkOSTestUserSeeder.php | 26,39 | `'tipo' => 'user'` | `'tipo' => 'administrador'` |
| database/seeders/ShieldPanelClienteSeeder.php | 163 | `'tipo' => 'admin'` | `'tipo' => 'cliente'` |
| **8. Migración** | | | |
| database/migrations/2026_03_10_012446_... | 21 | `->default('user')` | Se cambia en migración de datos + default nuevo |

## Paso 3: Restricción del campo tipo

- Migración `2026_03_10_012446_add_usuario_fields_to_users_table.php`: `$table->string('tipo', 50)->default('user')`.
- **No hay enum ni check constraint**: es `string(50)`. Solo hace falta migración de datos y, opcionalmente, cambiar el default para nuevas instalaciones (en una migración nueva).

## Paso 4: Plan de migración de datos

1. **Migración** `2026_03_10_212941_homologar_tipo_usuario_en_users_table`:
   - Actualiza registros existentes: `user` → `administrador`, `admin` → `cliente`, `employee` → `colaborador`.
   - El default de la columna en la migración original sigue siendo `user`; los seeders (Inicial, ClienteEjemploSeeder, etc.) asignan explícitamente los nuevos valores.

## Paso 5: Mejora futura (solo propuesta)

- **Opción A – Constantes en User**: `User::TIPO_ADMINISTRADOR`, `User::TIPO_CLIENTE`, `User::TIPO_COLABORADOR`.
- **Opción B – Enum PHP**: `enum TipoUsuario: string { case Administrador = 'administrador'; case Cliente = 'cliente'; case Colaborador = 'colaborador'; }` y usar `User.tipo` como ese enum (o atributo cast).

No implementado en esta tarea.

---

## Paso 6–7: Cambios aplicados y verificación

- **Migración:** `2026_03_10_212941_homologar_tipo_usuario_en_users_table.php` (actualiza datos en `users`; `down()` revierte).
- **Código actualizado:** User, EnsurePanelAccessByUserType, WorkOsAuthController, UsuarioForm, UsuariosTable, CreateUsuario, EditUsuario, CorregirTipoSuperAdmin, FixUsuarioAdmin, Inicial, ClienteEjemploSeeder, WorkOSTestUserSeeder, ShieldPanelClienteSeeder.
- **Verificación:** `php artisan migrate` y `php artisan migrate:fresh --seed` sin errores. No quedan referencias a `tipo === 'user'`, `tipo === 'admin'` ni `'tipo' => 'employee'` en `app/`.
- **Inicial:** Se añadió `'tipo' => 'administrador'` al crear admin@paco.com para que en `migrate:fresh --seed` el usuario quede con el tipo homologado.
