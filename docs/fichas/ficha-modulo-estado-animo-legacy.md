# Ficha técnica: Módulo Estado de Ánimo (Legacy Paco)

Documento de análisis integral del módulo de Estado de Ánimo. Incluye los catálogos de Características y Afecciones (administrados desde el panel), el registro diario del colaborador (app móvil), las estadísticas personales, y los mecanismos de notificación (recordatorios y alertas por estados bajos). Solo describe lo que existe en el código.

---

## MÓDULO: Estado de Ánimo

**FECHA ANÁLISIS:** 2026-04-08
**ANALIZADO POR:** Agente paco-legacy
**ESTADO EN TECBEN-CORE:** Implementado parcial (solo catálogos de Características y Afecciones; falta registro de moods, API, estadísticas, jobs y notificaciones)

El módulo permite a los **colaboradores** registrar diariamente cómo se sienten desde la app móvil, eligiendo un tipo de día (muy malo → muy bueno), seleccionando características que describen su estado y afecciones que pueden estar experimentando. Los **administradores** gestionan los catálogos de Características y Afecciones desde el panel. Un sistema de **recordatorios push** (comando programado) incentiva a los empleados a registrar su estado, y un sistema de **alerta por SMS** notifica a los responsables cuando hay empleados con estados bajos.

---

## ENTIDADES

### Tabla: `moods`

- **PK:** id (bigint unsigned).
- **Campos:**
  - `type` (enum: `very_bad`, `bad`, `normal`, `well`, `very_well`): tipo de día seleccionado por el colaborador.
  - `score` (integer): valor numérico asociado al type. Mapeo: very_bad=1, bad=2, normal=3, well=4, very_well=5.
  - `high_employee_id` (FK nullable → high_employees, cascade): colaborador que registró el estado.
- **Campos eliminados por migraciones:**
  - `mood_characteristic_id` (FK): eliminado en migración `update_moods_table` — relación 1:1 reemplazada por N:M vía pivot.
  - `mood_disorder_id` (FK): eliminado en migración `update_moods_2_table` — relación 1:1 reemplazada por N:M vía pivot.
- **Relaciones (modelo Mood):**
  - `high_employee()` → belongsTo HighEmployee
  - `mood_characteristics()` → belongsToMany MoodCharacteristic (pivot `mood_characteristic_mood`, withTimestamps)
  - `mood_disorders()` → belongsToMany MoodDisorder (pivot `mood_disorder_mood`, withTimestamps)
- **Accessors:**
  - `mood_characteristic` → devuelve la primera característica asociada (first()).
  - `mood_disorder` → devuelve la primera afección asociada (first()).
  - `day_text` → texto descriptivo en español: very_bad="Un día muy malo", bad="Un día malo", normal="Un día neutral", well="Un día bueno", very_well="Un día muy bueno".

### Tabla: `mood_characteristics`

- **PK:** id (bigint unsigned).
- **Campos:**
  - `name` (string): nombre de la característica (ej: "Estrés", "Motivado", "Cansado").
  - `initial_list` (enum nullable: `normal`, `bad`, `very_bad`, `well`, `very_well`): determina en qué "lista" de la app aparece inicialmente esta característica al seleccionar un tipo de día. Si es null, aparece como "SIN ASIGNAR".
- **Relaciones (modelo MoodCharacteristic):**
  - `moods()` → belongsToMany Mood (pivot `mood_characteristic_mood`, withTimestamps)

### Tabla: `mood_disorders`

- **PK:** id (bigint unsigned).
- **Campos:**
  - `name` (string): nombre de la afección (ej: "Ansiedad", "Insomnio", "Dolor de cabeza").
- **Campo eliminado:** `initial_list` existía en la migración original pero fue eliminado en `update_mood_disorders_table`.
- **Relaciones (modelo MoodDisorder):**
  - `moods()` → belongsToMany Mood (pivot `mood_disorder_mood`, withTimestamps)

