# Ficha técnica: Módulo Estatus de Voz del Colaborador (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, flujos, jobs y casos borde. Solo describe lo que existe en el código.

---

## MÓDULO: Estatus de Voz del Colaborador

**FECHA ANÁLISIS:** 2026-04-08
**ANALIZADO POR:** Agente paco-legacy
**ESTADO EN TECBEN-CORE:** No implementado (solo existen fichas de "Temas Voz del Colaborador" y "Segmentación Voz del Colaborador"; el módulo central de gestión de comentarios/estatus no está implementado)

Módulo central de comunicación empleado → empresa. Permite a los **colaboradores** enviar comentarios (anónimos o identificados) desde la app móvil, categorizados por tema. Los **usuarios del panel admin** visualizan los comentarios, gestionan su estatus (Pendiente → En Proceso → Atendido / Continuar conversación), asignan prioridad, reasignan temas, asignan responsables, y responden. Existe un sistema de **conversación** a través de mensajes extras (VoiceEmployeeExtra). Los comentarios se categorizan automáticamente con **GPT-4o** (sentimiento, urgencia, categoría, respuesta sugerida). Se integra con **Tableau** para analítica y con **OneSignal** para notificaciones push al colaborador.

---

## ENTIDADES

### Tabla: `voice_employees`

- **PK:** id (bigint unsigned auto-increment).
- **Campos:**
  - `date` (timestamp nullable): fecha de creación del comentario.
  - `status` (string): estado del comentario. Valores posibles: `Pendiente`, `En Proceso`, `Atendido`, `Continuar conversación`.
  - `priority` (string, default `Sin Asignar`): prioridad. Valores posibles: `Sin Asignar`, `Baja`, `Media`, `Alta`.
  - `comments` (longText): contenido del comentario del colaborador.
  - `results` (longText): respuesta del admin al comentario.
  - `is_anonyme` (string, originalmente integer cambiado a string en migración 2): indicador de anonimato. Valor `ANONIMO` para comentarios anónimos.
  - `other_subject` (string): tema alternativo cuando el colaborador elige "Otro" o la IA categoriza fuera de los temas existentes.
  - `images` (integer, nullable): cantidad de imágenes adjuntas al comentario principal.
  - `attention_date` (timestamp nullable): fecha en que se atendió el comentario.
  - `voice_employee_subject_id` (FK → voice_employee_subjects): tema asignado.
  - `high_employee_id` (FK → high_employees): colaborador que envió el comentario.
  - `user_id` (FK → users): usuario admin que leyó el comentario.
  - `attenuator_id` (FK → users): usuario admin que atendió el comentario.
  - `assigned_id` (FK → users, on delete set null): usuario admin asignado como responsable. Originalmente `assigned_employee` FK a high_employees, renombrado a `assigned_id` FK a users en migración 7.
  - `deleted_at` (SoftDeletes).
- **Relaciones (modelo VoiceEmployee):**
  - `voice_employee_subject()` → belongsTo VoiceEmployeeSubject
  - `sender()` → belongsTo HighEmployee (high_employee_id), withTrashed
  - `reader()` → belongsTo User (user_id)
  - `attenuator()` → belongsTo User (attenuator_id)
  - `assigned_user()` → belongsTo User (assigned_id)
  - `notifications()` → hasMany Notification
  - `voice_employee_extras()` → hasMany VoiceEmployeeExtra (orderBy id desc)
  - `voice_employee_extras_asc()` → hasMany VoiceEmployeeExtra (orderBy id asc)
  - `voice_employees_tableu()` → hasMany VoiceEmployeeTableu
  - `employee_voice_categorization()` → hasOne EmployeeVoiceCategorization

### Tabla: `voice_employee_extras`

- **PK:** id (bigint unsigned).
- **Campos:**
  - `comments` (longText): mensaje adicional del colaborador (reiteración/reapertura).
  - `results` (longText): respuesta del admin a este mensaje extra.
  - `voice_employee_id` (FK → voice_employees, nullable, cascade): comentario padre.
  - `attenuator_id` (bigint unsigned, nullable): usuario admin que respondió.
  - `attention_date` (timestamp nullable): fecha de atención de este extra.
