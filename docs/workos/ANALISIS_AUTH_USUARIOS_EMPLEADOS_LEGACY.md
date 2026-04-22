# Análisis: Autenticación y relación Usuarios–Empleados (Legacy Paco)

Documento generado a partir del análisis del código del proyecto legacy (paco) para apoyar el diseño de tecben-core.

---

## 1. AUTENTICACIÓN

| Aspecto | Detalle |
|--------|----------|
| **Tabla usada para login** | `users` |
| **Modelo de autenticación** | `App\User` (en `app/User.php`, no en `App\Models`) |
| **Provider en `config/auth.php`** | `'users'` → `driver: eloquent`, `model: App\User::class` |
| **Guard por defecto** | `web` (session), provider `users` |
| **Guard API** | `api` → driver `passport`, provider `users` |
| **Passwords (reset)** | `passwords.users` → tabla `password_resets`, provider `users` |

No existe tabla `usuarios`; solo `users`. Tanto el panel web como la app móvil autentican contra la misma tabla `users`. La distinción se hace por el campo `type` y por la relación opcional con `high_employees`.

---

## 2. RELACIÓN EMPLEADOS–USUARIOS

| Aspecto | Detalle |
|--------|----------|
| **Campo de unión** | `users.high_employee_id` → `high_employees.id` |
| **Cardinalidad** | **1:1 opcional**: un usuario puede tener o no un empleado asociado; un empleado puede tener o no un usuario (cuenta para app/panel). |
| **Dirección de la FK** | La FK está en `users`; `high_employees` no tiene columna `user_id`. |

**En el modelo User (legacy):**
```php
public function high_employee(){
    return $this->belongsTo('App\Models\HighEmployee')->withTrashed();
}
```

**En el modelo HighEmployee (legacy):**
```php
public function user()
{
    return $this->hasOne('App\User');  // Laravel usa por convención users.high_employee_id
}
```

**Consulta típica (app móvil – login):** se busca al empleado por móvil o email y luego el usuario vinculado con tipo `high_employee`:

```php
$user = User::has('high_employee')->where('email', $email)->where('type', 'high_employee')->first();
// ...
Auth::login($user);
$tokenResult = $user->createToken('Personal Access Token');
```

- **Caso “usuario sin empleado”:** Sí. Cualquier usuario del panel con `type` distinto de `high_employee` (o con `high_employee_id` nulo) no tiene empleado asociado (ej. administradores, backoffice).
- **Caso “empleado sin usuario”:** Sí. Un empleado puede existir en `high_employees` y no tener fila en `users` con `high_employee_id` apuntándole; en ese caso no tiene cuenta para app ni panel.

---

## 3. ESTRUCTURA DE `users`

Resumen a partir de la migración base y de las migraciones `update_users_*` y otras que modifican `users`:

| Campo | Tipo (BD) | Nulable | Único | Descripción |
|-------|-----------|---------|-------|-------------|
| id | bigint PK | NO | SÍ | Identificador |
| name | string | NO | NO | Nombre |
| email | string | NO | **NO** | **Unique eliminado en `update_users_4`**; la unicidad no está garantizada por BD |
| email_verified_at | timestamp | SÍ | NO | Verificación de email |
| password | string | NO | NO | Hash; **no nullable** en migraciones |
| remember_token | string | SÍ | NO | “Recordarme” |
| mother_last_name | string | NO | NO | Apellido materno |
| paternal_last_name | string | NO | NO | Apellido paterno |
| phone | string | NO | NO | Teléfono |
| mobile | string | NO | NO | Móvil |
| type | string | NO | NO | Tipo: `user`, `high_user`, `high_employee`, etc. |
| has_report_user | string | SÍ* | NO | Si tiene reportes |
| notification_voice_employees | enum SI/NO | SÍ | NO | Notificaciones de voz del colaborador |
| user_tableau | string | SÍ | NO | Usuario Tableau |
| position_id | bigint FK | SÍ | NO | → positions |
| department_id | bigint FK | SÍ | NO | → departments |
| company_id | bigint FK | SÍ | NO | → companies |
| high_employee_id | bigint FK | SÍ | NO | → high_employees (vinculación empleado) |
| image | string | SÍ* | NO | Ruta de imagen (en una migración se añade sin nullable) |
| receive_newsletter | enum SI/NO | SÍ | NO | Newsletter |
| update_password | enum SI/NO | SÍ | NO | Forzar actualización de contraseña |
| last_password_update | datetime | SÍ | NO | Última actualización |
| google2fa_secret | longText | SÍ | NO | Secret 2FA |
| verified_2fa_at | datetime | SÍ | NO | Fecha verificación 2FA |
| enable_2fa | boolean | SÍ | NO | 2FA habilitado (default false) |
| token_batch | string | SÍ | NO | Firma por lote |
| created_at | timestamp | SÍ | NO | |
| updated_at | timestamp | SÍ | NO | |

