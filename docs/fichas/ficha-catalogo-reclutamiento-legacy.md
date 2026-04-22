MÓDULO: Catálogo de Reclutamiento (legacy Paco)
FECHA ANÁLISIS: 2026-04-08
ANALIZADO POR: Agente paco-legacy
ESTADO EN TECBEN-CORE: Implementado parcial (existe página Tableau `ReclutamientoTableauPage`, config en `tableau_reports.php`, permisos en `ShieldPermisosLegacySeeder`; no se localizó modelo CRUD de vacantes ni candidatos equivalente en Filament)

---

## DESCRIPCIÓN GENERAL

El catálogo de **Reclutamiento** en Paco es un contenedor que agrupa el submódulo de **Vacantes** y la sección de **Analíticos de Reclutamiento** (Tableau). En el sidebar aparece como menú desplegable "Reclutamiento" con el único ítem "Vacantes" para los usuarios con `view_recruitments`.

El catálogo en sí no tiene una tabla propia; la entidad raíz es `recruitments` (una fila = una vacante). Los candidatos, mensajes, estatus y formularios dinámicos son entidades dependientes.

---

ENTIDADES:

- `recruitments`: `id`, `company_id` (FK), `created_by` (FK a `users`), `name` (en migración; ⚠️ no usado en controlador ni modelo), `position`, `requirements` (longText), `aptitudes` (longText), `benefits` (longText), `url` (string), `timestamps`, `deleted_at` (SoftDeletes) | `belongsTo` Company; `hasMany` RecruitmentCandidates; `hasMany` RecruitmentForm.
- `recruitment_candidates`: tabla de candidatos postulados (ver ficha de Vacantes para detalle completo).
- `recruitment_candidate_messages`: comentarios de usuarios admin sobre un candidato.
- `recruitment_candidate_statuses`: historial de cambios de estatus de un candidato con duración calculada.
- `recruitment_forms`: campos dinámicos del formulario de postulación, configurados por vacante.

REGLAS DE NEGOCIO:

- RN-01: La sección "Reclutamiento" (menú sidebar) requiere permiso `view_recruitments`. El primer enlace lleva al reporte Tableau de Reclutamiento; el desplegable contiene "Vacantes".
- RN-02: Cada `Recruitment` pertenece a una `Company`; si el usuario admin tiene empresa asignada, solo ve vacantes de su empresa.
- RN-03: Admin sin empresa asignada (superadmin) puede ver todas las vacantes de todas las empresas.
- RN-04: En analíticos Tableau, se renderiza un dashboard Tableau embebido en la ruta `admin/tableau/recruitment` (permiso `view_recruitments`).

FLUJO PRINCIPAL:

1. Usuario admin con `view_recruitments` abre menú "Reclutamiento" y ve la opción "Vacantes" y "Reclutamiento (Tableau)".
2. Si accede a Tableau → vista analítica embebida.
3. Si accede a Vacantes → listado de vacantes (ver ficha de Vacantes).

PERMISOS:

- `view_recruitments`: ver listado de vacantes, ver detalle, filtros, Tableau.
- `create_recruitments`: crear vacante.
- `edit_recruitments`: editar vacante.
- `trash_recruitments`: eliminar vacante.
- `view_recruitments_candidates`: ver candidatos de una vacante.
- `edit_recruitments_candidates`: editar candidato, subir archivos, agregar comentarios, cambiar estatus.
- `trash_recruitments_candidates`: eliminar candidato.
- `trash_recruitments_candidates_comment`: eliminar comentario de candidato.

SERVICIOS/ENDPOINTS INVOLUCRADOS:

- `GET admin/tableau/recruitment` — vista Tableau embebida de reclutamiento.
- Todos los endpoints de Vacantes y Candidatos (ver ficha de Vacantes para detalle).

RUTAS PÚBLICAS (sin autenticación):

- `GET {company_id}/recruitment` — redirige al formulario de la primera vacante de la empresa.
- `GET {company_id}/recruitment/{position}` — redirige al formulario público de una vacante por posición.
- `GET {company_name}/recruitment/vacancy/{position}` — formulario público de postulación para una vacante.
- `POST admin/recruitments/candidates/create` — crear candidatura desde formulario público (sin middleware auth).

JOBS/COLAS:

- `EmployeeHistoryJob` (cola `employee_history_create`): despacho vía Observer en `created` y `updated` de `RecruitmentCandidates` si el candidato tiene CURP válido de 18 caracteres. Tipo `R` (reclutamiento).

NOTIFICACIONES:

- No hay notificaciones push ni email asociadas al módulo de reclutamiento/vacantes en el código legacy.

⚠️ AMBIGÜEDADES:

- El campo `name` existe en la migración `create_recruitments_table` pero NO se usa en ningún controlador ni vista; `position` actúa como nombre/título de la vacante.
- La ruta pública `POST admin/recruitments/candidates/create` NO tiene middleware de autenticación (`logged`, `2fa`), pero el path empieza con `admin/` (naming inconsistente).

🔧 DEUDA TÉCNICA:

- Relación en modelo `Recruitment` se llama `recruitment_cadidates()` (typo: falta la "n" en candidates).
- `getList()` carga todas las vacantes en memoria con HTML embebido en PHP; patrón DataTables legacy.

📌 DIFERENCIAS CON TECBEN-CORE:

- LEGACY: CRUD completo de vacantes y candidatos con formularios dinámicos, validación de documentos (INE, CSF, comprobante domicilio, CV), historial de estatus, mensajes, export Excel/PDF.
- TECBEN-CORE: solo existe la página de analíticos Tableau (`ReclutamientoTableauPage`) y permisos mapeados en seeders. No hay recurso Filament para gestión de vacantes ni candidatos.
