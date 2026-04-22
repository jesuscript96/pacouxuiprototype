MÓDULO: Vacantes — Reclutamiento (legacy Paco)
FECHA ANÁLISIS: 2026-04-08
ANALIZADO POR: Agente paco-legacy
ESTADO EN TECBEN-CORE: No implementado (no se localizó modelo `Recruitment` / `RecruitmentCandidates` ni recurso Filament equivalente)

---

## DESCRIPCIÓN GENERAL

El módulo de **Vacantes** es el submódulo operativo del catálogo de Reclutamiento. Permite crear vacantes asociadas a una empresa, definir formularios dinámicos de postulación, recibir candidatos desde un formulario público, gestionar el ciclo de vida de candidatos (estatus, comentarios, archivos, validaciones de documentos) y exportar reportes.

---

ENTIDADES:

### `recruitments` (la vacante)
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigIncrements | PK |
| company_id | unsignedBigInteger | FK a `companies` |
| created_by | unsignedBigInteger | FK a `users` (quien creó) |
| name | string | ⚠️ No usado en controladores |
| position | string | Puesto / título de la vacante |
| requirements | longText | Requisitos (HTML vía CKEditor) |
| aptitudes | longText | Aptitudes (HTML) |
| benefits | longText | Prestaciones (HTML) |
| url | string | URL pública generada automáticamente |
| timestamps | | |
| deleted_at | timestamp nullable | SoftDeletes |

Relaciones: `belongsTo` Company; `hasMany` RecruitmentCandidates (`recruitment_cadidates()`); `hasMany` RecruitmentForm.

### `recruitment_forms` (campos dinámicos del formulario)
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigIncrements | PK |
| recruitment_id | unsignedBigInteger nullable | FK a `recruitments` (cascade) |
| type | string | Tipo de input: text, select, file, date, etc. |
| label | string | Etiqueta visible al candidato |
| name | string | Nombre técnico del campo |
| required | string | "SI" / "NO" (strtoupper) |
| placeholder | string nullable | |
| accept | string nullable | MIME types para campos file |
| min_length | string nullable | |
| max_length | string nullable | |
| options | text nullable | JSON de opciones para selects |
| is_dependent | boolean default false | Campo condicional |
| parent_field | string nullable | Campo padre para dependencia |
| trigger_value | string nullable | Valor que activa la visibilidad |
| timestamps | | |

### `recruitment_candidates` (candidato postulado)
Tabla con muchas migraciones acumuladas. Campos finales (todos nullable tras migración 11):

| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| recruitment_id | unsignedBigInteger nullable (FK) |
| status | string nullable |
| recruitment_form_values | json nullable (migración 11) |
| curp | string nullable |
| rfc | string nullable |
| name | string nullable |
| paternal_last_name | string nullable |
| maternal_last_name | string nullable |
| age | string nullable |
| birthdate | date nullable |
| place_of_birth | string nullable |
| gender | string nullable |
| civil_status | string nullable |
| number_children | string nullable |
| scholarship | string nullable |
| proof_of_studies | string nullable |
| profession | string nullable |
| university | string nullable |
| number_last_jobs | string nullable |
| postal_code | string nullable |
| address | string nullable |
| street | string nullable |
| number_in | string nullable |
| number_ex | string nullable |
| suburb | string nullable |
| town | string nullable |
| state | string nullable |
| about_job | string nullable |
| other_about_job | string nullable |
| worked_again | string nullable |
| phone | string nullable |
| home_phone | string nullable |
| email | string nullable |
| branch_office | string nullable |
| company | string nullable |
| initial_date | string nullable |
| final_date | string nullable |
| work_experience | string nullable |
| work_area | string nullable |
| experience_time | string nullable |
| work_history | string nullable |
| cause_of_job_abandonment | string nullable |
| other_cause_of_job_abandonment | string nullable |
| last_salary_received | string nullable |
| monthly_salary | string nullable |
| family_work_us | string nullable |
| family_name | string nullable |
| availability_to_travel | string nullable |
| driver_license | string nullable |
| valid_driver_license | string nullable |
| driver_license_type | string nullable |
| file_ine_front | string nullable |
| file_ine_reverse | string nullable |
| file_birth_certificate | string nullable |
| file_proof_address | string nullable |
| file_rfc | string nullable |
| file_cif | string nullable |
| file_curp | string nullable |
| file_social_security | string nullable |
| file_curriculum | string nullable |
| file_proof_of_studies | string nullable |
| service_received | string nullable |
| timestamps | |
| deleted_at | timestamp nullable |