### Tabla pivot: `mood_characteristic_mood`

- **PK:** id (bigint unsigned).
- **Campos:** `mood_characteristic_id` (FK → mood_characteristics, cascade), `mood_id` (FK → moods, cascade), timestamps.
- **Uso:** Relación N:M entre moods y características. Un registro de estado de ánimo puede tener múltiples características.

### Tabla pivot: `mood_disorder_mood`

- **PK:** id (bigint unsigned).
- **Campos:** `mood_disorder_id` (FK → mood_disorders, cascade), `mood_id` (FK → moods, cascade), timestamps.
- **Uso:** Relación N:M entre moods y afecciones. Un registro de estado de ánimo puede tener múltiples afecciones.

### Tablas auxiliares (no parte directa del módulo):

- `notifications_frequencies`: configuración de frecuencia de recordatorios por empresa (`days`, `type`, `next_date`). Relación: Company hasOne NotificationFrequency.
- `excluded_notifications`: empresas excluidas de ciertos tipos de notificación (`reason`, `type`). Filtra empresas con reason='Recordatorio para estado de ánimo'. Relación: Company hasMany ExcludedNotification.
- `products`: el módulo está ligado al producto "Estados de ánimo" que debe estar activo para la empresa y el empleado.

---

## REGLAS DE NEGOCIO

### Registro de Estado de Ánimo (App)

- **RN-01:** Cada colaborador puede registrar **máximo un estado de ánimo por día**. Si ya existe un mood con `created_at` del día actual para ese `high_employee_id`, se rechaza con error "Ya ha registrado un estado de ánimo el dia de hoy".
- **RN-02:** El registro requiere: `type` (string, obligatorio), `mood_characteristics` (array de IDs, obligatorio), `mood_disorders` (array de IDs, obligatorio).
- **RN-03:** El `score` se asigna automáticamente basado en el `type`: very_bad=1, bad=2, normal=3, well=4, very_well=5.
- **RN-04:** Se pueden seleccionar **múltiples** características y **múltiples** afecciones por registro (relaciones N:M).

### Catálogo de Características (Admin)

- **RN-05:** El nombre es obligatorio (`name` required).
- **RN-06:** La `initial_list` es opcional. Valores posibles: `normal` (Normal), `bad` (Mal), `very_bad` (Muy mal), `well` (Bien), `very_well` (Muy bien). Determina en qué grupo de la app aparece la característica al seleccionar un tipo de día.
- **RN-07:** No se puede eliminar una característica que tenga moods asociados (registros de uso). Mensaje: "No puede borrar una característica con registros asignados."
- **RN-08:** En la API, las características se devuelven filtradas por `initial_list` del type seleccionado por el colaborador. El endpoint `getOtherCharacteristics` devuelve las que **no** pertenecen a esa lista (para que el colaborador pueda elegir características adicionales), paginadas de 10 en 10.

### Catálogo de Afecciones (Admin)

- **RN-09:** El nombre es obligatorio (`name` required).
- **RN-10:** No se puede eliminar una afección que tenga moods asociados. Mensaje: "No puede borrar una afección con registros asignados."
- **RN-11:** Las afecciones no tienen `initial_list` (fue eliminado por migración); se muestran todas al colaborador sin importar el tipo de día.

### Recordatorios Push

- **RN-12:** El comando `send:mood_reminder` envía notificaciones push a los empleados que **no han registrado** su estado de ánimo hoy.
- **RN-13:** Solo se envía a empresas que tienen el producto "Estados de ánimo" activo.
- **RN-14:** Las empresas con `excluded_notifications` con reason "Recordatorio para estado de ánimo" quedan excluidas.
- **RN-15:** La frecuencia se controla por la tabla `notifications_frequencies`: si existe un registro para la empresa, solo se envía si `Carbon::now() > next_date`. Tras enviar, se actualiza `next_date = hoy + days`.
- **RN-16:** Solo se envía a empleados que tengan el producto "Estados de ánimo" con status `ACTIVO` y que tengan tokens de OneSignal registrados.
- **RN-17:** El push se despacha con un **delay aleatorio** entre 10 y 20 horas desde la hora actual (simulando un horario entre las 10:00 y 20:59), para no saturar a todos los empleados al mismo momento.

