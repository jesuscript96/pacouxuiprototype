MÓDULO: Catálogo de Cursos / Capacitaciones (legacy Paco)
FECHA ANÁLISIS: 2026-04-08
ANALIZADO POR: Agente paco-legacy
ESTADO EN TECBEN-CORE: No implementado como módulo funcional (existen permisos `view_capacitacion`, `create_capacitacion`, `delete_capacitacion` en `filament-shield.php` y `ShieldPermisosLegacySeeder`; campo `descargar_cursos` en modelo `Empresa`; sin modelo, migración ni recurso Filament equivalente)

---

## DESCRIPCIÓN GENERAL

En Paco el módulo se llama **"Cursos"** en el sidebar y UI, pero en backend todo se nombra **Capacitation** / **capacitations**. Permite crear cursos de capacitación empresarial con una estructura jerárquica: **Curso → Módulos → Lecciones → Temas** (contenido multimedia). Cada lección puede tener evaluaciones (preguntas con opciones) y actividades prácticas. Los cursos se segmentan por empresa, área, departamento, puesto, ubicación, razón social o empleados específicos. El módulo incluye reportes de avance, evaluaciones, encuestas de satisfacción, certificados de finalización, formato DC-3 (STPS) y acuses.

---

ENTIDADES:

### `capacitations` (el curso)
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigIncrements | PK |
| company_id | unsignedBigInteger nullable | FK a `companies` |
| user_id | unsignedBigInteger nullable | FK a `users` (creador) |
| title | string | Nombre del curso |
| date | datetime | Fecha de creación |
| description | longText | |
| image | string | Ruta a imagen de portada |
| path | string | Directorio base de archivos del curso |
| duration_hours | string nullable | Duración estimada |
| mandatory | boolean default true | Curso obligatorio |
| sequential | boolean default true | Lecciones secuenciales |
| deadline_days | string nullable | Plazo en días para completar |
| segments_type | string nullable | Tipo de segmentación: `all`, `area`, `department`, `position`, `location`, `business_name`, `employee` |
| notify | boolean default true | Enviar notificación push al activar |
| active | boolean default false | Borrador vs Activo |
| last_modification | datetime | |
| active_at | datetime nullable | Fecha de activación |
| expired_at | datetime nullable | Fecha de expiración calculada (`active_at + deadline_days`) |
| satisfaction_survey | boolean default false | Habilitar encuesta de satisfacción |
| completion_certificate | boolean default false | Generar certificado de finalización |
| active_by_antique_value | string nullable | Meses de antigüedad mínima del colaborador |
| acknowledgements | longText nullable | JSON con IDs de acuses asociados |
| proof_skill_id | unsignedBigInteger nullable | FK a `proof_skills` (datos DC-3 STPS) |
| softDeletes, timestamps | | |

Relaciones: `belongsTo` Company, User, ProofSkill; `hasMany` CapacitationModule, CapacitationSegment, CapacitationHighEmployee, CapacitationLessonCompleted, CapacitationSatSurveyRes; `belongsToMany` HighEmployee (pivote `high_employee_capacitation` con `segment_type`, `segments`).

### `capacitation_modules` (módulos del curso)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_id | unsignedBigInteger nullable (FK cascade) |
| title | string |
| softDeletes, timestamps | |

Relaciones: `belongsTo` Capacitation; `hasMany` CapacitationLesson, CapacitationLessonCompleted.

### `capacitation_lessons` (lecciones)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_module_id | unsignedBigInteger nullable (FK cascade) |
| title | string |
| practical_activity | longText nullable (instrucciones de actividad práctica) |
| softDeletes, timestamps | |

Relaciones: `belongsTo` CapacitationModule; `hasMany` CapacitationTheme, CapacitationLessonQSetting, CapacitationLessonQ, CapacitationLessonResPracticeActivity, CapacitationLessonCompleted.

### `capacitation_themes` (contenido multimedia de la lección)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_lesson_id | unsignedBigInteger nullable (FK cascade) |
| title | string |
| description | longText |
| media_type | string (`youtube`, `video`, `file`, `audio`, imágenes) |
| url | string nullable (ruta archivo o URL YouTube) |
| softDeletes, timestamps | |

