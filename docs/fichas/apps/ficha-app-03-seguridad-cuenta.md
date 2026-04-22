# Ficha técnica: App 03 — Seguridad de la cuenta

**Sprint:** 5  
**Módulo app:** `05 - Perfil y Seguridad (Credenciales y NIP)`  
**Plataforma:** React Native + Expo  
**Fecha:** Abril 2026

---

## Descripción

Módulo que cubre autenticación (login por email o teléfono), recuperación de contraseña, cambio de contraseña desde la app y gestión del NIP transaccional (si negocio lo confirma para v1). Es el módulo más crítico: sin él no hay acceso a la app.

---

## Estado en TECBEN-CORE

| Componente | Estado |
|------------|--------|
| Modelo `User` con `HasApiTokens` (Sanctum) | ✅ |
| Login web panel cliente (email+password) | ✅ Filament, no expuesto como API |
| Endpoint `POST /api/auth/login` | ❌ No existe |
| Cambio de contraseña vía API | ❌ No existe |
| Recuperación de contraseña vía API | ❌ No existe |
| Campo NIP en `users` o `colaboradores` | ❌ No existe en schema |
| Rate limiting en API | 🟡 Default Laravel, pendiente configurar explícito |

---

## Referencia legacy (paco-app-legacy)

### Flujo de login
- **Archivo:** `src/app/pages/start-app/log-in/log-in.page.ts`
- **Campo único en UI:** `credentials.user` (auto-detecta email vs teléfono con regex)
- **Flujo antes del login:** `POST /verify_registered_device` con `{ email|mobile, device: uuid }` → si el dispositivo no está registrado, redirige a verificación OTP; si sí, procede con login
- **Login:** `POST /login` con `{ email|mobile, password, onesignal_token, app_id }`
- **Storage del token:** objeto `{ token, token_type, expires_at, login_type }` en clave `'paco.app.token'` (Ionic Storage)
- **Interceptor HTTP:** añade `Authorization: Bearer {token}` a cada request

### Flujo de activación de cuenta (primer ingreso)
1. `POST /send_validation_code` con `{ email|mobile, app_id }` → envía OTP
2. OTP validado: `POST /set_validation_code` con `{ email|mobile, token: '4 dígitos' }`
3. `POST /user_name` con `{ email|mobile }` → devuelve nombre para mostrar
4. `POST /signup` con `{ email|mobile, password, password_confirmation }` → completa registro

### Cambio de contraseña (logueado)
- **Archivo:** `src/app/pages/in-app/menu/configuration/new-password/new-password.page.ts`
- **Endpoint:** `POST /update_configuration_password` con `{ current_password, password, password_confirmation }`

### Recuperación de contraseña
- No documentado explícitamente como flujo completo en `start-app`; existe `recover-password` en routing

### NIP transaccional (onboarding)
- **Flujo start-app:** `send-pin` → `confirm-pin`
- **Endpoint:** `POST /set_transaction_code` con `{ code, code_confirmation }` → al crearse por primera vez

### Cambio de NIP (logueado)
- **Archivo:** `src/app/pages/in-app/menu/configuration/new-pin/new-pin.page.ts`
- Paso 1: verificar NIP actual → `POST /verify_code` con `{ code: pin }`
- Pasos 2-3: nuevo NIP y confirmación → `POST /change_code` con `{ code, code_confirmation }`

### Recuperación de NIP (logueado)
- **Archivo:** `send-recover-pin.page.ts` + `recover-pin.page.ts`
- Envío de código: `GET /reset_pin/{login_type}/{send_whatsapp}` (por defecto WhatsApp `'NO'`)
- Validar código: `POST /set_recover_pin_code` con `{ token: pin }`
- Nuevo NIP: `POST /change_code`

### Manejo de 401
- Interceptor HTTP captura `Unauthenticated` o `401` → `authService.handleLogout()` → `storage.clear()` → navegar a `/log-in`

---

## Diseño para TECBEN-CORE