### Alerta por Estados Bajos (SMS)

- **RN-18:** El comando `check:low_mood_employees` verifica diariamente los registros del día anterior con type `bad` o `very_bad`.
- **RN-19:** Agrupa los registros bajos por empresa y envía **SMS** a los usuarios del panel (type `high_user`) de cada empresa que tengan el permiso `review_moods`.
- **RN-20:** El SMS incluye: nombre del usuario, cantidad de empleados con estados bajos, y un listado con nombre completo, número de empleado, ubicación y fecha de registro de cada uno.
- **RN-21:** Solo se envía a usuarios que tengan nombre y teléfono no vacíos.

### Estadísticas (App)

- **RN-22:** Las estadísticas son **personales** del colaborador (filtradas por `high_employee_id`).
- **RN-23:** Se ofrecen 4 periodos: `weekly` (semana actual), `monthly` (mes actual), `biannual` (últimos 6 meses), `annual` (año actual).
- **RN-24:** Cada periodo retorna: data agrupada (score promedio por día/mes), values (array de promedios), labels, total de registros, associations (conteo de afecciones en el periodo), y texto del periodo.

---

## FLUJO PRINCIPAL: Registro de estado de ánimo (App)

1. Colaborador abre la sección "Estado de ánimo" en la app.
2. La app consulta `GET moods/get_mood` para verificar si ya registró hoy; si ya existe, muestra el registro previo.
3. Si no ha registrado, selecciona su tipo de día (very_bad, bad, normal, well, very_well).
4. La app carga las características asociadas a ese tipo (`GET moods/get_characteristics?initial_list={type}`).
5. El colaborador selecciona una o más características. Puede buscar más con `GET moods/get_other_characteristics?initial_list={type}` (paginado).
6. La app carga las afecciones (`GET moods/get_disorders`).
7. El colaborador selecciona una o más afecciones.
8. `POST moods/create` con `type`, `mood_characteristics[]`, `mood_disorders[]`.
9. Se valida que no exista mood del día actual; se crea el Mood con score calculado; se hacen attach de characteristics y disorders; se asocia al high_employee.

## FLUJO SECUNDARIO: Consulta de estadísticas (App)

1. Colaborador accede a la sección de estadísticas.
2. `GET moods/get_statistics?type=weekly|monthly|biannual|annual`.
3. La API retorna datos agrupados (promedios de score por periodo), total de registros, afecciones asociadas y texto descriptivo del rango.
4. La app muestra gráficas de tendencia y listado de afecciones más frecuentes.

## FLUJO ADMINISTRACIÓN: CRUD de Características (Admin)

1. Admin accede a "Catálogos Admin" → "Estados de animo" → "Características".
2. Ve listado paginado (10 por página) con búsqueda y ordenamiento por ID, nombre o lista inicial.
3. Puede crear: ingresa nombre (requerido), selecciona lista inicial (opcional). Se crea Log.
4. Puede editar: modifica nombre y/o lista inicial. Se crea Log.
5. Puede ver: detalle de solo lectura.
6. Puede eliminar: solo si no tiene moods asociados. Se crea Log. Eliminación física (hard delete).

## FLUJO ADMINISTRACIÓN: CRUD de Afecciones (Admin)

1. Admin accede a "Catálogos Admin" → "Estados de animo" → "Afecciones".
2. Ve listado paginado (10 por página) con búsqueda y ordenamiento por ID o nombre.
3. Puede crear: ingresa nombre (requerido). Se crea Log.
4. Puede editar: modifica nombre. Se crea Log.
5. Puede ver: detalle de solo lectura.
6. Puede eliminar: solo si no tiene moods asociados. Se crea Log. Eliminación física (hard delete).

