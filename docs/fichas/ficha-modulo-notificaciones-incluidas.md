# Ficha técnica: Módulo Notificaciones Incluidas (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Notificaciones Incluidas (excluded_notifications por empresa)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

En el legacy **no existe un resource independiente** tipo `NotificacionesIncluidasResource` con rutas propias. La funcionalidad “notificaciones incluidas” es un **bloque dentro del módulo Empresas (Companies)**: en crear y editar empresa se muestra la lista de tipos de notificación; por empresa se define cuáles están **incluidas** (checkbox marcado) y cuáles **excluidas** (checkbox desmarcado → se guardan en `excluded_notifications`). Solo usuarios con rol **admin** ven y pueden modificar este bloque.

---

## ENTIDADES

### Tabla: `excluded_notifications`

- **PK:** `id` (bigint unsigned).
- **Campos:** `reason` (string), `type` (string), `company_id` (unsignedBigInteger nullable, FK a companies cascade). `timestamps`.
- **Relaciones (modelo ExcludedNotification):** `company()` belongsTo Company.
- **Sentido:** Cada fila indica que la empresa no debe recibir ese tipo de notificación. Si una notificación no está en excluded_notifications para la empresa, se considera **incluida** (se puede enviar).

### Catálogo fijo de tipos (en código, no en BD)

Lista definida en `CompaniesController` (getCreate, create, getEdit, update):

1. Adelanto de nómina disponible  
2. Confirmación en validación de cuenta  
3. Rechazó en validación de cuenta  
4. Registro Exitoso  
5. Bienvenida a la Empresa  
6. Recordatorio para estado de ánimo  

No hay tabla de “catálogo de notificaciones”; los textos se repiten en arrays en el controlador.

### Relación en Company

- **Company:** `excluded_notifications()` hasMany ExcludedNotification. Una empresa tiene N registros en excluded_notifications; cada uno es un “tipo” excluido (reason + type).

---

## REGLAS DE NEGOCIO

- **RN-01:** La configuración de notificaciones incluidas/excluidas solo la puede ver y editar un usuario con rol **admin** (`hasRoles('admin')`). En create y edit de empresa el bloque “Notificaciones” se muestra con `@if(Auth::user()->hasRoles('admin'))`; la persistencia en create/update también va dentro de `if ($user->hasRoles('admin'))`.
- **RN-02:** Por defecto (create): todos los tipos se consideran incluidos (checkboxes marcados en la vista); si el admin desmarca alguno, ese tipo se guarda en excluded_notifications (reason = texto del tipo, type = 'ALL').
- **RN-03:** En update: los checkboxes marcados = incluidos (no deben estar en excluded_notifications). Los desmarcados = se agregan a excluded_notifications si aún no existen; los que el usuario volvió a marcar se eliminan de excluded_notifications (`whereNotIn('reason', $notifications)->delete()`). La variable `$notifications` en el controlador es la lista de tipos **excluidos** (array_diff de la lista total menos los habilitados en el request).
- **RN-04:** Al enviar notificaciones (jobs, API, comandos), el código comprueba si la empresa tiene excluido ese tipo antes de enviar, por ejemplo: `$company->excluded_notifications()->where('reason','Adelanto de nómina disponible')->doesntExist()`. Si no existe registro excluido, se puede enviar.
- **RN-05:** El campo `type` en excluded_notifications en el flujo de Companies se guarda siempre como `'ALL'`. En seeds (ej. AlmaExcludedNotificationsSeeder) se usan también valores como `'SMS'`; la UI del legacy no permite elegir tipo, solo incluir/excluir por reason.

---

## RUTAS Y PERMISOS

No hay rutas propias de “Notificaciones Incluidas”. La funcionalidad se usa dentro de:

- **admin/companies/create** (GET/POST): permisos create_companies. Bloque Notificaciones solo si usuario es admin.
- **admin/companies/edit/{id}** (GET/POST): permisos edit_companies. Bloque Notificaciones solo si usuario es admin.

El acceso al bloque depende de **rol admin**, no de un permiso específico tipo view_notifications_included.

---

## FLUJO PRINCIPAL

### Crear empresa (create)

1. Usuario con create_companies y rol admin ve el bloque “Notificaciones” con los 6 tipos y switch por cada uno (por defecto todos marcados = incluidos).
2. Al enviar el formulario, si `$user->hasRoles('admin')`: se toma `$request->only(['notifications'])`; las keys son los tipos marcados (incluidos). Se calcula `$notifications = array_diff($notifications_list, $enabled)` (tipos excluidos). Por cada tipo en `$notifications` se crea ExcludedNotification (reason, type 'ALL') y se asocia a la empresa.
3. Si el usuario no es admin, no se ejecuta esta lógica y la empresa queda sin registros en excluded_notifications (todas incluidas por defecto).

### Editar empresa (update)

1. En getEdit se pasa `company_notifications = $company->excluded_notifications()->pluck('reason')->toArray()` a la vista. En la vista, el checkbox está marcado si el tipo **no** está en `company_notifications` (incluido).
2. Al enviar el formulario, si `$user->hasRoles('admin')`: se calculan de nuevo los excluidos (`array_diff`). Por cada tipo excluido que aún no exista en excluded_notifications se crea un registro; luego `$company->excluded_notifications()->whereNotIn('reason', $notifications)->delete()` para quitar los que pasaron a incluidos.

### Uso al enviar notificaciones

