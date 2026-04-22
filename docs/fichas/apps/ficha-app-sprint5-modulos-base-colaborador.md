# Fichas técnicas — App colaborador (Sprint 5 · tareas “app”)

> **Contexto:** Tareas solicitadas por PM para el Sprint 5 sobre la **app móvil** (React Native + Expo): configuración inicial, perfil, seguridad, sesiones e imagen de perfil.  
> **Stack backend:** Laravel 12, Sanctum (`User` usa `HasApiTokens`), panel Cliente con email/password para administradores de empresa.  
> **Regla de arquitectura (tecben-core):** datos RH canónicos en `colaboradores`; la app lee/actualiza vía API según políticas (contacto editable vs solo lectura según negocio).

## Fuente de verdad y referencias legacy


| Origen                    | Uso                                                                                                                                                                                                                                                                                                                                                                                                               |
| ------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **TECBEN-CORE**           | Implementación definitiva: modelos, migraciones, APIs nuevas, tests Pest, políticas. Toda ficha de app debe anclarse aquí.                                                                                                                                                                                                                                                                                        |
| **Paco (backend legacy)** | Referencia de **lógica de negocio** ya vivida en producción: reglas de login, recuperación, NIP, expediente, validaciones. Repo típico del equipo: `../paco-legacy` (Laravel). Puntos útiles: `App\Http\Controllers\Api\AuthController`, flujos de usuario colaborador, integración con tokens / OneSignal según versión.                                                                                         |
| **paco-app-legacy**       | Referencia de **UX y flujos en app**: pantallas, orden de pasos, textos. Repo típico: `../paco-app-legacy/paco-app-cap` (Angular + Ionic). Rutas de arranque en `src/app/pages/start-app/start-app-routing.module.ts` (login, activación, PIN, recuperación); menú en `in-app/menu/menu-routing.module.ts` (configuración, recibos, SUA, estado de ánimo); módulos en `in-app/modules/modules-routing.module.ts`. |


**Nota:** Las rutas `../paco-legacy` y `../paco-app-legacy` asumen el mismo directorio padre que el repo `tecben-core`. Ajustar si el workspace del dev difiere.

---

**Estado global API móvil (TECBEN-CORE al momento de esta ficha):**

- En `routes/api.php` existen rutas autenticadas con `auth:sanctum` para **notificaciones push** y **cartas SUA**.  
- **No** hay aún controlador de autenticación ni CRUD de perfil/sesión/imagen bajo `/api` para la app colaborador; habrá que implementarlo siguiendo Form Requests, policies y convención `Acción:Recurso` si aplica.

---

## 1. Configuración inicial de la aplicación y permisos

### MÓDULO

Primera ejecución o flujo post-instalación: solicitar permisos del SO necesarios para el funcionamiento acordado (notificaciones push, ubicación si aplica a reglas de negocio futuras, cámara si se usa en KYC más adelante, etc.) y dejar registrado en la app el estado de cada permiso para UX (explicaciones, re-intentos, deep link a ajustes).

### Estado en TECBEN-CORE

- **Notificaciones:** modelo y API de listado/marcado de notificaciones push (`NotificacionesPushController`); envío vía OneSignal según `configuracion_app` de empresa (`one_signal_app_id`, `one_signal_rest_api_key`). La app debe **registrar el token del dispositivo** en backend (tabla/token según diseño acordado; en legacy se asociaba al `User`).
- **Ubicación / cámara:** no hay módulo backend específico para “permiso”; es 100 % cliente. Documentar qué permisos pide la app y en qué pantallas se usan para evitar solicitar de más.

### Referencia legacy (app)

- Flujo de permisos y primer uso: revisar pantallas de introducción / dashboard en `paco-app-legacy` (módulo `start-app` y arranque post-login) para alinear copy y orden con producto.

### App (RN + Expo)

- Uso de APIs de permisos de Expo (`expo-notifications`, `expo-location`, etc.) según alcance cerrado con producto.
- Flujo guiado (onboarding corto) antes o después del login, según decisión de UX.
- Tras conceder notificaciones: llamada API para registrar token (endpoint a definir en tecben-core).

### APIs a exponer en TECBEN-CORE (propuesta)


| Método | Ruta (ejemplo)                    | Descripción                                                                |
| ------ | --------------------------------- | -------------------------------------------------------------------------- |
| POST   | `/api/v1/dispositivos/push-token` | Registrar/actualizar token OneSignal/Expo asociado al usuario autenticado. |
| DELETE | `/api/v1/dispositivos/push-token` | Quitar token al cerrar sesión o revocar dispositivo.                       |


Autenticación: `auth:sanctum` (token Bearer).

### Reglas de negocio (borrador)

- RN-01: No bloquear el login si el usuario niega un permiso no crítico; solo deshabilitar features que lo requieran.
- RN-02: Reintentar registro de token push tras login si antes falló por falta de permiso.

