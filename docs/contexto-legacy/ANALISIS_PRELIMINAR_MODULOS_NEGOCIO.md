# Análisis preliminar de módulos de negocio — Legacy Paco → tecben-core

**Propósito:** Acercamiento inicial para planificar Fase 3 (reglas de negocio) en tecben-core: identificar módulos, tablas involucradas, reglas críticas y prioridad de implementación por ramas.  
**Alcance:** No es un análisis exhaustivo; es una guía para crear ramas, estimar esfuerzo y ver dependencias.  
**Fuente:** Documentación existente (REGLAS_NEGOCIO_LEGACY_PACO.md, ANALISIS_BD_LEGACY_PACO.md, ANALISIS_DETALLADO_TABLAS_LEGACY.md) y revisión de controladores/modelos/jobs del legacy.

---

## Resumen ejecutivo

| # | Módulo | Complejidad | Prioridad (1–10) | Dependencias fuertes |
|---|--------|-------------|------------------|----------------------|
| 1 | Auth / Usuarios | Media | **10** | Ninguna (base de todo) |
| 2 | Empleados | Alta | **9** | Auth, Empresas (Rafa) |
| 3 | Financiero | Alta | **8** | Auth, Empleados |
| 4 | Voz del colaborador | Media | **7** | Auth, Empleados |
| 5 | Notificaciones | Media | **6** | Auth, Empleados |
| 6 | Encuestas | Alta | **6** | Auth, Empleados |
| 7 | Reconocimientos | Baja | **5** | Auth, Empleados |
| 8 | Chat | Media | **6** | Auth, Empleados |
| 9 | Solicitudes | Alta | **5** | Auth, Empleados |
| 10 | Documentos | Media | **5** | Auth, Empleados, Empresas |
| 11 | Capacitación | Alta | **4** | Auth, Empleados |
| 12 | Integraciones | Alta | **4** | Auth, Empleados, Financiero |
| 13 | Adicionales | Baja–Media | **3** | Auth, Empleados |

**Orden sugerido para ramas (primeras iteraciones):**  
1) Auth → 2) Empleados → 3) Financiero → 4) Voz → 5) Notificaciones + Chat (en paralelo si hay equipo) → 6) Encuestas → 7) Reconocimientos → 8) Solicitudes → 9) Documentos → 10) Capacitación → 11) Integraciones → 12) Adicionales.

---

## 1. MÓDULO: Auth / Usuarios

### Tablas involucradas (nuestras / legacy)

- usuarios → `users`
- roles → `roles`
- permisos → `permissions`
- rol_usuario → `role_user`
- permiso_rol → `permission_role`
- verify_2fa → `verify_2fa`
- oauth_clients, oauth_access_tokens, oauth_refresh_tokens, oauth_auth_codes, oauth_personal_access_clients
- password_resets → `password_resets`

### Flujo principal

**Panel (web):** Login con email/password → guard `web` → sesión. Opcional 2FA (enable_2fa, google2fa_secret, verify_2fa). Recuperación vía ForgotPasswordController (password_resets). **API/App:** Login con email o móvil + password → Passport (oauth_access_tokens) → token. Solo usuarios con `type` = 'high_employee' y `high_employee_id` no nulo pueden usar login app; además la empresa debe estar activa (`company->is_active`). Registro de usuarios desde panel (admin); no hay registro público de empleados: el empleado existe en high_employees y se le vincula User cuando se le da acceso a la app.

### Reglas críticas identificadas

1. **Tipos de usuario:** `user`, `high_user`, `high_employee`. Solo `high_employee` con `high_employee_id` puede login en app.
2. **Empresa activa:** Si `company->is_active` no es 'SI', el empleado no puede iniciar sesión en la app.
3. **Email sin UNIQUE en BD:** La unicidad de email se eliminó en migración; debe garantizarse en aplicación o restaurar constraint en tecben-core.
4. **Roles por empresa:** `roles.company_id`; asignación usuario–rol debe respetar company_id del usuario.
5. **current_rol:** Rol “actual” en sesión (getCurrentRol()); permisos se evalúan contra ese rol (hasRoles, hasOnePermission).
6. **2FA:** Campos enable_2fa, google2fa_secret, verified_2fa_at; verify_2fa guarda códigos por contact (único). No hay política “obligatorio para rol X” en legacy.
7. **Passport:** client_id en oauth_*; personal_access_client para tokens de app. No hay flujo OAuth explícito de terceros; es login directo con credenciales.

### Complejidad estimada

**Media.** Flujos estándar (login, logout, 2FA, Passport) pero con matices: tipos de usuario, vinculación empleado, empresa activa, roles por empresa y current_rol.

### Dependencias con otros módulos

- **Empleados:** users.high_employee_id → high_employees; sin empleados no hay usuarios tipo app.
- **Empresas (Rafa):** users.company_id, roles.company_id; sin empresas no hay contexto de negocio.

### Prioridad sugerida

**10.** Base de todo: sin auth estable no se puede implementar el resto de forma coherente.

### Archivos clave en legacy