Relaciones: `belongsTo` Recruitment; `hasMany` RecruitmentCandidateMessages (FK `recruitment_candidates_id`); `hasMany` RecruitmentCandidateStatus.

**Nota importante (migración 11, 2025-07-10):** todos los campos originales se hicieron nullable y se agregó `recruitment_form_values` (JSON). A partir de esta versión, los datos de candidatos se almacenan preferentemente en el campo JSON, no en columnas individuales. Los controladores leen primero `recruitment_form_values` y si no existe, caen a las columnas legacy.

### `recruitment_candidate_messages` (comentarios internos)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| recruitment_candidates_id | unsignedBigInteger nullable (FK) |
| user_id | unsignedBigInteger |
| comment | longText |
| timestamps | |
| deleted_at | timestamp nullable |

Relaciones: `belongsTo` User; `belongsTo` RecruitmentCandidates.

### `recruitment_candidate_statuses` (historial de estados)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| recruitment_candidates_id | unsignedBigInteger nullable (FK cascade) |
| status | string |
| initial_date | datetime |
| final_date | datetime nullable |
| custom_date | string nullable (duración legible: "2 MESES 5 DIAS") |
| timestamps | |

---

REGLAS DE NEGOCIO:

- RN-01: **Unicidad de vacante** por `(company_id, position)`. No se permite crear dos vacantes con la misma empresa y puesto.
- RN-02: **URL pública** generada automáticamente al crear/editar: `/{company_name_normalizado}/recruitment/vacancy/{position_normalizado}`. Normalización: quitar acentos, lowercase, espacios → guiones.
- RN-03: **Formulario dinámico** (`recruitment_forms`): cada vacante define sus propios campos; al editar se eliminan todos (`delete()`) y se recrean. Soporta campos condicionales (dependientes).
- RN-04: **Estatus de candidato**: valores posibles: `Sin atender`, `En proceso`, `Contratado`, `Rechazado`, `No se presentó`. El status inicial al crear candidatura es `Sin atender`.
- RN-05: **No se puede repetir estatus**: al agregar comentario + cambiar estatus, se valida que no exista ya un `RecruitmentCandidateStatus` con el mismo valor de `status` para ese candidato; si existe, devuelve error.
- RN-06: **Historial de estatus**: cada cambio cierra el registro anterior (pone `final_date` y calcula `custom_date` como duración legible) y crea uno nuevo con `initial_date`.
- RN-07: **CURP automático**: si el formulario público incluye lugar de nacimiento, nombres, apellidos, género y fecha de nacimiento, se genera CURP usando `CurpValidationTrait::createCurp()`.
- RN-08: **Validación de INE**: si el formulario incluye `ine_frontal` e `ine_reverso`, se valida con servicio externo (`INETrait::validateIneImage`). Se comparan nombres/apellidos/fecha de nacimiento con los datos del formulario. Se almacena `ine_is_valid` + `ine_data_is_valid` en el JSON.
- RN-09: **Validación de CSF (Constancia de Situación Fiscal)**: se extrae RFC, CURP, nombre completo y se compara. Almacena `constancia_de_situacion_fiscal_is_valid` + `_data_is_valid`.
- RN-10: **Validación de comprobante de domicilio**: servicio externo extrae dirección del documento. Almacena `comprobante_de_domicilio_is_valid` + `_data_is_valid` y guarda dirección en `domicilio`.
- RN-11: **Análisis de CV**: se envía el archivo junto con `position`, `requirements` y `aptitudes` de la vacante a un servicio externo de IA. Se almacena el análisis completo en `curriculum_vitae_cv_analysis` con un `job_comparison.score` (0–10).
- RN-12: **Reemplazo de archivos**: solo se puede reemplazar un archivo si su par de flags (`_is_valid` + `_data_is_valid`) NO son ambos `true`. Si ya pasó validación exitosa, se bloquea el reemplazo.
- RN-13: **Archivos de candidato** se almacenan en `assets/recruitments/{company_name}/{candidate_id}/`.
- RN-14: **Observer** `RecruitmentCandidatesObserver`: al crear o actualizar un candidato con CURP válido (18 chars), se despacha `EmployeeHistoryJob` con `employee_type = 'R'` para registrar historial laboral del candidato.
- RN-15: **Historial laboral del candidato**: en la vista detalle, se consulta `EmployeeHistory::where('identifier', curp)->where('paco_company_id', company_id)` para mostrar empleos anteriores del candidato en la misma empresa.