### AMBIGÜEDADES

- ¿Ubicación obligatoria en v1 o solo notificaciones? (Alcance funcional menciona dashboard con métricas; confirmar si la app v1 pide GPS.)

---

## 2. Gestión del perfil del usuario (“Mi expediente”)

### MÓDULO

Visualización consolidada de datos personales y laborales del colaborador y, donde aplique, edición de datos de contacto (correo, teléfono) alineado al legacy y a la ficha de expediente.

### Estado en TECBEN-CORE

- `**User`:** nombre, apellidos, email, `telefono`, `celular`, `imagen`, `avatar` (WorkOS en panel admin; en app colaborador puede no aplicar), `colaborador_id`, `empresa_id`, campos duplicados de RH en `users` por compatibilidad.
- `**Colaborador`:** fuente canónica RH: `nombre`, `apellido_paterno`, `apellido_materno`, `email`, `telefono_movil`, `numero_colaborador`, `fecha_nacimiento`, `genero`, `curp`, `rfc`, `nss`, `fecha_ingreso`, relaciones a `departamento`, `area`, `puesto`, `region`, `centroPago`, `razonSocial`, etc.
- **Filament:** `Perfil.php` y formularios de usuario editan perfil en web; la app replicará contrato de datos vía API, no reutilizando Filament.

### Referencia legacy

- **Paco (backend):** reglas de “mi expediente”, campos editables y validaciones de email/teléfono en controladores o servicios del colaborador (según versión del monolito).
- **paco-app-legacy:** pantalla “Mi expediente” / menú `configuration` → flujo de datos mostrados al usuario.

### App (RN + Expo)

- Pantalla “Mi expediente”: lectura desde API unificada implementada en tecben-core (recomendado: `GET /api/v1/perfil` o `GET /api/v1/colaborador/me` con `user` + `colaborador` + relaciones con eager loading).
- Edición: solo campos permitidos por negocio (típicamente email/teléfonos); resto solo lectura.

### APIs a exponer en TECBEN-CORE (propuesta)


| Método | Ruta (ejemplo)                               | Descripción                                      |
| ------ | -------------------------------------------- | ------------------------------------------------ |
| GET    | `/api/v1/perfil` o `/api/v1/colaborador/me`  | Expediente completo para el usuario autenticado. |
| PATCH  | `/api/v1/perfil` o `/api/v1/perfil/contacto` | Actualizar email/celular según validación.       |


### Validaciones

- Email único por reglas de `User` / empresa.
- Teléfonos en formato acordado (Laravel validation + mensajes en español).

### PERMISOS / AUTORIZACIÓN

- Solo el propio usuario puede ver/editar su perfil (policy `update` sobre su modelo o gate dedicado).

### DEUDA / PENDIENTE

- Definir lista cerrada de campos editables vs solo lectura con negocio (contrastar con legacy “Mi expediente”).

---

## 3. Seguridad de la cuenta

### MÓDULO

Cambio de contraseña, recuperación de acceso (flujo olvidé contraseña / activación por teléfono según producto) y, si aplica en v1, **NIP** para transacciones (adelantos, pagos).

### Estado en TECBEN-CORE

- Autenticación web Cliente: email + password (`User` con `password` hasheado).
- **Login por teléfono para app:** pendiente de diseño de API en tecben-core (no hay endpoint público de login móvil en `routes/api.php` al cierre de esta ficha).
- **NIP transaccional:** confirmar en modelo/servicios; si no existe en tecben-core, definir migración y reglas o posponer según negocio.

### Referencia legacy

- **Paco (backend):** `AuthController` API (login por móvil/email, verificación, tokens), cambio de contraseña, flujos de PIN/NIP si existen en esa versión.
- **paco-app-legacy:** `start-app` (login, activar cuenta, recuperar contraseña, confirmar PIN, etc.) según `start-app-routing.module.ts`.

### App (RN + Expo)

- Pantallas: cambiar contraseña (usuario autenticado), flujo “olvidé contraseña” (alineado a canales que exponga tecben-core).
- Almacenamiento seguro de tokens (SecureStore / Keychain) y política de refresh de Sanctum si se implementa.

### APIs a exponer en TECBEN-CORE (propuesta)


| Método | Ruta (ejemplo)                 | Descripción                                                        |
| ------ | ------------------------------ | ------------------------------------------------------------------ |
| POST   | `/api/v1/auth/login`           | Email/teléfono + password → token Sanctum (+ abilities si aplica). |
| POST   | `/api/v1/auth/logout`          | Revocar token actual.                                              |
| POST   | `/api/v1/auth/password/forgot` | Inicia flujo recuperación.                                         |
| POST   | `/api/v1/auth/password/reset`  | Completa recuperación con token.                                   |
| PUT    | `/api/v1/auth/password`        | Cambio de contraseña autenticado (password actual + nueva).        |
| PUT    | `/api/v1/auth/nip`             | Crear/cambiar NIP (si negocio lo confirma en tecben-core).         |


