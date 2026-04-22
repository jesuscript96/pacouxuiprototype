# Análisis de core.paco para integración WorkOS en tecben-core

Preparación del ambiente e implementación basada en el ejemplo funcional core.paco, con adaptaciones para **login dual** (tradicional + WorkOS) y **solo usuarios existentes en DB** pueden usar WorkOS.

---

## 1. Estructura de BD encontrada en core.paco

### Tablas relacionadas con WorkOS

- **Solo la tabla `users`** se modifica para WorkOS. No hay tablas nuevas dedicadas a WorkOS (organizations, etc.).

### Migración WorkOS en core.paco

- **Archivo:** `database/migrations/2026_02_17_224011_add_workos_fields_to_users_table.php`
- **Cambios en `users`:**
  - `workos_id` — string, nullable, unique, después de `id` (identificador del usuario en WorkOS).
  - `avatar` — string, nullable, después de `email` (URL de la foto de perfil de WorkOS).
  - `password` — pasa a **nullable** (usuarios que solo inician sesión por WorkOS no tienen password).

### Otras tablas en core.paco (contexto tenant)

- `empresas` y `empresa_user` son para **multitenant** (Filament tenant). Tu compañero hará la parte multitenant; para WorkOS solo necesitas los cambios en `users`.

### Resumen para tecben-core

- **No** hace falta crear tablas nuevas para WorkOS.
- **Sí** hace falta una migración que añada a `users`: `workos_id`, `avatar`, y hacer `password` nullable.

---

## 2. Migraciones a ejecutar (en orden)

### Orden actual de migraciones en tecben-core

1. `0001_01_01_000000_create_users_table.php`
2. `0001_01_01_000001_create_cache_table.php`
3. `0001_01_01_000002_create_jobs_table.php`
4. `2026_02_24_230219_create_industrias_table.php`
5. `2026_02_24_230237_create_sub_industrias_table.php`
6. `2026_02_25_002716_create_logs_table.php`
7. `2026_02_25_011219_create_productos_table.php`
8. `2026_02_25_013432_create_centro_costos_table.php`

### Nueva migración a crear (después de las anteriores)

Crear **una sola migración** que añada los campos de WorkOS a `users`:

**Ruta:** `database/migrations/YYYY_MM_DD_HHMMSS_add_workos_fields_to_users_table.php`

**Comando sugerido (cuando vayas a implementar):**

```bash
php artisan make:migration add_workos_fields_to_users_table --table=users
```

**Código de la migración (compatible SQLite y MySQL):**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('workos_id')->nullable()->unique()->after('id');
            $table->string('avatar')->nullable()->after('email');
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['workos_id', 'avatar']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
```

**Nota:** Para usar `->change()` en Laravel hace falta el paquete `doctrine/dbal`. Si no lo tienes:

```bash
composer require doctrine/dbal
```

SQLite y MySQL aceptan esta migración; al pasar a MySQL no tendrás que cambiarla.

### Comandos a ejecutar en tu entorno (Fase 1)

```bash
# Ya en feature/workos-integration, con .env configurado
php artisan migrate
# Cuando añadas la migración de WorkOS:
# php artisan migrate
```

---

## 3. Archivos a crear

Rutas exactas y propósito de cada archivo.

| # | Ruta | Propósito |
|---|------|-----------|
| 1 | `app/Http/Controllers/Auth/WorkOsAuthController.php` | Redirect a WorkOS, callback (auth + lógica "solo usuarios existentes"), logout |
| 2 | `app/Http/Responses/WorkOsLogoutResponse.php` | Respuesta de logout de Filament: redirigir a WorkOS y limpiar sesión |
| 3 | `app/Http/Middleware/CaptureWorkOsSession.php` | Rellena `workos_sid` en sesión desde el access token (para logout) |
| 4 | `app/Filament/Auth/WorkOsLogin.php` | Página de login de Filament (clase) — en tu caso con **doble opción**: tradicional + WorkOS |
| 5 | `resources/views/filament/auth/workos-login.blade.php` | Vista del login: formulario email/password **y** botón "Iniciar sesión con WorkOS" |
| 6 | `database/migrations/XXXX_XX_XX_XXXXXX_add_workos_fields_to_users_table.php` | Migración que añade `workos_id`, `avatar` y hace `password` nullable |

**No crear (solo modificar):**

- `config/services.php` — añadir bloque `workos`
- `routes/web.php` — añadir rutas WorkOS
- `app/Providers/AppServiceProvider.php` — binding de `LogoutResponse`
- `app/Providers/Filament/AdminPanelProvider.php` — usar login personalizado y middleware
- `app/Models/User.php` — añadir `workos_id` y `avatar` a `$fillable`

---

## 4. Configuraciones necesarias

### 4.1 Variables de entorno (.env)

Añadir (valores los rellenarás con los que te den):

```env
# WorkOS
WORKOS_CLIENT_ID=
WORKOS_API_KEY=
WORKOS_REDIRECT_URL="${APP_URL}/auth/workos/callback"
```

- `WORKOS_REDIRECT_URL` es opcional; por defecto puede construirse en config como `APP_URL + '/auth/workos/callback'`.

### 4.2 config/services.php

Añadir al array de retorno (junto a `postmark`, `resend`, etc.):

```php
'workos' => [
    'client_id' => env('WORKOS_CLIENT_ID'),
    'secret' => env('WORKOS_API_KEY'),
    'redirect_url' => env('WORKOS_REDIRECT_URL', env('APP_URL').'/auth/workos/callback'),
],
```

### 4.3 Rutas (routes/web.php)

Añadir después de la ruta existente:

```php
use App\Http\Controllers\Auth\WorkOsAuthController;

