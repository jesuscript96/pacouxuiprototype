# Análisis detallado de tablas Legacy — Paco

**Propósito:** Documentación exhaustiva de la estructura de todas las tablas del legacy para la migración a tecben-core (Fase 1 y Fase 2).  
**Fuente:** Migraciones Laravel y modelos Eloquent (no se ejecutó SQL contra la BD; para tipos exactos y datos de ejemplo ejecutar las consultas del Anexo).

---

## Metodología de análisis

1. **Estructura de tablas:** Obtenida de `Schema::create` y `Schema::table` en `database/migrations/`. Tipos Laravel → MySQL: `bigIncrements` → BIGINT UNSIGNED AUTO_INCREMENT, `string` → VARCHAR(255), `longText` → LONGTEXT, `timestamp` → TIMESTAMP, `decimal(20,2)` → DECIMAL(20,2), `enum(...)` → ENUM, `boolean` → TINYINT(1).
2. **Índices y FK:** Extraídos de `$table->foreign()`, `$table->unique()`, `$table->index()` en las mismas migraciones.
3. **Relaciones:** De los modelos en `app/Models/` y `app/User.php` (belongsTo, hasMany, belongsToMany, withPivot).
4. **Reglas implícitas:** Inferidas de NOT NULL, DEFAULT, ENUM, unique y comentarios en código.
5. **Triggers/eventos:** En Laravel no se usan triggers de BD en el código revisado; se indicará "Ninguno" salvo que existan migraciones con DB::statement.
6. **Ejemplos de datos:** No disponibles sin acceso a la BD; se incluye en el Anexo la consulta `SELECT * FROM tabla LIMIT 5` para ejecutar en el entorno legacy.

**Nota:** Las tablas listadas como "de Rafa" en el enunciado (empresas, productos, etc.) en el legacy tienen nombres en **inglés**: `companies`, `products`, `business_name`, `industries`, `sub_industries`, etc. Se documentan con el nombre real de la BD.

---

## Índice de tablas