- **workos_id / avatar:** No existen en el legacy. Hay `image` (ruta), no un campo `avatar` ni integración WorkOS en código.
- **email:** En la migración inicial era `unique`; `UpdateUsers4Table` ejecuta `dropUnique('users_email_unique')`, por lo que en BD el email **no es único**.
- **password:** En todas las migraciones revisadas es `string` no nullable; en el legacy no hay `password` nullable.

---

## 4. MODELOS PRINCIPALES

### `User.php`

- **Ruta:** `app/User.php` (namespace `App`, no `App\Models`).
- **Extiende:** `Illuminate\Foundation\Auth\User as Authenticatable`.
- **Traits:** `HasApiTokens` (Passport), `EntrustUserTrait` (roles/permisos), `Notifiable`.
- **Relaciones clave:**
  - `high_employee()` → belongsTo HighEmployee (withTrashed)
  - `company()`, `department()`, `position()`, `area()` → belongsTo (catálogos de Rafa)
  - `roles()` → belongsToMany Role (`role_user`)
  - `high_employee_filters()`, `logs()`, `folders()`, `created_surveys()`, `sent_messages()`, `sent_surveys()`, `sent_notifications_push()`, `read_comments()` / `comments_attended()` / `assigned_comments()` (VoiceEmployee), `one_signal_tokens()`, `custom_survey_shippings()`, `direct_debit_belvos()`, `voice_employee_subjects()`, `digital_documents()`, `witnesses()`, `employment_contracts_tokens()`, `capacitation()` → hasMany / belongsToMany según corresponda.

### `HighEmployee.php`

- **Ruta:** `app/Models/HighEmployee.php`.
- **Tabla:** `high_employees`.
- **Traits:** `SoftDeletes`.
- **Relación con User:** `user()` → hasOne `App\User` (FK en `users.high_employee_id`).
- **Otras relaciones:** company, location, department, area, position, region, accounts, account_states, requests, payroll_receipts, products (pivot), messages, survey_shippings, notifications, chat (chat_room_users / chat_messages usan `user_id` que en realidad es high_employees.id), etc.

No hay un modelo “custom” adicional para autenticación: se usa el mismo `User` con guards y providers estándar de Laravel.

---

## 5. FLUJO DE LOGIN

### Panel web

- **Controlador:** `App\Http\Controllers\Auth\LoginController` (trait `AuthenticatesUsers`).
- **Guard:** `web` (session), provider `users` → modelo `App\User`.
- **Mecánica:** Login clásico por email + password contra la tabla `users`; redirect a `/dashboard`.
- **2FA:** Existe `Google2FAController` y campos en `users` (`google2fa_secret`, `enable_2fa`, `verified_2fa_at`); el flujo de 2FA se aplica sobre el mismo `User`.

### App móvil

- **Controlador:** `App\Http\Controllers\Api\AuthController` (método `login` y variantes).
- **Guard:** Sigue siendo el mismo `users` (y en respuestas API se usa Passport con el mismo modelo `User`).
- **Mecánica:**
  1. Se recibe móvil o email.
  2. Se busca el empleado: `HighEmployee::where('email', $email)->first()` (o por mobile).
  3. Se busca el usuario vinculado con tipo `high_employee`:  
     `User::has('high_employee')->where('email', $email)->where('type', 'high_employee')->first()`.
  4. Si existe ese usuario, se valida código (p. ej. `VerifyEmployee`) y luego `Auth::login($user)`.
  5. Se genera token Passport con `$user->createToken(...)`.
- **Conclusión:** La app no autentica contra `high_employees`; autentica contra `users` cuyo `type` es `high_employee` y que tiene `high_employee_id` apuntando al empleado. El empleado es quien “identifica” al usuario (por email/móvil), pero quien inicia sesión y recibe el token es siempre un registro de `users`.

