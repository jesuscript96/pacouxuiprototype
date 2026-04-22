MÓDULO: Cartas SUA (carga y visualización — legacy Paco)
FECHA ANÁLISIS: 2026-04-07
ANALIZADO POR: Agente paco-legacy
ESTADO EN TECBEN-CORE: No implementado como recurso de negocio (existe entrada de permiso/menú “Cartas SUA” en `database/seeders/Inicial.php`; no se localizó modelo `SuaLetter` ni flujo equivalente en la app Filament analizada en esta ficha)

ENTIDADES:

- `sua_letters`: `id`, `high_employee_id` (FK nullable en migración inicial), `date`, `withdrawel`, `cv`, `infonavit`, `total`, `bimester`, `business_name`, `first_view_date`, `last_view_date`, `signed` (bool, default false), `signed_image`, `signature_date`, `nom151` (longText tras migración), `hash_nom151`, `validation_code`, `pdf_string` (longText, PDF en base64), `timestamps` | `belongsTo` `high_employees` (withTrashed); `hasMany` `notifications`.
- `high_employees`: identificación del colaborador por `company_id` + `employee_number` en carga admin; datos mostrados en PDF (RFC, CURP, nombre) vienen del Excel, no necesariamente sincronizados con el registro del colaborador.
- `companies`: filtro admin por empresa del colaborador; nombre de cola de job derivado de `general_name` (normalizado).
- `notifications`: `sua_letter_id` (FK a `sua_letters`, nullable) | notificación tipo “CARTA SUA” asociada al registro creado en job.
- `notification` ↔ colaborador: pivote `high_employee_notification` con `status` (ej. `NO LEIDA`).
- `app_setting` / `one_signal_tokens`: push al colaborador tras crear carta (vía job).

REGLAS DE NEGOCIO:

- RN-01: Solo se crea una carta SUA por combinación `(high_employee_id, bimester, business_name)`; si ya existe, la fila se cuenta como no procesada (`UNPROCESSED_SUA_LETTER`).
- RN-02: El colaborador debe existir en la empresa del usuario que carga: `HighEmployee::where('company_id', user.company_id)->where('employee_number', …)->first()`; si no, `UNPROCESSED_COLLABORATOR`.
- RN-03: El usuario que carga debe tener empresa asignada (`user->company`); si no, error “Debe tener una empresa asignada”.
- RN-04: Tras aceptar cada fila en servidor, el alta real (PDF + BD + notificación) ocurre en cola `CreateSuaLetterJob` con nombre `create_sua_letter_{empresa}` donde `{empresa}` es `mb_strtolower(str_replace(' ', '_', company.general_name))`, con caso especial `bc&b` → `bcb`.
- RN-05: Tras terminar el envío de todas las filas en el navegador, el cliente llama `GET /admin/jobs/run_jobs/create_sua_letter_{empresa}` para disparar procesamiento de la cola (comportamiento acoplado al front de carga).
- RN-06: Listado admin: usuarios con empresa ven solo cartas cuyo colaborador pertenece a `user.company_id`; sin empresa, la consulta base no filtra por compañía (⚠️ riesgo operativo).
- RN-07: Admin rol `admin` puede filtrar por empresas y colaboradores; filtros se aplican vía `POST admin/sua_letters/filters` (AJAX) que devuelve HTML de tabla.
- RN-08: Descarga de PDF en admin (`getSuaLetter`): devuelve JSON con `pdf_string` en base64 y registra `Log` de descarga; no actualiza `first_view_date` / `last_view_date` en este controlador (eso ocurre en API app).
- RN-09: En app móvil/API, `GET sua_letters/edit/{id}` actualiza `first_view_date` (si null) y `last_view_date` al “abrir” la carta.
- RN-10: Firma en app (`PUT sua_letters/sign/{id}`): comportamiento distinto si `checkbox` es `activo` (firma masiva por `high_employee_id` = id en ruta) vs `inactivo` (una carta por `sua_letter` id); integración opcional con Nubarium si `company.has_nubarium_sign`; si no, marca firmado y guarda PDF enviado sin NOM151.