- `app/User.php` (Authenticatable, HasApiTokens, EntrustUserTrait)
- `app/Http/Controllers/Auth/LoginController.php`, `ForgotPasswordController.php`, `ResetPasswordController.php`
- `app/Http/Controllers/Api/AuthController.php` (login app, sendVerificationCode, verifyCode, changePassword)
- `app/Http/Controllers/Auth/Google2FAController.php`
- `app/Http/Requests/LoginRequest.php`, `SignupRequest.php`, `ChangePasswordRequest.php`
- `config/auth.php` (guards web/api, provider users, driver passport para api)
- `app/Models/Role.php`, Entrust (permission_role, role_user)

### Notas para implementación en tecben-core

- Definir política de unicidad de email (por tenant o global).
- Mantener 1:1 opcional User ↔ Empleado (high_employee_id).
- Middleware por rol/permiso en rutas en lugar de comprobaciones manuales en cada controlador.
- Documentar cuándo 2FA es obligatorio (por rol o por empresa).

---

## 2. MÓDULO: Empleados

### Tablas involucradas

- empleados → `high_employees`
- empleado_producto → `high_employee_product`
- filtros_empleado → `high_employee_filters`, `user_filter_high_employee`
- location_histories, area_histories, position_histories, department_histories, business_names_histories, region_histories, payment_periodicity_histories

### Flujo principal

Alta desde panel (manual o masiva): se crea high_employee con company_id, datos personales, puesto, departamento, ubicación, área, región, fecha ingreso, productos asignados (pivot con status ACTIVO/INACTIVO, reason, change_type). Historiales se crean cuando cambian ubicación, área, puesto, etc. Baja = soft delete (deleted_at). Reingreso: LowEmployeesController; se reasignan productos, notificaciones y encuestas según lógica de reingreso. Productos: job CheckProductFilters reactiva ACTIVO si change_type = AUTOMATIC y reason ≠ "INCUMPLIMIENTO DE PAGO"; si es MANUAL con reason INCUMPLIMIENTO DE PAGO no se reactiva.

### Reglas críticas identificadas

1. **company_id obligatorio** en high_employees.
2. **SoftDeletes:** Eliminación lógica; relaciones con withTrashed() donde se deba ver empleados dados de baja.
3. **Productos (pivot):** status ACTIVO/INACTIVO; reason (ej. "INCUMPLIMIENTO DE PAGO"); change_type MANUAL/AUTOMATIC. MANUAL + INCUMPLIMIENTO DE PAGO = no reactivar por job.
4. **enable_from:** Meses desde admission_date para que un producto esté “habilitado” para el empleado (desde company_product).
5. **Validación IMSS/Nubarium:** Si company.transactions_with_imss = 'SI', validationEmployeeHistory() valida CURP/empresa/historial (EmployeeHistory).
6. **code_boss:** Atributo calculado (location.department.area.position) para reportes.
7. **Historiales:** Se crean al cambiar estructura organizacional; determinan “quién estaba dónde y cuándo” para auditoría y reportes.

### Complejidad estimada

**Alta.** Muchos campos, pivot con estados y lógica de reactivación, historiales, reingresos, validación IMSS y filtros guardados.

### Dependencias con otros módulos

- **Auth:** User.high_employee_id; empleados que usan app deben tener User.
- **Empresas/catálogos (Rafa):** company, department, area, position, location, region, payment_center, business_name, products.

### Prioridad sugerida

**9.** Siguiente después de Auth; la mayoría de módulos dependen de “empleado” como entidad central.

### Archivos clave en legacy

- `app/Models/HighEmployee.php` (SoftDeletes, fillable, validationEmployeeHistory, hasProducts, hasProductsActive, code_boss)
- `app/Http/Controllers/Admin/HighEmployeesController.php`, `app/Http/Controllers/Admin/LowEmployeesController.php`
- `app/Jobs/ProductFilters/CheckProductFilters.php`
- Migraciones: create_high_employees_table, update_high_employees_*, create_high_employee_product_table, update_high_employee_product_*,
- Modelos: LocationHistory, AreaHistory, PositionHistory, DepartmentHistory, etc.

### Notas para implementación en tecben-core

- Unificar nomenclatura (employee_number vs número_empleado) según estándar.
- Documentar valores permitidos de reason y transiciones de status/change_type.
- Definir cuándo se crean historiales (eventos o servicios al actualizar empleado).

---

## 3. MÓDULO: Financiero

### Tablas involucradas

- cuentas_empleado → `accounts`
- estados_cuenta → `account_states`
- transacciones → `transactions`
- cuentas_por_cobrar_empleado → `receivable_accounts`
- recibos_nomina_empleado → `payroll_receipts`
- adelantos_nomina_empleado → `payroll_advances`
- payroll_withholding_configs

### Flujo principal

Cuentas: el empleado tiene una o más cuentas (banco, número, alias); status verified/unverified. Solo cuentas verified se usan para adelantos. Estado de cuenta activo (account_states.status = ACTIVO) tiene balance y periodicidad. Adelantos: empleado solicita desde app → se valida que tenga cuenta verified, que no tenga receivable_accounts PENDIENTE (incumplimiento) y que el monto no supere el permitido (balance o balance_without_commission del account_state). Se crea transacción y payroll_advance; el pago externo actualiza estado (EXITOSA/FALLIDA/EN PROCESO). Recibos de nómina: generados por job (CreatePayrollReceiptJob) desde archivos/XML; asociados a high_employee_id. Retenciones: payroll_withholding_configs por empresa (periodicidad, días, weekday, emails).

