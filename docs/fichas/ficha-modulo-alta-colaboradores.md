# Ficha técnica: Módulo Alta de Colaboradores (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Alta de Colaboradores (high_employees)

Permite dar de alta, editar, listar, filtrar y dar de baja (soft delete + registro en low_employees) a colaboradores/empleados. Incluye carga masiva, edición masiva, plantillas, historiales de ubicación/área/departamento/puesto/región/razón social/periodicidad de pago, asignación de productos por empresa, beneficiarios, cuentas de nómina, encuesta de salida y cuentas por cobrar al momento de la baja.

---

## ENTIDADES

### Tabla principal: `high_employees`

- **PK:** `id` (bigint unsigned).
- **Campos relevantes (evolución por migraciones):** name, mother_last_name, paternal_last_name, birthdate, email, mobile, employee_number, periodicity_payment, periodicity_day, net_income, max_amount, gross_salary, daily_salary, daily_salary_integrated, salary_variable, admission_date, rfc, curp, business_name (FK id a business_names), gender, code, company_id (FK companies), location_id, department_id, area_id, position_id, region_id, payment_center_id, verified, massive_load_verification, update_employee, has_dni, social_security_number, boss_code, date_verification_mobile, annual_vacation_days, remaining_vacation_days, address, entry_time, departure_time, lunch_entry_time, lunch_departure_time, extra_day_entry_time, extra_day_departure_time, nationality, marital_status, additional_comment, payment_company_name, imss_registration_date.
- **Soft deletes:** deleted_at (migración update_high_employee_11).
- **Relaciones (modelo):** company, department, area, position, location, region, payment_center, user (1:1), accounts, low_employee (1:1), beneficiary (1:N), products (N:M con pivot status, reason, change_type), historiales (location_histories, area_histories, department_histories, position_histories, region_histories, business_names_histories, payment_periodicity_histories), receivable_accounts, filters (N:M user_filter_high_employee).

### Tabla: `low_employees`

- **PK:** id. **FK:** high_employee_id → high_employees (cascade). company_id, location_id, department_id, area_id, position_id (guardados al momento de la baja).
- **Campos:** reason, date, is_scheduled ('PROGRAMADA' | 'NO PROGRAMADA'), comments, imss_last_update_date. SoftDeletes.

### Tabla: `high_employee_beneficiaries`

- **PK:** id. **FK:** high_employee_id → high_employees (onDelete set null en migración CreateTableBeneficiary).
- **Campos:** full_name, relationship, percent (decimal nullable).

### Tablas de historial (mismo patrón: start_date, end_date nullable, FK high_employee_id y FK al catálogo)

- location_histories, area_histories, department_histories, position_histories, region_histories, payment_periodicity_histories.
- business_names_histories: high_employee_id, business_name_id (FK a business_name), start_date, end_date.

### Otras tablas relacionadas (solo referencia)

- accounts (cuenta nómina por empleado), receivable_accounts, high_employee_product (productos por empleado), user_filter_high_employee (filtros de empleados), acknowledgment_high_employee, high_employee_message, survey_responses, payroll_receipts, employment_contracts, etc.

---

## REGLAS DE NEGOCIO

- **RN-01:** Email o móvil obligatorio (al menos uno). Validación: required_without en ambos.
- **RN-02:** Unicidad de email y móvil entre **todos** los high_employees (sin filtrar por company_id). Si ya existe otro empleado con ese email o móvil, no se permite crear.
- **RN-03:** Unicidad de cuenta de nómina (número de cuenta) entre cuentas con high_employee no eliminado (deleted_at NULL).
- **RN-04:** Si el empleado tiene deudas (receivable_accounts en PENDIENTE, CONTRACARGO o INCOBRABLE), se marca como INACTIVO en productos de tipo Adelanto de Nómina, Pago de Servicios, Recargas; y no se envía notificación de adelanto disponible.
- **RN-05:** Productos de la empresa se asignan al empleado según enable_from (meses desde admisión), filtros de producto por empresa (región, ubicación, área, departamento, puesto, género, meses cumpleaños, edad, meses desde ingreso) y deuda (RN-04).
- **RN-06:** Si la empresa tiene fourteen_monthly_next_payment_date cuando periodicity_payment es CATORCENAL, es obligatorio; si no, se rechaza el alta.
- **RN-07:** Fecha de baja no puede ser menor o igual a la fecha de ingreso.
- **RN-08:** No puede haber dos bajas programadas para el mismo empleado con date > hoy.
- **RN-09:** Al dar de baja (NO PROGRAMADA) se cierra account_state ACTIVO, se calcula suma de transacciones EXITOSA / SALDO DEL SISTEMA y se crea receivable_account PENDIENTE; se desvincula seguro (job DeleteMembership) y descuentos (ChangeDiscountSubscriptionStatus); si la empresa tiene encuesta de salida y razón en exit_poll_reasons, se envía encuesta.
- **RN-10:** Historiales: al cambiar ubicación, área, departamento, puesto, región, razón social o periodicidad de pago se cierra el historial actual (end_date = hoy) y se crea nuevo registro con start_date = hoy.
- **RN-11:** Código de jefe (boss_code) se arma como concatenación location_id.department_id.area_id.position_id (guardado como string).
- **RN-12:** Carga masiva y edición masiva solo permitidas si el usuario tiene company_id; se usa la empresa del usuario para validar ubicaciones, departamentos, áreas, puestos, regiones y razones sociales del Excel.

