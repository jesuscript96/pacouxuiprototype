# Análisis de Base de Datos y Lógica de Negocio — Paco Legacy

**Objetivo:** Documentar la estructura de BD, módulos de negocio y reglas críticas del proyecto Laravel legacy (paco) como referencia para tecben-core.  
**Restricción:** Solo lectura sobre el legacy; no se modifica código ni se generan migraciones.

---

## Equivalencia de términos (español ↔ código)

| Término que mencionaste | En el código (BD/modelos) |
|-------------------------|---------------------------|
| empresas | `companies` |
| razones_sociales | `business_name` (razón social por ubicación: `business_name_location`) |
| centro_de_costos | `cost_centers` + pivot `company_cost_center` |
| configuracion_app | `app_settings` (y configuración por empresa en `companies`) |
| comisiones_rangos | `commission_ranks` |
| quincenas_personalizadas | `personalized_fortnights` |
| reconocimientos | `acknowledgments` + pivot `acknowledgment_company` |
| temas_voz_colaboradores | `voice_employee_subjects` + pivot `company_voice_employee_subject` |
| notificaciones_incluidas | `notifications` (push) + `excluded_notifications` por empresa |
| frecuencia_notificaciones | `notifications_frequencies` (por company) |
| razones_encuesta_salida | `exit_poll_reasons` (por company) |
| alias_tipo_transacciones | `transaction_type_aliases` (por company) |

---

## 1. ANÁLISIS DE BASE DE DATOS

### 1.1 Listado de tablas (por migraciones Schema::create)

Tablas creadas en migraciones (orden aproximado por módulo):

**Core / Auth / Sistema**
- `users` — Usuarios del panel (admin/backoffice)
- `password_resets` — Tokens de reseteo de contraseña (web)
- `password_resets_api` — Reseteo para API/app
- `pin_resets_api` — Reseteo de PIN API
- `verify_2fa` — Códigos 2FA
- `verify_devices` — Dispositivos verificados
- `roles` — Roles (Entrust)
- `role_user` — User ↔ Role (M:N)
- `permissions` — Permisos
- `permission_role` — Role ↔ Permission (M:N)
- `oauth_clients`, `oauth_access_tokens`, `oauth_refresh_tokens`, `oauth_auth_codes`, `oauth_personal_access_clients` — Passport
- `failed_jobs` — Colas fallidas
- `jobs` — Cola de trabajos
- `cache` — Cache
- `logs` — Auditoría por usuario

**Empresas y catálogos base**
- `industries` — Industrias
- `sub_industries` — Subindustrias
- `companies` — Empresas (clientes)
- `business_name` — Razones sociales
- `business_name_location` — Razón social por ubicación
- `business_name_company` — Company ↔ BusinessName
- `products` — Productos (catálogo)
- `company_product` — Productos por empresa (precios, enable_from, variación)
- `cost_centers` — Centros de costo
- `company_cost_center` — Centros de costo por empresa
- `app_settings` — Configuración de app (relación con company vía modelo)
- `plans` — Planes (suscripción)
- `subscription_payments` — Pagos de suscripción

**Estructura organizacional por empresa**
- `locations` — Sucursales/ubicaciones (company_id)
- `departments` — Departamentos (company_id)
- `areas` — Áreas (company_id)
- `positions` — Puestos (company_id)
- `general_positions`, `general_departments`, `general_areas` — Catálogos globales
- `regions` — Regiones (company_id)
- `occupation_catalogs` — Catálogo de ocupaciones
- `area_catalogs` — Catálogo áreas/subáreas
- `banks` — Bancos

**Empleados**
- `high_employees` — Empleados/collaboradores (usan app; núcleo del negocio)
- `low_employees` — Empleados “bajo” (otro tipo de registro)
- `verify_employees` — Verificación de empleados (cuenta/acceso)
- `user_filter_high_employee` — Filtros guardados de usuarios sobre high_employees
- `high_employee_product` — Productos asignados al empleado (estado, motivo, change_type)
- `business_name_high_employee` — Razones sociales del empleado
- Historiales: `location_histories`, `area_histories`, `position_histories`, `department_histories`, `business_names_histories`, `region_histories`, `payment_periodicity_histories`