- **Relaciones (modelo VoiceEmployeeExtra):**
  - `voice_employee()` → belongsTo VoiceEmployee
  - `attenuator()` → belongsTo User (attenuator_id)
  - `voice_employees_tableu()` → hasMany VoiceEmployeeTableu
- **Atributo calculado:** `date` (appends): formatea `created_at` con Carbon locale `es_ES` en formato relativo (Hoy/Ayer/dd MMM yy - h:mm A).

### Tabla: `voice_employee_reiterates`

- **PK:** id (bigint unsigned).
- **Campos:** comments (longText), results (longText), voice_employee_id (FK nullable), user_id (FK nullable → users), attenuator_id (FK nullable → users), attention_date (timestamp), deleted_at (SoftDeletes).
- **Nota:** El modelo `VoiceEmployeeReiterate` apunta a la tabla `voice_employee_extras` (no a `voice_employee_reiterates`). ⚠️ AMBIGUO: La tabla `voice_employee_reiterates` se creó por migración pero el modelo no la usa; parece que la funcionalidad de reiteraciones se consolidó en `voice_employee_extras`.

### Tabla: `voice_employees_tableu`

- **PK:** id (bigint unsigned).
- **Campos:**
  - `comments` (longText): copia de comments sin HTML (strip_tags + html_entity_decode).
  - `results` (longText nullable): copia de results sin HTML.
  - `voice_employee_id` (FK nullable → voice_employees, cascade).
  - `voice_employee_extra_id` (FK nullable → voice_employee_extras, cascade).
- **Uso:** Réplica de datos sin formato HTML para consumo de Tableau. Se crea al crear un comentario o un extra, y se actualiza al atender (updateStatus).

### Tabla: `voice_employees_categorization`

- **PK:** id (bigint unsigned).
- **Campos:**
  - `sentiment` (text): sentimiento detectado (positivo, mixto, neutral, negativo).
  - `category` (text): categoría sugerida por IA.
  - `urgency_level` (bigint): nivel de urgencia 1-10.
  - `keywords` (text): palabras clave extraídas.
  - `subcategory` (text): subcategoría sugerida.
  - `suggested_response` (text): respuesta sugerida por IA.
  - `voice_employee_id` (FK nullable → voice_employees, cascade).
- **Historial:** Originalmente tenía campo `summary` que fue reemplazado por `suggested_response`.

### Tablas auxiliares (documentadas en fichas separadas):

- `voice_employee_subjects`: temas/categorías (ver ficha-modulo-temas-voz-colaboradores.md).
- `user_voice_employee_subject`: segmentación usuario-tema (ver ficha-modulo-segmentacion-voz-colaborador.md).
- `company_voice_employee_subject`: segmentación empresa-tema.

---

## REGLAS DE NEGOCIO

### Gestión de Estatus

- **RN-01:** Al crearse un comentario desde la app, su estatus inicial es siempre `Pendiente`.
- **RN-02:** Cuando un admin abre (visualiza) un comentario `Pendiente` por primera vez, se cambia automáticamente a `En Proceso`. No se modifica si ya está en otro estado.
- **RN-03:** El admin puede cambiar el estatus a: `Atendido`, `En Proceso` o `Continuar conversación`.
- **RN-04:** Al cambiar a `Atendido` o `Continuar conversación`, se envía notificación push al colaborador.
- **RN-05:** El estatus `Continuar conversación` permite que el colaborador reabra el caso enviando un nuevo mensaje (VoiceEmployeeExtra), lo cual vuelve el estatus a `Pendiente`.

### Prioridad

- **RN-06:** La prioridad por defecto es `Sin Asignar`.
- **RN-07:** La IA (ChatGPT) asigna prioridad automáticamente al crear: urgency_level 1-3 → `Baja`, 4-7 → `Media`, 8-10 → `Alta`.
- **RN-08:** El admin puede modificar la prioridad manualmente al actualizar el estatus.

### Anonimato

- **RN-09:** El colaborador elige si su comentario es anónimo (`ANONIMO`) o identificado al crearlo. En el panel admin, si es anónimo se muestra "Anónimo" en lugar del nombre del colaborador (lógica en la vista y en SQL con `IF(is_anonyme = 'ANONIMO', 'Anónimo', concat(...))`).