---

## FLUJO PRINCIPAL

### Alta manual (create)

1. Validar request (nombre, apellidos, email/móvil, birthdate, max_amount, gross_salary, net_income, account, admission_date, curp, social_security_number).
2. Comprobar deudas (RFC/CURP) en receivable_accounts PENDIENTE/CONTRACARGO/INCOBRABLE.
3. Comprobar unicidad email y móvil (global).
4. Si hay cuenta, comprobar que no exista otra cuenta con ese número en empleados activos.
5. Comprobar empresa con fourteen_monthly_next_payment_date si periodicity_payment es CATORCENAL.
6. Parsear admission_date desde formato d/m/Y (explode por "/" y DateTime::createFromFormat).
7. Crear HighEmployee, asignar company, location, department, area, position (y opcional region, payment_center); crear historiales (location, department, area, position, region, business_name, payment_periodicity); guardar boss_code.
8. Si hay beneficiarios, crear HighEmployeeBeneficiary y asociar.
9. Si hay account, crear Account (tipo por longitud 18/16), asociar banco y empleado.
10. Asignar productos de la empresa al empleado (status ACTIVO/INACTIVO según filtros y deuda).
11. Si la empresa tiene high_employee_filters, asociar empleado a los filtros que cumpla.
12. Despachar AccountStateJob, AssignedNewOrEditHighEmployee (capacitación); si empresa activa, enviar SMS/Email bienvenida y opcionalmente notificación adelanto nómina.
13. Registrar log.

### Baja (Trash)

1. Obtener empleado; validar que no exista baja programada futura; validar fecha de baja > admission_date; si diff.invert == 1 y hay encuesta de salida, validar que exista encuesta y razón.
2. Crear LowEmployee (reason, date, is_scheduled PROGRAMADA/NO PROGRAMADA según diff.invert), asociar area, company, department, position, location.
3. Si is_scheduled == NO PROGRAMADA: cerrar account_state ACTIVO (until = hoy), crear receivable_account con suma de transacciones, desencadenar jobs de seguro y descuentos, y si aplica crear envío de encuesta de salida.
4. Registrar log.

### Listado (getIndex / getFilters)

1. Scope: si el usuario tiene high_employee_filters → HighEmployee::query() y filtrar por whereHas('filters', whereIn filter_id). Si no tiene filtros y no tiene company → HighEmployee::query(). Si no tiene filtros y tiene company → $user->company->high_employees().
2. Aplicar filtros opcionales (companies, locations, areas, departments, positions, has_dni, búsqueda por nombre/email/móvil/empresa/área/etc.).
3. Ordenar y paginar; para selectores de filtro se clona la query y se hacen leftJoin a catálogos para obtener valores distintos.

---

## VALIDACIONES

- **name:** required.
- **mother_last_name, paternal_last_name:** required.
- **email:** required_without:mobile, nullable, email.
- **mobile:** required_without:email, nullable, numeric, digits:10.
- **birthdate:** required, date.
- **max_amount:** required, regex:/^[0-9]{1,3}(,[0-9]{3})*\.[0-9]+$/ (formato con comas de miles).
- **gross_salary, net_income:** nullable, mismo regex.
- **account:** nullable, numeric, min:0.
- **admission_date:** required (sin formato date en reglas; en código se espera d/m/Y).
- **curp:** nullable, regex:/^[A-Za-z0-9\w]{18}+$/ (⚠️ el + tras {18} sobra en regex).
- **social_security_number:** nullable, min:11, max:11.
- **Carga masiva:** file required, mimes:xlsx. Usuario debe tener company_id.

---

## PERMISOS

- **view_high_employees:** listar, ver detalle, filtros, exportar Excel, empleados registrados en app, descargas.
- **create_high_employees:** formulario crear, create(), vista carga masiva, loadMassive().
- **edit_high_employees:** formulario editar, update(), edición masiva, subir archivos (junto con upload_high_employees_files en rutas que lo requieran), plantillas.
- **trash_high_employees:** getTrash, Trash, TrashEdit, TrashCancel, plantilla bajas, carga masiva bajas.
- **upload_high_employees_files:** subir archivos del empleado (ej. DNI).
- **view_employment_history:** ver bloque de historial laboral (Palenca/IMSS) en vista empleado.
- **view_insurance_policy_document:** ver documento de póliza en vista empleado.
- **load_authorizers:** ver/descargar autorizadores.