### `capacitation_lesson_q_settings` (configuración de evaluación por lección)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_lesson_id | unsignedBigInteger nullable (FK cascade) |
| value | string |
| type | string |
| softDeletes, timestamps | |

### `capacitation_lesson_qs` (preguntas de evaluación)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_lesson_id | unsignedBigInteger nullable (FK cascade) |
| type | string (tipo de pregunta) |
| question | string |
| count_checked | string (cantidad de opciones correctas) |
| explication | boolean default false (requiere explicación) |
| score | string (puntuación de la pregunta) |
| softDeletes, timestamps | |

Relaciones: `hasMany` CapacitationLessonQOption, CapacitationQuestionResponse.

### `capacitation_lesson_q_options` (opciones de respuesta)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_lesson_q_id | unsignedBigInteger nullable (FK cascade) |
| checked | boolean default false (opción correcta) |
| type | string default 'text' (añadido por migración) |
| value | longText (texto de la opción) |
| softDeletes, timestamps | |

### `capacitation_segments` (segmentación del curso)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_id | unsignedBigInteger nullable (FK cascade) |
| value | string (ID del segmento: area_id, department_id, etc.) |
| timestamps | |

### `capacitation_high_employees` (progreso del colaborador en el curso)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_id | unsignedBigInteger nullable (FK cascade) |
| high_employee_id | unsignedBigInteger nullable (FK cascade) |
| status | string (`pending`, `in_progress`, `completed`, `failed`) |
| started_at | datetime nullable |
| completed_at | datetime nullable |
| percent | string nullable |
| timestamps | |

### `capacitation_lesson_completed` (progreso granular por tema/lección)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_id, capacitation_module_id, capacitation_lesson_id, capacitation_theme_id | unsignedBigInteger nullable (FK cascade) |
| high_employee_id | unsignedBigInteger nullable (FK cascade) |
| completed | boolean default false |
| survey_completed | boolean default false |
| tries_survey | string nullable |
| tries_lesson | string nullable |
| score | string nullable |
| remaining_time | string nullable |
| survey_status | string nullable |
| timestamps | |

### `lesson_res_practical_activities` (entregas de actividad práctica)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_lesson_id | unsignedBigInteger nullable (FK cascade) |
| high_employee_id | unsignedBigInteger nullable (FK cascade) |
| path | string |
| name | string |
| timestamps | |

### `capacitation_q_responses` (respuestas de evaluación)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_lesson_q_id | unsignedBigInteger nullable (FK cascade) |
| high_employee_id | unsignedBigInteger nullable (FK cascade) |
| explication | string nullable |
| response | string nullable |
| options | longText nullable |
| timestamps | |

### `satisfaction_survey_qs` (preguntas de encuesta de satisfacción, catálogo global)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| question | longText |
| type | string |
| timestamps | |

### `capacitation_sat_survey_res` (respuestas encuesta de satisfacción)
| Campo | Tipo |
|-------|------|
| id | bigIncrements |
| capacitation_id | unsignedBigInteger nullable (FK cascade) |
| high_employee_id | unsignedBigInteger nullable (FK cascade) |
| satisfaction_survey_q_id | unsignedBigInteger nullable (FK cascade) |
| response | string nullable |
| timestamps | |

### Pivote `high_employee_capacitation` (asignación de colaboradores)
| Campo | Notas |
|-------|-------|
| capacitation_id | FK |
| high_employee_id | FK |
| segment_type | string |
| segments | JSON nullable |
| timestamps | |

### Entidades auxiliares (DC-3)
- `proof_skills`: `key_stps`, `agent_trainer_name`, `agent_trainer_rfc` | FK desde `capacitations.proof_skill_id`.
- `proof_skill_modalities`, `proof_skill_objectives`, `proof_skill_areas`, `proof_skill_agents`: catálogos STPS vinculados a `proof_skills`.

---

REGLAS DE NEGOCIO:

