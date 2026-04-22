# Ficha técnica: App 04 — Gestión de sesiones y eliminación de cuenta

**Sprint:** 5  
**Módulo app:** `04 - Perfil y Seguridad (Gestión de Sesiones)`  
**Plataforma:** React Native + Expo  
**Fecha:** Abril 2026

---

## Descripción

Control de sesiones activas del colaborador: cerrar sesión en el dispositivo actual, cerrar sesión en todos los dispositivos (revocación masiva de tokens Sanctum), y flujo de solicitud de eliminación de cuenta. Es una extensión del módulo de seguridad orientada a la gestión multi-dispositivo.

---

## Estado en TECBEN-CORE

| Componente | Estado |
|------------|--------|
| Sanctum con `personal_access_tokens` | ✅ `User` usa `HasApiTokens` |
| Revocar token actual | 🟡 Posible con `$user->currentAccessToken()->delete()` pero sin endpoint API |
| Revocar todos los tokens | 🟡 Posible con `$user->tokens()->delete()` pero sin endpoint API |
| Listado de sesiones activas | ❌ No existe (Sanctum no guarda metadata de dispositivo por defecto) |
| Eliminación de cuenta | ❌ No existe flujo ni endpoint |
| Borrado de push token al logout | ❌ No hay lógica implementada aún |

---

## Referencia legacy (paco-app-legacy)

### Logout normal
- **Archivo:** `src/app/core/services/auth.service.ts`
- **Método:** `logout()`
- **Endpoint:** `POST /logout` (body `{}`)
- **Post-logout:** limpia colores UI, `token = null`, `isLoggedIn = false`, `storage.clear()`, navega a `/log-in`
- **Push token:** no hay eliminación explícita del token push en el logout del legacy

### Cerrar sesión en todos los dispositivos
- **Método:** `logoutAllDevices()`
- **Endpoint:** `POST /logout_all_devices`
- **Post-logout:** mismo flujo de limpieza local
- Disponible en la UI de configuración de usuario (`user.page.html`)

### Eliminar cuenta
- **Método:** `deleteAccount()`
- **Endpoint:** `POST /removeAccount`
- **Post-eliminación:** `storage.clear()` y navegar a `/log-in`
- Sin flujo de confirmación adicional ni período de gracia documentado en el legacy

### Manejo de 401 (sesión revocada desde otro dispositivo)
- **Interceptor:** `src/app/core/interceptors/app-http.interceptor.ts`
- Al detectar `401` con mensaje `Unauthenticated`: `handleLogout()` → limpia storage → navega a `/log-in`

---

## Diseño para TECBEN-CORE

### Endpoints a implementar

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| POST | `/api/v1/auth/logout` | `auth:sanctum` | Revocar token actual + marcar push token inactivo |
| POST | `/api/v1/auth/sessions/revoke-all` | `auth:sanctum` | Revocar todos los tokens del usuario |
| GET | `/api/v1/auth/sessions` | `auth:sanctum` | Listar sesiones activas (v2, opcional) |
| POST | `/api/v1/cuenta/solicitud-baja` | `auth:sanctum` | Iniciar proceso de eliminación de cuenta |

### Lógica `POST /api/v1/auth/logout`
```php
public function logout(Request $request): JsonResponse
{
    // Marcar push token del dispositivo como inactivo
    $pushToken = $request->header('X-Push-Token'); // o body
    if ($pushToken) {
        DispositivoPush::where('user_id', $request->user()->id)
            ->where('token', $pushToken)
            ->update(['activo' => false]);
    }

    // Revocar token Sanctum actual
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Sesión cerrada correctamente']);
}
```

### Lógica `POST /api/v1/auth/sessions/revoke-all`
```php
public function revocarTodas(Request $request): JsonResponse
{
    // Revocar todos los tokens Sanctum
    $request->user()->tokens()->delete();

    // Marcar todos los push tokens como inactivos
    DispositivoPush::where('user_id', $request->user()->id)
        ->update(['activo' => false]);

    return response()->json(['message' => 'Todas las sesiones fueron cerradas']);
}
```

### Lógica `POST /api/v1/cuenta/solicitud-baja`
- Crear registro de solicitud (tabla o ticket interno)
- Enviar notificación a RH / panel admin
- **No** eliminar al usuario automáticamente (coordinar con legal y negocio)
- Responder confirmación al usuario con tiempo estimado de proceso

### Manejo de 401 en la app (RN/Expo)
- Interceptor Axios/fetch: si recibe `401`, limpiar SecureStore (token), navegar a pantalla de login
- No reintentar automáticamente peticiones que fallan con `401`

---

## Reglas de negocio

| ID | Regla |
|----|-------|
| RN-01 | Al cerrar sesión, revocar el token Sanctum actual Y marcar el push token del dispositivo como inactivo. |
| RN-02 | "Cerrar en todos los dispositivos" revoca **todos** los tokens Sanctum y todos los push tokens del usuario. |
| RN-03 | Si el token fue revocado desde otro dispositivo, la app debe recibir `401` y forzar logout local. |
| RN-04 | La eliminación de cuenta no es inmediata: genera una solicitud que debe aprobar el área de RH. |
| RN-05 | No eliminar físicamente al usuario hasta que el proceso de baja esté coordinado (afecta historial RH). |

---

## Subtareas

1. Implementar `Api\Auth\SesionController` con `logout` y `revocarTodas`.
2. Integrar desactivación de push token en el logout (depende de migración de ficha 01).
3. Crear endpoint `solicitud-baja` con proceso de confirmación (lógica mínima: registro + notificación).
4. App (RN/Expo): interceptor de peticiones HTTP para detectar `401` y ejecutar logout local automático.

---

## AMBIGÜEDADES / A DEFINIR

- ¿El listado de sesiones activas (`GET /sessions`) va en **v1 o v2**? Requiere guardar metadata extra en tokens Sanctum (nombre de dispositivo, SO, fecha de último uso).
- ¿Qué sucede con los datos del colaborador en la BD si solicita eliminación de cuenta? ¿Se conserva la ficha `Colaborador` por normativa laboral?
- ¿La solicitud de baja genera un ticket en el panel o solo un email a RH?

---

## Referencias

- [app/Models/User.php](../../../app/Models/User.php) — `HasApiTokens`, `tokens()`, `currentAccessToken()`
- [routes/api.php](../../../routes/api.php) — base de rutas API
- [paco-app-legacy] `src/app/core/services/auth.service.ts` — `logout()`, `logoutAllDevices()`, `deleteAccount()`
- [paco-app-legacy] `src/app/pages/in-app/menu/configuration/user/user.page.html` — opciones de sesión en UI
- [paco-app-legacy] `src/app/core/interceptors/app-http.interceptor.ts` — manejo de 401