---

## CASOS BORDE

- **Usuario con high_employee_filters:** el listado usa HighEmployee::query() y solo filtra por whereHas('filters'). No se restringe por company_id. Un usuario de una empresa con filtros asignados podría ver empleados de otras empresas que compartan esos filtros.
- **admission_date:** si el request envía un formato distinto a d/m/Y (por ejemplo Y-m-d o un solo número), explode("/") y createFromFormat pueden generar errores o fechas incorrectas.
- **Crear sin company en request:** no hay validación required para request->company; si falta, Company::find($request->company) es null y más adelante $company->high_employees() y $company->products fallan.
- **Edición cambiando de empresa:** se permite; receivable_accounts del empleado se actualizan con el nuevo company_id y catálogos. No hay comprobación de que el usuario pueda asignar la empresa destino.
- **Beneficiarios:** no se valida que la suma de percent sea 100 ni que relationship sea un valor permitido.
- **low_employees.is_scheduled:** en migración create_low_employees es integer; en modelo fillable y en código se usa 'PROGRAMADA'/'NO PROGRAMADA' (string). Posible inconsistencia si la columna fue cambiada después a enum/string.

---

## BUGS E INCONSISTENCIAS

1. **Scope de listado con filtros:** Si el usuario tiene `high_employee_filters`, la query base es `HighEmployee::query()` (todos los empleados). Solo se restringe por filtros asignados. Esto puede hacer que un admin de empresa vea empleados de otras empresas que compartan esos filtros.
2. **Unicidad email/móvil global:** La comprobación es `HighEmployee::where("email", ...)` y `where("mobile", ...)` sin company_id. Dos empresas no pueden tener empleados con el mismo email o móvil.
3. **CURP regex:** `^[A-Za-z0-9\w]{18}+$` — el `+` después de `{18}` es redundante o erróneo; debería ser `{18}`.
4. **admission_date sin validación de formato:** La regla solo exige required; el código asume d/m/Y. Si llega otro formato, explode o createFromFormat pueden fallar.
5. **company obligatorio no validado:** En create() se usa $request->company sin regla required; si no se envía, fallan $company->high_employees(), $company->products, etc.
6. **Bank id 23 excluido:** Para usuarios no admin se usa `Bank::where('id','!=',23)`. Valor mágico sin constante ni documentación.
7. **BusinessNameHistory en create:** Se crea con solo start_date; la tabla tiene end_date nullable. Al asociar con high_employee y business_name se reutiliza el mismo registro; en update sí se cierra el historial anterior con end_date. En create no hay "cierre" previo (correcto para alta).
8. **Referencia a tabla business_name:** La migración business_names_histories hace foreign a `business_name`; en el código se usa el modelo BusinessName (que puede usar tabla business_names). Verificar que el nombre de tabla coincida.
9. **low_employees.is_scheduled:** Migración inicial create_low_employees usa integer; en Trash se asigna 'PROGRAMADA'/'NO PROGRAMADA'. Si la columna sigue siendo integer, puede haber error o conversión implícita.
10. **$has_filter sin inicializar en bucle de productos:** En create(), dentro del foreach de product_filters se usa $has_filter = true al inicio de cada iteración; si product_filters está vacío no se entra al foreach y más abajo se usa $product_status; en ese caso el else usa "ACTIVO". Si product_filters existe y tiene elementos pero por alguna razón $has_filter no se define en algún camino, podría haber undefined. Revisión del flujo muestra que en el foreach siempre se asigna true y luego se pone false según condiciones, así que al salir $has_filter está definido. Solo si product_filters no existe se usa el else con ACTIVO. No es bug pero el flujo es denso.

---

## PROBLEMAS TÉCNICOS

- **Controlador muy grande:** HighEmployeesController tiene miles de líneas; lógica de productos, filtros, notificaciones y bajas mezclada en el mismo archivo.
- **Duplicación de lógica de filtros de productos:** La evaluación de product_filters (región, ubicación, área, departamento, puesto, género, meses, edad, meses desde ingreso) se repite para asignación de productos y para high_employee_filters en el mismo create(); podría extraerse a un servicio.
- **Clonación de query para selectores:** En getIndex y getFilters se clona varias veces la query para construir companies, areas, positions, etc.; con muchos registros puede ser costoso.
- **Uso de archivos locales:** Ruta de firma `assets/signatures/".$this->id."/signature.png` y logos `assets/companies/photos/".$id.".png`; sin uso de almacenamiento configurable (S3 ya se usa en otros puntos del mismo controlador).
- **Jobs y colas:** AccountStateJob, AssignedNewOrEditHighEmployee, DeleteMembership, ChangeDiscountSubscriptionStatus, NotificationSms, NotificationEmail, NotificationPush dependen de colas; si no se procesan, el estado puede quedar desincronizado.

