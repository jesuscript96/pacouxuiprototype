# Implementación de acceso por empresa completada

> **Desarrollo local:** Para el día a día se recomienda **un solo servidor** con ambos paneles. Ver [docs/desarrollo/README.md](../desarrollo/README.md). La opción de dos servidores (APP_MODULE, puertos 8000/8001) queda para QA o producción.

## 1. Cambios realizados

- **Tabla pivote `empresa_user`** creada (usuario_id, empresa_id, unique).
- **Modelo Usuario** con relación `empresas()` (belongsToMany), `perteneceAEmpresa($empresaId)` y atributo `empresa_ids` (empresa_id + pivot).
- **CRUD Usuarios (Admin):** para tipo `admin` se muestra selector múltiple "Empresas asignadas"; al guardar se hace `sync` en la pivote y se mantiene `empresa_id` con la primera empresa (compatibilidad con lógica existente).
- **Middleware `EnsurePanelAccessByUserType`:**
  - En rutas `/admin`: solo usuarios con `tipo === 'user'`; si no, redirección a `/cliente`.
  - En rutas `/cliente`: solo usuarios con `tipo === 'admin'` y al menos una empresa asignada; si no, redirección a `/admin` o 403.
- **Scope por empresa:** `ScopeByCompany` usa `empresa_id` o primera empresa del pivot para `shield.company_id`. `HasShieldPolicyHelpers::canAccessEmpresa()` usa `perteneceAEmpresa()` (empresa_id o pivot).
- **UsuarioResource::getEloquentQuery():** usuarios no super_admin se filtran por `empresa_ids` (whereIn empresa_id u orWhereNull).

## 2. Archivos creados / modificados

| Archivo | Acción |
|--------|--------|
| `database/migrations/2026_03_06_211230_create_empresa_user_table.php` | Creado |
| `app/Models/Usuario.php` | Añadidas `empresas()`, `perteneceAEmpresa()`, `empresa_ids` |
| `app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php` | Selector "Empresas asignadas" para tipo admin, `firstEmpresaIdForAdmin()`, empresa_id solo para tipo employee |
| `app/Filament/Resources/Usuarios/Pages/CreateUsuario.php` | Sync empresas y empresa_id en afterCreate; validateReportLimit con empresas; exclude empresas del create |
| `app/Filament/Resources/Usuarios/Pages/EditUsuario.php` | mutateFormDataBeforeFill empresas; sync/detach en afterSave; validateReportLimit con empresas |
| `app/Http/Middleware/ScopeByCompany.php` | Usa empresa_id o primera empresa del pivot para shield.company_id |
| `app/Policies/Concerns/HasShieldPolicyHelpers.php` | canAccessEmpresa usa perteneceAEmpresa() |
| `app/Filament/Resources/Usuarios/UsuarioResource.php` | getEloquentQuery por empresa_ids |
| `app/Http/Middleware/EnsurePanelAccessByUserType.php` | Creado: restricción por tipo y empresas |
| `app/Providers/Filament/AdminPanelProvider.php` | authMiddleware + EnsurePanelAccessByUserType |
| `app/Providers/Filament/ClientePanelProvider.php` | middleware ScopeByCompany; authMiddleware + EnsurePanelAccessByUserType |

## 3. Lógica de negocio respetada

- **Tipos de usuario:** `user` (super admin, panel Admin), `admin` (administrador de empresa, panel Cliente), `employee` (app). Sin cambios.
- **Shield / Spatie:** roles y permisos sin cambios. `empresa_id` se mantiene en sincronía con la primera empresa del admin para `rolesDisponibles()` y usos que dependen de un solo company_id.
- **Empresa::usuarios():** sigue siendo hasMany por `empresa_id`; los admins con pivot tienen además `empresa_id` igual a la primera empresa asignada.
- **Panel Cliente:** ya existía con tenant(Empresa::class); se añade restricción por tipo y empresas asignadas.

## 4. Verificaciones post-implementación