En varios puntos del código (HighEmployeesController, LowEmployeesController, AuthController, Jobs LCT/VR, AccountStatesController, CheckActiveCompanies, SendMoodReminder, etc.) se consulta antes de enviar, por ejemplo:

- `$company->excluded_notifications()->where('reason','Adelanto de nómina disponible')->doesntExist()`
- `$company->excluded_notifications()->where('reason','Bienvenida a la Empresa')->doesntExist()`
- `$company->excluded_notifications()->where('reason','Registro Exitoso')->doesntExist()`
- Similar para “Confirmación en validación de cuenta”, “Rechazó en validación de cuenta”, “Recordatorio para estado de ánimo”.

Si existe el registro, no se envía esa notificación a esa empresa.

---

## VALIDACIONES

- No hay validación específica de los nombres de reason ni de que vengan dentro del catálogo fijo. Si el request envía otros keys en `notifications`, se tratarían como incluidos y el array_diff podría dejar de incluir tipos que sí están en la lista fija; la lógica depende de que la vista solo envíe los 6 nombres definidos en el controlador.

---

## VISTAS

- **admin.companies.create:** Bloque “Notificaciones” (solo si Auth::user()->hasRoles('admin')). Título “Notificaciones”, subtítulo “Asigne las notificaciones que pueden enviarse a empleados de esta empresa”. Lista de 6 ítems con switch; name `notifications[{{ $notification }}]`. Por defecto todos checked.
- **admin.companies.edit:** Mismo bloque (solo admin). Checkbox checked si el tipo no está en `company_notifications` (es decir, está incluido).

Además, en edit se muestra el campo “Frecuencia de envíos para notificaciones de estado de ánimo (días)” solo si “Recordatorio para estado de ánimo” está **incluido** (`!in_array("Recordatorio para estado de ánimo", $company_notifications)`).

---

## MODELOS INVOLUCRADOS

- **ExcludedNotification** (App\Models\ExcludedNotification): tabla `excluded_notifications`, fillable reason, type. Relación company() belongsTo Company. No tiene company_id en fillable pero la migración y la relación lo usan; la asignación se hace con `$company->excluded_notifications()->save($excluded_notification)`.
- **Company:** relación excluded_notifications() hasMany ExcludedNotification.

---

## MIGRACIONES

- **2023_01_10_151117_create_excluded_notifications_table:** Crea excluded_notifications (id, reason string, type string, company_id nullable FK companies cascade, timestamps).

---

## CASOS BORDE

- **Usuario no admin:** No ve el bloque y no puede cambiar exclusiones; en create la empresa queda sin filas en excluded_notifications (todas incluidas). En update no se tocan excluded_notifications si no es admin.
- **Request sin key notifications:** En update, $enabled = []; entonces $notifications = todos los tipos y se crearían exclusiones para todos; luego el delete quitaría todo. Efecto: todas excluidas. Comportamiento coherente con “ningún checkbox marcado”.
- **Reason con typo en otro código:** Los jobs/controladores que consultan excluded_notifications usan el texto exacto (ej. 'Adelanto de nómina disponible'). Si en BD hubiera un reason distinto por error, no coincidiría y la notificación se enviaría o se bloquearía según el caso.

---

## AMBIGÜEDADES

- **Tipo ALL vs SMS/etc.:** La UI solo persiste type 'ALL'. El seeder Alma usa 'Bienvenido', 'Recordatorio de descarga' y type 'SMS'. No está documentado si en otro flujo se usa type para filtrar por canal (SMS, push, etc.); en las consultas actuales se usa solo reason.
- **Catálogo en código:** Los 6 tipos están hardcodeados en dos sitios del CompaniesController (getCreate/create y getEdit/update). Añadir un tipo nuevo exige cambiar código y vistas.

---

## DEUDA TÉCNICA

- **Sin resource propio:** “Notificaciones Incluidas” se documenta como módulo del Panel Admin pero en código es un bloque de Empresas; no hay rutas ni controlador dedicado. Para paridad en tecben-core podría implementarse como NotificacionesIncluidasResource (por ejemplo por empresa o global) o mantenerse como sección dentro de Empresas.
- **Duplicación de la lista:** La lista de 6 notificaciones está duplicada en create y en update; convendría una constante o config compartida.

---

## JOBS / PUNTOS QUE CONSULTAN EXCLUDED_NOTIFICATIONS

- LCT: RestoreEmployeesLCTJob, HighEmployeesLCTJob (Adelanto de nómina disponible, Bienvenida a la Empresa).
- VR: RestoreEmployeesVRJob, HighEmployeesVRJob (igual).
- CheckActiveCompanies, CheckActiveModules (Bienvenida, Adelanto).
- HighEmployeesController (create/alta), LowEmployeesController (baja), AccountStatesController (Confirmación/Rechazó validación cuenta).
- AuthController (Registro Exitoso), AuthNomipayController (Adelanto).
- ApiHighEmployeesController (Adelanto).
- SendMoodReminder: whereDoesntHave('excluded_notifications') para recordatorio estado de ánimo.
- FirstSheetImport (carga masiva): usa company->excluded_notifications() para lógica de notificaciones.

---

## DIFERENCIAS CON TECBEN-CORE (si aplica)

- No verificado en este análisis. Al implementar: decidir si es resource independiente (p. ej. por empresa con grid de tipos) o bloque dentro de Empresas; mantener la lista de tipos y la regla “solo admin puede configurar” si se desea paridad; extraer catálogo a config o BD si se quiere ampliar sin tocar código.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