### Endpoints a implementar

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| POST | `/api/v1/auth/login` | público | Email o teléfono + password → token Sanctum |
| POST | `/api/v1/auth/logout` | `auth:sanctum` | Revocar token actual |
| POST | `/api/v1/auth/password/forgot` | público | Iniciar recuperación (envía email/SMS) |
| POST | `/api/v1/auth/password/reset` | público | Completar recuperación con token |
| PUT | `/api/v1/auth/password` | `auth:sanctum` | Cambio de contraseña autenticado |
| POST | `/api/v1/auth/nip` | `auth:sanctum` | Crear NIP (primer uso) — si se confirma en v1 |
| PUT | `/api/v1/auth/nip` | `auth:sanctum` | Cambiar NIP (verificar actual + nuevo) |
| POST | `/api/v1/auth/nip/recuperar` | `auth:sanctum` | Enviar código para recuperar NIP |

### Body `POST /api/v1/auth/login`
```json
{
  "usuario": "email@empresa.com",
  "password": "contraseña",
  "push_token": "onesignal-subscription-id (opcional)"
}
```

### Response login exitoso
```json
{
  "token": "sanctum-token-string",
  "token_type": "Bearer",
  "usuario": {
    "id": 1,
    "nombre_completo": "Juan López García",
    "empresa": {
      "id": 1,
      "nombre": "Empresa Demo",
      "one_signal_app_id": "abc-123"
    }
  }
}
```

### Lógica de login (`LoginController@login`)
1. Buscar `User` por email o teléfono con `tipo` que incluya `'colaborador'`
2. Validar password con `Hash::check`
3. Si correcto: `$user->tokens()->where('name', 'app-movil')->delete()` (evitar tokens huérfanos)
4. `$token = $user->createToken('app-movil')->plainTextToken`
5. Si viene `push_token`: registrar en `dispositivos_push` (reutilizar lógica ficha 01)
6. Retornar token + datos básicos del usuario + `one_signal_app_id` de empresa

### Migración requerida para NIP
Si se confirma NIP en v1:
```sql
ALTER TABLE users ADD COLUMN nip VARCHAR(255) NULL; -- hasheado igual que password
```

---

## Reglas de negocio

| ID | Regla |
|----|-------|
| RN-01 | Login solo para usuarios con `tipo` que contenga `'colaborador'`; no para `'cliente'` ni `'administrador'`. |
| RN-02 | Detectar si campo `usuario` es email o teléfono antes de buscar en BD. |
| RN-03 | Rate limiting: máximo 5 intentos de login por IP/minuto (`throttle:5,1`). |
| RN-04 | La nueva contraseña no puede ser igual a la actual (comparar con `Hash::check`). |
| RN-05 | Token Sanctum: nombrar `'app-movil'`; un token por dispositivo (eliminar anterior al crear nuevo). |
| RN-06 | NIP transaccional: mínimo 4 dígitos, máximo 6, solo numérico. |
| RN-07 | Al recuperar contraseña por email, el token debe expirar en 60 minutos. |

---

## Subtareas

1. Crear `Api\Auth\LoginController` con lógica de detección email/teléfono, validación y emisión de token Sanctum.
2. Implementar `PUT /api/v1/auth/password` con `CambiarPasswordRequest` (validar actual + nueva + confirmación).
3. Implementar flujo forgot/reset password (usar `Password::sendResetLink` de Laravel adaptado a API).
4. Si NIP en v1: crear migración de columna `nip`, `Api\Auth\NipController` y Form Requests.
5. Tests Pest: login exitoso, login fallido, credenciales incorrectas, rate limit, cambio de contraseña.

---

## AMBIGÜEDADES / A DEFINIR

- ¿El login de la app requiere **verificación de dispositivo** (OTP) como en legacy o solo email+password en v1?
- ¿NIP transaccional en **Sprint 5 o se pospone** al Sprint de Adelanto de Nómina (Sprint 5)?
- ¿La recuperación de contraseña se hace por **email o también por SMS/WhatsApp**?

---

## Referencias

- [app/Models/User.php](../../../app/Models/User.php) — `HasApiTokens`, `tipo`, `scopeColaboradores`
- [routes/api.php](../../../routes/api.php) — base de rutas API
- [app/Http/Requests/Api/ListNotificacionesPushRequest.php](../../../app/Http/Requests/Api/ListNotificacionesPushRequest.php) — patrón Form Request API
- [paco-app-legacy] `src/app/pages/start-app/log-in/log-in.page.ts`
- [paco-app-legacy] `src/app/core/services/auth.service.ts`
- [paco-app-legacy] `src/app/pages/in-app/menu/configuration/new-password/new-password.page.ts`
- [paco-app-legacy] `src/app/pages/in-app/menu/configuration/new-pin/new-pin.page.ts`