- [users](#tabla-users)
- [roles](#tabla-roles)
- [role_user](#tabla-role_user)
- [permissions](#tabla-permissions)
- [permission_role](#tabla-permission_role)
- [oauth_clients (Laravel Passport)](#tabla-oauth_clients-laravel-passport)
- [oauth_access_tokens](#tabla-oauth_access_tokens)
- [oauth_refresh_tokens](#tabla-oauth_refresh_tokens)
- [oauth_auth_codes](#tabla-oauth_auth_codes)
- [oauth_personal_access_clients](#tabla-oauth_personal_access_clients)
- [password_resets](#tabla-password_resets)
- [verify_2fa](#tabla-verify_2fa)
- [high_employees](#tabla-high_employees)
- [high_employee_product](#tabla-high_employee_product)
- [high_employee_filters](#tabla-high_employee_filters)
- [accounts](#tabla-accounts)
- [account_states](#tabla-account_states)
- [transactions](#tabla-transactions)
- [receivable_accounts](#tabla-receivable_accounts)
- [payroll_receipts](#tabla-payroll_receipts)
- [payroll_advances](#tabla-payroll_advances)
- [payroll_withholding_configs](#tabla-payroll_withholding_configs)
- [chat_rooms](#tabla-chat_rooms)
- [chat_room_users](#tabla-chat_room_users)
- [chat_messages](#tabla-chat_messages)
- [chat_message_status](#tabla-chat_message_status)
- [chat_message_mentions](#tabla-chat_message_mentions)
- [chat_message_reactions](#tabla-chat_message_reactions)
- [voice_employee_subjects](#tabla-voice_employee_subjects)
- [voice_employees](#tabla-voice_employees)
- [voice_employee_extras](#tabla-voice_employee_extras)
- [voice_employee_reiterates](#tabla-voice_employee_reiterates)
- [one_signal_tokens](#tabla-one_signal_tokens)
- [witnesses](#tabla-witnesses)
- [direct_debit_belvos](#tabla-direct_debit_belvos)
- [employment_contracts_tokens](#tabla-employment_contracts_tokens)
- [digital_documents](#tabla-digital_documents)
- [folders](#tabla-folders)
- [Resumen tablas Fase 2 y Rafa](#resumen-tablas-fase-2-y-referencia-rafa)
- [Anexo: consultas SQL](#anexo-consultas-sql-para-ejecutar-en-la-bd)

---

## TABLA: users

### 1. ESTRUCTURA (consolidada desde migraciones base + update_users_*)

| Campo | Tipo (MySQL) | Nulo | Clave | Default | Extra | Descripción |
|-------|--------------|------|-------|---------|-------|-------------|
| id | bigint(20) unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | NO | | NULL | | Nombre |
| email | varchar(255) | YES | | NULL | | **Sin unique en BD** (se eliminó en update_users_4) |
| email_verified_at | timestamp | YES | | NULL | | Verificación email |
| password | varchar(255) | NO | | NULL | | Hash (no nullable en migraciones) |
| remember_token | varchar(100) | YES | | NULL | | |
| mother_last_name | varchar(255) | NO | | NULL | | Apellido materno |
| paternal_last_name | varchar(255) | NO | | NULL | | Apellido paterno |
| phone | varchar(255) | NO | | NULL | | Teléfono |
| mobile | varchar(255) | NO | | NULL | | Móvil |
| type | varchar(255) | NO | | NULL | | user, high_user, high_employee |
| has_report_user | varchar(255) | YES | | NULL | | |
| notification_voice_employees | enum('SI','NO') | YES | | NO | | Notificaciones voz colaborador |
| user_tableau | varchar(255) | YES | | NULL | | Usuario Tableau |
| position_id | bigint(20) unsigned | YES | MUL | NULL | | FK positions |
| department_id | bigint(20) unsigned | YES | MUL | NULL | | FK departments |
| company_id | bigint(20) unsigned | YES | MUL | NULL | | FK companies |
| high_employee_id | bigint(20) unsigned | YES | MUL | NULL | | FK high_employees (vinculación 1:1 opcional) |
| image | varchar(255) | YES | | NULL | | Ruta imagen |
| receive_newsletter | enum('SI','NO') | YES | | NO | | |
| update_password | enum('SI','NO') | YES | | NO | | Forzar cambio contraseña |
| last_password_update | datetime | YES | | NULL | | |
| google2fa_secret | longtext | YES | | NULL | | Secret 2FA |
| verified_2fa_at | datetime | YES | | NULL | | |
| enable_2fa | tinyint(1) | YES | | 0 | | |
| token_batch | varchar(255) | YES | | NULL | | Firma por lote |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** position_id → positions.id (CASCADE), department_id → departments.id (CASCADE), company_id → companies.id (CASCADE), high_employee_id → high_employees.id (CASCADE)
- **Unique:** ninguno en email (eliminado en migración)
- **Otros índices:** ninguno explícito adicional en migraciones revisadas

### 3. RELACIONES

- **Referenciada por:** role_user, logs, messages, surveys, survey_shippings, notifications, voice_employees (user_id, attenuator_id, assigned_id), voice_employee_extras, high_employee_filters, folders, custom_survey_shippings, direct_debit_belvos, verify_2fa, employment_contracts_tokens, witnesses, digital_documents, one_signal_tokens, recruitment_candidate_messages, capacitations, festivities
- **Referencia a:** companies, departments, positions, areas (si existe), high_employees

### 4. EJEMPLOS DE DATOS

Ejecutar en BD legacy:
```sql
SELECT id, name, email, type, company_id, high_employee_id, enable_2fa FROM users LIMIT 5;
```

### 5. TRIGGERS / EVENTOS

Ninguno (Laravel; lógica en aplicación).

### 6. REGLAS DE NEGOCIO IMPLÍCITAS

- Email ya no es UNIQUE en BD; la unicidad debe garantizarse en aplicación si se desea.
- high_employee_id define la relación 1:1 opcional con empleado (usuario que además es empleado en la app).
- type distingue comportamiento: high_employee para login app, high_user para panel API.

### 7. CÓDIGO RELEVANTE

- **Modelo:** `app/User.php` (Authenticatable, HasApiTokens, EntrustUserTrait, Notifiable).
- **Relaciones:** high_employee(), company(), department(), position(), area(), roles(), logs(), folders(), sent_messages(), sent_surveys(), sent_notifications_push(), read_comments(), comments_attended(), assigned_comments(), one_signal_tokens(), voice_employee_subjects(), digital_documents(), witnesses(), employment_contracts_tokens(), capacitation(), high_employee_filters(), direct_debit_belvos(), custom_survey_shippings().
- **Accessors:** getFullNameAttribute, getImageUrlAttribute.
- **Controladores:** Auth/LoginController, Api/AuthController, Admin/UsersController, Admin/ProfileController, etc.
- **Requests:** LoginRequest, SignupRequest, ChangePasswordRequest.

### 8. PATRONES DE USO

Login panel (web), login API (Passport), gestión de usuarios en admin; filtrado por company_id y roles.

### 9. OBSERVACIONES PARA TECBEN-CORE

- Restaurar UNIQUE en email o documentar política de unicidad por tenant.
- Mantener high_employee_id para 1:1 opcional con empleados.
- Considerar password nullable si hay SSO/WorkOS.

---

## TABLA: high_employees

### 1. ESTRUCTURA (base + múltiples update_high_employees_*)

Campos actuales según modelo `$fillable` y migraciones (resumen; tipos típicos Laravel):

| Campo | Tipo (MySQL) | Nulo | Clave | Default | Extra | Descripción |
|-------|--------------|------|-------|---------|-------|-------------|
| id | bigint(20) unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | NO | | NULL | | Nombre |
| mother_last_name | varchar(255) | NO | | NULL | | Apellido materno |
| paternal_last_name | varchar(255) | NO | | NULL | | Apellido paterno |
| birthdate | date | NO | | NULL | | Fecha nacimiento |
| email | varchar(255) | NO | | NULL | | Email (no único en BD) |
| mobile | varchar(255) | NO | | NULL | | Móvil |
| employee_number | varchar(191) | YES | MUL | NULL | | Número empleado |
| periodicity_payment | varchar(255) | YES | | NULL | | Periodicidad pago |
| periodicity_day | int | YES | | NULL | | Día de pago |
| net_income | decimal(20,2) | YES | | NULL | | Ingreso neto |
| max_amount | decimal(20,2) | YES | | NULL | | Monto máximo adelanto |
| admission_date | date | NO | | NULL | | Fecha ingreso |
| rfc | varchar(255) | YES | | NULL | | RFC |
| curp | varchar(255) | YES | | NULL | | CURP |
| area | varchar(255) | YES | | NULL | | Área (legacy texto) |
| code | varchar(255) | YES | | NULL | | Código |
| gender | varchar(255) | YES | | NULL | | Género |
| business_name | bigint unsigned | YES | MUL | NULL | | FK business_name (razón social) |
| gross_salary | decimal(20,2) | YES | | NULL | | Salario bruto |
| position_id | bigint(20) unsigned | YES | MUL | NULL | | FK positions |
| department_id | bigint(20) unsigned | YES | MUL | NULL | | FK departments |
| company_id | bigint(20) unsigned | NO | MUL | NULL | | FK companies |
| location_id | bigint(20) unsigned | YES | MUL | NULL | | FK locations |
| area_id | bigint(20) unsigned | YES | MUL | NULL | | FK areas |
| verified | varchar(255) | YES | | NULL | | |
| massive_load_verification | varchar(255) | YES | | NULL | | |
| update_employee | varchar(255) | YES | | NULL | | |
| has_dni | varchar(255) | YES | | NULL | | |
| social_security_number | varchar(255) | YES | | NULL | | NSS |
| region_id | bigint(20) unsigned | YES | MUL | NULL | | FK regions |
| boss_code | varchar(255) | YES | | NULL | | |
| date_verification_mobile | timestamp | YES | | NULL | | |
| annual_vacation_days | int | YES | | NULL | | |
| remaining_vacation_days | int | YES | | NULL | | |
| address | text | YES | | NULL | | |
| daily_salary | decimal(20,2) | YES | | NULL | | |
| daily_salary_integrated | decimal(20,2) | YES | | NULL | | |
| salary_variable | decimal(20,2) | YES | | NULL | | |
| entry_time | time | YES | | NULL | | |
| departure_time | time | YES | | NULL | | |
| lunch_entry_time | time | YES | | NULL | | |
| lunch_departure_time | time | YES | | NULL | | |
| extra_day_entry_time | time | YES | | NULL | | |
| extra_day_departure_time | time | YES | | NULL | | |
| nationality | varchar(255) | YES | | NULL | | |
| marital_status | varchar(255) | YES | | NULL | | |
| additional_comment | text | YES | | NULL | | |
| payment_center_id | bigint(20) unsigned | YES | MUL | NULL | | FK payment_centers |
| payment_company_name | varchar(255) | YES | | NULL | | |
| imss_registration_date | date | YES | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | Soft delete |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** company_id → companies.id (CASCADE), position_id, department_id, location_id, area_id, region_id, bank_id (en migración base), payment_center_id → payment_centers.id; business_name → business_name.id
- **SoftDeletes:** deleted_at
- **Índices:** varios en FKs; ver migraciones update_high_employees_* para índices adicionales (ej. employee_number).

### 3. RELACIONES

- **Referenciada por:** users (high_employee_id), accounts, account_states, payroll_receipts, voice_employees, chat_room_users (user_id), chat_messages (user_id), high_employee_product, high_employee_filters (vía user_filter_high_employee), acknowledgment_shippings, notifications (pivot), survey_shippings (pivot), messages (pivot), requests, authorization_stage_approvers, y muchas más (ver ANALISIS_BD_LEGACY_PACO).
- **Referencia a:** company, department, area, position, location, region, payment_center, social_reason (business_name); products (pivot high_employee_product), business_names (pivot), etc.

### 4. EJEMPLOS DE DATOS

```sql
SELECT id, name, email, company_id, admission_date, deleted_at FROM high_employees LIMIT 5;
```

### 5. TRIGGERS / EVENTOS

Ninguno.

### 6. REGLAS DE NEGOCIO IMPLÍCITAS

- Soft delete: no borrado físico.
- company_id obligatorio después de update_high_employees_tables.
- code_boss es atributo calculado (location_id + department_id + area_id + position_id) en modelo.

### 7. CÓDIGO RELEVANTE

- **Modelo:** `app/Models/HighEmployee.php` (SoftDeletes, fillable, appends code_boss).
- **Relaciones:** user(), company(), department(), area(), position(), location(), region(), payment_center(), accounts(), account_states(), products() withPivot('status','reason','change_type'), sent_voice_employees(), payroll_receipts(), chatRooms(), chatMessages(), etc.
- **Scopes:** scopeBirthdaysBetween, scopeAnniversariesBetween.
- **Métodos:** validationEmployeeHistory(), hasProducts(), hasProductsActive(), hasProductReason(), getCurrentFirstTransactionDateAttribute(), getSignatureAttribute().
- **Controladores:** Admin/HighEmployeesController, Api/HighEmployeesController, Admin/LowEmployeesController, Api/AuthController, Api/PayrollAdvancesController, Admin/VoiceEmployeesController, etc.
- **Jobs:** CheckProductFilters, CreatePayrollReceiptJob, AssignedNewOrEditHighEmployee, etc.

### 8. PATRONES DE USO

Núcleo del negocio: empleados por empresa, productos asignados, cuentas, nómina, voz, chat, encuestas, reconocimientos. Acceso con withTrashed() donde se permite ver dados de baja.

### 9. OBSERVACIONES PARA TECBEN-CORE

- Unificar nombres de columnas (ej. employee_number vs número_empleado) según estándar español/inglés acordado.
- Mantener historiales (location_histories, area_histories, etc.) si se usan en reportes o auditoría.

---

## TABLA: high_employee_product

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| product_id | bigint unsigned | NO | MUL | NULL | | FK products |
| high_employee_id | bigint unsigned | NO | MUL | NULL | | FK high_employees |
| status | varchar(255) | YES | | NULL | | ACTIVO, INACTIVO |
| reason | varchar(255) | YES | | NULL | | Motivo (ej. INCUMPLIMIENTO DE PAGO) |
| change_type | enum('MANUAL','AUTOMATIC') | YES | | AUTOMATIC | | Quién cambió |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** product_id → products.id (CASCADE), high_employee_id → high_employees.id (CASCADE)
- **Unique:** no hay unique compuesto (mismo empleado puede tener mismo producto una vez; lógica en app)

### 3. RELACIONES

- **Referenciada por:** ninguna (es pivot entre high_employees y products).
- **Referencia a:** products, high_employees.

### 6. REGLAS DE NEGOCIO IMPLÍCITAS

- status ACTIVO/INACTIVO; reason libre; change_type MANUAL o AUTOMATIC. Si reason = 'INCUMPLIMIENTO DE PAGO' y change_type MANUAL, el job CheckProductFilters no reactiva a ACTIVO.

### 7. CÓDIGO RELEVANTE

- **Modelo:** relación en HighEmployee: `products()->withPivot('status','reason','change_type')`.
- **Jobs:** `app/Jobs/ProductFilters/CheckProductFilters.php` (updateExistingPivot status/reason/change_type).
- **Controladores:** Admin/LowEmployeesController (alta/baja productos en reingresos), Admin/HighEmployeesController.

### 9. OBSERVACIONES PARA TECBEN-CORE

- Mantener pivot con status, reason y change_type; documentar valores permitidos de status y reason.

---

## TABLA: accounts

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| number | varchar(255) | NO | | NULL | | Número cuenta |
| type | varchar(255) | NO | | NULL | | Tipo cuenta |
| alias | varchar(255) | NO | | NULL | | Alias |
| status | varchar(255) | NO | | NULL | | verified, unverified |
| bank_id | bigint unsigned | YES | MUL | NULL | | FK banks |
| high_employee_id | bigint unsigned | YES | MUL | NULL | | FK high_employees |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | SoftDeletes en modelo |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** bank_id → banks.id (CASCADE), high_employee_id → high_employees.id (CASCADE)
- **SoftDeletes:** modelo Account usa SoftDeletes

### 3. RELACIONES

- **Referenciada por:** account_states (vía lógica: account_state pertenece a high_employee; en legacy receivable_accounts → account_state), payroll_advances (account_id)
- **Referencia a:** banks, high_employees

### 6. REGLAS IMPLÍCITAS

- status 'verified' necesario para adelantos y operaciones que exigen cuenta verificada. No eliminar si hay receivable_accounts PENDIENTE o si es la única cuenta verificada del empleado.

### 7. CÓDIGO RELEVANTE

- `app/Models/Account.php` (SoftDeletes), `app/Http/Controllers/Api/PayrollAdvancesController.php`.

---

## TABLA: account_states

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| since | timestamp | YES | | NULL | | Inicio periodo |
| until | timestamp | YES | | NULL | | Fin periodo |
| balance | varchar(255) | NO | | NULL | | Saldo (string en migración) |
| status | int | NO | | NULL | | Ej. ACTIVO |
| payment_periodicity | varchar(255) | NO | | NULL | | |
| high_employee_id | bigint unsigned | YES | MUL | NULL | | FK high_employees |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** high_employee_id → high_employees.id (CASCADE)

### 3. RELACIONES

- **Referenciada por:** transactions (account_state_id), receivable_accounts (account_state_id)
- **Referencia a:** high_employees

### 7. CÓDIGO RELEVANTE

- `app/Models/AccountState.php`, PayrollAdvancesController (balance para max_amount adelanto).

---

## TABLA: transactions

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| date | timestamp | YES | | NULL | | Fecha transacción |
| type | varchar(255) | NO | | NULL | | Tipo |
| amount | varchar(255) | NO | | NULL | | Monto |
| commission | varchar(255) | NO | | NULL | | Comisión |
| account_state_id | bigint unsigned | YES | MUL | NULL | | FK account_states |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** account_state_id → account_states.id (CASCADE)

### 3. RELACIONES

- **Referenciada por:** payroll_advances (transaction_id)
- **Referencia a:** account_states

### 6. REGLAS IMPLÍCITAS

- status en migraciones posteriores (EXITOSA, FALLIDA, EN PROCESO); payment_type (ej. SALDO DEL SISTEMA) usado en lógica de “primera transacción”.

### 7. CÓDIGO RELEVANTE

- `app/Models/Transaction.php`, PayrollAdvancesController, Jobs de transacciones.

---

## TABLA: receivable_accounts

### 1. ESTRUCTURA (desde create_receivable_accounts)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| status | int | NO | | NULL | | PENDIENTE, etc. |
| attempts_collection | int | NO | | NULL | | Intentos cobro |
| debit | decimal(20,2) | NO | | NULL | | Débito |
| account_state_id | bigint unsigned | YES | MUL | NULL | | FK account_states |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** account_state_id → account_states.id (CASCADE)

### 6. REGLAS IMPLÍCITAS

- Si hay receivable_accounts con status PENDIENTE para el empleado, se bloquea solicitud de adelanto (incumplimiento de pago).

### 7. CÓDIGO RELEVANTE

- `app/Models/ReceivableAccount.php`, PayrollAdvancesController.

---

## TABLA: payroll_receipts

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| high_employee_id | bigint unsigned | YES | MUL | NULL | | FK high_employees (SET NULL on delete) |
| initial_payment_date | date | NO | | NULL | | Inicio periodo pago |
| final_date_payment | date | NO | | NULL | | Fin periodo pago |
| pdf_string | longtext | NO | | NULL | | Contenido/PDF en base64 o ruta |
| fiscal_folio | varchar(255) | NO | | NULL | | Folio fiscal |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** high_employee_id → high_employees.id (ON DELETE SET NULL)

### 7. CÓDIGO RELEVANTE

- `app/Models/PayrollReceipt.php`, `app/Jobs/PayrollReceipts/CreatePayrollReceiptJob.php`, Admin/PayrollReceiptsController.

---

## TABLA: payroll_advances

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| transaction_id | bigint unsigned | YES | MUL | NULL | | FK transactions |
| account_id | bigint unsigned | YES | MUL | NULL | | FK accounts |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** transaction_id → transactions.id (CASCADE), account_id → accounts.id (CASCADE)

### 7. CÓDIGO RELEVANTE

- `app/Models/PayrollAdvance.php`, Api/PayrollAdvancesController (solicitud, validación cuentas y receivable_accounts, creación transacción y adelanto).

---

## TABLA: payroll_withholding_configs

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| date | date | YES | | NULL | | |
| days | int | YES | | NULL | | |
| weekday | int | YES | | NULL | | Día semana |
| emails | longtext | YES | | NULL | | |
| payment_periodicity | enum('SEMANAL','CATORCENAL','QUINCENAL','MENSUAL') | NO | | NULL | | |
| company_id | bigint unsigned | YES | MUL | NULL | | FK companies |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** company_id → companies.id (CASCADE)

### 7. CÓDIGO RELEVANTE

- `app/Models/PayrollWithholdingConfig.php`, Company tiene hasMany payroll_withholding_configs.

---

## TABLA: chat_rooms

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | YES | | NULL | | Nombre sala |
| file_url | varchar(255) | YES | | NULL | | |
| presigned_url | text | YES | | NULL | | |
| presigned_url_expires_at | timestamp | YES | | NULL | | |
| type | enum('private','group','channel') | NO | | private | | Tipo sala |
| created_by | bigint unsigned | YES | MUL | NULL | | FK **high_employees**.id (SET NULL) |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | Soft delete |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** created_by → high_employees.id (ON DELETE SET NULL)
- **SoftDeletes:** sí

### 3. RELACIONES

- **Referenciada por:** chat_room_users, chat_messages
- **Referencia a:** high_employees (created_by)

### 7. CÓDIGO RELEVANTE

- `app/Models/ChatRoom.php`, Api/ChatController. Importante: created_by es high_employee, no user.

---

## TABLA: chat_room_users

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| chat_room_id | bigint unsigned | NO | MUL | NULL | | FK chat_rooms |
| user_id | bigint unsigned | NO | MUL | NULL | | FK **high_employees**.id (no users) |
| is_admin | tinyint(1) | NO | | 0 | | |
| can_send_messages | tinyint(1) | NO | | 1 | | |
| is_archived | tinyint(1) | NO | | 0 | | |
| archived_at | timestamp | YES | | NULL | | |
| last_read_message_id | bigint unsigned | YES | MUL | NULL | | FK chat_messages (SET NULL) |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Unique:** (chat_room_id, user_id) nombre room_user_unique
- **Index:** user_id (cru_user_index)
- **Foreign Keys:** chat_room_id → chat_rooms.id (CASCADE), user_id → high_employees.id (CASCADE), last_read_message_id → chat_messages.id (SET NULL)

### 7. CÓDIGO RELEVANTE

- `app/Models/ChatRoomUser.php` (user_id → HighEmployee). Pivot: role, muted_until, can_send_messages, last_read_message_id.

### 9. OBSERVACIONES PARA TECBEN-CORE

- Renombrar columna user_id a employee_id en tecben-core para evitar confusión con usuarios del panel.

---

## TABLA: chat_messages

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| chat_room_id | bigint unsigned | NO | MUL | NULL | | FK chat_rooms |
| user_id | bigint unsigned | NO | MUL | NULL | | FK **high_employees**.id (remitente) |
| reply_to_message_id | bigint unsigned | YES | MUL | NULL | | FK chat_messages (SET NULL) |
| message | text | YES | | NULL | | Contenido |
| previous_message | text | YES | | NULL | | Edición |
| file_url | varchar(255) | YES | | NULL | | |
| file_name | varchar(255) | YES | | NULL | | |
| file_size | bigint unsigned | YES | | NULL | | |
| mime_type | varchar(255) | YES | | NULL | | |
| thumbnail_url | varchar(255) | YES | | NULL | | |
| message_type | enum('text','file','image','video','audio','system','other') | NO | | text | | |
| presigned_url | text | YES | | NULL | | |
| thumbnail_presigned_url | text | YES | | NULL | | |
| presigned_url_expires_at | timestamp | YES | | NULL | | |
| thumbnail_url_expires_at | timestamp | YES | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | Soft delete |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** chat_room_id → chat_rooms.id (CASCADE), user_id → high_employees.id (CASCADE), reply_to_message_id → chat_messages.id (SET NULL)
- **Index:** (chat_room_id, created_at), created_at; idx_chat_messages_room_created_desc (room_id, created_at DESC)
- **SoftDeletes:** sí

### 7. CÓDIGO RELEVANTE

- `app/Models/ChatMessage.php` (user_id → HighEmployee), Api/ChatController.

---

## TABLA: voice_employee_subjects

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | NO | | NULL | | Nombre tema |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

(En migraciones posteriores puede existir description, exclusive_for_company; ver modelo VoiceEmployeeSubject.)

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id

### 3. RELACIONES

- **Referenciada por:** voice_employees (voice_employee_subject_id), company_voice_employee_subject, user_voice_employee_subject
- **Referencia a:** ninguna

### 7. CÓDIGO RELEVANTE

- `app/Models/VoiceEmployeeSubject.php` (SoftDeletes, fillable name, description, exclusive_for_company). Pertenece a Rafa en catálogos; se usa en Fase 1/2 para voz.

---

## TABLA: voice_employees

### 1. ESTRUCTURA (create + update)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| date | timestamp | YES | | NULL | | Fecha comentario |
| status | varchar(255) | NO | | NULL | | Pendiente, En Proceso, Atendido, Continuar conversación |
| comments | longtext | NO | | NULL | | Comentario |
| is_anonyme | int | NO | | NULL | | Anónimo (0/1 o ANONIMO) |
| voice_employee_subject_id | bigint unsigned | YES | MUL | NULL | | FK voice_employee_subjects |
| high_employee_id | bigint unsigned | YES | MUL | NULL | | FK high_employees (emisor) |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users (lector) |
| attention_date | timestamp | YES | | NULL | | Fecha atención |
| results | longtext | YES | | NULL | | Resultado/respuesta |
| attenuator_id | bigint unsigned | YES | MUL | NULL | | FK users (quien atiende) |
| assigned_id | bigint unsigned | YES | MUL | NULL | | FK users (asignado) |
| other_subject | varchar(255) | YES | | NULL | | Otro tema (texto libre) |
| priority | varchar(255) | YES | | NULL | | Prioridad |
| images | int | YES | | NULL | | Cantidad imágenes (en migraciones posteriores) |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | SoftDeletes en modelo |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** voice_employee_subject_id → voice_employee_subjects.id (CASCADE), high_employee_id → high_employees.id (CASCADE), user_id → users.id (CASCADE), attenuator_id → users.id (CASCADE); assigned_id en migración posterior → users.id
- **SoftDeletes:** modelo VoiceEmployee usa SoftDeletes

### 6. REGLAS IMPLÍCITAS

- status: Pendiente → En Proceso (al abrir) → Atendido o Continuar conversación. user_id = quien leyó primero; attenuator_id = quien atiende; assigned_id = asignado para seguimiento.

### 7. CÓDIGO RELEVANTE

- `app/Models/VoiceEmployee.php`, Admin/VoiceEmployeesController, Api/VoiceEmployeesController.

---

## TABLA: voice_employee_extras

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| comments | longtext | NO | | NULL | | Comentario extra |
| results | longtext | NO | | NULL | | Resultado |
| voice_employee_id | bigint unsigned | YES | MUL | NULL | | FK voice_employees |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users (en migración; en otra attenuator_id) |
| attenuator_id | bigint unsigned | YES | MUL | NULL | | FK users |
| attention_date | timestamp | NO | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** voice_employee_id → voice_employees.id (CASCADE), user_id → users.id (CASCADE); attenuator_id en migración update → users.id

### 7. CÓDIGO RELEVANTE

- `app/Models/VoiceEmployeeExtra.php`, Admin/VoiceEmployeesController (updateStatus crea/actualiza extras).

---

## TABLA: voice_employee_reiterates

### 1. ESTRUCTURA (desde create_voice_employee_reiterates)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| comments | longtext | NO | | NULL | | |
| results | longtext | NO | | NULL | | |
| voice_employee_id | bigint unsigned | YES | MUL | NULL | | FK voice_employees |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users |
| attenuator_id | bigint unsigned | YES | | NULL | | FK users |
| attention_date | timestamp | NO | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | |

### 7. CÓDIGO RELEVANTE

- `app/Models/VoiceEmployeeReiterate.php`.

---

## TABLA: roles

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | NO | UNI | NULL | | Nombre único (ej. admin) |
| display_name | varchar(255) | YES | | NULL | | |
| description | varchar(255) | YES | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | (Role usa SoftDeletes) |
| company_id | bigint unsigned | YES | MUL | NULL | | FK companies (update_roles_table) |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Unique:** name
- **Foreign Key:** company_id → companies.id (CASCADE) en migración posterior

### 7. CÓDIGO RELEVANTE

- `app/Models/Role.php` (Entrust), role_user pivot, Admin/RolesController.

---

## TABLA: role_user

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| user_id | bigint unsigned | NO | PRI,MUL | NULL | | FK users |
| role_id | bigint unsigned | NO | PRI,MUL | NULL | | FK roles |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** (user_id, role_id)
- **Foreign Keys:** user_id → users.id (CASCADE), role_id → roles.id (CASCADE)

---

## TABLA: permissions

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | NO | UNI | NULL | | |
| display_name | varchar(255) | YES | | NULL | | |
| description | varchar(255) | YES | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Unique:** name

---

## TABLA: permission_role

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| permission_id | bigint unsigned | NO | PRI,MUL | NULL | | FK permissions |
| role_id | bigint unsigned | NO | PRI,MUL | NULL | | FK roles |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** (permission_id, role_id)
- **Foreign Keys:** permission_id → permissions.id (CASCADE), role_id → roles.id (CASCADE)

---

## TABLA: oauth_clients (Laravel Passport)

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | int unsigned | NO | PRI | NULL | auto_increment | PK |
| user_id | bigint | YES | MUL | NULL | | FK users (nullable) |
| name | varchar(255) | NO | | NULL | | Nombre cliente |
| secret | varchar(100) | NO | | NULL | | Secret |
| redirect | text | NO | | NULL | | URL redirect |
| personal_access_client | tinyint(1) | NO | | NULL | | |
| password_client | tinyint(1) | NO | | NULL | | |
| revoked | tinyint(1) | NO | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Index:** user_id (sin FK explícita en migración Passport estándar)

### 7. CÓDIGO RELEVANTE

- Laravel Passport; config/auth.php api driver passport.

---

## TABLA: oauth_access_tokens

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | varchar(100) | NO | PRI | NULL | | Token ID |
| user_id | bigint | YES | MUL | NULL | | FK users |
| client_id | int unsigned | NO | MUL | NULL | | FK oauth_clients |
| name | varchar(255) | YES | | NULL | | |
| scopes | text | YES | | NULL | | |
| revoked | tinyint(1) | NO | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| expires_at | datetime | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Index:** user_id

### 7. CÓDIGO RELEVANTE

- Passport; HasApiTokens en User.

---

## TABLA: oauth_refresh_tokens

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | varchar(100) | NO | PRI | NULL | | PK |
| access_token_id | varchar(100) | NO | MUL | NULL | | Referencia oauth_access_tokens.id |
| revoked | tinyint(1) | NO | | NULL | | |
| expires_at | datetime | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Index:** access_token_id

---

## TABLA: oauth_auth_codes

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | varchar(100) | NO | PRI | NULL | | PK |
| user_id | bigint | NO | | NULL | | FK users |
| client_id | int unsigned | NO | | NULL | | FK oauth_clients |
| scopes | text | YES | | NULL | | |
| revoked | tinyint(1) | NO | | NULL | | |
| expires_at | datetime | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id

---

## TABLA: oauth_personal_access_clients

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | int unsigned | NO | PRI | NULL | auto_increment | PK |
| client_id | int unsigned | NO | MUL | NULL | | FK oauth_clients |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Index:** client_id

---

## TABLA: password_resets

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| email | varchar(255) | NO | MUL | NULL | | Email usuario |
| token | varchar(255) | NO | | NULL | | Token reset |
| created_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Index:** email (para búsqueda rápida)

### 7. CÓDIGO RELEVANTE

- Laravel estándar; config auth passwords.users table = password_resets.

---

## TABLA: verify_2fa

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| user_id | bigint unsigned | NO | MUL | NULL | | FK users |
| contact | varchar(255) | NO | UNI | NULL | | Email/teléfono (único) |
| token | varchar(255) | NO | | NULL | | Código 2FA |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Unique:** contact
- **Foreign Key:** user_id → users.id (CASCADE)

### 7. CÓDIGO RELEVANTE

- `app/Models/Verify2FA.php`, Auth/Google2FAController.

---

## TABLA: witnesses

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users (SET NULL) |
| digital_document_id | bigint unsigned | YES | MUL | NULL | | FK digital_documents (SET NULL) |
| type | varchar(255) | YES | | NULL | | Tipo testigo |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** user_id → users.id (ON DELETE SET NULL), digital_document_id → digital_documents.id (ON DELETE SET NULL)

### 7. CÓDIGO RELEVANTE

- `app/Models/Witness.php`, EmploymentContractController, contratos/firmas.

### 9. OBSERVACIONES PARA TECBEN-CORE

- Si se usa relación polimórfica (testimonio_type, testimonio_id), migrar desde digital_document_id a polymorphic en tecben-core.

---

## TABLA: one_signal_tokens

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| token | varchar(255) | YES | | NULL | | Token push OneSignal |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** user_id → users.id (CASCADE)

### 7. CÓDIGO RELEVANTE

- `app/Models/OneSignalToken.php`. Usuarios del panel (users) tienen tokens para recibir push; el empleado recibe porque su User vinculado tiene one_signal_tokens.

---

## TABLA: direct_debit_belvos

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users |
| date | date | NO | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Key:** user_id → users.id (CASCADE)

### 7. CÓDIGO RELEVANTE

- `app/Models/DirectDebitBelvo.php`, Admin/DirectDebitPaymentController.

---

## TABLA: employment_contracts_tokens

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| employment_contract_id | bigint unsigned | YES | MUL | NULL | | FK employment_contracts (SET NULL) |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users (SET NULL) |
| type | varchar(255) | YES | | NULL | | |
| token | varchar(255) | YES | | NULL | | Token firma |
| signature_date | date | YES | | NULL | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |
| deleted_at | timestamp | YES | | NULL | | SoftDeletes |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** employment_contract_id → employment_contracts.id (SET NULL), user_id → users.id (SET NULL)
- **SoftDeletes:** sí

### 7. CÓDIGO RELEVANTE

- `app/Models/EmploymentContractToken.php`, Api/EmploymentContractsController.

---

## TABLA: digital_documents

### 1. ESTRUCTURA (create + add user_id)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| company_file_id | bigint unsigned | YES | MUL | NULL | | FK company_files |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users (SET NULL) |
| name | varchar(255) | NO | | NULL | | |
| business_name | varchar(255) | YES | | NULL | | |
| needs_authorization | tinyint(1) | NO | | 0 | | |
| is_exclusive | tinyint(1) | NO | | 0 | | |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** company_file_id → company_files.id (CASCADE), user_id → users.id (ON DELETE SET NULL)

### 7. CÓDIGO RELEVANTE

- `app/Models/DigitalDocument.php`, Admin/FileCompanyController, Api/DigitalDocumentsController.

---

## TABLA: folders

### 1. ESTRUCTURA (create_folders + add user_id)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | NO | | NULL | | |
| company_id | bigint unsigned | YES | MUL | NULL | | FK companies |
| url | varchar(255) | NO | | NULL | | Ruta/URL |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users (en add_user_id_to_folders) |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** company_id → companies.id (CASCADE), user_id → users.id

### 7. CÓDIGO RELEVANTE

- `app/Models/Folder.php`, Admin/FolderController.

---

## TABLA: high_employee_filters

### 1. ESTRUCTURA

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| name | varchar(255) | NO | | NULL | | Nombre filtro |
| genders | longtext | YES | | NULL | | JSON o lista |
| months | longtext | YES | | NULL | | |
| age_from | int | YES | | NULL | | |
| age_till | int | YES | | NULL | | |
| month_filter_from | int | YES | | NULL | | |
| month_filter_till | int | YES | | NULL | | |
| user_id | bigint unsigned | YES | MUL | NULL | | FK users |
| area_id | bigint unsigned | YES | MUL | NULL | | FK areas |
| department_id | bigint unsigned | YES | MUL | NULL | | FK departments |
| location_id | bigint unsigned | YES | MUL | NULL | | FK locations |
| position_id | bigint unsigned | YES | MUL | NULL | | FK positions |
| company_id | bigint unsigned | YES | MUL | NULL | | FK companies |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 2. ÍNDICES Y CONSTRAINTS

- **Primary Key:** id
- **Foreign Keys:** user_id → users.id (CASCADE), area_id, department_id, location_id, position_id, company_id → tablas respectivas (CASCADE)

### 3. RELACIONES

- **Referenciada por:** user_filter_high_employee (pivot con high_employees)
- **Referencia a:** users, areas, departments, locations, positions, companies

### 7. CÓDIGO RELEVANTE

- `app/Models/HighEmployeeFilter.php`, Admin/HighEmployeeFiltersController. Filtros guardados por usuario para segmentar empleados (encuestas, notificaciones, voz).

---

## TABLA: chat_message_status

### 1. ESTRUCTURA (inferida desde migración create_chat_message_status)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| chat_message_id | bigint unsigned | NO | MUL | NULL | | FK chat_messages |
| user_id | bigint unsigned | NO | MUL | NULL | | FK **high_employees**.id |
| status | varchar(255) | YES | | NULL | | enviado, entregado, leído |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 7. CÓDIGO RELEVANTE

- `app/Models/ChatMessageStatus.php` (user_id → HighEmployee).

---

## TABLA: chat_message_mentions

### 1. ESTRUCTURA (inferida)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| chat_message_id | bigint unsigned | NO | MUL | NULL | | FK chat_messages |
| user_id | bigint unsigned | YES | MUL | NULL | | FK high_employees (mencionado) |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 7. CÓDIGO RELEVANTE

- `app/Models/ChatMessageMention.php` (user_id → HighEmployee).

---

## TABLA: chat_message_reactions

### 1. ESTRUCTURA (inferida)

| Campo | Tipo | Nulo | Clave | Default | Extra | Descripción |
|-------|------|------|-------|---------|-------|-------------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment | PK |
| chat_message_id | bigint unsigned | NO | MUL | NULL | | FK chat_messages |
| user_id | bigint unsigned | NO | MUL | NULL | | FK high_employees |
| reaction | varchar(255) | YES | | NULL | | Tipo reacción |
| created_at | timestamp | YES | | NULL | | |
| updated_at | timestamp | YES | | NULL | | |

### 7. CÓDIGO RELEVANTE

- `app/Models/ChatMessageReaction.php` (user_id → HighEmployee).

---

## Resumen tablas Fase 2 y referencia Rafa

Para el resto de tablas de **Fase 2** (location_histories, area_histories, position_histories, department_histories, business_names_histories, region_histories, payment_periodicity_histories, requests_type, requests_status, request_categories, approval_flow_stages, requests, authorization_stage_approvers, status_histories, survey_categories, surveys, survey_sections, survey_questions, survey_responses, survey_shippings, high_employee_survey_shipping, nom35_sections, nom35_sections_responses, acknowledgments, acknowledgment_company, acknowledgment_shippings, acknowledgment_high_employee, notifications, high_employee_notification, notification_templates, excluded_notifications, notifications_frequencies, company_files, company_folder, digital_documents_requests, digital_documents_generated, digital_document_signs_locations, messages, high_employee_message, message_response, capacitations, capacitation_*, high_employee_capacitation, proof_skills, belvo_*, imss_nubarium_logs, ine_nubarium, voice_employees_tableu, messages_tableu, devices, device_locations, moods, festivities, readmissions, readmission_histories, user_records):

- La **estructura** se obtiene de la misma forma: abrir la migración que hace `Schema::create('nombre_tabla')` y las que hacen `Schema::table('nombre_tabla')` en `database/migrations/`.
- Las **relaciones** están en los modelos en `app/Models/` (nombre en PascalCase o nombre directo, ej. `LocationHistory`, `Survey`, `Acknowledgment`).
- **Rafa:** En el legacy las tablas de catálogos tienen nombres en **inglés**: `industries`, `sub_industries`, `companies`, `business_name`, `products`, `cost_centers` (o `cost_centers`), `app_settings` (configuración app), `commission_ranks`, `personalized_fortnights`, `banks`, `locations`, `departments`, `positions`, `regions`, `payment_centers`, `voice_employee_subjects`, `transaction_type_aliases`, `logs`, `excluded_notifications`, `notifications_frequencies`, `exit_poll_reasons`, `company_product`, `business_name_company`, `company_cost_center`, `acknowledgment_company`, `company_voice_employee_subject`, etc. No existe en el código una tabla llamada `empresas` ni `razones_sociales`; son `companies` y `business_name`.

---

## Anexo: Consultas SQL para ejecutar en la BD

Ejecutar en la base de datos **legacy** para obtener tipos exactos, índices y datos de ejemplo. Sustituir `nombre_tabla` por cada tabla.

```sql
-- Estructura exacta (MySQL)
DESCRIBE nombre_tabla;

-- Índices
SHOW INDEX FROM nombre_tabla;

-- Foreign keys (MySQL 8 / information_schema)
SELECT 
  COLUMN_NAME, 
  REFERENCED_TABLE_NAME, 
  REFERENCED_COLUMN_NAME,
  CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'nombre_tabla' 
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Ejemplos de datos (ajustar columnas según tamaño)
SELECT * FROM nombre_tabla LIMIT 5;

-- Triggers (si existieran)
SHOW TRIGGERS LIKE 'nombre_tabla';

-- Eventos
SHOW EVENTS WHERE Name LIKE '%nombre_tabla%';
```

**Tablas prioritarias para ejecutar primero:** users, high_employees, high_employee_product, accounts, account_states, transactions, receivable_accounts, payroll_receipts, payroll_advances, voice_employees, voice_employee_extras, chat_rooms, chat_room_users, chat_messages, roles, role_user, verify_2fa, witnesses, digital_documents, folders, high_employee_filters.

---

## Conclusiones y recomendaciones generales

1. **Nomenclatura:** En tecben-core unificar idioma (español para negocio o inglés técnico) y renombrar columnas ambiguas (ej. `user_id` en chat → `employee_id`).
2. **Unicidad:** Restaurar UNIQUE en `users.email` o documentar política por tenant.
3. **Soft deletes:** Mantener en tablas que lo usan; documentar qué listados excluyen eliminados.
4. **Chat:** Todas las FK “user_id” en chat apuntan a `high_employees.id`; dejar explícito en esquema y modelos.
5. **Pivot high_employee_product:** Conservar status, reason y change_type con valores documentados.
6. **Ejecutar anexo SQL** en la BD legacy para validar tipos y constraints reales antes de generar migraciones en tecben-core.

*Documento generado a partir de migraciones y modelos del proyecto Paco (solo lectura).*