### Categorización IA

- **RN-10:** Al crear un comentario nuevo (no reapertura), se despacha `ChatGPTVerification` en la cola `voice_employee_verification` — excepto si la empresa del colaborador está en `config('app.chat_gpt_excluded_companies')`.
- **RN-11:** La IA (GPT-4o, temperature 0.2) analiza el comentario contra los temas de la empresa (excluye temas tipo "otro") y retorna: sentimiento, categoría, urgency_level, keywords, subcategoría, suggested_response.
- **RN-12:** Si la categoría sugerida por la IA coincide con un tema existente de la empresa, se reasigna `voice_employee_subject_id`. Si no coincide, se asigna el tema "otro" y se guarda la categoría IA en `other_subject`.

### Asignación de Responsable

- **RN-13:** El admin puede asignar un usuario del panel (type `high_user`) como responsable (`assigned_id`).
- **RN-14:** Al asignar un responsable, se envía notificación por email al usuario asignado con el tema del comentario.

### Imágenes

- **RN-15:** El colaborador puede adjuntar imágenes al comentario. Se guardan en `public/assets/voice_employees/` con nomenclatura:
  - Comentario principal: `{voice_employee_id}-{index}.png`
  - Extra (reapertura): `{voice_employee_id}-{extra_id}-{index}.png`
- **RN-16:** Las imágenes se aceptan tanto como base64 (web) como UploadedFile (mobile).

### Conversación (Extras)

- **RN-17:** Cuando el admin responde por primera vez un comentario, se guardan `results` y `attention_date` directamente en `voice_employees`.
- **RN-18:** Para respuestas subsiguientes (el comentario ya tiene attention_date y attenuator_id), se crea o actualiza un `VoiceEmployeeExtra`:
  - Si el último extra tiene `results` vacío, se actualiza ese extra con la respuesta.
  - Si el último extra ya tiene results, se crea un nuevo VoiceEmployeeExtra con la respuesta.
- **RN-19:** Cuando el colaborador reenvía un mensaje (reapertura con `voice_employee_id`), se busca el último extra con `comments` vacío (creado por la respuesta del admin); si existe y el anterior no tiene comments vacío, se actualiza ese extra. Si no, se crea un nuevo VoiceEmployeeExtra. El estatus del comentario padre vuelve a `Pendiente`.

### Filtros y Segmentación

- **RN-20:** En el listado del admin, los comentarios se filtran según:
  1. Empresa del usuario logueado (`company_id`), o todos si no tiene empresa.
  2. Filtros de empleado del usuario (`high_employee_filters` → filtra por ubicación/área del sender).
  3. Temas asignados a la empresa del usuario (`company_voice_employee_subject`).
  4. Temas asignados al usuario (`user_voice_employee_subject`).
- **RN-21:** Por defecto solo se muestran comentarios del año actual (desde el 1 de enero hasta hoy).
- **RN-22:** Filtros disponibles: empresa, ubicación, estatus, prioridad, categoría (tema), rango de fechas, búsqueda textual.
- **RN-23:** La búsqueda textual abarca: id, prioridad, estatus, other_subject, nombre del sender (o "Anónimo"), tema, empresa, ubicación, usuario asignado, fecha.

---

## FLUJO PRINCIPAL: Envío de comentario desde la app

1. Colaborador abre la sección "Voz del Colaborador" en la app.
2. Selecciona un tema de los disponibles para su empresa (`getSubjects` → company.voice_employee_subjects).
3. Escribe su comentario, elige si es anónimo, opcionalmente adjunta imágenes.
4. API `POST voice_employees/create`: valida comments (required), voice_employee_subject_id (required).
5. Se crea `VoiceEmployee` con status=`Pendiente`, date=now, is_anonyme, other_subject.
6. Se crea `VoiceEmployeeTableu` con comments sin HTML.
7. Se asocia al high_employee del usuario autenticado (sender).
8. Se asocia al voice_employee_subject seleccionado.
9. Se guardan imágenes (si las hay) en `assets/voice_employees/`.
10. Se despacha `ChatGPTVerification` (si la empresa no está excluida).
11. Se envía email a todos los usuarios del panel con `notification_voice_employees = 'SI'` de la empresa del colaborador, respetando segmentación por filtros y temas del usuario.