### Reglas críticas identificadas

1. **Cuenta verified:** Necesaria para adelantos; no eliminar cuenta si es la única verified del empleado o si tiene receivable_accounts PENDIENTE.
2. **Receivable_accounts PENDIENTE:** Bloquea solicitud de adelanto (mensaje “Servicio no disponible por incumplimiento de pago”).
3. **Monto máximo adelanto:** Viene del account_state activo (balance o balance_without_commission); también high_employee.max_amount en otros contextos.
4. **Estados de transacción:** EXITOSA, FALLIDA, EN PROCESO; el proveedor de pago actualiza el estado.
5. **Primera transacción:** Lógica de “primera transacción” (payment_type SALDO DEL SISTEMA) usada en flujos de domiciliación/PDF.
6. **Recibos:** Solo admin puede realizar ciertas acciones (PayrollReceiptsController); generación vía job con initial_payment_date, final_date_payment, pdf_string, fiscal_folio.
7. **Retenciones:** payment_periodicity (SEMANAL, CATORCENAL, QUINCENAL, MENSUAL); configuración por company_id.

### Complejidad estimada

**Alta.** Cuentas, estados, transacciones, cuentas por cobrar, adelantos, integración con pasarela (Belvo/Nomipay) y generación de recibos.

### Dependencias con otros módulos

- **Auth, Empleados:** Quien solicita es User con high_employee; cuentas y account_states son del empleado.
- **Integraciones:** Belvo/Nomipay para pago; Nubarium para firma/domiciliación.

### Prioridad sugerida

**8.** Crítico para producto “adelantos”; depende de Auth y Empleados.

### Archivos clave en legacy

- `app/Http/Controllers/Api/PayrollAdvancesController.php` (getData, solicitud, validaciones, cuenta, receivable_accounts)
- `app/Http/Controllers/Admin/PayrollReceiptsController.php`, `app/Jobs/PayrollReceipts/CreatePayrollReceiptJob.php`
- `app/Models/Account.php`, `AccountState.php`, `Transaction.php`, `ReceivableAccount.php`, `PayrollAdvance.php`, `PayrollWithholdingConfig.php`
- `app/Http/Controllers/Admin/AccountStatesController.php`, `app/Http/Controllers/Admin/ReceivableAccountsController.php`

### Notas para implementación en tecben-core

- Reglas de eliminación de cuentas (única verified, receivable PENDIENTE) como políticas o validaciones explícitas.
- Unificar origen del “monto máximo” (solo account_state o también high_employee.max_amount) y documentarlo.

---

## 4. MÓDULO: Voz del colaborador

### Tablas involucradas

- voces_empleado → `voice_employees`
- usuario_tema_voz → `user_voice_employee_subject`, `company_voice_employee_subject`
- voice_employee_subjects
- reiteraciones_voz → `voice_employee_reiterates`
- voice_employee_extras
- voice_employees_tableu (integración)

### Flujo principal

Empleado envía comentario desde app (comments, voice_employee_subject_id, opcional other_subject, is_anonyme). Estado inicial 'Pendiente'. En panel, un usuario abre el comentario → pasa a 'En Proceso', se asigna user_id (lector) y se notifica a quien tenga notification_voice_employees = 'SI' y el tema asignado. Al responder se setea attenuator_id, results, attention_date; si ya hubo atención previa se crea/actualiza VoiceEmployeeExtra. Estados finales: 'Atendido' o 'Continuar conversación'. Reapertura: nuevo mensaje sobre el mismo voice_employee → nuevo extra o actualización del último; estado vuelve a 'Pendiente'. Tableau: voice_employees_tableu se actualiza con comments/results para reporting.

### Reglas críticas identificadas

1. **Solo high_employees** envían comentarios (vía User con high_employee en app).
2. **Obligatorios:** comments, voice_employee_subject_id.
3. **Estados:** Pendiente → En Proceso → Atendido | Continuar conversación. user_id = quien leyó primero; attenuator_id = quien atiende; assigned_id = asignado para seguimiento.
4. **Temas por empresa y por usuario:** company_voice_employee_subject (temas habilitados), user_voice_employee_subject (temas que atiende cada usuario).
5. **Notificaciones:** Email a usuarios con notification_voice_employees = 'SI' y tema asignado; push al empleado al marcar Atendido/Continuar conversación si tiene User y OneSignal tokens.
6. **Anónimo:** is_anonyme; en listados se muestra "Anónimo" pero high_employee_id se guarda.
7. **Eliminación:** Delete físico; se borran imágenes en assets/voice_employees.

### Complejidad estimada

**Media.** Flujo de estados claro; complejidad en filtros por tema, asignación y notificaciones.

### Dependencias con otros módulos

- **Auth, Empleados:** Emisor es high_employee; lectores/asignados son users (panel).
- **Notificaciones:** Push y email ligados a voz.

### Prioridad sugerida

**7.** Muy visible para el empleado; depende de Auth y Empleados.

### Archivos clave en legacy

