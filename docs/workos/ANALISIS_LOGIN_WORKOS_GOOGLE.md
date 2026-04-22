# Análisis: Login con WorkOS + Google y error "Ocurrió un error al completar el inicio de sesión"

## Resumen

Tras intentar iniciar sesión con WorkOS usando la suite de Google o una cuenta personal de Google, el botón **"Iniciar sesión con WorkOS"** redirige correctamente a AuthKit y, al volver al callback, la aplicación muestra:

> **Ocurrió un error al completar el inicio de sesión. Intenta de nuevo.**

Este documento resume el flujo, las causas detectadas y las soluciones aplicadas o recomendadas.

---

## 1. Flujo de peticiones (resumen)

| Paso | URL / Origen | Resultado |
|------|----------------|-----------|
| 1 | Usuario en `http://localhost:8000/admin/login` → clic "Iniciar sesión con WorkOS" | GET `/auth/workos` → 302 a WorkOS |
| 2 | WorkOS | GET `https://api.workos.com/user_management/authorize?...&redirect_uri=http://localhost:8000/auth/workos/callback&state=...` → 302 |
| 3 | AuthKit (WorkOS) | Redirecciones internas (bootstrap, refresh-token, etc.) |
| 4 | Callback en la app | GET `http://localhost:8000/auth/workos/callback?code=...&state=...` → **302** (redirige de vuelta al login con mensaje de error) |

La **Redirect URI** configurada en el Dashboard de WorkOS (`http://localhost:8000/auth/workos/callback`) es correcta y coincide con la usada por la app.

El fallo ocurre **dentro del callback**, al procesar el `code` y llamar a la API de WorkOS.

---

## 2. Causas identificadas (en orden cronológico)

### 2.1 Sesión perdida en la redirección ("Sesión inválida")

- **Qué pasaba:** Al volver desde WorkOS/Google a `localhost:8000/auth/workos/callback`, a veces la cookie de sesión de Laravel no se enviaba, por lo que `session()->pull('workos_oauth_state')` venía vacío y se mostraba "Sesión inválida".
- **Solución aplicada:** Se guarda el `state` también en **cache** (clave `workos_oauth_state_{state}`, TTL 10 min). En el callback se valida primero la sesión y, si no hay state en sesión, se usa la cache. Así la validación no depende de la cookie en la redirección.

### 2.2 State enviado con comillas

- **Qué pasaba:** WorkOS/AuthKit enviaba el `state` en la URL entre comillas (`state=%22...%22`). En la app se guardaba en cache sin comillas, por lo que la clave no coincidía.
- **Solución aplicada:** En el callback se normaliza el `state` con `trim($request->query('state'), '"')` antes de validar y de buscar en cache.

### 2.3 Extensión cURL de PHP no habilitada

- **Qué pasaba:** El SDK de WorkOS usa cURL para hacer POST a `https://api.workos.com/...` en el callback. Si la extensión `curl` no está cargada, PHP lanza `Undefined constant "CURLOPT_RETURNTRANSFER"` y se captura como error genérico.
- **Solución aplicada:** En `php.ini` se descomentó `extension=curl`. Se añadió en el controlador un mensaje de error específico cuando el mensaje de la excepción contiene `CURLOPT_RETURNTRANSFER`.

### 2.4 Verificación SSL de cURL fallida (causa actual)

- **Qué pasaba:** Con cURL ya habilitado, la petición HTTPS a `api.workos.com` falla con:
  ```text
  SSL certificate OpenSSL verify result: unable to get local issuer certificate (20)
  ```
  Es decir, cURL/OpenSSL no encuentran una cadena de certificados de confianza (CA bundle) para validar el certificado del servidor de WorkOS.
- **Dónde ocurre:** En `WorkOS\UserManagement->authenticateWithCode()` → `CurlRequestClient->request()` → `curl_exec()`. El SDK no configura `CURLOPT_CAINFO`; usa el valor por defecto de PHP.
- **Por qué en Windows:** En muchas instalaciones de PHP en Windows, `curl.cainfo` y `openssl.cafile` en `php.ini` están comentados y no hay un archivo de CA bundle por defecto, por lo que la verificación SSL falla.

---

## 3. Solución recomendada para el error SSL (20)

Hay que indicar a PHP/cURL la ruta a un **archivo CA bundle** (listado de certificados de autoridades de confianza).

### Opción A: Configurar `php.ini` (recomendado)

1. Descargar el CA bundle de Mozilla (usado por cURL):
   - https://curl.se/ca/cacert.pem  
   - Guardarlo, por ejemplo, en `C:\php\php-8.5.1\ext\cacert.pem`.

2. En `php.ini` (p. ej. `C:\php\php-8.5.1\php.ini`):
   - Localizar o añadir:
     ```ini
     curl.cainfo = "C:\php\php-8.5.1\ext\cacert.pem"
     openssl.cafile = "C:\php\php-8.5.1\ext\cacert.pem"
     ```
   - Ajustar la ruta si guardaste `cacert.pem` en otra ubicación.