- **Panel Admin:** login como usuario tipo `user` → acceso a `/admin`. Usuario tipo `admin` que intente entrar a `/admin` → redirección a `/cliente`.
- **Panel Cliente:** login como usuario tipo `admin` con empresas asignadas → acceso a `/cliente`. Sin empresas → 403. Usuario tipo `user` que intente `/cliente` → redirección a `/admin`.
- **CRUD Usuarios (solo en Admin):** crear/editar usuario tipo `admin` y asignar varias empresas; comprobar que se guarda la pivote y que el listado/filtros por empresa siguen correctos.
- **Políticas:** recursos que usen `canAccessEmpresa()` deben permitir acceso a cualquier empresa del usuario (empresa_id o pivot).

## 5. Migraciones

### Ejecutar en entorno nuevo o tras clonar

```bash
php artisan migrate
```

Crea (entre otras) la tabla `empresa_user` (pivote usuario–empresa). Las migraciones están ordenadas; no hace falta ejecutarlas a mano en ningún orden especial.

### Dependencias de la migración `empresa_user`

- Requiere que existan las tablas `usuarios` y `empresas` (creadas por migraciones anteriores en el mismo batch).
- Si ya tienes la base creada y solo falta esta tabla, `php artisan migrate` solo ejecutará las pendientes.

### Si algo falla al migrar

| Situación | Qué hacer |
|-----------|-----------|
| Error "Table 'empresa_user' already exists" | La tabla ya existe; no es necesario volver a crearla. Si quieres partir de cero: `php artisan migrate:fresh` (borra toda la BD y vuelve a migrar). |
| Error de foreign key (usuarios/empresas no existen) | Ejecutar todas las migraciones desde el inicio: `php artisan migrate`. No ejecutar migraciones sueltas en otro orden. |
| La migración `create_departamentos_table` ya se ejecutó antes | Es segura: comprueba si la tabla existe y, si ya está creada, solo añade la columna `departamento_general_id` si falta. No duplica tablas. |

### Verificación rápida tras migrar

```bash
php artisan migrate:status
```

Todas las filas deberían mostrar "Ran". Si hay "Pending", ejecutar de nuevo `php artisan migrate`.

## 6. Próximos pasos sugeridos

- Probar un admin con varias empresas y cambio de tenant en el panel Cliente.
- Revisar recursos del panel Cliente que filtren por empresa y asegurar que usen `empresa_ids` o el tenant actual cuando aplique.
- Documentar para el equipo el flujo de alta de admins y asignación de empresas.

## 7. Corrección acceso WorkOS y 403 admin (adicional)

**Problemas detectados:** Usuarios WorkOS (tipo `user`) podían acceder a ambos paneles; usuarios tipo `admin` con empresas en `empresa_user` recibían 403 "No tienes empresas asignadas".

**Acciones:** Usuario: relación `empresas()` con FKs explícitos y `hasEmpresasAsignadas()`. Middleware: uso de `hasEmpresasAsignadas()`. WorkOsAuthController: solo tipo `user` y redirect a `/admin`.

---

## 8. Login independiente por panel (redirección)

**Problema:** Al acceder a `/cliente/login`, el sistema redirigía a `/admin/login`, impidiendo ver el login del panel Cliente por separado.

**Objetivo:** Que cada panel tenga su propio login independiente: `/cliente/login` muestra el formulario del panel Cliente y `/admin/login` el del Admin.

**Cambios realizados:**

| Archivo | Cambio |
|---------|--------|
| `app/Http/Middleware/EnsurePanelAccessByUserType.php` | Al inicio del `handle()`: si la ruta es `cliente/login` o `admin/login`, se hace `return $next($request)` sin comprobar usuario ni redirigir. Así las rutas de login nunca son redirigidas a otro panel. |
| `app/Providers/Filament/AdminPanelProvider.php` | Eliminado el `->login()` duplicado que sobrescribía `->login(WorkOsLogin::class)`. El panel Admin queda con un único `->login(WorkOsLogin::class)`. |

**Verificación:** En ventana de incógnito, `/cliente/login` muestra el login del panel Cliente (email/contraseña) y `/admin/login` el del Admin (WorkOS). Tras autenticarse, cada panel redirige correctamente.