FLUJO PRINCIPAL — CREAR VACANTE:

1. Admin con `create_recruitments` abre `GET admin/recruitments/vacant/create`.
2. Selecciona empresa, escribe puesto, requisitos (CKEditor), aptitudes y prestaciones.
3. En pestaña "Formulario", define campos dinámicos: tipo, etiqueta, nombre, requerido, placeholder, opciones, dependencias.
4. `POST admin/recruitments/vacant/create` → valida requeridos, verifica unicidad `(company_id, position)`, genera URL, crea `Recruitment`, itera `form_fields` JSON para crear `RecruitmentForm`.
5. Redirect a listado con mensaje de éxito.

FLUJO PRINCIPAL — POSTULACIÓN PÚBLICA:

1. Candidato accede a URL pública `/{company_name}/recruitment/vacancy/{position}`.
2. Vista `recruitment/form.blade.php` muestra formulario con los campos definidos en `recruitment_forms`.
3. Al enviar, `POST admin/recruitments/candidates/create` (sin auth) → crea `RecruitmentCandidates` con status `Sin atender`, genera CURP si aplica, valida INE/CSF/comprobante/CV si se suben, almacena archivos, guarda todo en `recruitment_form_values` JSON.
4. Se crea primer `RecruitmentCandidateStatus` con status `Sin atender`.
5. Observer dispara `EmployeeHistoryJob` si CURP válido.
6. Redirect con mensaje de éxito.

FLUJO PRINCIPAL — GESTIÓN DE CANDIDATOS:

1. Admin con `view_recruitments_candidates` abre `GET admin/recruitments/vacant/{id}/candidates` → lista de candidatos con estatus, fecha, CURP, nombre, teléfono, email, evaluación CV (/10).
2. Ver detalle (`GET admin/recruitments/candidates/view/{id}`): datos del formulario, documentos con banderas de validación, historial laboral (si hay), mensajes/comentarios, historial de estatus.
3. Editar (`GET/POST ...candidates/edit/{id}`): modifica valores textuales del JSON del formulario.
4. Subir/reemplazar archivo (`POST ...candidates/upload-file`): sube archivo, re-ejecuta validaciones, actualiza JSON. Bloquea reemplazo si validación previa fue exitosa.
5. Eliminar archivo (`POST ...candidates/delete-file`): borra archivo físico, limpia entrada del JSON y flags de validación.
6. Cambiar estatus + comentar (`POST ...candidates/add-comment`): valida que el nuevo estatus no esté repetido, cierra estatus anterior, crea nuevo historial, guarda comentario.
7. Eliminar candidato (`GET ...candidates/trash/{id}`): soft delete.
8. Eliminar comentario (`GET ...candidates/comments/trash/{id}`).

FLUJO SECUNDARIO — REPORTES:

- **Excel**: `POST admin/recruitments/candidates/report/excel` → `CandidatesExport` con Maatwebsite; genera hoja con cabeceras dinámicas basadas en `recruitment_forms` (sin campos tipo `file`); filtrable por estatus.
- **PDF**: `POST admin/recruitments/candidates/report/pdf` → DomPDF con vista `exports.pdf.recruitment.candidate-report-modal`; descarga reporte individual de un candidato.

VALIDACIONES:

### Vacante (crear/editar)
- `company`: required (selección de empresa existente).
- `position`: required.
- `requirements`: required.
- `aptitudes`: required.
- `benefits`: required.
- Unicidad `(company_id, position)`: validación manual en controlador, excluyendo el registro actual en edición.