3. Reiniciar el servidor (p. ej. `php artisan serve`) para que cargue el nuevo `php.ini`.

### Opción B: Variable de entorno (alternativa)

Si no puedes editar `php.ini`, puedes definir antes de arrancar PHP:

- `CURL_CA_BUNDLE` = ruta al archivo `cacert.pem`  
- O en Windows, configurar la variable de entorno del sistema con esa ruta.

---

## 4. Mensaje de error mostrado al usuario

Ante cualquier excepción no controlada en el callback, se muestra de forma genérica:

> Ocurrió un error al completar el inicio de sesión. Intenta de nuevo.

Para depuración, el mensaje real y el stack trace se registran en `storage/logs/laravel.log` bajo la clave `WorkOS callback error`. Se ha añadido un mensaje más específico cuando el error es por cURL no habilitado; se puede ampliar de forma similar para el error SSL (20) si se desea guiar al usuario (p. ej. indicando que configure `curl.cainfo` / CA bundle).

---

## 5. Checklist de configuración WorkOS

- [x] Redirect URI en Dashboard WorkOS = `http://localhost:8000/auth/workos/callback` (sin barra final).
- [x] En `.env`: `APP_URL=http://localhost:8000` (o la URL que uses para el panel).
- [x] En `.env`: `WORKOS_CLIENT_ID`, `WORKOS_API_KEY` (y opcionalmente `WORKOS_REDIRECT_URL` si no usas el valor por defecto).
- [x] State guardado en sesión y en cache; state recibido normalizado (trim de comillas).
- [x] Extensión PHP `curl` habilitada en `php.ini`.
- [ ] **CA bundle configurado** en `php.ini` (`curl.cainfo` y/o `openssl.cafile`) para evitar el error SSL (20).

---

## 6. WorkOS vs base de usuarios de la app: por qué hay que darte de alta antes

### Qué hace WorkOS

WorkOS es el **proveedor de identidad**: gestiona el login (Google, etc.), crea o encuentra el usuario en **su** base de datos y devuelve a tu app un `user_id` (p. ej. `user_01KJX0SK72DMC5NXEK3TTAENJK`), el email y el nombre.

Por eso en el **Dashboard de WorkOS → Users** sí aparece tu cuenta (Adrián Ismael García Balan, aismaelgarcia@gmail.com) y los eventos `user.created`, `authentication.oauth_succeeded`, `session.created`: WorkOS ya te creó cuando iniciaste sesión con Google.

### Qué hace la app (tecben-core)

La app tiene **su propia tabla de usuarios** (`usuarios`). Esa tabla define **quién tiene permiso para entrar al panel**: empresa, tipo (user/admin/employee), roles, etc.

En el callback (`WorkOsAuthController::callback`), después de recibir el código de WorkOS y obtener el usuario de WorkOS, la app hace:

```php
$user = Usuario::where('workos_id', $workosId)->first()
    ?? ($email ? Usuario::where('email', $email)->first() : null);

if (! $user) {
    return $this->redirectWithError(
        'Este usuario no está autorizado. Contacta al administrador para darte de alta.'
    );
}
```

Es decir: **solo permite el acceso si ya existe un usuario en la app** con ese `workos_id` o con ese **email**. La app **no crea usuarios automáticamente** al hacer login con WorkOS.

### Resumen

| Dónde | Qué pasa |
|------|----------|
| **WorkOS** | Te identifica (Google, etc.) y crea/mantiene tu usuario en WorkOS. Ahí ya apareces. |
| **App (tecben-core)** | Comprueba si ese email (o workos_id) existe en la tabla `usuarios`. Si no existe → "Este usuario no está autorizado. Contacta al administrador para darte de alta." |

Por tanto **sí**: hay que darte de alta primero en la app (Admin → Usuarios → Nuevo usuario) con el **mismo email** que usas en WorkOS (p. ej. `aismaelgarcia@gmail.com`). Una vez creado ese usuario en la app:

1. La primera vez que entres con WorkOS, la app te encontrará por **email** y te dejará pasar.
2. La app guardará tu `workos_id` y avatar en ese usuario.
3. Las siguientes veces te encontrará por **workos_id** o por email.

Si quisieras que cualquiera que entre con WorkOS/Google pudiera acceder sin estar previamente en la app, habría que implementar **auto-provisioning** (crear el usuario en `usuarios` en el primer login), lo cual es una decisión de producto y seguridad (p. ej. permitir solo dominios de empresa).

---

## 7. Referencias

- WorkOS AuthKit: flujo OAuth y callback.
- Laravel: sesión, cache, rutas `/auth/workos` y `/auth/workos/callback`.
- Controlador: `App\Http\Controllers\Auth\WorkOsAuthController` (`redirect` y `callback`).
- Logs: `storage/logs/laravel.log` (buscar "WorkOS callback" o "WorkOS redirect").