FLUJO PRINCIPAL — CARGA (admin):

1. Usuario con permiso `load_sua_letters` abre `GET admin/sua_letters/get_load` (vista `view_load_sua_letters`).
2. Opcional: descarga plantilla Excel `GET admin/sua_letters/get_upload_template` → `LoadRegistersExport` / hoja con cabeceras: Número de empleado, Razón social, RFC, CURP, Nombre, Retiro, C.V., Infonavit, Tot RCV_INF, Bimestre.
3. Usuario selecciona `.xlsx`; el front (SheetJS) convierte la hoja a objetos por fila y filtra filas con todas las claves anteriores no vacías.
4. Por cada fila, `POST admin/sua_letters/load_data` con `row` = JSON de la fila; el servidor valida colaborador y duplicado (bimestre + razón social) y despacha o no el job.
5. Al completar el último POST, el front invoca `GET /admin/jobs/run_jobs/create_sua_letter_{empresa}`.
6. `CreateSuaLetterJob` crea registro, genera PDF DomPDF con vista `exports.pdf.sua_letters.letter`, guarda `pdf_string`, crea notificación “CARTA SUA”, adjunta colaborador y encola `NotificationPush` si hay tokens OneSignal.

FLUJO PRINCIPAL — VISUALIZACIÓN (admin):

1. Usuario con `view_sua_letters` abre `GET admin/sua_letters` → listado paginado, enriquecido con nombre de colaborador y empresa; columnas de firma y fechas de vista con HTML (“Firmado” / “No firmado”, “No visualizado”).
2. Búsqueda, paginación, orden y filtros (admin) vía `POST admin/sua_letters/filters` que renderiza parcial `admin.sua_letters.table`.
3. “Descargar” en tabla dispara petición al endpoint que ejecuta `getSuaLetter` y entrega el PDF en base64 al cliente (implementación en JS de la página list).

FLUJOS SECUNDARIOS:

- Eliminación: `GET admin/sua_letters/trash/{sua_letter_id}` con permiso `trash_sua_letters`, log y redirect con mensaje.
- App colaborador: listado `POST api/.../sua_letters` (cuerpo con `sort` y uso de `from`/`to` sobre campo `bimestre`); detalle `GET sua_letters/{id}`; marcar visto `GET sua_letters/edit/{id}`; firmar `PUT sua_letters/sign/{id}`.
- Clases `App\Imports\SuaLetters\ImportData` y `App\Imports\SuaLetters\Sheets\FirstSheetImport`: importación tipo Maatwebsite Excel con generación de PDF; **no** referenciadas desde controladores en el grep del repo (código muerto o uso no localizado).

VALIDACIONES:

- Carga front: tipo MIME `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`; usuario con empresa (validado también en JS).
- Carga back: mismas claves JSON que la plantilla; trim en todos los campos; duplicado `(high_employee_id, bimester, business_name)`.
- Plantilla Excel: validaciones de longitud en celdas (RFC 10–13, CURP 18, no vacíos) a nivel hoja exportada.
- API `getSuaLetters`: validación explícita solo de `sort` ∈ {ASC, DESC}; uso de `from`/`to` sin validación en el mismo bloque.
- `ImportData::rules`: RFC longitud, CURP mínimo 18, unicidad bimestre por colaborador (por `employee_number`); cabeceras de fila ignoradas.

PERMISOS:

- `view_sua_letters`: índice, filtros, descarga PDF (`getSuaLetter`).
- `load_sua_letters`: pantalla de carga, `load_data`, plantilla Excel.
- `trash_sua_letters`: borrado de carta.
- Rutas con middleware `logged`, `2fa` y JSON de permisos en `routes/web.php`.
- Mapeo documentado en tecben-core (`view_carta_sua`, `load_carta_sua`, `delete_carta_sua`) respecto a legacy en `docs/contexto-legacy/ANALISIS_ROLES_Y_PERMISOS_LEGACY.md`.

