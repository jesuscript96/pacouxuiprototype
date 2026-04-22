# Ficha técnica: Módulo Notificaciones Push (Legacy Paco)

Documento de análisis para extraer lógica de negocio, integración con la app móvil, servicio de envío, colas y permisos. Solo describe lo que existe en el código.

---

## MÓDULO: Notificaciones push (panel admin + API + colas)

**FECHA ANÁLISIS:** 2025-03-25  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

El panel admin permite **crear y enviar notificaciones push personalizadas** a colaboradores (`HighEmployee`) con filtros por empresa, ubicación, área, departamento, puesto, género, edad, antigüedad en meses, mes de cumpleaños, razón social y (solo admin) filtro de adeudos. El envío real a dispositivos se hace a través de **OneSignal** (REST API vía paquete Laravel). Los **tokens de dispositivo** se registran desde la **app** en la API de autenticación y se guardan en `one_signal_tokens` asociados al **User** del colaborador. Las credenciales de OneSignal **por empresa** vienen de `app_settings` (`one_signal_app_id`, `one_signal_rest_api_key`, `android_channel_id`). Controlador principal del módulo: `App\Http\Controllers\Admin\NotificationPushController`.

---

## SERVICIO DE ENVÍO: OneSignal

- **Proveedor:** [OneSignal](https://onesignal.com/) (notificaciones push multiplataforma).
- **Paquete PHP/Laravel:** `berkayk/onesignal-laravel` (^1.0 en `composer.json`), proveedor `Berkayk\OneSignal\OneSignalServiceProvider`.
- **Uso en código:** Facade `OneSignal` y métodos del trait `App\Traits\NotificationTrait`:
  - `sendNotificationToUser`: arma un payload con `app_id`, `contents` (idioma `en`), `include_player_ids` (array de IDs de suscripción/player de OneSignal), `headings`, `large_icon`, `ios_badgeType`, `ios_sound`, `priority`, `data`, `buttons`, `send_after` (programación), `android_channel_id`, `api_key` (REST API key cuando se pasa explícita).
  - `sendNotificationCustom`: delega en `OneSignal::sendNotificationCustom($parametros)`.
  - También existen `sendNotificationToAll`, `sendNotificationUsingTags`, `sendNotificationToSegment` (menos usados en el flujo de notificaciones custom del panel, pero disponibles en el trait).
- **Configuración global:** `config/onesignal.php` define `app_id`, `rest_api_key` y `user_auth_key` para el paquete. **El envío de notificaciones custom desde el panel no usa solo este archivo:** en `sendNotificationPush` se toman `one_signal_app_id` y `one_signal_rest_api_key` de **`$receiver->company->app_setting`** por cada colaborador, y se agrupan los envíos por `app_id` de empresa.
- **Límite por petición:** Los player IDs se parten en trozos de **2000** (`array_chunk(..., 2000)`) antes de despachar cada `NotificationPush` job.

### Cómo encaja la app móvil

1. El usuario de la app (modelo `User` vinculado al colaborador) debe tener filas en **`one_signal_tokens`** (tabla `one_signal_tokens`: `user_id`, `token`).
2. En **`App\Http\Controllers\Api\AuthController`** (login/registro/actualización de sesión), si el request incluye `onesignal_token` y no está vacío, se crea o reutiliza un `OneSignalToken` y se asocia al usuario (`$user->one_signal_tokens()->save(...)`). También hay rutas que eliminan tokens al cerrar sesión o al invalidar sesión.
3. Al enviar una notificación custom, para cada receptor se obtiene `$receiver->user` y se iteran `$user_high_employee->one_signal_tokens`; cada `token` se trata como **player ID / subscription ID** de OneSignal y se pasa en `include_player_ids`.
4. **Requisito:** el colaborador debe tener relación `user` (`$receivers = $receivers->has('user')`) y la empresa debe tener **`app_setting`** con OneSignal configurado; si falta, el acceso a `$receiver->company->app_setting` puede fallar en runtime.

---

## ENTIDADES

### Tabla: `notifications`

- Uso para tipo **`NOTIFICACION CUSTOM`**: `message` (cuerpo), `custom_title` (título mostrado en push), `date`, `user_id` (remitente panel), `is_sent` (`ENVIADA` / `NO ENVIADA`), `is_scheduled` (`PROGRAMADA` / `NO PROGRAMADA`), filtros serializados JSON (`companies_filter`, `locations_filter`, `departments_filter`, `areas_filter`, `positions_filter`, `genders_filter`, `month_birthdays_filter`, `has_debts`, `age_from_filter`, `age_till_filter`, `month_filter_from`, `month_filter_till`), etc.
- La misma tabla se usa para otros tipos de notificación (encuestas, mensajes, carpetas, etc.) con otros `type` y FKs opcionales.

### Tabla pivot: `high_employee_notification`

- **FK:** `high_employee_id`, `notification_id`.
- **Pivot:** `status` enum `LEIDA` | `NO LEIDA`, timestamps.
- Asignación masiva: job `CustomNotificationsReceivers` hace `attach($receivers, ['status' => 'NO LEIDA'])` en cola `sync_receivers`.

### Tabla: `one_signal_tokens`

- `user_id`, `token` (ID de jugador OneSignal del dispositivo).

### Tabla: `app_settings`

- Por empresa (relación `Company` → `app_setting`): `one_signal_app_id`, `one_signal_rest_api_key`, `android_channel_id`, enlaces de tienda, versiones iOS/Android, etc.

---

## JOB Y COLAS

- **`App\Jobs\Notifications\NotificationPush`** (ShouldQueue, `tries = 1`): en `handle()` llama a `sendNotificationToUser` del `NotificationTrait` con título, mensaje, `data` (p. ej. `type` => `NOTIFICACION CUSTOM`), array de tokens, `appId`, `restApiKey`, `android_channel_id`.
- **Cola usada en envío inmediato desde panel:** `medium_priority_notifications`.
- **`App\Jobs\NotificationPush\CustomNotificationsReceivers`:** adjunta destinatarios al pivot; cola **`sync_receivers`**.

---

## PROGRAMACIÓN (ENVÍO DIFERIDO)

- Si en el formulario se indica **fecha** (`$request->date`), la notificación queda `is_sent = 'NO ENVIADA'` y `is_scheduled = 'PROGRAMADA'`. **No se despachan** jobs `NotificationPush` en ese momento (solo se guardan registro y destinatarios).
- Comando Artisan **`send:notifications_push`** (`App\Console\Commands\SendScheduledNotificationsPush`): busca `Notification` con `is_scheduled = PROGRAMADA`, `is_sent = NO ENVIADA` y `date <= now()`, marca como enviada, **recalcula destinatarios** aplicando los filtros guardados en el registro (lógica extensa; puede añadir nuevos receptores según estado actual de adeudos/empresa/etc.) y despacha `NotificationPush` por empresa/chunks.
- En **`App\Console\Kernel`:** el comando se programa **cada 5 minutos**, `withoutOverlapping(10)`, `onOneServer`, `runInBackground`.

---

## RUTAS Y PERMISOS (ADMIN)

| Ruta (prefijo admin) | Método | Acción | Permiso típico |
|----------------------|--------|--------|----------------|
| notifications_push | GET | getIndex | view_notifications_push |
| notifications_push/filters | POST | getFilters | view_notifications_push |
| notifications_push/create | GET | getCreate | create_notifications_push |
| notifications_push/send_notification_push | POST | sendNotificationPush | create_notifications_push |
| notifications_push/edit/{id} | GET | getEdit | edit_notifications_push |
| notifications_push/update | POST | update | edit_notifications_push |
| notifications_push/view/{id} | GET | getView | view_notifications_push |
| notifications_push/trash/{id} | GET | trash | trash_notifications_push |
| notifications_push/receivers_view/{id} | GET | getReceiversView | view_notifications_push |
| notifications_push/receivers_filters | POST | getReceiversFilters | view_notifications_push |

Middleware: `logged`, `2fa`, `Permissions` según ruta.

**Permiso adicional:** `view_companies_notifications_push` — si el usuario tiene empresa y este permiso, en listado/filtros ve notificaciones custom cuyo **sender** pertenece a su `company_id` (ámbito “empresa”); si no, solo las que él envió (`sent_notifications_push`).

---

## FLUJO PRINCIPAL: ENVIAR NOTIFICACIÓN CUSTOM

1. **getCreate:** Construye query base de `HighEmployee` según `high_employee_filters` del usuario y `company`; exige `has('user')`. Carga selectores (áreas, puestos, ubicaciones, departamentos, empresas, días del mes para cumpleaños).
2. **sendNotificationPush:** Valida `title` (cuerpo), `title_notification` (título), `company_filter` (requerido). Exige `select_all` **o** al menos un id en `manual_activation` (JSON). Aplica filtros análogos a getCreate más `manual_deactivation` (excluir ids). Si admin, filtra por empresas y opcionalmente **adeudos** (`has_debts == SI`: colaboradores con cuentas por cobrar `PENDIENTE` y más de un intento de cobranza).
3. Si `select_all`, se hace `get()` de la query filtrada; si no, colección vacía que se fusiona con empleados de `manual_activation`.
4. Crea `Notification` tipo `NOTIFICACION CUSTOM`, asocia remitente, guarda filtros y estado enviado/programado.
5. Despacha `CustomNotificationsReceivers` para pivot.
6. Si `is_sent == ENVIADA`, agrupa tokens por `app_id` de la empresa del receptor y despacha `NotificationPush` por cada chunk de hasta 2000 tokens.
7. Registra **Log** y redirige con mensaje de éxito.

---

## EDICIÓN Y BORRADO

- **getEdit / update:** Solo si la notificación **no** está ya `ENVIADA`. Permite cambiar título (`custom_title`), cuerpo (`message`) y, si está programada y no enviada, fecha/hora de envío.
- **trash:** Elimina el registro `Notification`. En el código se asigna el mensaje de éxito usando `$notification->custom_title` **antes** de comprobar si `$notification` existe → ⚠️ mismo patrón de error que en otros módulos si el id no existe.

---

## VALIDACIONES (ENVÍO)

- `title`, `title_notification`, `company_filter` required.
- Debe haber al menos un destinatario (`select_all` o `manual_activation` no vacío).
- `max_input_vars` se eleva a 10000 para formularios con muchos checkboxes.

---

## OTROS USOS DE `NotificationPush` EN EL LEGACY

El mismo job y OneSignal se usan desde **muchos** puntos: encuestas programadas (`SendScheduledSurveys`), mensajes recurrentes, nómina/recibos, carpetas, bajas de colaboradores, cumpleaños, cuentas por cobrar, altas API, seguros, importaciones, etc. Siempre con el mismo patrón: credenciales desde `company->app_setting`, tokens del `User` del colaborador, cola variable (`high_priority_notifications`, `medium_priority_notifications`, `low_priority_notifications`, `notifications`).

---

## VISTAS (REFERENCIA)

- `admin.notifications_push.list`, `.table`, `.create`, `.edit`, `.view`, receptores y export Excel de receptores (`App\Exports\NotificationPush\ReceiversExport`).

---

## CASOS BORDE Y DEUDA TÉCNICA

- **Credenciales en repo:** `config/onesignal.php` contiene valores reales de App ID y REST API Key en el código versionado → riesgo de seguridad; en producción deberían ser variables de entorno y rotación de claves.
- **trash:** Uso de `$notification` antes del `if (!$notification)`.
- **sendNotificationPush:** Uso de `Builder` en closure de adeudos sin `use Illuminate\Database\Eloquent\Builder` visible en el fragmento del controlador (posible error si no está importado al inicio del archivo).
- **Programadas:** La ventana de envío depende del cron cada 5 minutos (no es instantánea a la hora exacta salvo coincidencia con el tick).
- **Multi-empresa en un solo envío:** Si un lote mezclara empresas con distintos `app_id`, el código agrupa por clave string de `$appId`; cada grupo usa sus propias credenciales.

---

## DIFERENCIAS / NOTAS PARA TECBEN-CORE

- Documentar equivalencia: OneSignal vs FCM/APNs nativo, almacenamiento de tokens, `app_settings` por tenant, colas y comando programado.
- Revisar si se mantiene `include_player_ids` vs External User ID de OneSignal para simplificar envíos.

---

## REFERENCIAS DE CÓDIGO (SIN COPIAR SECRETOS)

- Trait: `app/Traits/NotificationTrait.php`
- Job envío: `app/Jobs/Notifications/NotificationPush.php`
- Panel: `app/Http/Controllers/Admin/NotificationPushController.php` (`sendNotificationPush`, `getCreate`, `getFilters`, …)
- Programadas: `app/Console/Commands/SendScheduledNotificationsPush.php` + `app/Console/Kernel.php`
- Registro token app: `app/Http/Controllers/Api/AuthController.php` (`onesignal_token`)
- Modelos: `App\Models\Notification`, `App\Models\OneSignalToken`, `App\Models\AppSetting`
- Paquete: `composer.json` → `berkayk/onesignal-laravel`