// WorkOS
Route::get('/auth/workos/redirect', [WorkOsAuthController::class, 'redirect'])
    ->middleware('guest')
    ->name('workos.login');

Route::get('/auth/workos/callback', [WorkOsAuthController::class, 'callback'])
    ->middleware('guest')
    ->name('workos.callback');

Route::match(['get', 'post'], '/auth/workos/logout', [WorkOsAuthController::class, 'logout'])
    ->middleware('guest')
    ->name('workos.logout');
```

### 4.4 app/Providers/AppServiceProvider.php

En el método `register()`:

```php
use App\Http\Responses\WorkOsLogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;

// Dentro de register():
$this->app->singleton(LogoutResponse::class, WorkOsLogoutResponse::class);
```

### 4.5 app/Models/User.php

- Añadir a `$fillable`: `'workos_id'`, `'avatar'`.
- Mantener `password` en fillable (seguirá pudiendo ser null para usuarios solo WorkOS una vez nullable en BD).

No es necesario implementar `HasTenants` ni `FilamentUser` si por ahora no usas multitenant; cuando tu compañero lo integre, lo añadís.

---

## 5. Plan de implementación paso a paso

### Fase 1 — Preparación (manual)

1. Crear rama `adrian-dev` desde `dev`.
2. Crear rama `feature/workos-integration` desde `adrian-dev`.
3. Configurar `.env` (SQLite + variables WorkOS cuando las tengas).
4. Ejecutar migraciones existentes: `php artisan migrate`.

### Fase 2 — Dependencias y migración

5. Instalar paquete WorkOS: `composer require laravel/workos`.
6. Si usas `->change()` en migraciones: `composer require doctrine/dbal`.
7. Crear migración `add_workos_fields_to_users_table` con el código de la sección 2.
8. Ejecutar migraciones: `php artisan migrate`.

### Fase 3 — Configuración

9. Añadir bloque `workos` en `config/services.php`.
10. Añadir variables WorkOS en `.env` (y opcionalmente `WORKOS_REDIRECT_URL`).

### Fase 4 — Controlador y rutas

11. Crear `WorkOsAuthController` (redirect, callback, logout).
12. En el **callback**, implementar lógica "solo usuarios existentes":
    - Tras obtener el usuario de WorkOS, buscar por `workos_id`; si no hay, buscar por `email`.
    - Si existe usuario por email: actualizar `workos_id` y `avatar`, luego login.
    - Si no existe: no crear usuario; redirigir a login con mensaje tipo "Usuario no autorizado" o `abort(403)`.
13. Añadir rutas WorkOS en `routes/web.php`.

### Fase 5 — Logout y sesión WorkOS

14. Crear `WorkOsLogoutResponse` (captureSession + toResponse usando SID y `getLogoutUrl`).
15. Crear middleware `CaptureWorkOsSession`.
16. Registrar en `AppServiceProvider` el binding de `LogoutResponse` a `WorkOsLogoutResponse`.

### Fase 6 — Filament (login dual)

17. Crear clase `App\Filament\Auth\WorkOsLogin` que extienda la página simple de Filament y use una vista que tenga **formulario tradicional + botón WorkOS** (o reutilizar la vista de login por defecto y solo añadir el botón; depende de cómo exponga Filament el formulario).
18. Crear vista `resources/views/filament/auth/workos-login.blade.php` con:
    - Formulario email/password (login tradicional).
    - Enlace/botón a `route('workos.login')` ("Iniciar sesión con WorkOS").
19. En `AdminPanelProvider`: `->login(WorkOsLogin::class)`, mantener `->registration(false)` si no quieres registro público, y añadir `CaptureWorkOsSession` al array `->middleware([...])`.

### Fase 7 — Pruebas

20. Probar login tradicional (usuario existente con password).
21. Probar login WorkOS con usuario que **ya existe** en DB (mismo email): debe quedar vinculado y poder entrar.
22. Probar que un usuario que **no** existe en DB no pueda entrar por WorkOS (mensaje o 403).
23. Probar logout (desde Filament y desde ruta `/auth/workos/logout`) y que redirija a WorkOS cuando haya SID.

---

## 6. Flujo completo (referencia)

- **Inicio de login:** Usuario va a `/admin` → Filament muestra la página de login personalizada → puede elegir:
  - Email + password → login tradicional de Laravel/Filament.
  - "Iniciar sesión con WorkOS" → GET `/auth/workos/redirect` → WorkOS AuthKit.
- **Callback:** WorkOS redirige a GET `/auth/workos/callback?code=...&state=...` → controlador valida state, intercambia code por usuario y tokens, **busca usuario por workos_id o por email**; si existe, actualiza workos_id/avatar y hace login; si no existe, no crea usuario y redirige/403.
- **Creación/actualización:** Solo **actualización** de usuarios existentes (link por email o por workos_id). Sin creación automática.
- **Logout:** Desde Filament o desde `/auth/workos/logout` → se usa `workos_sid` para construir URL de logout de WorkOS, se hace logout local y redirección a esa URL.

---

## 7. Adaptaciones para tu proyecto

| Diferencia | Solución |
|------------|----------|
| **Login dual (tradicional + WorkOS)** | Página de login de Filament personalizada que muestre tanto el formulario email/password como el botón "Iniciar sesión con WorkOS". No sustituir el login por defecto solo por WorkOS. |
| **Solo usuarios existentes pueden usar WorkOS** | En el callback, **no** usar `$request->authenticate()` tal cual (que crea usuarios). Implementar lógica propia: 1) Intercambiar code por usuario WorkOS (con el SDK o el mismo flujo del paquete). 2) Buscar usuario por `workos_id`; si no hay, por `email`. 3) Si existe: actualizar `workos_id` y `avatar`, hacer `Auth::guard('web')->login($user)` y guardar tokens/sid en sesión. 4) Si no existe: redirigir a login con mensaje "Usuario no autorizado" o `abort(403)`. |
| **No romper CRUDs de industrias** | No tocar recursos ni migraciones de industrias/sub_industrias/productos/centro_costos/logs. Solo añadir migración de WorkOS en `users`, rutas, controlador, respuesta de logout, middleware y página de login de Filament. |
| **SQLite ahora, MySQL después** | Usar migración con `Schema::table` y tipos estándar (string, etc.) y `->change()` con doctrine/dbal. Válido para SQLite y MySQL. |
| **Tema Filament (FilamentAwinTheme)** | Mantener `->plugins([FilamentAwinTheme::make()...])` en `AdminPanelProvider`; la nueva página de login puede usar los mismos componentes de Filament (`<x-filament-panels::page.simple>`) para que se vea coherente. |
| **Sin tenant por ahora** | No implementar `HasTenants` ni `tenant(Empresa::class)` ni redirección con tenant en el callback hasta que tu compañero defina el modelo de tenant. Tras login WorkOS puedes redirigir a `route('filament.admin.pages.dashboard')` sin parámetro tenant. |

---

## 8. Dependencias y configuración (resumen)

- **Composer:** `laravel/workos` (^0.5.0). Opcional: `doctrine/dbal` para migración con `change()`.
- **Config:** Solo `config/services.php` con bloque `workos` (client_id, secret, redirect_url).
- **Rutas:** GET `/auth/workos/redirect`, GET `/auth/workos/callback`, GET|POST `/auth/workos/logout`, con middleware `guest` y nombres `workos.login`, `workos.callback`, `workos.logout`.

---

## 9. Login en Filament (core.paco vs tu proyecto)

- **core.paco:** Solo WorkOS; página `WorkOsLogin` con vista que tiene un único botón a `workos.login`; `->registration(false)`, `->passwordReset(false)`.
- **Tu proyecto:** Login dual. Misma clase/vista personalizada debe incluir:
  - Formulario de login por email/password (comportamiento por defecto de Filament o formulario que envíe a la ruta de login de Filament).
  - Botón/enlace "Iniciar sesión con WorkOS" → `route('workos.login')`.

Para mantener el login tradicional en Filament y añadir el botón WorkOS, puedes extender la página de login por defecto de Filament o usar una custom que renderice ambos (depende de la API de Filament v4 para personalizar la pantalla de login; en cualquier caso el archivo a "crear" es la página de login personalizada y la vista Blade correspondiente).

---

*Documento de preparación para integración WorkOS en tecben-core. Cuando tengas las credenciales WorkOS y la rama lista, sigue el plan de implementación y adapta el callback para "solo usuarios existentes" como se indica en la sección 7.*