---

## 9. Por integrar (pendiente de instrucciones) — Integrado en §11

Cuando se restrinja el acceso de modo que cada tipo de usuario entre a un servidor/panel distinto y no pueda ingresar al otro:

- **Restricción de cambio de URL:** Un usuario **super_admin** (tipo `user`) no debe poder cambiar la URL para acceder al panel Cliente (`/cliente`). Un usuario **cliente** (tipo `admin`) no debe poder cambiar la URL para acceder al panel Admin (`/admin`).
- **Motivo:** Por las relaciones (modelos, tenant, políticas), permitir que uno cambie manualmente la URL al panel que no le corresponde haría que la aplicación "tronara" (errores o datos incoherentes).

**Implementado en §11:** APP_MODULE (si entras en la URL del otro panel, redirección al panel de este servidor = restringir acceso) y FilamentUser::canAccessPanel (403 si accede al panel que no le corresponde).

---

## 11. APP_MODULE (separación por servidor) y seguridad FilamentUser

### 11.1 APP_MODULE

- **Variable:** `config('app.module')` desde `env('APP_MODULE', '')`. Si está vacía, ambos paneles responden (desarrollo local). Si es `admin` o `cliente`, solo ese panel responde; entrar en la URL del otro panel **redirige al panel de este servidor** (restringe acceso: usuario de admin que escribe /cliente vuelve a /admin, y al revés).
- **Middleware `EnsureModulePanel`:** Recibe el id del panel (`module:admin` / `module:cliente`). Si `APP_MODULE` está definido y no coincide con el panel actual, redirige al panel de este servidor (admin_url en servidor Admin, cliente_url en servidor Cliente); si no están configuradas, 404.
- **Panel providers:** En ambos se añade al inicio del stack de middleware `module:admin` o `module:cliente`.
- **Config adicional:** `config('app.admin_url')` y `config('app.cliente_url')` desde `ADMIN_URL` y `CLIENTE_URL`, usados para redirigir al otro servidor cuando el usuario tiene el tipo equivocado para el panel actual.

### 11.2 Seguridad entre paneles (FilamentUser)

- **Modelo `Usuario`:** Implementa `Filament\Models\Contracts\FilamentUser` y método `canAccessPanel(Panel $panel): bool`.
  - Panel **admin:** `true` si `hasRole('super_admin')` o `tipo === 'user'`.
  - Panel **cliente:** `true` si `tipo === 'admin'` y `hasEmpresasAsignadas()`.
  - Cualquier otro panel: `false`.
- Filament usa `canAccessPanel` en su middleware de autenticación; si devuelve `false`, responde **403**.

### 11.3 Redirecciones con APP_MODULE

- **EnsurePanelAccessByUserType:** Si el usuario no debe estar en el panel actual, redirige a `/admin` o `/cliente`. Si `APP_MODULE` está definido, usa `config('app.admin_url')` o `config('app.cliente_url')` para enviar al usuario al otro servidor (p. ej. de admin:8000 a cliente:8001).

### 11.4 Archivos tocados

| Archivo | Cambio |
|---------|--------|
| `config/app.php` | `module`, `admin_url`, `cliente_url` |
| `bootstrap/app.php` | Alias de middleware `module` → `EnsureModulePanel` |
| `app/Http/Middleware/EnsureModulePanel.php` | Redirección al panel de este servidor (restringe acceso) si APP_MODULE no coincide |
| `app/Providers/Filament/AdminPanelProvider.php` | Middleware `module:admin` al inicio |
| `app/Providers/Filament/ClientePanelProvider.php` | Middleware `module:cliente` al inicio |
| `app/Models/Usuario.php` | Implementa `FilamentUser`, `canAccessPanel()` |
| `app/Http/Middleware/EnsurePanelAccessByUserType.php` | `redirectUrlForModule()` para ADMIN_URL/CLIENTE_URL |
| `.env.example` | Comentarios APP_MODULE, ADMIN_URL, CLIENTE_URL |
| `.env.admin.example` | Snippet para servidor Admin |
| `.env.cliente.example` | Snippet para servidor Cliente |