- `app/Http/Controllers/Admin/VoiceEmployeesController.php` (getIndex, specificVoiceEmployee, updateStatus, Trash, getFilters)
- `app/Http/Controllers/Api/VoiceEmployeesController.php` (create, index, getSent, getSubjects)
- `app/Models/VoiceEmployee.php`, `app/Models/VoiceEmployeeExtra.php`, `app/Models/VoiceEmployeeReiterate.php`, `app/Models/VoiceEmployeeSubject.php`
- Jobs: ChatGPTVerification (opcional); sincronización Tableau (voice_employees_tableu)

### Notas para implementación en tecben-core

- Documentar transiciones de estado y quién puede hacer cada transición.
- Unificar nombres de estados (Pendiente/En Proceso/Atendido/Continuar conversación) en un enum o constante.

---

## 5. MÓDULO: Notificaciones

### Tablas involucradas

- notifications
- high_employee_notification
- notification_templates
- excluded_notifications
- notifications_frequencies
- one_signal_tokens

### Flujo principal

Notificaciones push creadas desde panel (user_id = quien envía). Destinatarios: high_employees vía pivot high_employee_notification (status NO LEIDA/LEIDA). Los empleados que reciben push son los que tienen User vinculado y ese User tiene one_signal_tokens. Frecuencias: notifications_frequencies por empresa (days, type, next_date); comandos/jobs (SendScheduledNotificationsPush) envían según next_date. Excluded_notifications: por empresa se excluyen tipos de notificación. Plantillas: notification_templates por empresa para contenido. Segmentación por filtros (companies_filter, locations_filter, etc.) o por filtros guardados del usuario; se usa app_setting de la empresa (OneSignal app id, rest api key, android channel) para enviar.

### Reglas críticas identificadas

1. **one_signal_tokens:** Pertenece a users; el empleado recibe push porque su User (high_employee_id) tiene tokens.
2. **Frecuencias:** next_date y days por tipo; jobs actualizan next_date al enviar.
3. **Exclusiones:** excluded_notifications por empresa; no se envían esos tipos a esa empresa.
4. **Segmentación:** Por empresa, ubicación, filtros guardados (high_employee_filters); solo empleados que cumplan filtros reciben.
5. **Plantillas:** notification_templates permiten personalizar mensajes por empresa.

### Complejidad estimada

**Media.** Lógica de envío y segmentación; integración OneSignal y configuración por empresa.

### Dependencias con otros módulos

- **Auth, Empleados:** Destinatarios son empleados; tokens en User vinculado al empleado.
- **Voz, Encuestas, etc.:** Muchos módulos disparan notificaciones (voz atendido, adelanto disponible, etc.).

### Prioridad sugerida

**6.** Necesario para experiencia de app; se puede implementar después de Voz/Chat.

### Archivos clave en legacy

- `app/Models/Notification.php`, `app/Models/NotificationFrequency.php`, `app/Models/ExcludedNotification.php`, `app/Models/NotificationTemplate.php`, `app/Models/OneSignalToken.php`
- `app/Console/Commands/SendScheduledNotificationsPush.php`
- `app/Http/Controllers/Admin/NotificationPushController.php`
- `app/Jobs/Notifications/NotificationPush.php`
- `app/Traits/NotificationTrait.php`

### Notas para implementación en tecben-core

- Centralizar configuración OneSignal (app_id, api_key, channel) por tenant/empresa.
- Definir tipos de notificación y cuáles son programables (frecuencias) vs puntuales.

---

## 6. MÓDULO: Encuestas

### Tablas involucradas

- surveys
- survey_categories
- survey_sections
- survey_questions
- survey_responses
- survey_shippings
- high_employee_survey_shipping
- nom35_sections
- nom35_sections_responses
- (survey_totals, section_responses, custom_survey_shippings, etc.)

### Flujo principal

Creación desde panel: encuesta con categoría, secciones, preguntas (user_id = creador; company_id en migraciones posteriores). Si usuario tiene rol admin puede ver/editar encuestas de todas las empresas; si no, solo las de su empresa o las que él creó. Envío: survey_shippings vinculados a empleados por high_employee_survey_shipping (status Not answered/Answered). Destinatarios por filtros (empresa, ubicación, etc.) o filtros guardados. Respuestas en survey_responses y section_responses; totales en survey_totals. NOM35 tiene secciones y respuestas específicas (nom35_sections, nom35_sections_responses). Jobs/comandos para recordatorios (SendScheduledSurveys).

### Reglas críticas identificadas

1. **Permisos:** hasRoles('admin') permite ver todas las encuestas; si no, filtro por creador o por empresa. Permisos granulares: edit_survey_shipping, trash_survey_shipping, view_companies_surveys (getCurrentRol()->hasOnePermission).
2. **Envíos:** Un envío tiene muchos empleados vía pivot; status en pivot indica si respondió.
3. **Respuestas:** Parciales y completas; totales calculados para reportes.
4. **NOM35:** Dimensiones, secciones y valores NPS propios; lógica específica en controladores y modelos.
5. **Filtros:** Mismo mecanismo que notificaciones (filtros guardados, company, location, etc.).

### Complejidad estimada

**Alta.** Tipos de encuesta (clima, NOM35, personalizadas), secciones, preguntas, dimensiones, envíos, respuestas y reportes.

### Dependencias con otros módulos

- **Auth, Empleados:** Creador = user; destinatarios = high_employees; filtros = high_employee_filters.
- **Roles:** Permisos edit_survey_shipping, view_companies_surveys, etc.