## FLUJO PRINCIPAL: Gestión en panel admin

1. Admin accede a "Voz del colaborador" (sidebar).
2. Ve listado con filtros, paginado (10 por página), ordenado por fecha desc.
3. Selecciona un comentario → `specificVoiceEmployee`:
   - Si status=`Pendiente`: cambia automáticamente a `En Proceso`, se registra el usuario que leyó (`user.read_comments()`), se crea Notification "VOZ DEL EMPLEADO LEIDO" y se envía push al colaborador.
   - Se renderiza la vista de detalle (in_process o attended) con formulario para responder.
4. Admin completa respuesta (results), selecciona prioridad, opcionalmente reasigna tema, asigna responsable, y elige estatus (Atendido / En Proceso / Continuar conversación).
5. `POST updateStatus`: actualiza voice_employee (status, priority, assigned_id, voice_employee_subject_id), guarda results (primera vez directo, subsiguientes vía extras), actualiza voice_employees_tableu.
6. Si estatus=`Atendido` o `Continuar conversación`: crea Notification "VOZ DEL EMPLEADO ATENDIDO" y envía push.
7. Si se asignó responsable: envía email "Asignación de voz del colaborador".
8. Se crea Log de la acción.

## FLUJO SECUNDARIO: Reapertura por colaborador

1. El estatus debe estar en `Continuar conversación` o `Atendido` (la app permite reabrir).
2. API `POST voice_employees/create` con `voice_employee_id` (no vacío): se crea/actualiza VoiceEmployeeExtra con el nuevo comments del colaborador.
3. Se crea VoiceEmployeeTableu asociado al extra.
4. El estatus del voice_employee padre se revierte a `Pendiente`.
5. El admin ve el nuevo mensaje en la conversación y puede responder nuevamente.

## FLUJO SECUNDARIO: Consulta de comentario enviado

1. Colaborador abre la lista de comentarios enviados → API `GET voice_employees` (paginado) o `POST voice_employees/get_sent_list` (todos).
2. Selecciona uno → API `GET voice_employees/get_sent/{id}`: devuelve detalle completo con extras, imágenes, status con nombre del lector si "En Proceso".

---

## VALIDACIONES

### API - Crear comentario

- `comments`: required ("El mensaje es requerido").
- `voice_employee_subject_id`: required ("El tema es requerido").

### Admin - Actualizar estatus

- Sin validación formal del lado servidor. El estatus se toma directamente del request (`$request->status`). Si status no es "Atendido" ni "En Proceso", se asigna "Continuar conversación".

---

## PERMISOS

| Acción | Permiso requerido | Middleware |
|--------|------------------|------------|
| Ver listado de comentarios | `view_voice_employees` | logged, 2fa, Permissions (permissions_and) |
| Ver detalle de comentario | `view_voice_employees` | logged, 2fa, Permissions |
| Actualizar estatus | `edit_voice_employees` | logged, 2fa, Permissions |
| Eliminar comentario | `trash_voice_employees` | logged, 2fa, Permissions |
| Eliminar extra | `trash_voice_employees` | logged, 2fa, Permissions |
| Ver filtros | `view_voice_employees` | logged, 2fa, Permissions |
| Ver detalle de empleado | `view_voice_employees` | logged, 2fa, Permissions |
| Tableau Voz del Colaborador | `view_voice_employees` | logged, 2fa, Permissions |

En el sidebar, el ítem "Voz del colaborador" se muestra si el usuario tiene al menos uno de: `edit_voice_employees`, `view_voice_employees`, `trash_voice_employees`, `create_voice_employees`.

---

## SERVICIOS/ENDPOINTS INVOLUCRADOS

### Admin (web.php)