SERVICIOS/ENDPOINTS INVOLUCRADOS:

- `GET admin/sua_letters` — listado inicial.
- `GET admin/sua_letters/get_load` — formulario de carga.
- `POST admin/sua_letters/load_data` — una fila JSON por request.
- `GET admin/sua_letters/get_upload_template` — Excel plantilla.
- `POST admin/sua_letters/filters` — tabla + selectores de filtro (JSON).
- `GET admin/sua_letters/get/{sua_letter_id}` — respuesta JSON PDF base64 + metadatos (uso desde admin).
- `GET admin/sua_letters/trash/{sua_letter_id}` — borrado.
- `GET /admin/jobs/run_jobs/{queue_name}` — ejecución de cola tras carga (nombre `create_sua_letter_*`).
- API (prefijo grupo autenticado `api` en `routes/api.php`): `POST sua_letters`, `GET sua_letters/{id}`, `GET sua_letters/edit/{id}`, `PUT sua_letters/sign/{id}`.

JOBS/COLAS:

- `App\Jobs\SuaLetters\CreateSuaLetterJob`: `tries = 1`; genera PDF, persiste `sua_letters`, notificación y push; cola dinámica `create_sua_letter_{slug_empresa}`.
- `App\Jobs\Notifications\NotificationPush`: envío OneSignal en cola `low_priority_notifications` cuando hay tokens.

NOTIFICACIONES:

- Tipo `CARTA SUA`, mensaje fijo orientado a firma; asociada al `SuaLetter` y al `HighEmployee` en pivote; payload push incluye `data.id` = id de carta SUA.

CASOS BORDE:

- Usuario admin sin `company`: listado y carga con comportamiento distinto (sin `whereHas` por compañía en consultas base).
- `getSuaLetter` admin: si el id no existe, acceso a propiedades sobre null puede fallar antes de la rama de error (orden de código).
- `suaLetterSign` con `checkbox=activo`: el parámetro de ruta se usa como `high_employee_id` para buscar **todas** las cartas de ese colaborador; firma en lote.
- API `getSuaLetters`: encadenamiento `where(...)->get()` en subconsultas no aplica filtros como se esperaría (🔧 ver deuda técnica).
- `FirstSheetImport` resuelve colaborador por RFC y asigna `$high_employee->id` sin comprobar null antes (riesgo de error si no existe).

⚠️ AMBIGÜEDADES:

- En `getFilters`, asignación `payment_date` desde `initial_payment_date` y `final_date_payment`: **no** existen en migración ni modelo `SuaLetter`; el significado en UI es incierto o la columna no se muestra en la tabla actual.
- Lista API: contrato de `from` / `to` (años vs meses en `bimester` string) frente a validación mínima del controlador.
- Firma Nubarium: rutas de temporales y uso de `File::deleteDirectory($path_pdf)` donde `$path_pdf` es archivo, no directorio (comportamiento filesystem ambiguo).

🔧 DEUDA TÉCNICA:

- Variables de resumen en front nombradas `payroll_receipt` / “existing_payroll_receipt” pero refieren a carta SUA no duplicada.
- `App\Http\Controllers\Api\SuaLettersController`: uso de `Log::channel` sin `use Log` importado en el fragmento leído; posible error en catch.
- Importaciones Excel en servidor (`ImportData`, `FirstSheetImport`) duplican lógica del job pero aparentemente no usadas en el flujo admin actual.
- Dependencia de SheetJS en CDN y lectura en cliente para carga masiva (sin revalidación server-side fila a fila equivalente a `ImportData`).

📌 DIFERENCIAS CON TECBEN-CORE (si ya está implementado):

- LEGACY: tabla `sua_letters`, PDF en base64 en BD, colas por nombre de empresa, notificación + push al crear, firma Nubarium opcional en API.
- TECBEN-CORE (búsqueda puntual): permiso de producto “Cartas SUA” en seeder; sin recurso Filament / modelo homónimo localizado en este análisis. La migración funcional queda pendiente de definición (almacenamiento PDF, colas, Nubarium).