### Reglas de negocio (borrador)

- RN-01: Política de complejidad de contraseña alineada a `Password::defaults()` o reglas explícitas del proyecto.
- RN-02: Rate limiting en login y recuperación (middleware `throttle`).

---

## 4. Gestión de sesiones y eliminación de cuenta

### MÓDULO

Cerrar sesión en el dispositivo actual, opción de cerrar sesión en todos los dispositivos (revocación masiva de tokens Sanctum), y flujo de **baja de cuenta** o solicitud de eliminación según cumplimiento.

### Estado en TECBEN-CORE

- Sanctum guarda tokens en `personal_access_tokens` (revocar con `$user->tokens()->delete()` o por id).
- Implementar endpoints y políticas en tecben-core (no duplicar lógica solo en la app).

### Referencia legacy

- **paco-app-legacy:** cierre de sesión e invalidación de token / sesión en flujo API (revisar servicios HTTP interceptors y `AuthController` del backend legacy).

### App (RN + Expo)

- Botón “Cerrar sesión” → `logout` API + borrar token local + opcionalmente `DELETE` push token.
- “Cerrar en todos los dispositivos” → endpoint en tecben-core que revoque tokens según regla acordada.
- “Eliminar cuenta”: flujo legal (confirmación, email, período de gracia); **definir con legal/PM** e implementar en tecben-core.

### APIs a exponer en TECBEN-CORE (propuesta)


| Método | Ruta (ejemplo)                     | Descripción                                    |
| ------ | ---------------------------------- | ---------------------------------------------- |
| POST   | `/api/v1/auth/logout`              | Revoca token actual (y opcionalmente refresh). |
| POST   | `/api/v1/auth/sessions/revoke-all` | Revoca todos los tokens del usuario.           |
| GET    | `/api/v1/auth/sessions`            | Lista dispositivos/sesiones (opcional v2).     |
| POST   | `/api/v1/cuenta/solicitud-baja`    | Inicia eliminación o ticket (según política).  |


### CASOS BORDE

- Usuario con sesión revocada desde otro dispositivo: la app debe recibir 401 y forzar login.

---

## 5. Imagen de perfil

### MÓDULO

Subida, recorte opcional en cliente y persistencia de foto de perfil asociada al usuario colaborador.

### Estado en TECBEN-CORE

- Campo `users.imagen`: usado en Filament (`UsuarioForm`, `Perfil`) con `FileUpload` en directorio `usuarios` (disco por defecto del filesystem en algunos formularios).
- **Storage (S3 / ArchivoService):** alinear decisión con el estándar que defina el equipo para tecben-core.

### Referencia legacy

- **Paco (backend):** subida de imagen de perfil, resize y disco (p. ej. S3 en legacy); usar solo como referencia de reglas (tamaño, formatos), implementar en tecben-core con `ArchivoService` cuando aplique.

### App (RN + Expo)

- Selector de imagen (`expo-image-picker`), compresión opcional, `multipart/form-data` hacia API en tecben-core.
- Mostrar imagen vía URL firmada o URL pública según disco final.

### APIs a exponer en TECBEN-CORE (propuesta)


| Método | Ruta (ejemplo)          | Descripción                                        |
| ------ | ----------------------- | -------------------------------------------------- |
| POST   | `/api/v1/perfil/imagen` | Multipart: archivo imagen; validación mime/tamaño. |
| DELETE | `/api/v1/perfil/imagen` | Eliminar foto y dejar placeholder.                 |


### Validaciones

- `image|max:2048` (o límite acordado con UX), formatos jpeg/png/webp según política.

### Reglas de negocio (borrador)

- RN-01: Al subir nueva imagen, eliminar archivo anterior del storage si existe (evitar huérfanos).
- RN-02: Respetar tamaño máximo y dimensiones para no saturar ancho de banda móvil.

---

## Resumen transversal — testing y calidad

- Tests de feature Pest para cada endpoint nuevo en **tecben-core** (`auth`, `perfil`, `imagen`).
- Contrato OpenAPI (objetivo del alcance funcional) generado o mantenido junto con los endpoints.
- Coordinación con tareas de **panel** del mismo sprint cuando compartan modelo de colaborador; la lógica de negocio debe vivir en servicios reutilizables en tecben-core, no copiada desde legacy sin revisión.

---

## Referencias en repo (TECBEN-CORE)

- API actual: [routes/api.php](../../../routes/api.php)
- Modelo usuario: [app/Models/User.php](../../../app/Models/User.php)
- Modelo colaborador: [app/Models/Colaborador.php](../../../app/Models/Colaborador.php)
- Notificaciones (contexto push): [ficha-modulo-notificaciones-push.md](../ficha-modulo-notificaciones-push.md)
- Roadmap app: [roadmap-app-rn-expo.md](../../roadmap-app-rn-expo.md)