| Ruta | Método HTTP | Controlador@método | Descripción |
|------|------------|-------------------|-------------|
| `admin/voice_employees` | GET | VoiceEmployeesController@getIndex | Listado con filtros |
| `admin/voice_employees/specific_voice_employee/{id}` | GET | specificVoiceEmployee | Ver detalle (cambia estatus a En Proceso si Pendiente) |
| `admin/voice_employees/update_status` | POST | updateStatus | Actualizar estatus, respuesta, prioridad, asignación |
| `admin/voice_employees/trash/{id}` | GET | Trash | Eliminar comentario (soft delete) |
| `admin/voice_employees/extra_trash/{id}` | GET | extraTrash | Eliminar extra |
| `admin/voice_employees/filters` | POST | getFilters | Filtros AJAX con paginación |
| `admin/voice_employees/view_employee/{id}` | GET | getViewEmployee | Ver detalle del colaborador que envió |
| `admin/tableau/voice_employees` | GET | TableauServerController@voiceEmployees | Página Tableau |

### API (api.php)

| Ruta | Método HTTP | Controlador@método | Descripción |
|------|------------|-------------------|-------------|
| `api/voice_employees` | GET | VoiceEmployeesController@index | Listar comentarios enviados (paginado) |
| `api/voice_employees/create` | POST | create | Crear comentario o reabrir caso |
| `api/voice_employees/get_sent_list` | POST | getSentList | Listar comentarios enviados (sin paginar) |
| `api/voice_employees/get_sent/{id}` | GET | getSent | Ver detalle de un comentario enviado |
| `api/voice_employees/get_subjects` | GET | getSubjects | Obtener temas disponibles para la empresa |

---

## JOBS/COLAS

- **ChatGPTVerification:** Cola `voice_employee_verification`. Se despacha al crear un comentario nuevo (no reapertura). Llama a la API de OpenAI (GPT-4o, temperature 0.2) para analizar sentimiento, categoría, urgencia, keywords, subcategoría y respuesta sugerida. Crea `EmployeeVoiceCategorization`, asigna prioridad automática, y reasigna tema si la IA sugiere uno existente. Si la empresa está en `config('app.chat_gpt_excluded_companies')`, no se despacha. `$tries = 1`.
- **NotificationPush:** Cola `medium_priority_notifications`. Se despacha al cambiar estatus a "En Proceso" (leído) y al cambiar a "Atendido"/"Continuar conversación". Envía push vía OneSignal.
- **NotificationEmail:** Cola `low_priority_notifications`. Se despacha al crear un comentario nuevo (a usuarios del panel con `notification_voice_employees = 'SI'`), y al asignar un responsable.

---

## NOTIFICACIONES

### Push (OneSignal)

| Evento | Tipo | Título | Mensaje | Destinatario |
|--------|------|--------|---------|--------------|
| Admin lee comentario pendiente | VOZ DEL EMPLEADO LEIDO | "Voz del colaborador leído" (o "colaborador(a)" si company_id=54) | "Tu comentario ya fue recibido y está en proceso de atención." | Colaborador que envió |
| Admin atiende/continúa conversación | VOZ DEL EMPLEADO ATENDIDO | "Voz del colaborador atendido" (o "colaborador(a)" si company_id=54) | "Tu comentario ha sido atendido. Entra a {name_app} para ver la respuesta" | Colaborador que envió |

### Email

| Evento | Razón | Template | Destinatarios |
|--------|-------|----------|---------------|
| Nuevo comentario creado | "Nuevo Comentario en Voz del Empleado" | Template con name, email, theme, logo_company | Usuarios del panel con `notification_voice_employees = 'SI'` de la empresa del colaborador, filtrados por segmentación |
| Asignación de responsable | "Asignación de voz del colaborador" | Template con email, name, user, category, logo_company | El usuario del panel asignado |

---

## COMANDOS ARTISAN

- **`format:voice_employee_response`** (`FillVoiceEmployeeResponsesTableu`): Comando de migración de datos. Busca voice_employees sin registros en voice_employees_tableu y crea la réplica sin HTML. Útil para backfill de datos históricos anteriores a la creación de la tabla tableu.

---

## INTEGRACIÓN CON TABLEAU