**Nómina y financiero**
- `accounts` — Cuentas (high_employee_id)
- `account_states` — Estados de cuenta (activo/inactivo)
- `transactions` — Transacciones
- `receivable_accounts` — Cuentas por cobrar
- `refills` — Recargas
- `services` — Servicios
- `collection_attempts` — Intentos de cobro
- `payroll_receipts` — Recibos de nómina
- `payroll_advances` — Adelantos de nómina
- `payroll_withholdings` — Retenciones
- `payroll_withholding_configs` — Configuración de retenciones por empresa
- `txt_receipts`, `processed_txt_receipts`, `txt_receipt_receivable_account`
- `net_pay_transactions`, `net_pay_subscriptions`
- `excluded_transactions` — Transacciones excluidas
- `payment_centers` — Centros de pago (company_id)
- `commission_ranks` — Rangos de comisión por empresa
- `personalized_fortnights` — Quincenas personalizadas por empresa

**Voz del colaborador (voice_employees)**
- `voice_employee_subjects` — Temas/categorías de comentarios
- `company_voice_employee_subject` — Temas habilitados por empresa
- `user_voice_employee_subject` — Temas asignados a usuarios (quién atiende qué)
- `voice_employees` — Comentarios/quejas de empleados (high_employee_id, user_id, attenuator_id, assigned_id)
- `voice_employee_extras` — Seguimiento/extra (attenuator_id, user_id)
- `voice_employee_reiterates` — Reiteraciones
- `voice_employees_tableu` — Integración Tableau
- `voice_employees_categorization` — Categorización

**Reconocimientos (acknowledgments)**
- `acknowledgments` — Tipos de reconocimiento
- `acknowledgment_company` — Reconocimientos por empresa (is_shippable, necessary_mentions)
- `acknowledgment_shippings` — Envíos a empleados
- `acknowledgment_high_employee` — Empleado ↔ envío (status, reaction)

**Encuestas**
- `surveys` — Encuestas (user_id, company_id en migraciones posteriores)
- `survey_categories` — Categorías
- `survey_sections`, `survey_questions`, `survey_responses`, `section_responses`
- `survey_shippings` — Envíos (user_id)
- `high_employee_survey_shipping` — Empleado ↔ envío
- `survey_actions`, `survey_totals`, `survey_duplicate`
- `custom_survey_shippings` — Envíos personalizados (user_id, company_id)
- NPS: `nps_questions`, `nps_values`
- `question_categories`, `question_dimensions`, `questions`, `optional_questions`
- `scores`, `nom35_sections`, `nom35_sections_responses`, `optional_question_responses`
- `application_questions`, `application_question_values`, `application_question_sections`
- `application_survey_responses`

**Mensajería y notificaciones**
- `messages` — Mensajes (user_id, company_id)
- `high_employee_message` — Mensaje ↔ empleados (status, reaction)
- `message_response` — Respuestas a mensajes
- `notifications` — Notificaciones push (user_id quien envía; high_employees vía pivot)
- `high_employee_notification` — Empleado ↔ notificación (status)
- `notifications_frequencies` — Frecuencia por empresa (days, type, next_date)
- `notification_templates` — Plantillas por empresa
- `excluded_notifications` — Notificaciones excluidas por empresa
- `one_signal_tokens` — Tokens push (user_id → users)
- `token_notification` — (legacy, puede estar deprecado)

**Chat**
- `chat_rooms` — Salas
- `chat_room_users` — user_id → **high_employees.id** (chat entre empleados)
- `chat_messages` — user_id → **high_employees.id**
- `chat_message_status`, `chat_message_mentions`, `chat_message_reactions`

**Solicitudes y flujos**
- `requests` — Solicitudes (high_employee_id)
- `requests_type`, `requests_status`, `request_categories`
- `approval_flow_stages` — Etapas de flujo de aprobación
- `authorization_stage_approvers` — Aprobadores por etapa (high_employee_id)
- `status_histories` — Historial de estados

**Reclutamiento**
- `recruitments` — Procesos de reclutamiento (company_id)
- `recruitment_candidate_statuses`
- `recruitment_candidates` (en modelos)
- `recruitment_candidate_messages` — user_id → users
- `recruitment_forms`

**Capacitación y DC3**
- `capacitations` — Cursos (company_id, user_id)
- `capacitation_modules`, `capacitation_themes`, `capacitation_lessons`
- `capacitation_lesson_qs`, `capacitation_lesson_q_settings`, `capacitation_lesson_q_options`
- `capacitation_lesson_completed`, `lesson_res_practical_activities`, `capacitation_q_responses`
- `capacitation_sat_survey_res`, `satisfaction_survey_qs`
- `high_employee_capacitation` — Asignación empleado ↔ capacitación
- `capacitation_segments`
- `proof_skills`, `proof_skill_objectives`, `proof_skill_areas`, `proof_skill_modalities`, `proof_skill_agents`
- `address_proof` — Comprobantes de domicilio (high_employee_id)