### Prioridad sugerida

**6.** Alto valor; complejidad alta; puede ir después de Notificaciones y Chat.

### Archivos clave en legacy

- `app/Http/Controllers/Admin/SurveysController.php`, `app/Http/Controllers/Admin/SurveyCategoriesController.php`, `app/Http/Controllers/Admin/SurveyResponsesController.php`
- Modelos: Survey, SurveyCategory, SurveySection, SurveyQuestion, SurveyResponse, SurveyShipping, SurveyTotal, Nom35Section, Nom35SectionsResponse
- Comandos/Jobs: SendScheduledSurveys y similares

### Notas para implementación en tecben-core

- Unificar permisos (admin vs permisos por recurso) y documentar matriz de permisos por rol.
- Separar lógica NOM35 en servicio o sub-módulo para mantener claridad.

---

## 7. MÓDULO: Reconocimientos

### Tablas involucradas

- acknowledgments
- acknowledgment_company
- acknowledgment_shippings
- acknowledgment_high_employee

### Flujo principal

Reconocimientos son catálogos; por empresa se configuran en acknowledgment_company (is_shippable, necessary_mentions). Envíos: usuarios del panel crean acknowledgment_shippings dirigidos a empleados; se relacionan por acknowledgment_high_employee con status y reaction. Solo los reconocimientos con is_shippable (ej. ENVIABLE) para esa empresa se pueden enviar. El empleado recibe en app y puede reaccionar; el pivot guarda estado de lectura y reacción.

### Reglas críticas identificadas

1. **Configuración por empresa:** is_shippable, necessary_mentions en pivot acknowledgment_company.
2. **Enviables:** Solo reconocimientos con is_shippable para la empresa aparecen como enviables.
3. **A quién:** high_employees; pivot con status y reaction.
4. **Exclusivos:** acknowledgment.is_exclusive; en listados se diferencian con etiquetas.

### Complejidad estimada

**Baja.** CRUD de reconocimientos, configuración por empresa y envíos a empleados; sin flujos complejos de aprobación.

### Dependencias con otros módulos

- **Auth, Empleados:** Quien envía = user; destinatarios = high_employees.
- **Empresas:** acknowledgment_company por company_id.

### Prioridad sugerida

**5.** Útil para engagement; baja complejidad; se puede hacer en paralelo con otros módulos “simples”.

### Archivos clave en legacy

- `app/Http/Controllers/Admin/AcknowledgmentsController.php`, `app/Http/Controllers/Api/AcknowledgmentsController.php`
- `app/Models/Acknowledgment.php` (SoftDeletes), `app/Models/AcknowledgmentShipping.php`

### Notas para implementación en tecben-core

- Modelar is_shippable y necessary_mentions como configuración explícita por tenant/empresa.
- Definir valores de status/reaction en pivot para historial y reportes.

---

## 8. MÓDULO: Chat

### Tablas involucradas

- chat_rooms
- chat_room_employees → `chat_room_users` (user_id = high_employees.id)
- chat_messages
- chat_message_status
- chat_message_mentions
- chat_message_reactions

### Flujo principal

Salas creadas desde app por empleados (created_by = high_employees.id). Tipos: private, group, channel. Participantes en chat_room_users (user_id referencia high_employees.id). Mensajes: user_id = remitente (high_employees.id); tipos text, file, image, video, audio, system, other. Estado “leído”: chat_message_status por mensaje y por usuario (high_employee); entregado/leído. Menciones en chat_message_mentions; reacciones en chat_message_reactions. SoftDeletes en salas y mensajes. Importante: en todo el chat, “user” es high_employee, no users del panel.

### Reglas críticas identificadas

1. **Todas las FK “user” en chat son high_employees.id:** created_by, chat_room_users.user_id, chat_messages.user_id, chat_message_status.user_id, mentions, reactions. En tecben-core conviene renombrar a employee_id.
2. **Tipos de sala:** private, group, channel; determinan permisos de creación y participación.
3. **Último leído:** last_read_message_id en chat_room_users para marcar hasta dónde leyó cada participante.
4. **Mensajes:** SoftDeletes; edición guarda previous_message; reply_to_message_id para hilos.
5. **Presigned URLs:** file_url, presigned_url, thumbnail para archivos en S3; expiración por presigned_url_expires_at.

### Complejidad estimada

**Media.** Modelo de datos claro; complejidad en tiempo real (presumably broadcasting), archivos y presigned URLs.

### Dependencias con otros módulos

- **Auth, Empleados:** Participantes son empleados; autenticación API con Passport (User con high_employee).

### Prioridad sugerida

**6.** Muy usado en app; puede ir en paralelo con Notificaciones si hay capacidad.

### Archivos clave en legacy

- `app/Http/Controllers/Api/ChatController.php`
- `app/Models/ChatRoom.php`, `app/Models/ChatRoomUser.php`, `app/Models/ChatMessage.php`, `app/Models/ChatMessageStatus.php`, `app/Models/ChatMessageMention.php`, `app/Models/ChatMessageReaction.php`
- Migraciones create_chat_* (todas con FK a high_employees)

### Notas para implementación en tecben-core

