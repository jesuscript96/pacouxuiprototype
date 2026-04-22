# Reglas de negocio — Proyecto Legacy Paco

**Propósito:** Documento de referencia de las reglas de negocio del sistema Paco (legacy) para soportar el diseño y la migración a tecben-core.  
**Alcance:** Validaciones, cálculos, flujos, seguridad, integraciones y casos borde por módulo.  
**Fuente:** Análisis de código (controladores, modelos, requests, jobs) — solo lectura.

---

## Índice

1. [Empresas (Companies)](#1-módulo-empresas-companies)
2. [Empleados (High Employees)](#2-módulo-empleados-high_employees)
3. [Usuarios y autenticación](#3-módulo-usuarios-users-y-autenticación)
4. [Roles y permisos (Entrust)](#4-módulo-roles-y-permisos-entrust)
5. [Nómina y financiero](#5-módulo-nómina-y-financiero)
6. [Voz del colaborador](#6-módulo-voz-del-colaborador-voice_employees)
7. [Reconocimientos](#7-módulo-reconocimientos-acknowledgments)
8. [Encuestas](#8-módulo-encuestas-surveys)
9. [Chat](#9-módulo-chat)
10. [Notificaciones push](#10-módulo-notificaciones-push)
11. [Documentos y contratos](#11-módulo-documentos-y-contratos)
12. [Solicitudes y aprobaciones](#12-módulo-solicitudes-y-aprobaciones)
13. [Capacitación y DC3](#13-módulo-capacitación-y-dc3)
14. [Integraciones externas](#14-módulo-integraciones-externas)
15. [Reglas globales](#15-módulo-reglas-globales)
16. [Glosario](#16-glosario)
17. [Anexo: código relevante](#17-anexo-código-relevante)

---

## 1. MÓDULO: Empresas (Companies)

### REGLAS DE CREACIÓN

- Para crear una empresa deben existir: industrias con subindustrias, subindustrias, productos, puestos, departamentos (`CompaniesController::getCreate`).
- Campos obligatorios en creación (`CompaniesController::create`):
  - `general_name`, `contact_name`, `contact_email`, `contact_phone`, `contact_mobile` (teléfonos numéricos, máx. 10 dígitos).
  - `billing_email` (email válido).
  - `contract_start`, `contract_end` (fechas).
  - `commission_type`: uno de `PERCENTAGE`, `FIXED_AMOUNT`, `MIXED`.
  - Comisiones según tipo: si no es MIXED, son obligatorias y numéricas ≥ 0: `biweekly_commission`, `monthly_commission`, `fourteen_monthly_commission`, `weekly_commission`, `payment_gateway_commission`.
  - Si `commission_type` = MIXED: se requiere al menos un rango en `commission_rank` con `price_from`, `price_until` (until > from), `fixed_amount`, `percentage` (0–100).
  - `report_users`: numérico.
  - Por cada razón social: `optional_business_name`, `rfc`, `cp`, `street`, `number`, `suburb`, `town`, `state`.
  - Por producto: `unit_price`, `base_price` (formato con decimales), `enable_from` (numérico ≥ 0), `variation_margin` (numérico ≥ 0).

### REGLAS DE ACTUALIZACIÓN

- La empresa se edita desde el mismo controlador; las validaciones de productos y comisiones aplican según el tipo de comisión.
- `is_active` controla si la empresa está activa; se usa en login de app (ej. `company->is_active != 'SI'` bloquea acceso).

### REGLAS DE ELIMINACIÓN

- La tabla `companies` usa **SoftDeletes** (modelo `Company`). Al “eliminar” se llena `deleted_at`; no se borra el registro.
- Relaciones (usuarios, empleados, productos, etc.) siguen apuntando por FK; el scoping por empresa puede excluir empresas eliminadas si se usa `withoutTrashed()` donde corresponda. Verificar en rutas de listados.

### REGLAS DE ESTADO

- **is_active:** valor 'SI' / 'NO' (u otro según BD). Si no es 'SI', los empleados de esa empresa no pueden iniciar sesión en la app (`AuthController`).

### REGLAS DE VALIDACIÓN

- `contact_phone`, `contact_mobile`: numéricos, `digits_between:0,10`.
- Emails: formato `email`.
- Fechas: `date`.
- Precios: regex con decimales; comisiones numéricas min 0; percentage max 100 en rangos mixtos.

### REGLAS DE CÁLCULO

- Comisiones por tipo: PERCENTAGE/FIXED_AMOUNT usan los campos directos por periodicidad; MIXED usa `commission_ranks` (rango por precio) para calcular monto o porcentaje.

### REGLAS DE SEGURIDAD

- Acceso al CRUD de empresas: usuarios del panel (admin); no se encontró middleware por rol en el fragmento leído; en otros módulos se usa `hasRoles('admin')`. Verificar rutas en `routes/`.

### REGLAS DE INTEGRACIÓN

- No hay integración externa directa en creación de empresa; centros de costo Belvo/EMIDA/STP se seleccionan desde catálogos (`CostCenter` por service).

### CASOS BORDE CONOCIDOS

- Crear empresa sin productos o sin industrias/subindustrias: el formulario de creación no se muestra (redirect con errores).
- Contrato: `contract_end` puede ser anterior a `contract_start` si no hay validación adicional; conviene validar en tecben-core.

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Admin/CompaniesController.php` (create, getCreate, validaciones ~líneas 152–250).
- `app/Models/Company.php` (fillable, SoftDeletes, relaciones).

---

## 2. MÓDULO: Empleados (High Employees)

### REGLAS DE CREACIÓN

- Alta de empleados desde panel (carga manual o masiva) y posiblemente desde API; el empleado pertenece a una `company_id`.
- Datos típicos: nombre, apellidos, email, móvil, fecha ingreso, puesto, departamento, ubicación, área, empresa, productos asignados (pivot `high_employee_product` con `status`, `reason`, `change_type`).

### REGLAS DE ACTUALIZACIÓN

- El modelo usa **SoftDeletes**; al “dar de baja” se hace soft delete.
- Productos del empleado: se actualizan vía pivot. Estados en pivot: `ACTIVO`, `INACTIVO`. `change_type`: `AUTOMATIC` o `MANUAL`. `reason`: texto (ej. "INCUMPLIMIENTO DE PAGO"); si es INCUMPLIMIENTO DE PAGO no se reactiva automáticamente por filtros de producto (`CheckProductFilters`).

### REGLAS DE ELIMINACIÓN

- **Soft delete:** `HighEmployee` usa `SoftDeletes`; al eliminar se setea `deleted_at`. Relaciones que usan `withTrashed()` pueden seguir mostrando el empleado (ej. User→high_employee).

### REGLAS DE ESTADO (productos del empleado)

- **Estados en pivot high_employee_product:** `ACTIVO`, `INACTIVO`.
- **Transiciones:**
  - A INACTIVO: manual (con reason) o automático (p. ej. por filtros de producto o incumplimiento).
  - A ACTIVO: automático por job `CheckProductFilters` solo si el pivot no es MANUAL con reason "INCUMPLIMIENTO DE PAGO"; si cumple filtros de producto y estaba INACTIVO con change_type AUTOMATIC y reason distinta, se pasa a ACTIVO y reason vacía.
- **enable_from:** meses desde fecha de admisión para que el producto esté “habilitado” para el empleado; se usa en vistas (ej. VoiceEmployeesController::getViewEmployee) para mostrar si el producto aplica según antigüedad.

### REGLAS DE VALIDACIÓN

- Validación con IMSS/Nubarium: el modelo `HighEmployee` tiene `validationEmployeeHistory()` que, si la empresa tiene `transactions_with_imss == 'SI'`, busca en `EmployeeHistory` por CURP y empresa y comprueba historial laboral y razón social. Ver `app/Models/HighEmployee.php`.

### REGLAS DE CÁLCULO

- **enable_from:** meses desde `admission_date`; si `meses >= product.pivot.enable_from` el producto se considera habilitado para ese empleado.
- **code_boss:** atributo calculado `location_id.department_id.area_id.position_id` (concatenación).

### REGLAS DE SEGURIDAD

- Listados de empleados filtrados por empresa del usuario o por filtros guardados (`high_employee_filters`) del usuario.

### CASOS BORDE CONOCIDOS

- Empleado con productos INACTIVO por INCUMPLIMIENTO DE PAGO: el job de filtros de producto no los reactiva.
- Reingresos: existe `readmissions` y `readmission_histories`; lógica de reingreso en `LowEmployeesController` (reasignar productos, notificaciones, encuestas).

### CÓDIGO RELEVANTE

- `app/Models/HighEmployee.php` (fillable, SoftDeletes, validationEmployeeHistory, hasProducts, hasProductsActive).
- `app/Jobs/ProductFilters/CheckProductFilters.php` (reactivación automática de productos).
- `app/Http/Controllers/Admin/HighEmployeesController.php`, `app/Http/Controllers/Admin/LowEmployeesController.php` (baja, productos, reingresos).

---

## 3. MÓDULO: Usuarios (Users) y autenticación

### REGLAS DE CREACIÓN

- Usuarios se crean desde el panel (admin). No hay Request específico de creación en los archivos revisados; validación puede estar inline en `UsersController`.
- Tipos vistos en código: `user`, `high_user`, `high_employee`. `high_employee` es el que puede iniciar sesión en la app y debe tener `high_employee_id` y relación con empleado.

### REGLAS DE ACTUALIZACIÓN

- `update_password` (SI/NO): si es SI, se puede forzar cambio de contraseña; `last_password_update` guarda la última actualización.
- 2FA: `enable_2fa`, `google2fa_secret`, `verified_2fa_at`; activación/desactivación desde `Google2FAController`.

### REGLAS DE ELIMINACIÓN

- No se encontró SoftDeletes en el modelo `User`; eliminación sería física. Verificar en `UsersController` si existe soft delete.

### REGLAS DE PASSWORD

- Login app: `LoginRequest` exige `password` required y string; mobile/email opcionales pero al menos uno para identificar.
- Si `update_password` = 'SI', la aplicación puede redirigir a cambio de contraseña (verificar en middleware o controlador).

### REGLAS DE 2FA

- Campos: `google2fa_secret`, `enable_2fa`, `verified_2fa_at`. Activación/verificación en `Google2FAController`. No hay regla explícita de “obligatorio para cierto rol” en el código revisado; se puede definir por política en tecben-core.

### REGLAS DE VINCULACIÓN CON EMPLEADOS

- **1:1 opcional:** `users.high_employee_id` → `high_employees.id`. Un usuario puede no tener empleado (admin/backoffice). Un empleado puede no tener usuario (no tiene cuenta aún).
- Un usuario no puede tener múltiples empleados; un empleado no tiene múltiples usuarios (una sola cuenta User por empleado en el diseño actual).

### REGLAS DE SEGURIDAD

- Panel: guard `web`, provider `users`, modelo `App\User`.
- API/App: guard `api` (Passport), mismo provider; login devuelve token Passport. Solo usuarios con `type` = 'high_employee' y con `high_employee` relacionado pueden hacer login desde la app (según `AuthController`).

### CÓDIGO RELEVANTE

- `app/User.php`, `config/auth.php`.
- `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Controllers/Api/AuthController.php`, `app/Http/Requests/LoginRequest.php`, `app/Http/Requests/SignupRequest.php`.

---

## 4. MÓDULO: Roles y permisos (Entrust)

### REGLAS DE CREACIÓN

- Roles: tabla `roles` (name, display_name, description); luego se añade `company_id` (roles por empresa).
- Permisos: tabla `permissions`. Asignación rol–permiso vía `permission_role`; asignación usuario–rol vía `role_user`.

### REGLAS DE ASIGNACIÓN

- Un usuario puede tener **múltiples roles** (belongsToMany en User).
- **current_rol:** se guarda en sesión (`getCurrentRol()` en User); si no hay en sesión se toma el primer rol del usuario. Se usa para operaciones que dependen del “rol actual”.

### REGLAS POR EMPRESA

- Los roles tienen `company_id`; los roles son por empresa. Al asignar roles a un usuario, típicamente se asignan roles de su `company_id`. Verificar en `RolesController` que no se asignen roles de otra empresa.

### REGLAS DE SEGURIDAD

- Verificación con `$user->hasRoles('admin')` o `hasRoles(['admin', 'otro'])`. No se encontró middleware genérico `role:` en el fragmento; la comprobación es manual en controladores (ej. SurveysController, PayrollReceiptsController).

### CÓDIGO RELEVANTE

- `app/Models/Role.php`, `app/User.php` (EntrustUserTrait, roles(), getCurrentRol, hasRoles).
- `app/Http/Controllers/Admin/RolesController.php`.
- Migración Entrust y `update_roles_table` (company_id).

---

## 5. MÓDULO: Nómina y financiero

### REGLAS DE CUENTAS (accounts)

- Un empleado puede tener **múltiples cuentas** (hasMany). Cuenta tiene `status`: p. ej. `verified`, `unverified`. Solo cuentas con `status` = 'verified' se usan para adelantos y operaciones que requieren cuenta verificada.
- No se puede eliminar una cuenta si: el estado de cuenta actual tiene transacciones por liquidar; la cuenta está en una cuenta por cobrar PENDIENTE; o si es la única cuenta verificada (debe haber al menos 2 verificadas para borrar una).

### REGLAS DE ESTADOS DE CUENTA (account_states)

- Estados: p. ej. `ACTIVO` (el vigente). El saldo disponible para adelanto viene de `account_state->balance` o `balance_without_commission`.

### REGLAS DE TRANSACCIONES

- Transacciones tienen `status`: `EXITOSA`, `FALLIDA`, `EN PROCESO`. Tipo de pago: p. ej. `SALDO DEL SISTEMA`. Una transacción exitosa con tipo “SALDO DEL SISTEMA” se considera primera transacción para flujos que requieren “primera transacción”.

### REGLAS DE ADELANTOS (payroll_advances)

- **Quién solicita:** empleado (vía app), identificado por el User con type high_employee.
- **Condiciones:** empleado debe tener al menos una cuenta verificada; no debe tener cuentas por cobrar en estado PENDIENTE (por incumplimiento se bloquea con mensaje “Servicio no disponible por incumplimiento de pago”).
- **Monto máximo:** viene del estado de cuenta activo (`balance` o `balance_without_commission`); también se usa `high_employee->max_amount` en otros contextos (ej. PDF domiciliación).
- **Estados de transacción:** EXITOSA, FALLIDA, EN PROCESO; el resultado del pago externo actualiza el estado.
- Flujo: solicitud → transacción → respuesta del proveedor (éxito/error) → actualización de estado; si está EN PROCESO hay flujo de consulta posterior.

### REGLAS DE RECIBOS DE NÓMINA (payroll_receipts)

- Se asocian a `high_employee_id`. Contienen `initial_payment_date`, `final_date_payment`, `pdf_string`, `fiscal_folio`. Generación vía job `CreatePayrollReceiptJob` (carga de archivo/XML). Solo usuarios con rol admin pueden realizar ciertas acciones (ej. en PayrollReceiptsController).

### REGLAS DE RETENCIONES (payroll_withholding_configs)

- Por empresa: `company_id`. Campos: `date`, `days`, `weekday`, `emails`, `payment_periodicity` (SEMANAL, CATORCENAL, QUINCENAL, MENSUAL). Define cuándo y cómo se aplican retenciones.

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Api/PayrollAdvancesController.php` (cuentas, adelantos, validaciones, estados).
- `app/Jobs/PayrollReceipts/CreatePayrollReceiptJob.php`, `app/Http/Controllers/Admin/PayrollReceiptsController.php`.
- Modelos: `Account`, `AccountState`, `Transaction`, `ReceivableAccount`, `PayrollReceipt`, `PayrollWithholdingConfig`.

---

## 6. MÓDULO: Voz del colaborador (voice_employees)

### REGLAS DE CREACIÓN

- **Quién envía:** solo high_employees (desde la app, el usuario autenticado es un User con high_employee).
- **Obligatorios:** `comments`, `voice_employee_subject_id`. Opcional: `other_subject`; si se envía, el tema puede mostrarse como “Otro”.
- **Estado inicial:** 'Pendiente'. `is_anonyme` indica si es anónimo; el `high_employee_id` se guarda igual (anonimato es de presentación en UI).
- **Imágenes:** se guardan en `assets/voice_employees/{id}-{index}.png`; el campo `images` guarda cantidad. Reapertura: si se envía un comentario sobre un voice_employee existente, se crea `VoiceEmployeeExtra` o se actualiza el último extra con comments vacío; el estado del voice_employee vuelve a 'Pendiente'.

### REGLAS DE ACTUALIZACIÓN

- **Estados:** 'Pendiente', 'En Proceso', 'Atendido', 'Continuar conversación'. Al abrir un comentario en panel: si estaba 'Pendiente' se cambia a 'En Proceso', se asigna `user_id` (lector) y se notifica al empleado.
- Al actualizar estado se puede setear: `attention_date`, `attenuator_id` (quien atiende), `results`, `priority`, `assigned_id` (usuario asignado), `voice_employee_subject_id`. Si ya había atención previa, se crea o actualiza `VoiceEmployeeExtra` con los nuevos results y attenuator_id.
- Al marcar "Atendido" o "Continuar conversación" se envía notificación push al empleado (si tiene user y tokens OneSignal).

### REGLAS DE ELIMINACIÓN

- **VoiceEmployee:** delete físico (no soft delete en el modelo revisado). Se elimina archivo en `assets/voice_employees/{id}.png` si existe. Se registra en Log.
- **VoiceEmployeeExtra:** delete físico; también se borra imagen asociada si existe.

### REGLAS DE ESTADO

- **Estados:** Pendiente → En Proceso (al abrir/asignar) → Atendido o Continuar conversación. "Continuar conversación" permite seguir añadiendo extras y mantener el hilo.
- **user_id:** usuario que leyó primero (cuando pasa a En Proceso). **attenuator_id:** usuario que atiende/responde. **assigned_id:** usuario asignado para dar seguimiento.

### REGLAS DE VALIDACIÓN

- API create: `comments` required, `voice_employee_subject_id` required. Sin longitud mínima/máxima explícita en el Request revisado.

### REGLAS DE SEGURIDAD

- Listado en panel: por empresa del usuario y/o filtros de empleados del usuario; además por temas de la empresa (`company_voice_employee_subject`) y por temas del usuario (`user_voice_employee_subject`). Solo se ven comentarios cuyos temas coinciden.
- Asignación: solo usuarios tipo `high_user` de la misma empresa se listan para asignar.
- Notificación por email a usuarios con `notification_voice_employees` = 'SI' y que tengan el tema en sus temas asignados (y opcionalmente filtros de empleados que incluyan al emisor).

### REGLAS DE INTEGRACIÓN

- **Tableau:** se crea/actualiza `VoiceEmployeeTableu` con comments/results (texto sin HTML) para sincronización.
- **ChatGPT:** job `ChatGPTVerification` para verificación de comentarios, excluyendo empresas en `config('app.chat_gpt_excluded_companies')`.

### CASOS BORDE CONOCIDOS

- Anónimo: en búsqueda se muestra "Anónimo" cuando `is_anonyme` = ANONIMO (consulta con IF en raw).
- Reapertura: si el admin envía mensaje vacío y ya existe un extra con comments vacío, la lógica evita crear otro extra vacío (se reutiliza el último vacío si el anterior tiene contenido).

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Admin/VoiceEmployeesController.php` (getIndex, specificVoiceEmployee, updateStatus, Trash, getFilters).
- `app/Http/Controllers/Api/VoiceEmployeesController.php` (create, index, getSent, getSubjects).
- `app/Models/VoiceEmployee.php`, `app/Models/VoiceEmployeeExtra.php`.

---

## 7. MÓDULO: Reconocimientos (Acknowledgments)

### REGLAS DE CONFIGURACIÓN POR EMPRESA

- **acknowledgment_company:** pivot entre empresa y reconocimiento. Campos en pivot: `is_shippable` (ej. 'ENVIABLE'), `necessary_mentions` (cantidad de menciones necesarias). Un reconocimiento puede estar en varias empresas con distinta configuración.

### REGLAS DE ENVÍO

- **Quién envía:** usuarios del panel (no se restringe por rol en el fragmento leído). Se crean `AcknowledgmentShipping` dirigidos a empleados.
- **A quién:** high_employees; se relacionan por pivot `acknowledgment_high_employee` con `status` y `reaction`. Los reconocimientos enviables son los que la empresa tiene con `is_shippable` (ej. ENVIABLE).

### REGLAS DE RECEPCIÓN

- El empleado recibe el envío; en el pivot se guarda estado de lectura y reacción. Valores de status/reaction dependen de la implementación en vistas y API (ver AcknowledgmentShipping, acknowledgment_high_employee).

### REGLAS DE VALIDACIÓN

- Acknowledgment tiene `is_shippable`, `is_exclusive` (ej. EXCLUSIVO); en listados se muestran con etiquetas diferenciadas.

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Admin/AcknowledgmentsController.php`, `app/Http/Controllers/Api/AcknowledgmentsController.php`.
- `app/Models/Acknowledgment.php` (SoftDeletes), `app/Models/AcknowledgmentShipping.php`.

---

## 8. MÓDULO: Encuestas (Surveys)

### REGLAS DE CREACIÓN

- **Quién crea:** usuarios del panel; encuesta tiene `user_id` (creador). Si el usuario tiene rol `admin`, puede ver/editar encuestas de todas las empresas; si no, solo las de su empresa o las que él creó (según lógica en SurveysController).
- Tipos: NOM35, clima, personalizadas; existen categorías, secciones, preguntas, dimensiones, etc.

### REGLAS DE ENVÍO

- **survey_shippings:** creados por un user; se vinculan a empleados vía `high_employee_survey_shipping` con status (ej. 'Not answered', 'Answered'). Destinatarios se seleccionan por filtros (empresa, ubicación, etc.) o por filtros guardados del usuario.
- Si un empleado no responde, el status en el pivot permanece como no respondido; hay comandos/jobs para recordatorios (ej. SendScheduledSurveys).

### REGLAS DE RESPUESTA

- Respuestas en `survey_responses`, `section_responses`; totales en `survey_totals`. NOM35 tiene secciones y valores específicos (`nom35_sections`, `nps_values`, etc.).

### REGLAS DE SEGURIDAD

- Acceso a encuestas: `$user->hasRoles('admin')` permite ver todas; si no es admin, se filtra por creador o por empresa del usuario. Encuestas pueden tener `company_id` (en migraciones posteriores).

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Admin/SurveysController.php` (hasRoles('admin') en múltiples puntos).
- Modelos: `Survey`, `SurveyShipping`, `SurveyResponse`, `SurveyTotal`, `SurveyQuestion`, `SurveySection`.

---

## 9. MÓDULO: Chat

### REGLAS DE CREACIÓN DE SALAS

- **chat_rooms:** tienen `type` ('private', 'group', 'channel'), `created_by` (FK a **high_employees.id**, no users). SoftDeletes en sala.
- Creación desde app por empleados (high_employee).

### REGLAS DE PARTICIPACIÓN

- **chat_room_users:** pivot con `user_id` que referencia **high_employees.id** (no users). Campos: `is_admin`, `can_send_messages`, `last_read_message_id`, etc. Un empleado puede estar en varias salas.

### REGLAS DE MENSAJES

- **chat_messages:** `user_id` es el remitente y referencia **high_employees.id**. Tipos: text, file, image, video, audio, system, other. SoftDeletes en mensajes. No se encontraron reglas explícitas de edición/eliminación en el fragmento; ver ChatController.

### REGLAS DE ESTADO (entregado, leído)

- **chat_message_status:** por mensaje y por usuario (high_employee); indica si fue entregado/leído por cada participante. **chat_message_mentions** y **chat_message_reactions** también usan `user_id` como high_employee_id.

### CÓDIGO RELEVANTE

- Migraciones: `create_chat_rooms_table`, `create_chat_room_users_table` (FK a high_employees), `create_chat_messages_table`, `create_chat_message_status_table`, etc.
- `app/Http/Controllers/Api/ChatController.php`, modelos `ChatRoom`, `ChatRoomUser`, `ChatMessage`, `ChatMessageStatus`, `ChatMessageMention`, `ChatMessageReaction`.

---

## 10. MÓDULO: Notificaciones push

### REGLAS DE CREACIÓN

- **Quién crea:** usuarios del panel (notifications tienen `user_id`). Tipos: p. ej. 'VOZ DEL EMPLEADO LEIDO', 'VOZ DEL EMPLEADO ATENDIDO', 'Adelanto de nómina disponible', etc.
- Destinatarios: high_employees vinculados por pivot `high_employee_notification` con status (ej. 'NO LEIDA', 'LEIDA').

### REGLAS DE PROGRAMACIÓN

- **notifications_frequencies:** por empresa (`company_id`). Campos: `days`, `type`, `next_date`. Define cada cuántos días y cuándo es la próxima fecha por tipo de notificación programada. Usado en comandos/jobs que envían notificaciones programadas (SendScheduledNotificationsPush).

### REGLAS DE EXCLUSIÓN

- **excluded_notifications:** por empresa; lista de notificaciones (o tipos) que la empresa tiene excluidos y no reciben.

### REGLAS DE ENVÍO

- Destinatarios: por filtros (companies_filter, locations_filter, etc.) o por filtros guardados del usuario; solo se envían push a empleados que tengan User vinculado y que ese User tenga `one_signal_tokens`. Se usa el `app_setting` de la empresa (OneSignal app id, rest api key, android channel) para enviar.

### CÓDIGO RELEVANTE

- `app/Models/Notification.php`, `app/Models/NotificationFrequency.php`, `app/Models/ExcludedNotification.php`, `app/Models/OneSignalToken.php`.
- `app/Console/Commands/SendScheduledNotificationsPush.php`, `app/Http/Controllers/Admin/NotificationPushController.php`, jobs `NotificationPush`.

---

## 11. MÓDULO: Documentos y contratos

### REGLAS DE DOCUMENTOS DIGITALES

- **digital_documents:** tienen `company_file_id`, `user_id` (responsable/creador), `name`, `business_name`, `needs_authorization`, `is_exclusive`. Subida desde panel; asociados a empresa vía company_file.
- Firmas: `digital_document_signs_locations`; reglas de firma dependen de integración (Nubarium u otra).

### REGLAS DE CONTRATOS LABORALES

- **employment_contracts:** asociados a high_employee. **employment_contracts_tokens:** token por usuario (user_id) y contrato; tipo, token, signature_date; SoftDeletes. Usados para flujo de firma (ej. ZapSign).

### REGLAS DE TESTIGOS

- **witnesses:** `user_id` (usuario que es testigo), `digital_document_id` o tipo polimórfico según migración. En legacy una migración usa `digital_document_id` y `type`.

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Admin/EmploymentContractController.php`, `app/Http/Controllers/Api/EmploymentContractsController.php`, `app/Http/Controllers/Api/DigitalDocumentsController.php`.
- Modelos: `DigitalDocument`, `EmploymentContract`, `EmploymentContractToken`, `Witness`.

---

## 12. MÓDULO: Solicitudes y aprobaciones

### REGLAS DE CREACIÓN

- **requests:** creadas por empleados (high_employee_id). Tienen tipo (`requests_type`), categoría (`request_categories`, por empresa), estado (`requests_status`).
- Flujo de aprobación: `approval_flow_stages` (etapas); `authorization_stage_approvers` (aprobadores por etapa, high_employee_id). Los aprobadores son empleados (p. ej. jefes).

### REGLAS DE FLUJO

- Las etapas definen el orden de aprobación; cada etapa puede tener uno o más aprobadores. Si se rechaza, el estado de la solicitud cambia (ver RequestStatus, status_histories). Transiciones exactas dependen del controlador de solicitudes.

### REGLAS DE HISTORIAL

- **status_histories:** registran cambios de estado de la solicitud; quién y cuándo.

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Api/RequestsController.php`, `app/Http/Controllers/Admin/` (buscar Request).
- Modelos: `Request`, `RequestType`, `RequestStatus`, `RequestCategory`, `ApprovalFlowStage`, `AuthorizationStageApprover`, `StatusHistory`.

---

## 13. MÓDULO: Capacitación y DC3

### REGLAS DE CURSOS

- **capacitations:** creadas por usuario (`user_id`), empresa (`company_id`). Tienen lecciones (`capacitation_lessons`), módulos, temas, cuestionarios, fechas de vigencia, obligatoriedad, etc.
- Asignación a empleados: `high_employee_capacitation` (pivot). Progreso: `capacitation_lesson_completed`, respuestas a preguntas, actividades prácticas.

### REGLAS DE ASIGNACIÓN

- Se asignan por segmentos (por antigüedad, área, etc.) o manualmente. Jobs como `AssignedNewOrEditHighEmployee` asignan capacitaciones a empleados según criterios de segmento.

### REGLAS DE DC3

- **proof_skills** y tablas relacionadas (objectives, areas, modalities, agents); **address_proof** para comprobante de domicilio. Validez y generación de constancias según implementación en controladores y servicios.

### CÓDIGO RELEVANTE

- `app/Http/Controllers/Admin/CapacitationController.php`, `app/Http/Controllers/Api/CapacitationController.php`.
- `app/Jobs/Capacitations/AssignedNewOrEditHighEmployee.php`, modelos `Capacitation`, `CapacitationHighEmployee`, `ProofSkill`, etc.

---

## 14. MÓDULO: Integraciones externas

### Belvo (débito directo)

- **direct_debit_belvos:** por usuario (`user_id`), fecha. **belvo_payment_requests**, **belvo_payment_methods**, **belvo_direct_debit_customers:** vinculados a empleados/cuentas para cobros. Reglas de vinculación y cobro en controladores de pasarela y jobs (CreateClientBelvoJob, UpdateClientBelvo, etc.).

### Tableau

- **voice_employees_tableu:** copia de comments/results de voice_employees para reporting. **messages_tableu** para mensajes. Actualización al cambiar comentarios o mensajes.

### IMSS / Nubarium

- Validación de empleados cuando `company.transactions_with_imss` = 'SI'; uso de `EmployeeHistory`, `EmploymentHistory`, Nubarium (INE, IMSS). Validación de cuentas (validate_accounts_automatically); Nubarium para firma (has_nubarium_sign). Ver `VerificationAppController`, `AuthController`, modelos `IneNubarium`, `ImssNubariumLog`.

### OneSignal

- Tokens en `one_signal_tokens` (user_id → users). Envío usando `app_setting` de la empresa (one_signal_app_id, rest_api_key, android_channel_id). Segmentación por destinatarios de la notificación (high_employees) y luego por tokens del User vinculado al empleado.

### CÓDIGO RELEVANTE

- Jobs en `app/Jobs/` (Belvo, Notifications), `app/Http/Controllers/Admin/PaymentGatewayController.php`, `app/Http/Controllers/Api/VerificationAppController.php`, `app/Http/Controllers/Api/AuthController.php` (validación Nubarium/VerifyEmployee).

---

## 15. MÓDULO: Reglas globales

### Soft deletes

- **Tablas con SoftDeletes (modelos):** Company, HighEmployee, Account, Acknowledgment, Role, Position, Department, Location, Product, SubIndustry, SurveyQuestion, RequestCategory, EmploymentContractToken, y otros (CapacitationLesson, ProofSkill, etc.). Al eliminar se setea `deleted_at`; las relaciones que usan `withTrashed()` pueden seguir accediendo al registro.
- Relaciones: al dar de baja una empresa no se borran usuarios ni empleados; siguen con company_id. Al dar de baja un empleado, el User con high_employee_id sigue apuntando al empleado (withTrashed en la relación).

### Auditoría

- **logs:** tabla `logs` con `user_id`, `date`, `action`. Se crean en varios controladores (VoiceEmployees, Companies, etc.) y se asocian al usuario y a la empresa (`company->logs()->save($log)`). No hay regla explícita de quién puede ver logs; suele ser admin.

### Timezone

- Uso de `Carbon` y `new \DateTime("now")` sin forzar timezone en el código revisado; depende de `config/app.php` (timezone). Fechas en BD en formato del servidor/APP_TIMEZONE.

### Validación general

- **RFC/CURP/NSS:** validación en alta de empleados y en integración IMSS (CURP 18 caracteres, etc.); no hay Request único centralizado para formato RFC/CURP en el fragmento.
- **Emails:** regla `email` en validaciones. En users el unique fue eliminado en migración (update_users_4).
- **Teléfonos:** numéricos, a veces `digits_between:0,10` para móvil/teléfono.

### CÓDIGO RELEVANTE

- Modelos con `use SoftDeletes` y `$dates = ['deleted_at']`.
- `app/Models/Log.php`, creación de Log en controladores.
- `config/app.php` (timezone).

---

## 16. Glosario

- **Company / Empresa:** Cliente del sistema; tiene empleados, usuarios, productos, configuración de nómina y notificaciones.
- **High employee / Empleado:** Collaborador de una empresa; usa la app; puede tener User vinculado.
- **User / Usuario:** Cuenta del panel (y opcionalmente app si type = high_employee).
- **Voice employee:** Comentario/queja de “voz del colaborador”; lo envía un empleado; lo leen/atienden/asignan usuarios (user_id, attenuator_id, assigned_id).
- **Acknowledgment:** Tipo de reconocimiento; por empresa se configura si es enviable y menciones necesarias.
- **Account state:** Estado de cuenta activo del empleado; contiene saldo y periodicidad de pago.
- **Receivable account:** Cuenta por cobrar; estado PENDIENTE bloquea adelantos si hay incumplimiento.
- **change_type (producto):** AUTOMATIC o MANUAL; si es MANUAL con reason INCUMPLIMIENTO DE PAGO no se reactiva por filtros.
- **enable_from:** Meses desde la fecha de admisión para que un producto esté “habilitado” para el empleado.
- **current_rol:** Rol seleccionado en sesión para el usuario (Entrust).

---

## 17. Anexo: Código relevante

| Módulo | Archivos principales |
|--------|---------------------|
| Empresas | `app/Http/Controllers/Admin/CompaniesController.php`, `app/Models/Company.php` |
| Empleados | `app/Models/HighEmployee.php`, `app/Http/Controllers/Admin/HighEmployeesController.php`, `app/Http/Controllers/Admin/LowEmployeesController.php`, `app/Jobs/ProductFilters/CheckProductFilters.php` |
| Usuarios/Auth | `app/User.php`, `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Controllers/Api/AuthController.php`, `app/Http/Requests/LoginRequest.php`, `config/auth.php` |
| Roles | `app/Models/Role.php`, `app/Http/Controllers/Admin/RolesController.php` |
| Nómina | `app/Http/Controllers/Api/PayrollAdvancesController.php`, `app/Http/Controllers/Admin/PayrollReceiptsController.php`, `app/Jobs/PayrollReceipts/CreatePayrollReceiptJob.php` |
| Voz | `app/Http/Controllers/Admin/VoiceEmployeesController.php`, `app/Http/Controllers/Api/VoiceEmployeesController.php`, `app/Models/VoiceEmployee.php`, `app/Models/VoiceEmployeeExtra.php` |
| Reconocimientos | `app/Http/Controllers/Admin/AcknowledgmentsController.php`, `app/Models/Acknowledgment.php` |
| Encuestas | `app/Http/Controllers/Admin/SurveysController.php` |
| Chat | `app/Http/Controllers/Api/ChatController.php`, modelos ChatRoom, ChatMessage, ChatRoomUser, ChatMessageStatus |
| Notificaciones | `app/Console/Commands/SendScheduledNotificationsPush.php`, `app/Models/Notification.php`, `app/Models/NotificationFrequency.php` |
| Documentos/Contratos | `app/Http/Controllers/Admin/EmploymentContractController.php`, `app/Http/Controllers/Api/DigitalDocumentsController.php` |
| Solicitudes | `app/Http/Controllers/Api/RequestsController.php` |
| Capacitación | `app/Http/Controllers/Admin/CapacitationController.php`, `app/Jobs/Capacitations/AssignedNewOrEditHighEmployee.php` |
| Integraciones | Jobs Belvo, NotificationPush, VerificationAppController, AuthController (Nubarium/VerifyEmployee) |
| Global | Modelos con SoftDeletes, `app/Models/Log.php`, `config/app.php` |

---

## Conclusiones y recomendaciones

- **Consistencia de estados:** En tecben-core conviene unificar nombres de estados (ej. Pendiente/En Proceso/Atendido en voz) y transiciones documentadas en un solo lugar.
- **Validaciones:** Centralizar reglas de RFC, CURP, teléfonos y emails en Form Requests o reglas reutilizables.
- **Seguridad:** Definir middleware por rol/permiso en rutas en lugar de comprobaciones manuales en cada controlador.
- **Soft deletes:** Mantener política clara de qué relaciones usan withTrashed y cuándo se debe ocultar lo eliminado.
- **Integraciones:** Documentar por integración (Belvo, Nubarium, OneSignal, Tableau) el flujo completo y los estados de error.

*Documento generado a partir del análisis del código del proyecto Paco (solo lectura). Para uso como referencia en el diseño de tecben-core.*