**Documentos y firma**
- `digital_documents` — Documentos digitales (company_file_id, user_id)
- `digital_documents_requests` — Solicitudes
- `digital_documents_generated` — Generados por empleado/empresa
- `digital_document_signs_locations`
- `employment_contracts` — Contratos laborales
- `employment_contracts_tokens` — user_id → users
- `witnesses` — Testigos (user_id)
- `corporate_documents` — Documentos corporativos (high_employee_id)
- `company_files`, `company_folder` — Archivos por empresa
- `folders`, `sub_folders`, `department_folder`, `area_folder`, `folder_location`, `folder_position`

**Bienestar y estado de ánimo**
- `moods` — Estado de ánimo (high_employee_id)
- `mood_characteristics`, `mood_characteristic_mood`, `mood_disorders`, `mood_disorder_mood`
- `online_wellness_documents`, `high_employee_o_w_document`

**Otros**
- `festivities` — Festividades (company_id, user_id)
- `insurances` — Seguros (contractor_id = high_employee_id)
- `beneficiaries`, `high_employee_beneficiaries`
- `readmissions`, `readmission_histories`
- `employee_histories`, `employment_histories` — Integración IMSS/externo
- `videos`, `high_employee_video` — Vídeos vistos por empleado
- `devices`, `device_locations` — Dispositivos de empleados
- `nom151s` — NOM 151
- `ine_nubarium`, `imss_nubarium_logs`
- `belvo_payment_requests`, `belvo_payment_methods`, `belvo_direct_debit_customers`, `direct_debit_belvos` (user_id)
- `nomipay`, `nomipay_reserves`, `nomipay_files`
- `discount_subscriptions`, `applied_promotions`
- `maximum_templates`
- `user_records` — Registros por high_employee
- `complains` — Quejas (recreado en migración drop)

---

### 1.2 Tabla `users` — Estructura completa

**Migración base:** `2014_10_12_000000_create_users_table`  
**Actualizaciones:** `update_users_table`, `update_users_2_table` … `update_users_9_table`, Google 2FA, `add_field_2fa_enable`, `create_field_signer_batch`, etc.

| Campo | Tipo (resumen) | Propósito |
|-------|----------------|-----------|
| id | bigint PK | Identificador |
| name | string | Nombre |
| email | string | **Sin unique a nivel BD** (update_users_4 quitó unique) |
| email_verified_at | timestamp nullable | Verificación de email |
| password | string | Contraseña |
| remember_token | string | “Recordarme” |
| mother_last_name | string | Apellido materno |
| paternal_last_name | string | Apellido paterno |
| phone | string | Teléfono |
| mobile | string | Móvil |
| type | string | Tipo de usuario (ej. user, high_user, high_employee) |
| has_report_user | string | Si tiene reportes |
| notification_voice_employees | enum SI/NO | Notificaciones de voz del colaborador |
| user_tableau | string nullable | Usuario Tableau |
| position_id | FK → positions | Puesto (panel) |
| department_id | FK → departments | Departamento (panel) |
| company_id | FK → companies | Empresa del usuario (panel) |
| high_employee_id | FK → high_employees nullable | **Vinculación con empleado** (cuando el usuario es también empleado) |
| image | string | Ruta de imagen (local o S3) |
| receive_newsletter | enum SI/NO | Newsletter |
| update_password | enum SI/NO | Forzar actualización de contraseña |
| last_password_update | datetime nullable | Última actualización de contraseña |
| google2fa_secret | string nullable | Secret 2FA |
| verified_2fa_at | timestamp nullable | Verificación 2FA |
| enable_2fa | boolean etc. | 2FA habilitado |
| token_batch | string nullable | Firma por lote |
| created_at, updated_at | timestamps | Auditoría |

**Relaciones (desde User):**
- **BelongsTo:** company, department, position, area, high_employee (withTrashed)
- **HasOne (inverso):** high_employee tiene hasOne(User) cuando tiene cuenta de panel
- **HasMany:** logs, folders, created_surveys (surveys), sent_messages (messages), sent_surveys (survey_shippings), sent_notifications_push (notifications), read_comments / comments_attended / assigned_comments (voice_employees), one_signal_tokens, custom_survey_shippings, direct_debit_belvos, digital_documents, witnesses, employment_contracts_tokens, capacitation (Capacitation), high_employee_filters
- **BelongsToMany:** roles (role_user), voice_employee_subjects (user_voice_employee_subject)

---