### Candidato (postulación)
- Campos del formulario dinámico: required según configuración de `recruitment_forms.required` = "SI".
- Archivos: almacenados en filesystem local, tipos validados: PNG, JPG, PDF (en upload de admin), PNG/JPG para INE.
- Validación de documentos: INE (frontal + reverso contra datos personales + MRZ), CSF (nombre + RFC + fecha nacimiento), comprobante de domicilio (extracción de dirección), CV (match score contra descripción de puesto).

### Estatus
- No se puede repetir estatus ya registrado en historial para el mismo candidato.

PERMISOS:

- `view_recruitments`: listado de vacantes, detalle, filtros, acceso a Tableau.
- `create_recruitments`: crear vacante.
- `edit_recruitments`: editar vacante.
- `trash_recruitments`: eliminar vacante (soft delete).
- `view_recruitments_candidates`: ver candidatos de una vacante, obtener listado, filtros, reporte Excel/PDF.
- `edit_recruitments_candidates`: editar candidato, subir/eliminar archivos, agregar comentario + cambiar estatus, actualizar mensajes.
- `trash_recruitments_candidates`: eliminar candidato.
- `trash_recruitments_candidates_comment`: eliminar comentario de candidato.

SERVICIOS/ENDPOINTS INVOLUCRADOS:

### Admin — Vacantes
| Método | Ruta | Acción | Permiso |
|--------|------|--------|---------|
| GET | `admin/recruitments/vacant` | Listado paginado | `view_recruitments` |
| GET | `admin/recruitments/vacant/get` | Listado JSON DataTables | `view_recruitments` |
| GET | `admin/recruitments/vacant/create` | Form crear | `create_recruitments` |
| POST | `admin/recruitments/vacant/create` | Guardar nueva vacante | `create_recruitments` |
| GET | `admin/recruitments/edit/{id}` | Form editar | `edit_recruitments` |
| POST | `admin/recruitments/edit` | Actualizar vacante | `edit_recruitments` |
| GET | `admin/recruitments/vacant/trash/{id}` | Eliminar vacante | `trash_recruitments` |
| GET | `admin/recruitments/vacant/view/{id}` | Ver detalle vacante | `view_recruitments` |
| POST | `admin/recruitments/filters` | Filtrar vacantes AJAX | `view_recruitments` |

### Admin — Candidatos
| Método | Ruta | Acción | Permiso |
|--------|------|--------|---------|
| GET | `admin/recruitments/vacant/{id}/candidates` | Listado candidatos | `view_recruitments_candidates` |
| GET | `admin/recruitments/vacant/{id}/candidates/get` | Lista JSON DataTables | `view_recruitments_candidates` |
| GET | `admin/recruitments/candidates/view/{id}` | Ver detalle candidato | `view_recruitments_candidates` |
| GET | `admin/recruitments/candidates/edit/{id}` | Form editar candidato | `edit_recruitments_candidates` |
| POST | `admin/recruitments/candidates/update` | Actualizar candidato | `edit_recruitments_candidates` |
| POST | `admin/recruitments/candidates/add-comment` | Comentario + cambio status | `edit_recruitments_candidates` |
| POST | `admin/recruitments/candidates/upload-file` | Subir/reemplazar archivo | `edit_recruitments_candidates` |
| POST | `admin/recruitments/candidates/delete-file` | Eliminar archivo | `edit_recruitments_candidates` |
| GET | `admin/recruitments/candidates/trash/{id}` | Eliminar candidato | `trash_recruitments_candidates` |
| GET | `admin/recruitments/candidates/comments/trash/{id}` | Eliminar comentario | `trash_recruitments_candidates_comment` |
| POST | `admin/recruitments/candidates/report/excel` | Reporte Excel | `view_recruitments_candidates` |
| POST | `admin/recruitments/candidates/report/pdf` | Reporte PDF individual | `view_recruitments_candidates` |
| POST | `admin/recruitments/candidates/edit/messages` | Actualizar mensajes | `edit_recruitments_candidates` |

### Público (sin autenticación)
| Método | Ruta | Acción |
|--------|------|--------|
| GET | `{company_id}/recruitment` | Redirect primera vacante |
| GET | `{company_id}/recruitment/{position}` | Redirect a formulario |
| GET | `{company_name}/recruitment/vacancy/{position}` | Formulario de postulación |
| POST | `admin/recruitments/candidates/create` | Crear candidatura |