- Ruta `admin/tableau/voice_employees` → `TableauServerController@voiceEmployees`.
- La tabla `voice_employees_tableu` almacena copias de comments y results sin formato HTML (strip_tags + html_entity_decode) para que Tableau pueda consumir texto limpio.
- Se mantiene sincronizada: se crea al crear un comentario o extra, y se actualiza el campo results al atender (updateStatus).

---

## ALMACENAMIENTO DE ARCHIVOS

- Las imágenes adjuntas se guardan en `public/assets/voice_employees/` con el disco `uploads`.
- Nomenclatura:
  - Comentario principal: `{voice_employee_id}-{index}.png` (index: 0, 1, 2...)
  - Extra/reapertura: `{voice_employee_id}-{extra_id}-{index}.png`
- Al eliminar un comentario (Trash), se intenta borrar `{id}.png` del disco uploads. No se eliminan las imágenes indexadas (`{id}-{index}.png`).
- Al eliminar un extra (extraTrash), se intenta borrar `{voice_employee_id}-{extra_id}.png`.

---

## CASOS BORDE

- **Company_id == 54:** Tiene un tratamiento especial de lenguaje inclusivo en los títulos de notificación push ("Voz del colaborador(a)"). Hardcodeado en el controlador.
- **Comentario sin tema ni other_subject:** Si `other_subject` está vacío y `voice_employee_subject` no existe, el comentario se omite de los listados de la API (condición `if(!empty($voice_employee->other_subject) || isset($voice_employee->voice_employee_subject))`).
- **IA con respuesta inválida:** Si ChatGPT no devuelve JSON válido, se usa un response default (sentiment neutral, category Otro, urgency_level 1, campos vacíos) y se loguea el error.
- **Empresa excluida de IA:** Las empresas en `config('app.chat_gpt_excluded_companies')` no pasan por ChatGPTVerification; la prioridad queda como `Sin Asignar` y no se genera categorización.
- **Eliminación de imágenes incompleta:** Al hacer Trash de un voice_employee, solo se intenta borrar `{id}.png` (formato antiguo). Las imágenes nuevas con formato `{id}-{index}.png` no se eliminan.
- **Extra sin comments del admin:** Cuando el admin responde y el colaborador no ha reenviado, el extra puede tener comments vacío y solo results (respuesta admin).
- **Doble respuesta del admin:** Si el admin responde dos o más veces sin que el colaborador reenvíe, se crean extras consecutivos con solo results (cada uno como nuevo VoiceEmployeeExtra).

---

## ⚠️ AMBIGÜEDADES

- **Tabla `voice_employee_reiterates` vs modelo `VoiceEmployeeReiterate`:** La migración crea la tabla `voice_employee_reiterates`, pero el modelo `VoiceEmployeeReiterate` declara `$table = 'voice_employee_extras'`. La tabla `voice_employee_reiterates` existe pero no se usa activamente. Parece una funcionalidad planificada que se consolidó en extras.
- **Campo `images` sin migración explícita:** El modelo VoiceEmployee incluye `images` en fillable y se usa en la API, pero no se encontró una migración explícita que agregue este campo. Posiblemente fue agregado directamente en base de datos o en una migración con nombre genérico.
- **`assigned_id` apunta a users o high_employees:** Originalmente `assigned_employee` FK a high_employees. La migración 7 lo renombró a `assigned_id` FK a users. El controlador admin lista `User::where('type', 'high_user')` para la asignación, confirmando que ahora apunta a users.
- **`notification_voice_employees`:** Campo en la tabla `users` (string, valores 'SI'/'NO'). Filtra qué usuarios del panel reciben email al crear un nuevo comentario. No hay validación explícita del valor.

---

## 🔧 DEUDA TÉCNICA