### Otro endpoint de API (panel-like)

- **ApiLoginController** (`Api\PacoApi\ApiLoginController`): login por email + password sobre `User::where('email', $request->email)->where('type', 'high_user')->first()`. Sigue siendo tabla `users`, tipo `high_user` (no empleados de app).

### Redes sociales / SSO

- No hay integración WorkOS ni campo `workos_id` en el código revisado.
- Sí hay 2FA con Google (campos y controlador propios).

---

## 6. TIPOS Y ROLES

### Campo `type` en `users`

- **Uso:** Sí; valores vistos en código:
  - `high_employee`: usuario que es además empleado (tiene `high_employee_id`); usado para login en app móvil.
  - `high_user`: usuario de tipo “alto” (panel); usado en `ApiLoginController` para login API por email/password.
  - `user`: valor por defecto u otro tipo de usuario de panel.
- **Distinción admin / superadmin / empleado:** Se hace por **roles** (Entrust), no solo por `type`. El `type` separa sobre todo “quién puede hacer login en la app” (`high_employee`) frente a “quién en el panel” (`user`, `high_user`); los permisos finos vienen de roles y permisos.

### Roles y permisos

- **Tablas:** `roles`, `permissions`, `role_user`, `permission_role` (migración Entrust).
- **Modelo:** `App\Models\Role` (Entrust); más adelante se añade `company_id` a `roles` (roles por empresa).
- **Asignación:** User belongsToMany Role vía `role_user`; Role belongsToMany Permission vía `permission_role`.
- **En User:** trait `EntrustUserTrait` y método `roles()`; helpers tipo `hasRoles()`, `getCurrentRol()` (y sesión `current_rol`).

---

## 7. RECOMENDACIÓN PARA TECBEN-CORE

Basado en el análisis del legacy:

1. **Usar una sola tabla para autenticación (`users` o `usuarios`)**  
   El legacy usa una sola tabla `users` para panel y app; la diferencia es el `type` y la relación opcional con empleados. Mantener una sola tabla de “cuentas” simplifica guards, Passport/Sanctum y políticas de seguridad. Si se renombra a `usuarios`, que sea la única tabla de autenticación.

2. **Mantener la relación 1:1 opcional usuarios–empleados**  
   - FK en la tabla de usuarios (`empleado_id` o `high_employee_id`) apuntando a `empleados`.  
   - Un usuario puede no tener empleado (admin, backoffice).  
   - Un empleado puede no tener usuario (aún no tiene cuenta para app/panel).  
   Así se preserva el comportamiento actual y se evita duplicar lógica de autenticación en otra tabla.

3. **Ubicación de WorkOS y avatar**  
   - **WorkOS:** Si en tecben-core se usa WorkOS como IdP, el `workos_id` (y si aplica `workos_connection_id`, etc.) debe ir en la **misma tabla de autenticación** (`users`/`usuarios`), ya que es quien inicia sesión.  
   - **Avatar:** En legacy la “foto” es `image` (ruta). En tecben-core se puede tener `avatar` (URL o path) en la tabla de usuarios; si solo los empleados tienen foto en la app, se puede seguir delegando en la relación usuario→empleado para mostrarla (como hace el legacy con `getImageAttribute` en HighEmployee que usa `$this->user->image_url`).

4. **Email y password**  
   - Restaurar **unique** en `email` en BD para la tabla de usuarios (en legacy se eliminó y es un riesgo).  
   - Mantener **password nullable** si se soporta SSO/WorkOS: usuarios que solo inician sesión por IdP pueden no tener contraseña local.

5. **Tipos y roles**  
   - Conservar un campo `type` (o equivalente) para distinguir “usuario de panel” vs “usuario que es empleado” (y otros si aplican).  
   - Mantener roles/permisos por usuario (y si aplica por empresa) para autorización; no confiar solo en `type` para permisos granulares.

6. **API y app móvil**  
   - Si la app sigue identificando al usuario por empleado (email/móvil del empleado), el flujo puede ser: localizar empleado → obtener usuario vinculado (`empleado_id` / `high_employee_id`) → autenticar ese usuario y emitir token. Así se reutiliza un solo guard y un solo modelo de autenticación.

---

*Referencia: proyecto legacy en `c:\...789\paco`. Análisis solo lectura.*