- RN-01: **Unicidad de curso** por directorio: `assets/companies/capacitation/{company_id}/{title_sanitizado}`; si el directorio ya existe, la creación se rechaza ("Ya existe un curso con ese nombre para esta empresa").
- RN-02: **Creación en pasos (stepper)**: Step 1 = Información (empresa, título, imagen, descripción, duración); Step 2 = Contenido (módulos → lecciones → temas con multimedia + preguntas); Step 3 = Configuración (obligatorio, secuencial, plazo, antigüedad, segmentación, notificación, encuesta satisfacción, certificado, acuses, DC-3); Step 4 = Destinatarios (segmentos).
- RN-03: **Segmentación**: `segments_type` puede ser `all`, `area`, `department`, `position`, `location`, `business_name` o `employee`. Los IDs de segmento se guardan en `capacitation_segments`.
- RN-04: **Activación**: un curso puede guardarse como borrador (`active = false`) o publicarse (`active = true`). Al publicar se establece `active_at`, se calcula `expired_at` si hay `deadline_days`, se despacha `AssignedHighEmployee` para vincular colaboradores en pivote, y opcionalmente se envía notificación push.
- RN-05: **Antigüedad mínima** (`active_by_antique_value`): si está configurado, solo se notifica/asigna a colaboradores cuya `admission_date` indica al menos N meses de antigüedad.
- RN-06: **Expiración**: en la API, si `expired_at` ya pasó, el curso no aparece para el colaborador (se filtra con `compareDates`).
- RN-07: **Progreso del colaborador**: estados en `capacitation_high_employees`: `pending` → `in_progress` → `completed` / `failed`. El porcentaje se recalcula como `(lecciones completadas / total lecciones) * 100`.
- RN-08: **Lecciones secuenciales**: si `sequential = true`, las lecciones deben completarse en orden.
- RN-09: **Evaluación por lección**: cada lección puede tener un bloque de preguntas con configuración (settings como intentos, tiempo, puntaje mínimo). El colaborador puede reintentar (`tries_survey`).
- RN-10: **Actividad práctica por lección**: si `practical_activity` no es null, el colaborador debe subir un archivo como evidencia (`lesson_res_practical_activities`).
- RN-11: **Encuesta de satisfacción**: si `satisfaction_survey = true`, al finalizar el curso el colaborador responde preguntas del catálogo `satisfaction_survey_qs`.
- RN-12: **Certificado de finalización**: si `completion_certificate = true`, se genera PDF DomPDF al completar el curso (API `getCompletionCertificate`).
- RN-13: **Formato DC-3 (STPS)**: si se configura un `proof_skill`, se puede descargar el formato DC-3 con datos del agente capacitador, modalidad, objetivo, área y datos del colaborador.
- RN-14: **Acuses** (`acknowledgements`): JSON de IDs de `Acknowledgment` asociados al curso.
- RN-15: **Descarga de cursos**: flag `has_download_capacitation` en `companies` habilita/deshabilita la descarga de material desde la app.
- RN-16: **Desactivar curso**: endpoint `desactivate` pone `active = false`; no elimina datos.
- RN-17: **Duplicar curso**: endpoint `duplicate` copia toda la estructura (módulos, lecciones, temas, preguntas, opciones, settings, segmentos) a un nuevo directorio.
- RN-18: **Asignación de nuevos colaboradores**: cuando se da de alta o edita un colaborador (vía `HighEmployeesLCTJob`), se despacha `AssignedNewOrEditHighEmployee` que asigna automáticamente todos los cursos activos de la empresa que apliquen al colaborador según segmentación y antigüedad.

FLUJO PRINCIPAL — CREAR CURSO (admin):

1. Admin con `create_capacitation` abre `GET admin/capacitation/create`.
2. Selecciona empresa → se cargan catálogos (razones sociales, departamentos, áreas, puestos, ubicaciones).
3. Completa Step 1 (Información): título, imagen base64, descripción, duración.
4. Completa Step 2 (Contenido): define módulos; por cada módulo define lecciones; por cada lección define temas con multimedia (YouTube, video chunk-upload, archivo, audio, imágenes) y opcionalmente preguntas de evaluación.
5. Completa Step 3 (Configuración): obligatoriedad, secuencialidad, plazo, antigüedad mínima, segmentación, notificación, encuesta de satisfacción, certificado, acuses, DC-3.
6. `POST admin/capacitation/create` con JSON completo de steps → crea `Capacitation`, `CapacitationModule`s, `CapacitationLesson`s, `CapacitationTheme`s, `CapacitationLessonQSetting`s, `CapacitationLessonQ`s con `CapacitationLessonQOption`s, `CapacitationSegment`s, `ProofSkill`, archivos en disco.
7. Si `publish = true`: despacha `AssignedHighEmployee` en cola `assigned_high_employee_capacitation` + notificaciones push.