- **HTML en responses:** Los campos `comments` y `results` almacenan HTML crudo (RichText/CKEditor). Se necesita una tabla espejo (`voice_employees_tableu`) con strip_tags para Tableau. Idealmente se debería normalizar el almacenamiento.
- **Hardcoded company_id 54:** El tratamiento de lenguaje inclusivo está hardcodeado (`$voice_employee->sender->company->id == 54`). Debería ser una configuración por empresa.
- **Lógica duplicada de notificación email:** El bloque que envía emails a usuarios del panel al crear un comentario tiene la misma estructura repetida 4 veces (con/sin filtros, con/sin temas segmentados). Debería extraerse a un método o trait.
- **Lógica compleja de extras:** La lógica para determinar si crear o actualizar un extra en `create` (API) y `updateStatus` (admin) es compleja y difícil de seguir. Hay varios caminos (primer extra, último extra vacío, último extra con results, sin extras) que deberían simplificarse.
- **Eliminar imágenes incompleto:** La eliminación solo busca `{id}.png`, no itera por las imágenes con índice `{id}-{index}.png`.
- **Sin paginación en getSentList (API):** El endpoint `get_sent_list` retorna TODOS los comentarios enviados sin paginar. Puede ser problemático para colaboradores con muchos comentarios.
- **VoiceEmployeeReiterate apunta a tabla incorrecta:** El modelo apunta a `voice_employee_extras` en lugar de `voice_employee_reiterates`.
- **Import de `Mockery\Undefined` en controlador admin:** El controlador importa `use Mockery\Undefined;` que es una dependencia de testing y no debería estar en producción.
- **Controlador admin usa View::make con cleanView:** Renderiza vistas parciales y limpia saltos de línea/tabs (`str_replace(["\n", "\t"], "", $view)`) para retornar HTML limpio en JSON. Patrón frágil.
- **Validación de estatus inexistente en admin:** `updateStatus` no valida que el status recibido sea un valor válido; cualquier valor que no sea "Atendido" ni "En Proceso" se asigna como "Continuar conversación".
- **Mezcla de formatos de imagen:** Soporta base64 y UploadedFile con detección por tipo, pero siempre guarda como .png sin importar el formato original.

---

## 📌 DIFERENCIAS CON TECBEN-CORE

- **No implementado:** El módulo central de gestión de estatus/comentarios de "Voz del Colaborador" no está implementado en tecben-core.
- **Ya existente en tecben-core:**
  - Modelo `TemaVozColaborador` (equivalente a `VoiceEmployeeSubject`).
  - Recurso Filament `TemasVozColaboradoresResource` (CRUD de temas).
  - Recurso Filament `SegmentacionVozColaboradorResource` (segmentación por usuario/empresa).
  - Página Tableau `VozColaboradorTableauPage`.
  - Permisos en seeders: `view_voice_employees`, `edit_voice_employees`, `trash_voice_employees`, etc.
  - Migraciones: `create_tema_voz_colaboradores_table`, `create_usuarios_temas_voz_colaboradores_table`.
- **Pendiente de implementar en tecben-core:**
  - Modelo y tabla equivalente a `voice_employees` (comentarios de voz).
  - Modelo y tabla equivalente a `voice_employee_extras` (conversación/reapertura).
  - Modelo y tabla equivalente a `voice_employees_categorization` (categorización IA).
  - Modelo y tabla equivalente a `voice_employees_tableu` (réplica Tableau).
  - Recurso Filament para gestión de estatus de comentarios.
  - API endpoints para la app móvil.
  - Job de categorización con IA.
  - Notificaciones push y email.

---

## VISTAS BLADE (Admin)

- **admin.voice_employees.new_view:** Vista principal del listado. Filtros: empresa, ubicación, status, prioridad, categoría, rango de fechas, búsqueda. Paginación AJAX. Panel lateral que muestra detalle del comentario seleccionado con formulario de respuesta.
- **admin.voice_employees.table:** Tabla parcial renderizada por AJAX (getFilters). Muestra: folio, nombre/anónimo, empresa, ubicación, tema, comentario (truncado), prioridad, status, fecha, usuario asignado.
- **admin.voice_employees.content.in_process:** Detalle en proceso: formulario con results (textarea/editor), select prioridad, select tema, select usuario asignado, botones Atender/En Proceso/Continuar.
- **admin.voice_employees.content.attended:** Detalle atendido: solo lectura con historial de conversación.
- **admin.voice_employees.items.pending / in_process / attended:** Ítems individuales para actualizar la lista sin recargar.
- **admin.voice_employees.view_employee:** Detalle del empleado que envió el comentario (datos personales, productos, etc.).
- **admin.analytics.tableau_voice_employees:** Embed de reporte Tableau.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