JOBS/COLAS:

- `EmployeeHistoryJob` (cola `employee_history_create`): creación/actualización de historial laboral; disparado por `RecruitmentCandidatesObserver` en eventos `created` y `updated` cuando CURP tiene 18 caracteres. Payload con `employee_type = 'R'`.

NOTIFICACIONES:

- No se generan notificaciones push, SMS ni email al crear vacante ni candidatura.

CASOS BORDE:

- **Candidato sin `recruitment_form_values`**: candidatos creados antes de migración 11 tienen datos en columnas individuales; los controladores hacen fallback a estas columnas si `recruitment_form_values` es null.
- **Formulario vacante sin campos**: si `recruitment_forms` está vacío, `create` de candidato devuelve error "Vacante sin información disponible".
- **Empresa sin general_name o con caracteres especiales**: la normalización de URL usa `textFormat()` (reemplazo manual de acentos); si la empresa tiene otros caracteres especiales no mapeados, la URL podría romperse.
- **Eliminación de todos los `recruitment_forms` al editar**: se borran y recrean cada vez (`delete()` + loop `save()`), lo que cambia los IDs.
- **Archivos con validación exitosa no reemplazables**: el check `canReplaceFile()` impide reemplazar archivos que pasaron validación; el admin debe primero eliminar el archivo para poder subir uno nuevo.
- **Evaluación CV = 0**: si no hay análisis de CV o si falló la validación, `evaluation = 0` por defecto.
- **Campo `name` en migración `recruitments`**: existe pero nunca se asigna; el controlador usa `position` como identificador de la vacante.
- **POST `candidates/create` sin middleware auth**: la ruta de creación de candidatura está abierta públicamente pero tiene path `admin/...`.

⚠️ AMBIGÜEDADES:

- Campo `name` en tabla `recruitments` vs `position`: no queda claro si `name` tenía un uso previo deprecado.
- `updateMessages()` referenciada en rutas pero no se encontró implementación explícita en el controlador leído (puede estar heredada o ser un método `__call`).
- Trait `VerificationNotificationTrait` se importa en `FirstSheetImport` de `SuaLetters` pero no en reclutamiento; los traits `CurpValidationTrait`, `INETrait`, `ComprobanteDomicilioTrait`, `CSFTrait`, `CVTrait` se usan en el controlador de candidatos pero no se documentan sus endpoints externos.

🔧 DEUDA TÉCNICA:

- Relación `recruitment_cadidates()` con typo persistente en modelo `Recruitment`.
- `getList()` y `getFilters()` generan HTML de botones directamente en PHP con concatenación de strings.
- `getIndex()` en vacantes ejecuta N+1 queries: `count($recruitment->recruitment_cadidates()->get())` por cada vacante.
- Al editar vacante, todos los `recruitment_forms` se eliminan y recrean, perdiendo los IDs originales.
- Migraciones de `recruitment_candidates` son 11 archivos incrementales; la tabla final tiene ~60 columnas, la mayoría ya reemplazadas por el campo JSON `recruitment_form_values`.
- Array de columnas Excel A–ZZ hardcodeado en `CandidatesExport` (cientos de strings literales).
- Validaciones de documentos (INE, CSF, etc.) se ejecutan sincrónicamente en el request HTTP de postulación, lo que puede hacer lenta la respuesta.
- `POST admin/recruitments/candidates/create` (ruta pública de postulación) comienza con `admin/` pero no tiene middleware auth.

📌 DIFERENCIAS CON TECBEN-CORE:

- LEGACY: CRUD completo de vacantes y candidatos con formularios dinámicos, validación documental (INE, CSF, domicilio, CV con IA), historial de estatus con duración, mensajes/comentarios, export Excel/PDF, formulario público, observer con historial laboral, generación de QR para URL pública.
- TECBEN-CORE: solo existe la página de analíticos Tableau (`ReclutamientoTableauPage` en `app/Filament/Cliente/Pages/Analiticos/`), permisos mapeados en `ShieldPermisosLegacySeeder` (grupo "Reclutamiento"). No hay modelo, migración, ni recurso Filament para la gestión de vacantes o candidatos.