FLUJO PRINCIPAL — COLABORADOR REALIZA CURSO (API app):

1. `GET api/capacitations/get_capacitations` → lista agrupada por estado (`pending`, `in_progress`, `completed`, `failed`) filtrando por segmentación, expiración y antigüedad.
2. `POST api/capacitations/create_capacitation_employee` → crea registro en `capacitation_high_employees` con status `pending`.
3. `POST api/capacitations/get_capacitation` → devuelve estructura completa del curso con módulos, lecciones, temas y progreso.
4. `POST api/capacitations/mark_lesson_completed` → marca tema/lección como completado.
5. `POST api/capacitations/save_practice_activity` → sube archivo de actividad práctica.
6. `POST api/capacitations/lesson/get_questions` → obtiene preguntas de evaluación de una lección.
7. `POST api/capacitations/lesson/create_survey` → crea sesión de evaluación.
8. `POST api/capacitations/lesson/save_survey` → guarda respuestas, calcula puntaje, marca aprobado/reprobado.
9. `POST api/capacitations/lesson/question/save_explication` → guarda explicación de una pregunta.
10. `POST api/capacitations/save_satisfaction_survey` → guarda respuestas de encuesta de satisfacción.
11. `POST api/capacitations/get_completion_certificate` → genera y devuelve certificado PDF.
12. `POST api/capacitations/add_days_date` → extiende plazo (agrega días).
13. `POST api/capacitations/save_capacitations` → guarda progreso general del curso.

FLUJOS SECUNDARIOS — REPORTES (admin):

- **Reporte de colaboradores**: `GET admin/capacitation/report/employee/{id}` → lista de colaboradores asignados con su progreso. Filtrable y exportable a Excel (`CapacitationsEmployeeExport`).
- **Reporte de evaluaciones**: `GET admin/capacitation/report/evaluation/{id}` → resultados de evaluaciones por lección. Exportable a Excel (`CapacitationsEvaluationExport`).
- **Reporte de actividades prácticas**: `GET admin/capacitation/lesson/practices_activities/{cap_id}/{employee_id}` → entregas de un colaborador. Descarga individual de archivos.
- **Reporte histórico**: `GET admin/capacitation/report/history` → historial de cursos con filtros. Exportable a Excel (`HistoryReportExport`).
- **Reporte encuesta de satisfacción**: `POST admin/capacitation/report/satisfaction_survey` → exporta a Excel (`CapacitationSatisfactionSurveyResExport`).
- **Reporte respuestas de evaluaciones**: `POST admin/capacitation/report/evaluations_responses` → exporta a Excel (`CapacitationsEvaluationsResponsesExport`).
- **Descarga DC-3**: `POST admin/capacitation/download_dc3` → genera PDF del formato DC-3 para un colaborador.

VALIDACIONES:

- Empresa: required (selección de empresa con áreas, departamentos, puestos y ubicaciones).
- Título: required; unicidad verificada por existencia de directorio en disco.
- Imagen: base64, extensiones image válidas (jpg/jpeg/png).
- Multimedia: upload por chunks (`FileReceiver` + `HandlerFactory` de `pion/laravel-chunk-upload`); tipos: video, audio, archivo, imágenes.
- Preguntas: tipo, texto, opciones con indicador de respuesta correcta, puntaje.

PERMISOS:

- `view_capacitation`: listado de cursos, reportes de colaboradores, evaluaciones, encuesta de satisfacción, respuestas de evaluaciones, desactivar curso.
- `create_capacitation`: crear curso, editar curso, duplicar, obtener razones sociales, subir archivo, obtener logo empresa, historial, actividades prácticas, descarga DC-3, obtener empleados, obtener acuses.
- `edit_capacitation`: editar curso.
- `trash_capacitation`: eliminar curso (soft delete).

