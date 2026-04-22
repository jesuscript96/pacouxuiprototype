# Ficha técnica: App 01 — Configuración inicial y permisos

**Sprint:** 5  
**Módulo app:** `01 - Inicio / Dashboard Principal`  
**Plataforma:** React Native + Expo  
**Fecha:** Abril 2026

---

## Descripción

Primera ejecución de la app: solicitar permisos del sistema operativo (notificaciones push, ubicación, cámara) y registrar el token de dispositivo en el backend. Es el módulo base que habilita funcionalidades transversales como push notifications, onboarding guiado y, en el futuro, geolocalización y KYC.

---

## Estado en TECBEN-CORE

| Componente | Estado |
|------------|--------|
| OneSignal config por empresa | ✅ `configuracion_apps.one_signal_app_id` + `one_signal_rest_api_key` |
| Envío push desde panel | ✅ `NotificacionesPushController`, `EnviarNotificacionPushAction`, `OneSignalService` |
| API listado push (`GET /api/notificaciones-push`) | ✅ Implementada con `auth:sanctum` |
| Tabla de tokens de dispositivo | ❌ No existe en schema actual (`one_signal_tokens` referenciada en docs pero sin migración) |
| Endpoint para registrar token de app | ❌ No existe en `routes/api.php` |

---

## Referencia legacy (paco-app-legacy)

### Flujo de arranque
- **Archivo:** `src/app/app.component.ts`
- Tras `storage.init()` y `getLocalUser()`, llama `await this.pushService.initNotifications()`.

### Servicio de notificaciones
- **Archivo:** `src/app/core/services/push-notification.service.ts`
- Flujo `initNotifications()`:
  1. `PushNotifications.checkPermissions()` → si `prompt`, `requestPermissions()` → `PushNotifications.register()`
  2. `OneSignal.initialize(environment.ONE_SIGNAL_APP_ID)` + `OneSignal.Notifications.requestPermission(true)`
  3. `getSubscriptionId()` → guarda en storage con clave `AppStorageKey.OneSignalUserToken`

### Registro del token en API (legacy)
- **Endpoint legacy:** `POST /save_notification_token` con body `{ onesignal_token: string }`
- **Cuándo:** después del login, en `dashboard.page.ts` `ionViewWillEnter`
- Si no hay token: `setOneSignalTokenLogged()` intenta obtenerlo de OneSignal con 3s de delay

### Geolocalización (legacy)
- **Archivo:** `src/app/core/services/utils.service.ts` método `setDevice()`
- `Geolocation.getCurrentPosition()` → guarda lat/long en storage
- **No es pantalla de permisos explícita**: ocurre de forma silenciosa tras login, en nativo

### Cámara (legacy)
- No hay solicitud central en onboarding; aparece en módulos específicos (KYC, voz del colaborador, foto de perfil)

---

## Diseño para TECBEN-CORE

### Migración nueva requerida
```sql
-- tabla: dispositivos_push
id, user_id (FK users), token, plataforma (ios|android|web), activo (bool), timestamps
```

### Endpoint a implementar

| Método | Ruta | Auth | Controller propuesto |
|--------|------|------|----------------------|
| POST | `/api/v1/dispositivos/push-token` | `auth:sanctum` | `Api\DispositivoPushController@store` |
| DELETE | `/api/v1/dispositivos/push-token` | `auth:sanctum` | `Api\DispositivoPushController@destroy` |

### Request de registro (body JSON)
```json
{
  "token": "string (OneSignal subscription id)",
  "plataforma": "ios|android"
}
```

### Lógica `store`
1. Buscar si ya existe token para este `user_id` en `dispositivos_push`
2. Si existe → actualizar (`token`, `activo = true`)
3. Si no → crear
4. Responder `200 OK` con `{ message: 'Token registrado' }`

### Lógica `destroy`
1. Marcar `activo = false` donde `user_id = auth()->id()` y `token = request->token`
2. Responder `200 OK`

---

## Reglas de negocio

| ID | Regla |
|----|-------|
| RN-01 | No bloquear login si el usuario niega notificaciones; solo deshabilitar alertas push. |
| RN-02 | Reintentar registro de token en cada `dashboard` mount si aún no se registró. |
| RN-03 | Al cerrar sesión, marcar token como inactivo (no eliminar, por auditoría). |
| RN-04 | Un usuario puede tener múltiples tokens (multi-dispositivo). |
| RN-05 | OneSignal se configura por empresa, no global: la app requiere el `app_id` de la empresa del usuario logueado. |

---

## Subtareas

1. Crear migración `dispositivos_push` y modelo `DispositivoPush`.
2. Implementar `DispositivoPushController` con `store` y `destroy` + Form Requests + tests Pest.
3. Registrar rutas en `routes/api.php` bajo grupo `auth:sanctum`.
4. App (RN/Expo): implementar flujo de permisos post-login con `expo-notifications` y llamar al endpoint de registro.

---

## AMBIGÜEDADES / A DEFINIR

- ¿Se pide permiso de **ubicación** en v1? El alcance funcional lo menciona; confirmarlo con PM antes de implementar.
- ¿Se expone el `one_signal_app_id` de la empresa vía API para que la app inicialice OneSignal correctamente? Requiere endpoint o incluirlo en el response del login.

---

## Referencias

- [app/Models/User.php](../../../app/Models/User.php) — `notificacionesPushRecibidas()` (pivot con `onesignal_player_id`)
- [app/Models/ConfiguracionApp.php](../../../app/Models/ConfiguracionApp.php) — `one_signal_app_id`, `tieneOneSignalConfigurado()`
- [routes/api.php](../../../routes/api.php) — base de rutas API actuales
- [app/Services/OneSignal/OneSignalService.php](../../../app/Services/OneSignal/OneSignalService.php)
- [paco-app-legacy] `src/app/core/services/push-notification.service.ts`
- [paco-app-legacy] `src/app/app.component.ts`