---

## MODELOS INVOLUCRADOS

- **HighEmployee** (App\Models\HighEmployee): fillable amplio, SoftDeletes, relaciones con company, location, area, department, position, region, payment_center, user, accounts, low_employee, beneficiary, products, historiales, receivable_accounts, filters, etc. Métodos: hasProducts, hasProductsActive, hasProductReason, validationEmployeeHistory, scopes BirthdaysBetween, AnniversariesBetween.
- **HighEmployeeBeneficiary:** fillable high_employee_id, full_name, relationship, percent. belongsTo HighEmployee withTrashed.
- **LowEmployee:** SoftDeletes, fillable reason, date, is_scheduled, comments, high_employee_id, company_id, location_id, department_id, area_id, position_id, imss_last_update_date. Relaciones a high_employee, company, location, department, area, position.
- **BusinessNameHistory:** start_date, end_date; relaciones high_employee, business_name. Tabla business_names_histories con high_employee_id y business_name_id (FK a business_name).

---

## MIGRACIONES PRINCIPALES (high_employees y relacionadas)

- **2019_09_16_195440_create_high_employees_table:** Crea high_employees (company string, location string, mother_last_name, paternal_last_name, birthdate, email, mobile, employee_number, direct_boss, area_boss, periodicity_payment, net_income, max_amount, account, admission_date, user, code, bank_id, position_id, department_id).
- **2019_09_27_210714_update_high_employees_tables:** Sustituye company por company_id (FK companies).
- **2019_10_01_203059_update_high_employees_2_table:** Añade rfc.
- **2019_10_04_150953_update_high_employees_3_table:** Añade location_id (FK locations).
- **2019_10_04_205145_update_high_employees_4_table:** Añade business_name (string).
- **2019_10_05_update_high_employees_5:** drop location (string).
- **2019_10_07_update_high_employees_6:** area_id (FK areas). 🔧 DEUDA: down() usa dropForeign('companies_area_id_foreign'); debería ser high_employees_area_id_foreign.
- **2019_10_15_003231_update_high_employees_7_table:** gender.
- **2019_10_15_162046_update_high_employees_8_table:** net_income y max_amount pasan a string.
- **2019_10_11_232329_update_high_employees_6_table:** area_id.
- **2020_02_26_074720_update_high_employee_11_table:** softDeletes en high_employees.
- **2020_02_26_070339_create_low_employees_table:** Crea low_employees (reason, date, is_scheduled integer, high_employee_id).
- **2025_01_06_000005_create_table_beneficiary:** Crea high_employee_beneficiaries (high_employee_id, full_name, relationship, percent). FK high_employees onDelete set null.
- **2024_08_23_113024_create_business_names_histories_table:** Crea business_names_histories (start_date, end_date nullable, high_employee_id, business_name_id FK a business_name).
- Otras muchas migraciones añaden columnas a high_employees (verified, region_id, annual_vacation_days, remaining_vacation_days, salary_variable, entry_time, departure_time, nationality, marital_status, additional_comment, payment_center_id, imss_registration_date, etc.).

---

## AMBIGÜEDADES

- **Sentido de update_employee ('SI'/'NO'):** Se pone 'SI' en create y 'NO' en update; no está documentado si lo usa otro módulo o reporte.
- **Campo business_name en high_employees:** Es FK a business_names (id); el modelo usa social_reason() con segundo argumento 'business_name'. La migración 2019_10_04_205145 añade como string; luego pudo haber migración que lo convirtiera a unsignedBigInteger; no revisado todas las migraciones.
- **Filtros de empleados (high_employee_filters):** Si un usuario tiene filtros, se entiende que solo debe ver empleados que cumplan esos filtros, pero la implementación actual no restringe por company_id cuando hay filtros, lo que puede ser intencional (super admin con filtros) o un error de scope.

---

## DEUDA TÉCNICA

- **Validación de empresa en create/update:** No se comprueba que el usuario (no admin) solo pueda asignar su propia empresa; un request manipulado podría asignar otra company_id.
- **Formato de montos:** Se usa regex con comas de miles; getAmount() en controlador normaliza; mezcla de entrada formateada y guardado en BD como decimal/string.
- **Idioma de mensajes:** Mensajes de error y lógica en español; constantes y valores (PROGRAMADA, NO PROGRAMADA, ACTIVO, INACTIVO) en español.
- **Uso de isset($user->company):** En varios sitios se usa isset($user->company) para decidir scope; si la relación company no se carga, puede dar comportamiento distinto.
- **Logs:** Se crea Log y se asocia a user y opcionalmente a company del usuario; no hay trazabilidad del empleado afectado en la tabla logs más allá del texto en action.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