### 1.3 Relación `high_employees` ↔ `users`

- **high_employees** son los empleados/collaboradores de las empresas; son la entidad que usa la app móvil (cuentas, nómina, encuestas, reconocimientos, chat, etc.).
- **users** son cuentas del panel web (admin/backoffice). Un mismo humano puede ser ambos:
  - **users.high_employee_id** apunta a `high_employees.id` cuando ese usuario del panel es además un empleado.
  - **high_employees** tiene `user()` hasOne → User (el usuario de panel asociado, si existe).

Regla de negocio: no hay tabla “high_user” actual; la relación es **users.high_employee_id → high_employees**. Los tipos (type) como `high_user` o `high_employee` se usan en lógica de negocio para distinguir quién puede hacer qué en el panel.

---

## 2. MAPEO DE MÓDULOS DE NEGOCIO

### MÓDULO: Empresas (Companies)

- **Tablas:** companies, business_name, business_name_company, business_name_location, company_product, company_cost_center, company_folder, company_files, company_voice_employee_subject, industries, sub_industries, products, cost_centers, app_settings, regions, payment_centers, notification_templates, excluded_notifications, notifications_frequencies, personalized_fortnights, commission_ranks, payroll_withholding_configs, exit_poll_reasons, transaction_type_aliases, roles (company_id).
- **Propósito:** Definir clientes (empresas), sus razones sociales, productos contratados, centros de costo, configuración de nómina, notificaciones, quincenas y comisiones.
- **Relaciones clave:** Company hasMany locations, departments, areas, positions, regions, users, high_employees, surveys, messages, payroll_receipts (through high_employees), etc. Roles y muchos catálogos están scoped por company_id.
- **Reglas de negocio:** Productos y precios por empresa (company_product). Centros de costo y quincenas por empresa. Comisiones por rangos (commission_ranks). Roles por empresa.
- **Dependencias:** industries, sub_industries, products; luego todo el resto de módulos dependen de companies.

---

### MÓDULO: Empleados (High Employees)

- **Tablas:** high_employees, low_employees, verify_employees, high_employee_product, business_name_high_employee, user_filter_high_employee, location_histories, area_histories, position_histories, department_histories, business_names_histories, region_histories, payment_periodicity_histories, user_records, devices, device_locations.
- **Propósito:** Mantener el catálogo de empleados por empresa, sus productos, ubicación/departamento/área/puesto/razón social y historiales.
- **Relaciones clave:** HighEmployee belongsTo company, location, department, area, position, region, payment_center, social_reason (business_name); hasMany accounts, account_states, requests, payroll_receipts, etc.; belongsToMany products (high_employee_product), business_names, messages (high_employee_message), survey_shippings, notifications (high_employee_notification).
- **Reglas de negocio:** Soft deletes. Productos con status/reason/change_type en pivot. Validación con IMSS (validationEmployeeHistory) cuando la empresa tiene transactions_with_imss. Scopes birthdays/anniversaries.
- **Dependencias:** Companies, locations, departments, areas, positions, products, business_name.

---

### MÓDULO: Nómina y financiero

- **Tablas:** accounts, account_states, transactions, receivable_accounts, refills, services, collection_attempts, payroll_receipts, payroll_advances, payroll_withholdings, payroll_withholding_configs, net_pay_transactions, net_pay_subscriptions, excluded_transactions, txt_receipts, processed_txt_receipts, payment_centers, commission_ranks, personalized_fortnights.
- **Propósito:** Cuentas de empleados, transacciones, recibos de nómina, adelantos, retenciones, cobros y comisiones.
- **Relaciones clave:** Account → high_employee; account_states → account; transactions → account_state; payroll_receipts → high_employee; receivable_accounts → high_employee.
- **Reglas de negocio:** Retenciones y quincenas configurables por empresa. Comisiones por rango (commission_ranks). Recibos ligados a high_employee y datos fiscales.
- **Dependencias:** High employees, companies.

---

### MÓDULO: Voz del colaborador (Voice employees)

- **Tablas:** voice_employee_subjects, company_voice_employee_subject, user_voice_employee_subject, voice_employees, voice_employee_extras, voice_employee_reiterates, voice_employees_tableu, voice_employees_categorization.
- **Propósito:** Canal de comentarios/quejas/sugerencias de empleados (anonimato opcional), asignación a usuarios del panel, seguimiento y atenuación.
- **Relaciones clave:** VoiceEmployee belongsTo high_employee (sender), voice_employee_subject; belongsTo User como user_id (lector), attenuator_id (quien atiende/atúa), assigned_id (asignado). Company y User tienen temas vía pivots.
- **Reglas de negocio:** is_anonyme, status, priority. Usuarios del panel leen, atienden y asignan; high_employee es el origen. Notificaciones a usuarios (notification_voice_employees en users).
- **Dependencias:** High employees, companies, users.