---

## VALIDACIONES

### API - Crear Mood

- `mood_characteristics`: required, array ("Los id de las características son requeridos").
- `mood_disorders`: required, array ("Los id de las afecciones son requeridos").
- `type`: required, string ("El tipo de estado de animo es requerido").
- Unicidad diaria: verificación manual `whereDate('created_at', Carbon::today())`.

### Admin - Características

- `name`: required ("El nombre es requerido").
- `initial_list`: opcional, sin validación de valores en el controlador (depende del select en la vista con valores fijos).

### Admin - Afecciones

- `name`: required ("El nombre es requerido").

---

## PERMISOS

### Panel Admin

| Acción | Permiso requerido |
|--------|------------------|
| Ver listado de Características | `view_moods` |
| Crear Característica | `create_moods` |
| Editar Característica | `edit_moods` |
| Eliminar Característica | `trash_moods` |
| Ver Característica | `view_moods` |
| Ver listado de Afecciones | `view_moods` |
| Crear Afección | `create_moods` |
| Editar Afección | `edit_moods` |
| Eliminar Afección | `trash_moods` |
| Ver Afección | `view_moods` |

### Alerta SMS por estados bajos

| Acción | Permiso requerido |
|--------|------------------|
| Recibir SMS de estados bajos | `review_moods` |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

En el sidebar, el submenú "Estados de animo" (con hijos Características y Afecciones) se muestra si el usuario tiene al menos uno de: `edit_moods`, `view_moods`, `trash_moods`, `create_moods`.

---

## SERVICIOS/ENDPOINTS INVOLUCRADOS

### Admin (web.php) — Características

| Ruta | Método HTTP | Controlador@método | Descripción |
|------|------------|-------------------|-------------|
| `admin/mood_characteristics` | GET | MoodCharacteristicsController@getIndex | Listado paginado |
| `admin/mood_characteristics/get` | POST | getTable | Tabla AJAX con filtros/búsqueda/ordenamiento |
| `admin/mood_characteristics/create` | GET | getCreate | Formulario de creación |
| `admin/mood_characteristics/create` | POST | create | Crear característica |
| `admin/mood_characteristics/edit/{id}` | GET | getEdit | Formulario de edición |
| `admin/mood_characteristics/edit` | POST | update | Actualizar característica |
| `admin/mood_characteristics/trash/{id}` | GET | Trash | Eliminar característica |
| `admin/mood_characteristics/view/{id}` | GET | getView | Ver detalle |

### Admin (web.php) — Afecciones

| Ruta | Método HTTP | Controlador@método | Descripción |
|------|------------|-------------------|-------------|
| `admin/mood_disorders` | GET | MoodDisordersController@getIndex | Listado paginado |
| `admin/mood_disorders/get` | POST | getTable | Tabla AJAX con filtros/búsqueda/ordenamiento |
| `admin/mood_disorders/create` | GET | getCreate | Formulario de creación |
| `admin/mood_disorders/create` | POST | create | Crear afección |
| `admin/mood_disorders/edit/{id}` | GET | getEdit | Formulario de edición |
| `admin/mood_disorders/edit` | POST | update | Actualizar afección |
| `admin/mood_disorders/trash/{id}` | GET | Trash | Eliminar afección |
| `admin/mood_disorders/view/{id}` | GET | getView | Ver detalle |

### API (api.php)

| Ruta | Método HTTP | Controlador@método | Descripción |
|------|------------|-------------------|-------------|
| `api/moods/get_mood` | GET | MoodsController@getMood | Obtener mood del día actual del colaborador |
| `api/moods/get_characteristics` | GET | getCharacteristics | Listar características por initial_list (sin paginar) |
| `api/moods/get_other_characteristics` | GET | getOtherCharacteristics | Listar características fuera de la initial_list (paginado 10) |
| `api/moods/get_disorders` | GET | getDisorders | Listar todas las afecciones (sin paginar) |
| `api/moods/get_statistics` | GET | getStatistics | Estadísticas del colaborador por periodo |
| `api/moods/create` | POST | create | Registrar estado de ánimo diario |