SERVICIOS/ENDPOINTS INVOLUCRADOS:

### Admin
| Método | Ruta | Acción |
|--------|------|--------|
| GET | `admin/capacitation/index` | Listado paginado con estadísticas |
| GET | `admin/capacitation/create` | Formulario stepper de creación |
| POST | `admin/capacitation/create` | Guardar curso completo (JSON steps) |
| GET | `admin/capacitation/edit/{id}` | Formulario stepper de edición |
| POST | `admin/capacitation/edit` | Actualizar curso |
| GET | `admin/capacitation/trash/{id}` | Eliminar curso |
| GET | `admin/capacitation/desactivate/{id}` | Desactivar curso |
| GET | `admin/capacitation/duplicate/{id}` | Duplicar curso |
| POST | `admin/capacitation/filter` | Filtros AJAX |
| POST | `admin/capacitation/get_business_names` | Razones sociales de empresa |
| POST | `admin/capacitation/get_employees` | Colaboradores filtrados |
| GET | `admin/capacitation/get_company_logo/{id}` | Logo empresa |
| POST | `admin/upload_file` | Upload de archivo por chunks |
| GET | `admin/capacitation/report/employee/{id}` | Reporte colaboradores |
| POST | `admin/capacitation/filter_report_employee` | Filtros reporte colaboradores |
| GET | `admin/capacitation/report/evaluation/{id}` | Reporte evaluaciones |
| POST | `admin/capacitation/filter_report_evaluation` | Filtros reporte evaluaciones |
| GET | `admin/capacitation/lesson/practices_activities/{cap}/{emp}` | Actividades prácticas |
| GET | `admin/capacitation/download_practice_activity/{id}` | Descarga actividad |
| POST | `admin/capacitation/filter_practices_activities` | Filtros prácticas |
| GET | `admin/capacitation/report/history` | Historial de cursos |
| POST | `admin/capacitation/report/history/filters` | Filtros historial |
| GET | `admin/capacitation/get_acknowledgements/{id}` | Acuses del curso |
| POST | `admin/capacitation/report/satisfaction_survey` | Export encuesta satisfacción |
| POST | `admin/capacitation/report/evaluations_responses` | Export respuestas evaluación |
| POST | `admin/capacitation/download_dc3` | Descarga formato DC-3 |

### API (app colaborador)
| Método | Ruta | Acción |
|--------|------|--------|
| GET | `api/capacitations/get_capacitations` | Listar cursos agrupados por estado |
| POST | `api/capacitations/create_capacitation_employee` | Iniciar curso |
| POST | `api/capacitations/get_capacitation` | Detalle con estructura completa |
| POST | `api/capacitations/mark_lesson_completed` | Marcar tema completado |
| POST | `api/capacitations/save_practice_activity` | Subir actividad práctica |
| POST | `api/capacitations/lesson/get_questions` | Obtener preguntas |
| POST | `api/capacitations/lesson/create_survey` | Crear sesión evaluación |
| POST | `api/capacitations/lesson/save_survey` | Guardar respuestas evaluación |
| POST | `api/capacitations/lesson/question/save_explication` | Guardar explicación |
| POST | `api/capacitations/add_days_date` | Extender plazo |
| POST | `api/capacitations/save_satisfaction_survey` | Guardar encuesta satisfacción |
| POST | `api/capacitations/get_completion_certificate` | Generar certificado PDF |
| POST | `api/capacitations/save_capacitations` | Guardar progreso general |

JOBS/COLAS:

- `AssignedHighEmployee` (cola `assigned_high_employee_capacitation`, tries=3): al publicar un curso, vincula en pivote `high_employee_capacitation` a todos los colaboradores que apliquen según segmentación.
- `AssignedNewOrEditHighEmployee` (cola `assigned_high_employee_capacitation`, tries=3): al crear o editar un colaborador, lo asigna a todos los cursos activos de su empresa que apliquen (segmentación + antigüedad + no expirados). Despachado desde `HighEmployeesLCTJob`.
- `NotificationPush` (cola `low_priority_notifications` u otra): push via OneSignal cuando se activa el curso con notificación habilitada.