### 11.5 Cómo usar (equipo)

**Desarrollo local (ambos paneles):** No definir `APP_MODULE` en `.env` (o dejarlo vacío). Un solo servidor responde en `/admin` y `/cliente`.

**Dos servidores (p. ej. puertos 8000 y 8001):**

1. **Servidor Admin:** En el `.env` de ese proceso: `APP_MODULE=admin`, `ADMIN_URL=http://localhost:8000/admin`, `CLIENTE_URL=http://localhost:8001/cliente`. Levantar: `php artisan serve --port=8000`.
2. **Servidor Cliente:** En el `.env` de ese proceso: `APP_MODULE=cliente`, mismas URLs. Levantar: `php artisan serve --port=8001`.

En producción, cada despliegue tiene su propio `.env` con `APP_MODULE` y las URLs del otro servidor según corresponda.

**Sesión y cookie (mismo navegador, dos servidores):** Para que al iniciar sesión en un panel no se bloquee el otro (403), la app usa **cookie de sesión distinta por módulo**: nombre `{APP_NAME}-session-{admin|cliente}` y path `/admin` o `/cliente`. Así cada proceso (8000 Admin, 8001 Cliente) tiene su propia sesión en el navegador y puedes estar logueado en ambos a la vez. Configuración en `config/session.php` (clave `cookie` y `path` en función de `config('app.module')`).

**Verificación:**

- En servidor Admin (8000): `/admin` y `/admin/login` OK; `/cliente` y `/cliente/login` → redirección a admin (este servidor), acceso restringido.
- En servidor Cliente (8001): `/cliente` y `/cliente/login` OK; `/admin` y `/admin/login` → redirección a cliente (este servidor), acceso restringido.
- Usuario tipo `user` que intente acceder al panel cliente (URL directa en 8001) → 403 por `canAccessPanel`.
- Usuario tipo `admin` que intente acceder al panel admin (URL directa en 8000) → redirección a `CLIENTE_URL` o 403 según orden de middlewares.

---

## 10. Verificación: lógica de negocio y migraciones (tras §8)

Tras integrar los cambios del **login independiente por panel** (§8), se ha comprobado lo siguiente para que otro colega pueda correr el proyecto sin sorpresas.

### Lógica de negocio sin cambios

- **Tipos de usuario y paneles:** Siguen igual: `user`/super_admin → solo panel Admin; `admin` con empresas → solo panel Cliente; sin empresas → 403. El middleware `EnsurePanelAccessByUserType` solo añade una excepción al inicio: si la ruta es `cliente/login` o `admin/login`, deja pasar la petición sin redirigir. El resto de la lógica (comprobación de `tipo`, `hasEmpresasAsignadas()`, redirecciones a `/admin` o `/cliente`) no se ha tocado.
- **Modelo Usuario:** Relaciones `empresas()`, `hasEmpresasAsignadas()`, `perteneceAEmpresa()`, `getTenants()` / `canAccessTenant()` y uso de la tabla `empresa_user` siguen igual.
- **CRUD Usuarios, ScopeByCompany, políticas, WorkOS:** Sin cambios; solo se eliminó el `->login()` duplicado en el panel Admin (se mantiene `->login(WorkOsLogin::class)`).

### Migraciones

- **Estado comprobado:** `php artisan migrate:status` con todas las migraciones en "Ran", incluida `2026_03_06_211230_create_empresa_user_table`.
- **Orden y dependencias:** La migración `empresa_user` depende de `usuarios` y `empresas`; esas tablas se crean en migraciones anteriores, por lo que un `php artisan migrate` desde cero ejecuta todo en el orden correcto.
- **Migración `create_departamentos_table`:** Es idempotente: si la tabla ya existe, solo añade la columna `departamento_general_id` si falta; no intenta crear la tabla de nuevo.

Con esto, quien clone el repo y ejecute `php artisan migrate` no debería encontrar errores por orden de migraciones ni por tablas ya existentes (salvo que tenga un estado de BD distinto; en ese caso, ver §5 "Si algo falla al migrar").