---

## JOBS/COLAS

- **MoodNotification:** Cola `moods_push_notifications`. Se despacha por el comando `send:mood_reminder` para cada empleado que no ha registrado su mood hoy. Envía notificación push vía OneSignal con título "Reflexiona sobre tu estado de ánimo" y mensaje personalizado con el nombre del empleado. Se despacha con **delay aleatorio** (entre 10 y 20 horas desde la ejecución del comando) para distribuir las notificaciones. `$tries = 1`.

---

## COMANDOS ARTISAN

- **`send:mood_reminder`** (`SendMoodReminder`): Envía recordatorios push a empleados que no han registrado su estado de ánimo diario. Filtra empresas con el producto "Estados de ánimo" activo, excluye empresas en `excluded_notifications`, respeta `notification_frequency` (días entre envíos), y despacha `MoodNotification` para cada empleado sin mood de hoy que tenga tokens OneSignal.

- **`check:low_mood_employees`** (`CheckLowMoodEmployees`): Verificación diaria que revisa los moods del día anterior con type `bad` o `very_bad`. Agrupa por empresa, y para cada empresa envía **SMS** (vía `NotificationSms` en cola `high_priority_notifications`) a los usuarios del panel con permiso `review_moods` que tengan nombre y teléfono. El SMS incluye listado de empleados con estados bajos (nombre, número, ubicación, fecha).

---

## NOTIFICACIONES

### Push (OneSignal)

| Evento | Título | Mensaje | Cola | Destinatario |
|--------|--------|---------|------|--------------|
| Recordatorio diario | "Reflexiona sobre tu estado de ánimo" | "¡Hola {nombre}! ¿Cómo te sientes hoy? Tómate un segundo para registrar tu estado de ánimo. Tu bienestar es nuestra prioridad." | moods_push_notifications | Cada colaborador que no ha registrado mood hoy |

### SMS

| Evento | Razón | Cola | Destinatarios |
|--------|-------|------|---------------|
| Estados bajos del día anterior | "Estados de animo" | high_priority_notifications | Usuarios del panel con permiso `review_moods`, nombre y teléfono no vacíos |

---

## VISTAS BLADE

### Características (admin.moods.characteristics)

- **list.blade.php:** Listado paginado con tabla AJAX. Columnas: N°, Nombre, Lista inicial (traducida: Mal, Muy mal, Normal, Bien, Muy bien, SIN ASIGNAR), Acciones (editar, ver, eliminar).
- **table.blade.php:** Tabla parcial renderizada por AJAX (getTable). Soporta búsqueda y ordenamiento.
- **create.blade.php:** Formulario: nombre (required), select lista inicial (normal, bad, very_bad, well, very_well con traducciones).
- **edit.blade.php:** Igual que create con valores precargados.
- **view.blade.php:** Detalle de solo lectura.

### Afecciones (admin.moods.disorders)

- **list.blade.php:** Listado paginado con tabla AJAX. Columnas: N°, Nombre, Acciones (editar, ver, eliminar).
- **table.blade.php:** Tabla parcial AJAX con búsqueda y ordenamiento.
- **create.blade.php:** Formulario: nombre (required).
- **edit.blade.php:** Igual que create con valor precargado.
- **view.blade.php:** Detalle de solo lectura.

---

## MIGRACIONES