---

### MÓDULO: Reconocimientos (Acknowledgments)

- **Tablas:** acknowledgments, acknowledgment_company, acknowledgment_shippings, acknowledgment_high_employee.
- **Propósito:** Tipos de reconocimiento por empresa, envíos a empleados y estado de lectura/reacción.
- **Relaciones clave:** Company belongsToMany acknowledgments (pivot is_shippable, necessary_mentions). AcknowledgmentShipping → acknowledgment, high_employee; empleados reciben vía acknowledgment_high_employee (status, reaction).
- **Reglas de negocio:** Configuración por empresa (qué reconocimientos y si son enviables, menciones necesarias).
- **Dependencias:** Companies, high_employees.

---

### MÓDULO: Encuestas

- **Tablas:** surveys, survey_categories, survey_sections, survey_questions, survey_responses, survey_shippings, high_employee_survey_shipping, section_responses, survey_actions, survey_totals, custom_survey_shippings, nps_questions, nps_values, question_categories, question_dimensions, questions, optional_questions, scores, nom35_sections, application_questions, application_question_values, application_survey_responses.
- **Propósito:** Crear encuestas, enviarlas a empleados, registrar respuestas y totales; NOM 35 y preguntas de aplicación.
- **Relaciones clave:** Survey user_id (creador); survey_shippings user_id; high_employee_survey_shipping relaciona envíos con empleados (pivot status). Custom_survey_shippings tiene user_id y company_id.
- **Reglas de negocio:** Envíos creados por users; destinatarios son high_employees. Filtros por empresa, ubicación, etc. en notificaciones/mensajes aplican lógica similar.
- **Dependencias:** Users, companies, high_employees.

---

### MÓDULO: Mensajería y notificaciones push

- **Tablas:** messages, high_employee_message, message_response, notifications, high_employee_notification, notifications_frequencies, notification_templates, excluded_notifications, one_signal_tokens.
- **Propósito:** Mensajes desde el panel a empleados, notificaciones push y control de frecuencia por empresa.
- **Relaciones clave:** Message user_id (quien envía), company_id; high_employee_message (pivot status, reaction). Notification user_id (quien envía); high_employee_notification (pivot status). notifications_frequencies company_id.
- **Reglas de negocio:** Filtros por empresa, ubicación, departamento, etc. Notificaciones programadas. Exclusión por empresa (excluded_notifications). Frecuencia por tipo y próxima fecha por company.
- **Dependencias:** Users, companies, high_employees.

---

### MÓDULO: Chat

- **Tablas:** chat_rooms, chat_room_users, chat_messages, chat_message_status, chat_message_mentions, chat_message_reactions.
- **Propósito:** Chat entre empleados (no entre users del panel).
- **Relaciones clave:** chat_room_users.user_id y chat_messages.user_id referencian **high_employees.id**, no users.id. ChatMessageStatus y ChatMessageMention también usan user_id como high_employee_id.
- **Reglas de negocio:** El chat es solo entre high_employees; last_read_message_id y estados de lectura por “user” del chat (que es high_employee).
- **Dependencias:** High employees.

---

### MÓDULO: Solicitudes y aprobaciones

- **Tablas:** requests, requests_type, requests_status, request_categories, approval_flow_stages, authorization_stage_approvers, status_histories.
- **Propósito:** Solicitudes de empleados y flujos de aprobación por etapas.
- **Relaciones clave:** Request → high_employee; authorization_stage_approvers → high_employee (aprobadores).
- **Dependencias:** High employees, companies (request_categories).

---

### MÓDULO: Capacitación y DC3

- **Tablas:** capacitations, capacitation_modules, capacitation_themes, capacitation_lessons, high_employee_capacitation, capacitation_lesson_completed, capacitation_q_responses, satisfaction_survey_qs, capacitation_sat_survey_res, lesson_res_practical_activities, proof_skills, proof_skill_objectives, proof_skill_areas, proof_skill_modalities, proof_skill_agents, address_proof.
- **Propósito:** Cursos por empresa, asignación a empleados, lecciones, cuestionarios y comprobantes de competencias (DC3).
- **Relaciones clave:** Capacitation company_id, user_id (creador); high_employee_capacitation asigna empleados. proof_skills y tablas DC3 vinculadas a capacitación/empleado.
- **Dependencias:** Companies, users, high_employees.