- Renombrar user_id → employee_id en tablas de chat para evitar confusión con users del panel.
- Definir política de edición/borrado de mensajes (quién puede, ventana de tiempo).

---

## 9. MÓDULO: Solicitudes

### Tablas involucradas

- requests
- request_types → `requests_type`
- request_status → `requests_status`
- request_categories
- approval_flow_stages
- authorization_stage_approvers
- status_histories

### Flujo principal

Solicitudes creadas por empleados (high_employee_id); tienen tipo (requests_type), categoría (request_categories, por empresa), estado (requests_status). Flujo de aprobación: approval_flow_stages define etapas; authorization_stage_approvers define aprobadores por etapa (high_employee_id = empleados, ej. jefes). Transiciones de estado según aprobación/rechazo; status_histories registra quién y cuándo cambió el estado. Solo admin puede ver/editar ciertos tipos (RequestsTypeController hasRoles('admin')).

### Reglas críticas identificadas

1. **Creador:** high_employee_id; aprobadores también son high_employees (authorization_stage_approvers).
2. **Categorías por empresa:** request_categories con company_id.
3. **Etapas y aprobadores:** Orden de etapas; uno o más aprobadores por etapa; rechazo cambia estado y puede terminar flujo.
4. **Historial:** status_histories obligatorio para auditoría.
5. **Permisos:** admin ve/edita todo; otros por empresa o por rol.

### Complejidad estimada

**Alta.** Flujos configurables por tipo/categoría, múltiples etapas y aprobadores, transiciones y notificaciones.

### Dependencias con otros módulos

- **Auth, Empleados:** Solicitante y aprobadores son empleados; usuarios panel gestionan tipos/categorías/flujos.
- **Notificaciones:** Aprobaciones/rechazos suelen disparar notificaciones.

### Prioridad sugerida

**5.** Importante para RRHH; puede implementarse después de módulos más core (Financiero, Voz, Chat).

### Archivos clave en legacy

- `app/Http/Controllers/Api/RequestsController.php`
- `app/Http/Controllers/Admin/RequestsTypeController.php`, `app/Http/Controllers/Admin/RequestCategoriesController.php`
- Modelos: Request, RequestType, RequestStatus, RequestCategory, ApprovalFlowStage, AuthorizationStageApprover, StatusHistory

### Notas para implementación en tecben-core

- Modelar flujos como configuración (etapas + aprobadores) por tipo o por categoría.
- Documentar máquina de estados (transiciones permitidas por estado y rol).

---

## 10. MÓDULO: Documentos

### Tablas involucradas

- digital_documents
- digital_documents_requests
- digital_documents_generated
- digital_document_signs_locations
- company_files
- company_folder
- folders

### Flujo principal

Documentos digitales (digital_documents) con company_file_id, user_id (responsable); name, business_name, needs_authorization, is_exclusive. Subida desde panel; asociados a empresa vía company_files/company_folder. Solicitudes de documentos: digital_documents_requests; generados: digital_documents_generated por empleado/empresa. Firmas: digital_document_signs_locations; employment_contracts_tokens para flujo de firma (user_id, token, signature_date). Testigos (witnesses) vinculados a digital_document_id o polimórfico. Folders por empresa y usuario (url, company_id, user_id).

### Reglas críticas identificadas

1. **Responsable:** digital_documents.user_id (creador/responsable); company_file relaciona con archivo de empresa.
2. **Firmas:** Ubicaciones de firma y tokens por usuario; integración posible con Nubarium/ZapSign.
3. **Testigos:** witnesses.user_id, digital_document_id, type; para contratos y documentos que requieren testigos.
4. **needs_authorization, is_exclusive:** Condicionan flujo de visibilidad y firma.
5. **Folders:** Estructura de carpetas por empresa y usuario para organización de archivos.

### Complejidad estimada

**Media.** Gestión de archivos, versionado implícito, flujos de firma y testigos; integración con proveedores de firma.

### Dependencias con otros módulos

- **Auth, Empleados:** user_id en documentos y tokens; destinatarios de documentos generados = empleados.
- **Empresas:** company_files, company_folder, company_id en folders.

### Prioridad sugerida

**5.** Necesario para contratos y documentos legales; puede ir después de Solicitudes.

### Archivos clave en legacy

- `app/Http/Controllers/Admin/FileCompanyController.php`, `app/Http/Controllers/Admin/EmploymentContractController.php`
- `app/Http/Controllers/Api/DigitalDocumentsController.php`, `app/Http/Controllers/Api/EmploymentContractsController.php`
- Modelos: DigitalDocument, CompanyFile, EmploymentContract, EmploymentContractToken, Witness, Folder

### Notas para implementación en tecben-core

- Definir si witnesses es polimórfico (testimonio_type, testimonio_id) para reutilizar en otros documentos.
- Documentar flujo de firma (quién firma, orden, timeout de tokens).

---

## 11. MÓDULO: Capacitación

### Tablas involucradas

- capacitations
- capacitation_modules
- capacitation_themes
- capacitation_lessons
- high_employee_capacitation
- capacitation_lesson_completed
- proof_skills
- (proof_skill_objectives, areas, modalities, agents, address_proof)

### Flujo principal