| Migración | Acción |
|-----------|--------|
| `2024_05_03_121930_create_mood_characteristics_table` | Crea `mood_characteristics`: id, name, initial_list (enum nullable), timestamps |
| `2024_05_03_123006_create_mood_disorders_table` | Crea `mood_disorders`: id, name, initial_list (enum nullable), timestamps |
| `2024_05_03_124302_create_moods_table` | Crea `moods`: id, type (enum), score, high_employee_id (FK), mood_characteristic_id (FK), mood_disorder_id (FK), timestamps |
| `2024_05_28_092641_create_mood_characteristic_mood_table` | Crea pivot `mood_characteristic_mood`: mood_characteristic_id (FK cascade), mood_id (FK cascade) |
| `2024_05_28_125414_update_moods_table` | Elimina FK y columna `mood_characteristic_id` de moods (relación 1:1 reemplazada por N:M vía pivot) |
| `2024_05_29_102617_create_mood_disorder_mood_table` | Crea pivot `mood_disorder_mood`: mood_disorder_id (FK cascade), mood_id (FK cascade) |
| `2024_05_29_102906_update_mood_disorders_table` | Elimina columna `initial_list` de mood_disorders |
| `2024_05_29_102922_update_moods_2_table` | Elimina FK y columna `mood_disorder_id` de moods (relación 1:1 reemplazada por N:M vía pivot) |

**Evolución del esquema:** El diseño original (mayo 2024) usaba relaciones 1:1 (un mood → una característica y una afección) con FKs directas en la tabla `moods`. En menos de un mes se migró a N:M con tablas pivot, eliminando las FKs directas. Las afecciones perdieron su `initial_list` (ya no se agrupan por tipo de día como las características).

---

## CASOS BORDE

- **Producto no activo:** Si la empresa no tiene el producto "Estados de ánimo" o el empleado no lo tiene activo, los recordatorios no se envían. Sin embargo, la API de crear mood no valida esto; un colaborador con acceso a la app podría registrar un mood aunque el producto no esté activo para él.
- **Sin tokens OneSignal:** Si el colaborador no tiene tokens de OneSignal registrados, no recibe el push de recordatorio (pero sí puede registrar su mood).
- **notification_frequency nula:** Si la empresa no tiene registro en `notifications_frequencies`, los recordatorios se envían todos los días (el comando continúa sin restricción de frecuencia).
- **Estadísticas con días vacíos:** Los periodos que no tienen registros devuelven `null` en el array de values, permitiendo a la app dibujar huecos en la gráfica.
- **Múltiples características/afecciones:** La API valida que ambos arrays sean requeridos, pero no valida que los IDs existan en la base de datos. Si se envían IDs inexistentes, el attach podría fallar o crear registros pivot con FKs inválidas.
- **Eliminación física:** Tanto Características como Afecciones usan hard delete (no SoftDeletes). Si se elimina una que no tiene moods, se pierde permanentemente.

---

## ⚠️ AMBIGÜEDADES

- **initial_list solo para características:** Tras la migración, las afecciones ya no tienen `initial_list`, pero las características sí. No queda claro si el diseño pretende que las afecciones sean globales (se muestran siempre) o si faltó implementar la agrupación para afecciones.
- **Accessors `mood_characteristic` y `mood_disorder` devuelven solo el primero:** A pesar de que la relación es N:M, los accessors del modelo Mood solo devuelven `->first()`. Esto podría ser intencional para la API de `getMood` (mostrar resumen) o un remanente del diseño 1:1.
- **Delay de MoodNotification:** El delay se calcula como la diferencia en minutos entre la hora actual y un horario aleatorio entre 10:00 y 20:59. Si el comando se ejecuta después de las 20:00, el cálculo de `abs(timestamp2 - timestamp1)` produce un delay a una hora pasada del mismo día, lo que podría resultar en un envío inmediato o incorrecto.
- **Validación `mood_disorders: required|array`:** Obliga a enviar al menos un array de afecciones, pero no valida `min:1` ni `exists:mood_disorders,id`. Un array vacío `[]` pasaría la validación de tipo array pero no crearía ningún attach.

---

## 🔧 DEUDA TÉCNICA