NOTIFICACIONES:

- Push (OneSignal): se envía a cada colaborador asignado si `notify = true` al activar el curso. Verifica antigüedad antes de enviar. No se envía si el curso está en borrador.

EXPORTS:

- `CapacitationsEmployeeExport`: listado de colaboradores con progreso.
- `CapacitationsEvaluationExport`: resultados de evaluaciones por lección.
- `CapacitationsEvaluationsResponsesExport`: respuestas detalladas de evaluaciones.
- `CapacitationSatisfactionSurveyResExport`: respuestas de encuesta de satisfacción.
- `HistoryReportExport`: histórico de cursos.

PDF:

- Certificado de finalización: `exports.pdf.capacitations.completion_certificate`.
- Formato DC-3 STPS: `exports.pdf.capacitations.dc3` + `dc3-info`.

CASOS BORDE:

- Curso con directorio existente en disco: se rechaza la creación aunque no exista registro en BD (la validación de unicidad se basa en filesystem, no en BD).
- Colaborador dado de baja (soft delete en `high_employees`): los registros en pivote y `capacitation_high_employees` permanecen; el API filtra por empresa activa.
- Curso expirado: `expired_at` pasó → no aparece en API; en admin sigue visible.
- Curso con `active_by_antique_value`: colaboradores nuevos no ven el curso hasta cumplir la antigüedad mínima.
- Porcentaje 0 en `in_progress`: el API resetea el status a `pending` si el progreso calculado es 0.
- Controlador admin de ~3100 líneas con lógica compleja de edición que reutiliza y modifica toda la estructura de contenido.

⚠️ AMBIGÜEDADES:

- Dos rutas con el mismo name `admin_capacitation_get_company_logo`: `getCompanyLogo` y `getAcknowledgements`.
- `segments_type` vs nomenclatura anterior (`segment_type`, `segments`): las migraciones eliminaron las columnas antiguas y las reemplazaron, pero el modelo y controlador usan `segments_type`.
- Persistencia de `high_employee_capacitation` (pivote) vs `capacitation_high_employees` (tabla propia): ambas coexisten; el pivote se usa para la relación `receivers()` y la tabla para tracking de progreso.
- Tipo y format de `remaining_time`, `tries_survey`, `tries_lesson`: son strings sin formato definido documentado.

🔧 DEUDA TÉCNICA:

- Controlador admin de **~3100 líneas** con toda la lógica de CRUD + reportes + exports + DC-3 en una sola clase.
- `ini_set('memory_limit', '-1')` y `ini_set('MAX_EXECUTION_TIME', 0)` en métodos del controlador.
- Unicidad de curso validada por existencia de directorio en disco en lugar de constraint de BD.
- Archivos multimedia almacenados directamente en el filesystem del servidor (`assets/companies/capacitation/`), no en storage configurado.
- Upload de videos por chunks con archivos temporales en `assets/companies/capacitation/files/` que se mueven manualmente con `rename()`.
- Imagen de portada almacenada como base64 en el request y decodificada a archivo con `file_put_contents`.
- N+1 queries en listados (acceso a `company`, `user`, `modules`, `lessons` por cada capacitación en loops).
- Fillable del modelo `CapacitationHighEmployee` tiene un typo: `'completed_at'.'percent'` (concatenación accidental de strings en lugar de comma-separated).

📌 DIFERENCIAS CON TECBEN-CORE:

- LEGACY: módulo completo con ~14 tablas, stepper de creación, contenido multimedia, evaluaciones, encuestas de satisfacción, certificados, DC-3, reportes Excel, push, asignación automática a nuevos colaboradores.
- TECBEN-CORE: solo permisos legacy mapeados (`view_capacitacion`, `create_capacitacion`, `delete_capacitacion`), campo `descargar_cursos` en modelo `Empresa` (equivalente a `has_download_capacitation`). Sin modelo, migración, recurso Filament ni API equivalente.