Cursos (capacitations) con company_id, user_id (creador); módulos, temas, lecciones (capacitation_lessons). Asignación a empleados: high_employee_capacitation. Progreso: capacitation_lesson_completed; respuestas a preguntas y actividades prácticas. Job AssignedNewOrEditHighEmployee asigna capacitaciones a empleados según segmentos (antigüedad, área, etc.). DC3: proof_skills y tablas relacionadas (objectives, areas, modalities, agents); address_proof para comprobante de domicilio; generación de constancias según controladores/servicios.

### Reglas críticas identificadas

1. **Asignación por segmento:** Jobs asignan según criterios (nuevo empleado, edición de empleado, segmentos).
2. **Progreso:** Lecciones completadas por empleado; puede haber cuestionarios y actividades prácticas.
3. **DC3:** proof_skills, objetivos, áreas, modalidades, agentes; validez y generación de constancias.
4. **Vigencia:** Cursos pueden tener fechas de vigencia y obligatoriedad.
5. **Encuesta de satisfacción:** capacitation_sat_survey_res, satisfaction_survey_qs vinculados.

### Complejidad estimada

**Alta.** Estructura curso → módulo → tema → lección, asignación automática/manual, progreso, DC3 y constancias.

### Dependencias con otros módulos

- **Auth, Empleados:** Creador = user; destinatarios = high_employees; segmentos usan datos del empleado.
- **Documentos:** Constancias y comprobantes de domicilio.

### Prioridad sugerida

**4.** Muy valorado en RRHH; se puede implementar después de Solicitudes y Documentos.

### Archivos clave en legacy

- `app/Http/Controllers/Admin/CapacitationController.php`, `app/Http/Controllers/Api/CapacitationController.php`
- `app/Jobs/Capacitations/AssignedNewOrEditHighEmployee.php`
- Modelos: Capacitation, CapacitationModule, CapacitationTheme, CapacitationLesson, CapacitationHighEmployee, CapacitationLessonCompleted, ProofSkill, AddressProof

### Notas para implementación en tecben-core

- Separar claramente “asignación automática por segmento” (job/reglas) de “asignación manual”.
- Documentar flujo de generación de constancias DC3 y requisitos (proof_skills, address_proof).

---

## 12. MÓDULO: Integraciones

### Tablas involucradas

- belvo_payment_requests
- belvo_payment_methods
- belvo_direct_debit_customers
- direct_debit_belvos (user_id)
- imss_nubarium_logs
- ine_nubarium
- messages_tableu
- voice_employees_tableu (sync con voz)

### Flujo principal

**Belvo:** direct_debit_belvos por user (panel); belvo_payment_requests, belvo_payment_methods, belvo_direct_debit_customers para cobros y métodos de pago de empleados. Jobs CreateClientBelvoJob, UpdateClientBelvo vinculan clientes y métodos. **IMSS/Nubarium:** Validación de empleados cuando company.transactions_with_imss = 'SI'; EmployeeHistory, ImssNubariumLog, IneNubarium para validación de identidad/cuenta. **Tableau:** voice_employees_tableu y messages_tableu se actualizan al cambiar comentarios o mensajes para reporting externo.

### Reglas críticas identificadas

1. **Belvo:** Flujo de alta de cliente y métodos de pago; cobros ligados a receivable_accounts y transacciones.
2. **Nubarium:** Firma (has_nubarium_sign), validación INE/IMSS; logs en imss_nubarium_logs, ine_nubarium.
3. **Tableau:** Solo sincronización de datos (voz y mensajes); sin lógica de negocio adicional.
4. **OneSignal:** (Visto en Notificaciones) tokens en one_signal_tokens; app_setting por empresa.

### Complejidad estimada

**Alta.** Cada integración tiene su flujo, estados de error y configuración por empresa; Belvo y Nubarium son críticos para financiero y documentos.

### Dependencias con otros módulos

- **Auth, Empleados:** Clientes Belvo y validaciones son por empleado/user.
- **Financiero:** Belvo para cobros y pagos; Nubarium para firma/domiciliación.
- **Documentos:** Nubarium para firma.

### Prioridad sugerida

**4.** Necesarias para producción real pero se pueden implementar cuando Financiero y Documentos estén estables.

### Archivos clave en legacy

- `app/Http/Controllers/Admin/PaymentGatewayController.php`, `app/Http/Controllers/Admin/DirectDebitPaymentController.php`
- `app/Http/Controllers/Api/PaymentGatewayController.php`, `app/Http/Controllers/Api/VerificationAppController.php`
- `app/Repositories/Nubarium.php`
- Jobs: CreateClientBelvoJob, UpdateClientBelvo, SignFileJob (Nomipay/Nubarium)
- Modelos: DirectDebitBelvo, BelvoPaymentRequest, BelvoPaymentMethod, BelvoDirectDebitCustomer, ImssNubariumLog, IneNubarium, VoiceEmployeeTableu, MessageTableu

### Notas para implementación en tecben-core

- Por integración: documentar flujo completo, estados de error y reintentos.
- Considerar capa de adaptadores (Belvo, Nubarium, OneSignal, Tableau) para facilitar pruebas y futuros cambios de proveedor.

---

## 13. MÓDULO: Adicionales

### Tablas involucradas

- devices
- device_locations
- moods
- festivities
- readmissions
- readmission_histories
- user_records

### Flujo principal