---

### MÓDULO: Documentos y firma

- **Tablas:** digital_documents, digital_documents_requests, digital_documents_generated, digital_document_signs_locations, employment_contracts, employment_contracts_tokens, witnesses, corporate_documents, company_files, company_folder.
- **Propósito:** Documentos digitales, solicitudes, firma, contratos laborales y testigos (users).
- **Relaciones clave:** DigitalDocument user_id (users), company_file_id; employment_contracts_tokens user_id; witnesses user_id; corporate_documents high_employee_id.
- **Dependencias:** Companies, users, high_employees.

---

### MÓDULO: Reclutamiento

- **Tablas:** recruitments, recruitment_candidates, recruitment_candidate_statuses, recruitment_candidate_messages, recruitment_forms.
- **Propósito:** Vacantes y candidatos; mensajes con user_id (users).
- **Dependencias:** Companies, users.

---

### MÓDULO: Bienestar y estado de ánimo

- **Tablas:** moods, mood_characteristics, mood_characteristic_mood, mood_disorders, mood_disorder_mood, online_wellness_documents, high_employee_o_w_document.
- **Propósito:** Estado de ánimo de empleados y documentos de bienestar.
- **Dependencias:** High employees.

---

## 3. MAPA DE RELACIONES DE `users`

```
users
├── company_id         → companies
├── department_id      → departments (company)
├── position_id       → positions (company)
├── high_employee_id   → high_employees  [opcional; usuario que también es empleado]
│
├── role_user         → roles (M:N)
├── roles.company_id  → companies (roles por empresa)
│
├── HasMany (user como actor en panel):
│   ├── logs
│   ├── folders
│   ├── surveys (created_surveys)
│   ├── messages (sent_messages)
│   ├── survey_shippings (sent_surveys)
│   ├── notifications (sent_notifications_push)
│   ├── custom_survey_shippings
│   ├── direct_debit_belvos
│   ├── digital_documents
│   ├── witnesses
│   ├── employment_contracts_tokens
│   ├── capacitation (Capacitation creadas)
│   ├── high_employee_filters
│   └── one_signal_tokens
│
├── Voice employees (user como lector/atenuador/asignado):
│   ├── voice_employees.user_id       → reader
│   ├── voice_employees.attenuator_id → attenuator
│   ├── voice_employees.assigned_id   → assigned
│   └── user_voice_employee_subject  → temas que puede atender
│
└── Tablas con FK a users (resumen):
    role_user, logs, messages, survey_shippings, surveys, notifications (user_id),
    voice_employees (user_id, attenuator_id, assigned_id), voice_employee_extras,
    voice_employee_reiterates, high_employee_filters, folders, custom_survey_shippings,
    direct_debit_belvos, verify_2fa, employment_contracts_tokens, witnesses,
    digital_documents, recruitment_candidate_messages, one_signal_tokens, capacitations,
    festivities (user_id), add_field_user_digital_documents.
```

**Nota:** En **chat** (chat_room_users, chat_messages, chat_message_status, chat_message_mentions), `user_id` apunta a **high_employees.id**, no a users. No incluir esas tablas en “tablas que apuntan a users”.

---

## 4. REGLAS DE NEGOCIO CRÍTICAS

- **Email en users:** En BD el unique de email fue eliminado (update_users_4). La unicidad debe garantizarse en aplicación si se desea un email único por usuario.
- **Autenticación:** Panel con users (Laravel + Passport); app con high_employees (verify_employees, cuentas, PIN/API). Un user puede estar vinculado a un high_employee vía high_employee_id.
- **Roles y permisos:** Entrust (roles, permissions, role_user, permission_role). Roles con company_id: roles por empresa.
- **High_employees vs users:** high_employees = empleados (app); users = panel. La relación 1:1 opcional es users.high_employee_id → high_employees. Tipo (type) en users distingue comportamiento (high_user, high_employee, etc.).
- **Voz del colaborador:** Solo high_employees envían comentarios; users leen, atienden (attenuator) y asignan (assigned). Temas por empresa (company_voice_employee_subject) y por usuario (user_voice_employee_subject).
- **Notificaciones:** Creadas por users; destinatarios son high_employees (pivot high_employee_notification). Frecuencia y exclusiones por company. one_signal_tokens asociados a users (panel).
- **Chat:** Exclusivamente entre high_employees; todas las FKs “user_id” del chat son high_employee.id.
- **Scoping por empresa:** Casi todos los módulos filtran por company_id (o por relaciones company → locations, departments, etc.). Roles y productos por empresa.