- **Eliminación física sin SoftDeletes:** Los catálogos de Características y Afecciones usan hard delete. Si se requiere auditoría o recuperación, no hay forma de restaurar registros eliminados.
- **No hay validación de existencia de IDs en la API:** Al crear un mood, los IDs de `mood_characteristics` y `mood_disorders` no se validan con `exists:` en la BD; se confía en que la app envíe IDs válidos.
- **No hay validación del producto en la API de crear:** La API `moods/create` no verifica que el empleado o su empresa tengan el producto "Estados de ánimo" activo. Cualquier colaborador autenticado puede registrar un mood.
- **Lógica de delay aleatorio en MoodNotification:** El cálculo del delay usa `new \DateTime('now')` y `new \DateTime($random)` donde `$random` es un horario como "14:35". Si el servidor está en UTC y la lógica espera hora local, el delay podría ser incorrecto.
- **Consultas SQL con DAYNAME/MONTHNAME dependientes del locale de MySQL:** Las estadísticas agrupan por `DAYNAME(created_at)` y `MONTHNAME(created_at)` que dependen de la configuración de idioma de MySQL. Si el servidor no está en inglés, los nombres de días/meses podrían no coincidir con los arrays hardcodeados (`Monday`, `January`, etc.).
- **Búsqueda traducida en Características:** El método `getTable` traduce el texto buscado de español a los valores del enum (ej: "Mal" → "bad") para buscar en `initial_list`. Esto cubre solo coincidencias exactas de la primera letra mayúscula.
- **Permiso `review_moods` no aparece en las rutas web:** Solo se usa en el comando `CheckLowMoodEmployees` para filtrar destinatarios del SMS. No tiene ruta admin asociada.
- **Controladores de catálogos no filtran por empresa:** Las Características y Afecciones son **globales** — no están segmentadas por empresa. Cualquier admin con permisos ve y modifica las mismas.

---

## 📌 DIFERENCIAS CON TECBEN-CORE

### Ya implementado en tecben-core:

- **Modelo `EstadoAnimoCaracteristica`:** Equivalente a `MoodCharacteristic`. Recurso Filament `EstadoAnimoCaracteristicaResource` con CRUD completo (list, create, edit, view).
- **Modelo `EstadoAnimoAfeccion`:** Equivalente a `MoodDisorder`. Recurso Filament `EstadoAnimoAfeccionResource` con CRUD completo.
- **Migraciones:** `create_estado_animo_caracteristicas_table`, `create_estado_animo_afecciones_table`.
- **Policies:** `EstadoAnimoCaracteristicaPolicy`, `EstadoAnimoAfeccionPolicy`.
- **Seeders:** `EstadoAnimoCaracteristicaSeeder`, `EstadoAnimoAfeccionSeeder`.
- **Permisos en Shield:** Configurados en `filament-shield.php` y `ShieldPermisosLegacySeeder`.
- **Fichas existentes:** `ficha-modulo-estado-animo-caracteristica.md`, `ficha-modulo-estado-animo-afeccion.md`.

### Pendiente de implementar en tecben-core:

- **Modelo y tabla `moods`:** Registro central de estados de ánimo con type, score, high_employee_id.
- **Tablas pivot:** `mood_characteristic_mood`, `mood_disorder_mood` para relaciones N:M.
- **API endpoints para app móvil:** getMood, getCharacteristics, getOtherCharacteristics, getDisorders, getStatistics, create.
- **Job `MoodNotification`:** Recordatorio push con delay aleatorio.
- **Comando `send:mood_reminder`:** Recordatorio diario con lógica de frecuencia por empresa y producto.
- **Comando `check:low_mood_employees`:** Alerta SMS a responsables por estados bajos.
- **Integración con tabla `notifications_frequencies`:** Frecuencia de envío por empresa.
- **Integración con tabla `excluded_notifications`:** Exclusión de empresas de recordatorios.
- **Integración con tabla `products`:** Validación de producto "Estados de ánimo" activo.
- **Permiso `review_moods`:** Para recibir alertas SMS por estados bajos.
- **Estadísticas personales:** Gráficas weekly/monthly/biannual/annual con agrupación por periodo.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