**Dispositivos:** devices por empleado o user; device_locations para ubicación; verificación en login (VerifyDevice). **Mood:** Estado de ánimo del empleado (moods); mood_characteristics, mood_disorder; posiblemente encuestas cortas. **Festividades:** festivities (company_id, user_id); mensajes o reconocimientos en fechas especiales. **Reingresos:** readmissions, readmission_histories; lógica en LowEmployeesController (reasignar productos, notificaciones, encuestas). **user_records:** Registros por high_employee para auditoría o historial de acciones.

### Reglas críticas identificadas

1. **Dispositivos:** Registro y verificación para seguridad (nuevo dispositivo → código por email/SMS).
2. **Mood:** Datos para bienestar; puede estar ligado a encuestas o características.
3. **Festividades:** Por empresa; envío de mensajes o reconocimientos en fechas configuradas.
4. **Reingresos:** Historial de bajas/reingresos; al reingresar se reasignan productos y se disparan flujos (notificaciones, encuestas).
5. **user_records:** Trazabilidad de acciones por empleado; sin reglas de negocio complejas en legacy.

### Complejidad estimada

**Baja–Media.** Dispositivos y reingresos tienen más lógica; mood y festividades son relativamente simples; user_records es trazabilidad.

### Dependencias con otros módulos

- **Auth, Empleados:** devices y user_records ligados a empleado/user; reingresos = empleados; festividades = empresa.
- **Notificaciones:** Código verificación nuevo dispositivo; festividades pueden disparar notificaciones.

### Prioridad sugerida

**3.** Útil pero no bloqueante; se puede implementar al final o en paralelo con pulido de otros módulos.

### Archivos clave en legacy

- `app/Models/Device.php`, `app/Models/DeviceLocation.php`, `app/Models/Mood.php`, `app/Models/Festivity.php`, `app/Models/Readmission.php`, `app/Models/UserRecord.php`
- `app/Http/Controllers/Admin/MoodDisordersController.php`, `app/Http/Controllers/Admin/MoodCharacteristicsController.php`
- `app/Http/Controllers/Api/MoodsController.php`
- `app/Http/Controllers/Admin/LowEmployeesController.php` (reingresos)
- AuthController (verificación dispositivo, sendVerificationCodeNotification, etc.)

### Notas para implementación en tecben-core

- Unificar política de verificación de dispositivos (siempre, solo nuevo, por empresa).
- Reingresos: documentar qué se reasigna (productos, encuestas, notificaciones) y en qué orden.

---

## Anexo: Orden de ramas y dependencias

```text
Auth (10)
  └── Empleados (9) [Empresas/catálogos Rafa]
        ├── Financiero (8)
        ├── Voz (7)
        ├── Notificaciones (6) ──┬── Encuestas (6)
        ├── Chat (6)             └── Reconocimientos (5)
        ├── Solicitudes (5)
        ├── Documentos (5)
        ├── Capacitación (4)
        ├── Integraciones (4) [Belvo, Nubarium, Tableau, OneSignal]
        └── Adicionales (3)
```

**Ramas sugeridas (orden):**

1. `fase3/auth-usuarios` — Login panel + API, roles, permisos, 2FA, Passport.
2. `fase3/empleados` — CRUD empleados, pivot productos (status/reason/change_type), historiales, filtros, reingresos.
3. `fase3/financiero` — Cuentas, estados, transacciones, receivable_accounts, adelantos, recibos, retenciones.
4. `fase3/voz-colaborador` — Comentarios, estados, asignación, extras, reiteraciones, temas.
5. `fase3/notificaciones` — Creación, frecuencias, exclusiones, plantillas, OneSignal.
6. `fase3/chat` — Salas, participantes (employee_id), mensajes, estado leído, menciones, reacciones.
7. `fase3/encuestas` — Encuestas, secciones, preguntas, envíos, respuestas, NOM35.
8. `fase3/reconocimientos` — Configuración por empresa, envíos, pivot empleado.
9. `fase3/solicitudes` — Tipos, categorías, flujos de aprobación, etapas, aprobadores, historial.
10. `fase3/documentos` — Digital documents, company_files, firmas, testigos, contratos.
11. `fase3/capacitacion` — Cursos, módulos, lecciones, asignación, progreso, DC3.
12. `fase3/integraciones` — Belvo, Nubarium, Tableau (y OneSignal si no se hizo en notificaciones).
13. `fase3/adicionales` — Dispositivos, mood, festividades, reingresos (si no en empleados), user_records.

**Estimación muy aproximada (referencia):**  
Auth 1–2 sem, Empleados 2–3 sem, Financiero 2–3 sem, Voz 1–2 sem, Notificaciones 1 sem, Chat 1–2 sem, Encuestas 2–3 sem, Reconocimientos 0.5–1 sem, Solicitudes 1.5–2 sem, Documentos 1.5–2 sem, Capacitación 2–3 sem, Integraciones 2–3 sem, Adicionales 1–2 sem. Ajustar según equipo y solapamientos.

---

*Documento de análisis preliminar; no sustituye el análisis exhaustivo por módulo en Fase 3. Para reglas detalladas ver REGLAS_NEGOCIO_LEGACY_PACO.md y ANALISIS_DETALLADO_TABLAS_LEGACY.md.*