---

## 5. GLOSARIO DE TÉRMINOS

- **Company / Empresa:** Cliente del sistema; tiene empleados (high_employees), usuarios de panel (users), productos, centros de costo, configuración de nómina y notificaciones.
- **High employee / Empleado:** Collaborador de una empresa; usa la app (cuentas, nómina, encuestas, reconocimientos, chat). Puede tener un user asociado (high_employee_id en users).
- **User / Usuario:** Cuenta del panel web; puede tener roles por empresa y opcionalmente estar vinculado a un high_employee.
- **Voice employee:** Registro de “voz del colaborador” (comentario/queja/sugerencia); lo envía un high_employee; lo leen/atienden/asignan users.
- **Acknowledgment / Reconocimiento:** Tipo de reconocimiento configurado por empresa; se envían a empleados (acknowledgment_shippings) y se registra estado en acknowledgment_high_employee.
- **Business name / Razón social:** Razón social; se asocia a companies y a high_employees (business_name_high_employee, business_name_location).
- **Account / Cuenta:** Cuenta financiera del empleado (high_employee); account_states y transactions dependen de ella.
- **Receivable account:** Cuenta por cobrar asociada a high_employee/cuenta.
- **Survey shipping:** Envío de una encuesta a uno o más empleados; creado por user; alta_employee_survey_shipping con status.
- **Personalized fortnight / Quincena personalizada:** Rango de días de quincena por empresa (personalized_fortnights).
- **Commission rank:** Rango de comisión (precio desde/hasta, monto fijo o porcentaje) por empresa.

---

## 6. PREGUNTAS ESPECÍFICAS RESPONDIDAS

- **¿Cómo se relaciona high_employees con users?**  
  Por `users.high_employee_id` → `high_employees.id`. Es 1:1 opcional: un user puede ser además un empleado (tiene cuenta en la app). HighEmployee tiene `user()` hasOne → User.

- **¿Qué hace exactamente voice_employees?**  
  Es el módulo “voz del colaborador”: el empleado (high_employee) envía un comentario/queja/sugerencia (anonimato opcional); se clasifica por voice_employee_subject; un user del panel puede leer (user_id), atenuar (attenuator_id) o ser asignado (assigned_id). Hay seguimiento (voice_employee_extras) y reiteraciones. Temas habilitados por empresa y asignados a usuarios.

- **¿Para qué sirven las tablas “empresas_*”?**  
  En código son tablas `company_*` o relaciones con `companies`: configuran por empresa qué productos, centros de costo, reconocimientos, temas de voz, notificaciones, quincenas y comisiones tiene cada cliente. Ej.: company_product, acknowledgment_company, company_voice_employee_subject, company_cost_center, etc.

- **¿Cómo funciona el sistema de notificaciones?**  
  Notificaciones push creadas por users; se envían a high_employees vía pivot high_employee_notification (status). notifications tiene user_id (creador), message, tipo, fechas; puede tener filtros (companies_filter, locations_filter, etc.). notifications_frequencies por company (días, tipo, next_date). notification_templates y excluded_notifications por empresa. one_signal_tokens en users para enviar desde el panel.

- **¿Qué módulos son críticos para el negocio?**  
  Empresas (companies y configuración), Empleados (high_employees), Nómina y financiero (cuentas, transacciones, recibos, adelantos, retenciones), Voz del colaborador, Reconocimientos, Encuestas, Mensajería y notificaciones push. Chat y solicitudes/aprobaciones son importantes para la experiencia del empleado. Capacitación y DC3, documentos y firma, y reclutamiento son módulos de valor añadido.

---

## 7. HALLAZGOS IMPORTANTES

**Mantener en tecben-core:**
- Distinción clara entre users (panel) y high_employees (app) y la relación opcional 1:1.
- Scoping por empresa (company_id) en roles, productos, centros de costo, notificaciones, quincenas y comisiones.
- Modelo de voz del colaborador (temas por empresa y por usuario; flujo lector/atenuador/asignado).
- Pivots con estado (high_employee_notification, high_employee_survey_shipping, acknowledgment_high_employee, high_employee_product con reason/change_type).
- Historiales de ubicación, área, puesto, departamento, razón social y periodo de pago para empleados.

**Mejorable en la nueva versión:**
- Revisar eliminación de unique en email (users) y definir política única (por tenant o global).
- Unificar nomenclatura: en BD todo está en inglés; documentación externa puede usar español (esta doc ya incluye equivalencias).
- Chat: nombre de columna `user_id` referenciando high_employees puede renombrarse a `high_employee_id` en tecben-core para evitar confusiones.
- Roles con company_id: validar en aplicación que role_user solo asigne roles de la misma company del user.
- Centralizar reglas de productos por empresa y por empleado (high_employee_product) en un solo lugar.

**Riesgos / deuda técnica:**
- Tabla `high_user` fue creada y luego eliminada (drop_high_user_table); la lógica pasó a users + high_employee_id. Cualquier referencia residual a “high_user” debe buscarse en código.
- Varias tablas con prefijo “update_*” o recreaciones (ej. payroll_receipts en varias migraciones); al migrar a tecben-core conviene un único esquema consolidado.
- token_notification y posibles tablas deprecadas: revisar antes de migrar.
- Passport y Entrust son dependencias legacy; en tecben-core se puede valorar reemplazo por Laravel Sanctum y permisos/roles más explícitos.

---

*Documento generado a partir del análisis del código del proyecto paco (solo lectura). Para uso como referencia en el diseño de tecben-core.*

---

## ANEXO A: Tablas con FK a `users` (referencia a users.id)

| Tabla | Columna(s) | Uso |
|-------|------------|-----|
| role_user | user_id | Asignación rol |
| logs | user_id | Auditoría |
| messages | user_id | Creador del mensaje |
| surveys | user_id | Creador de encuesta |
| survey_shippings | user_id | Quien envía |
| notifications | user_id | Quien envía notificación push |
| voice_employees | user_id, attenuator_id, assigned_id | Lector, atenuador, asignado |
| voice_employee_extras | attenuator_id, user_id | Seguimiento |
| voice_employee_reiterates | user_id, attenuator_id | Reiteraciones |
| high_employee_filters | user_id | Filtros guardados |
| folders | user_id | Propietario carpeta |
| custom_survey_shippings | user_id | Creador envío personalizado |
| direct_debit_belvos | user_id | Débito directo Belvo |
| verify_2fa | user_id | 2FA |
| employment_contracts_tokens | user_id | Token contrato |
| witnesses | user_id | Testigo |
| digital_documents | user_id | Documento digital (creador/responsable) |
| recruitment_candidate_messages | user_id | Mensaje a candidato |
| one_signal_tokens | user_id | Token push (panel) |
| capacitations | user_id | Creador capacitación |
| festivities | user_id | Creador festividad |

---

## ANEXO B: Tablas con FK a `high_employees` (referencia a high_employees.id)

Principales: accounts, account_states, requests, payroll_receipts, payroll_advances, receivable_accounts, high_employee_product, business_name_high_employee, acknowledgment_shippings (remitente), acknowledgment_high_employee (destinatario), voice_employees (high_employee_id), survey_responses, high_employee_message, high_employee_notification, high_employee_survey_shipping, high_employee_video, corporate_documents, digital_documents_requests, digital_documents_generated, employment_contracts, insurances (contractor_id), verify_employees, low_employee, discount_subscription, devices, moods, payroll_withholdings, user_records, location/area/position/department/business_names/region/payment_periodicity_histories, authorization_stage_approvers, high_employee_capacitation, capacitation_lesson_completed, capacitation_q_responses, lesson_res_practice_activities, capacitation_sat_survey_res, address_proof, high_employee_beneficiaries, belvo_direct_debit_customers, nomipay, nomipay_reserves, chat_room_users (user_id→high_employees), chat_messages (user_id→high_employees), chat_message_status, chat_message_mentions, chat_message_reactions (user_id→high_employees). users.high_employee_id también apunta a high_employees.

---

## ANEXO C: Tablas pivot o configuración por empresa (company_id o company_*)

- company_product  
- company_cost_center  
- company_folder  
- company_voice_employee_subject  
- business_name_company  
- acknowledgment_company  
- roles (company_id)  
- notification_templates (company_id)  
- notifications_frequencies (company_id)  
- excluded_notifications (company_id)  
- personalized_fortnights (company_id)  
- commission_ranks (company_id)  
- payroll_withholding_configs (company_id)  
- exit_poll_reasons (company_id)  
- transaction_type_aliases (company_id)  
- request_categories (company_id)  
- product_filters (company_id)  
- high_employee_filters (company_id)  
- cost_centers (company_id en migración reciente)  
- payment_centers (company_id)  
- company_files (company_id)  
- capacitations (company_id)  
- digital_documents_generated (company_id)  
- survey_shippings (company_id en alguna migración), custom_survey_shippings (company_id)  
- locations, departments, areas, positions, regions (company_id)

