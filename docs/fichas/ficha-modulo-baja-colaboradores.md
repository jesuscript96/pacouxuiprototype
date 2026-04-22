# Ficha técnica: Módulo Baja de Colaboradores (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Baja de Colaboradores (low_employees)

Gestiona la baja de empleados (colaboradores): registrar baja inmediata o programada, listar/ver/editar bajas, reenviar encuesta de salida, generar reporte de encuesta de salida, restaurar (reingreso creando nuevo high_employee) y cancelar baja programada. La **ejecución de la baja** (crear registro LowEmployee y, si es inmediata, soft-delete de HighEmployee, cuenta por cobrar, encuesta de salida, etc.) se hace en **HighEmployeesController** (Trash, TrashEdit, TrashCancel). **LowEmployeesController** se encarga del listado, vista detalle, edición del empleado ya dado de baja, reenvío de encuesta, reporte, "restaurar" (reingreso) y carga masiva de bajas.

---

## ENTIDADES

### Tabla: `low_employees`

- **PK:** id (bigint unsigned).
- **Campos:** reason (string), date (timestamp nullable; en código se usa como fecha de baja), is_scheduled (string: 'PROGRAMADA' | 'NO PROGRAMADA'), high_employee_id (FK → high_employees, cascade), comments (longText nullable), company_id, location_id, department_id, area_id, position_id (añadidos en update_low_employees_4; snapshot del empleado al momento de la baja), imss_last_update_date (date nullable).
- **Soft deletes:** deleted_at (migración update_low_employees_3).
- **Relaciones (modelo LowEmployee):** high_employee (belongsTo, withTrashed), company, department, area, position, location (belongsTo, withTrashed), readmission (hasOne Readmission).

### Tabla: `high_employees` (contexto)

- La baja se registra creando un LowEmployee y, si la baja es **NO PROGRAMADA**, se hace soft delete del HighEmployee. Los listados de "bajas" parten de HighEmployee::onlyTrashed()->has('low_employee').

### Tabla: `readmissions` (reingresos)

- **FK:** low_employee_id → low_employees. Relaciona una baja con un reingreso (previous_admission_date, new_admission_date, etc.).

---

## REGLAS DE NEGOCIO

- **RN-01:** No puede haber más de una baja **programada** (fecha futura) por empleado. Si ya existe low_employee con is_scheduled = 'PROGRAMADA' y date > hoy, no se permite otra baja.
- **RN-02:** La fecha de baja no puede ser menor o igual a la fecha de ingreso (admission_date) del empleado.
- **RN-03:** Si la baja es **inmediata** (fecha ≤ hoy, diff->invert == 1): (1) se cierra el account_state ACTIVO (until = hoy 23:59:59); (2) si hay transacciones EXITOSA / SALDO DEL SISTEMA se crea ReceivableAccount PENDIENTE con debit = suma(amount+commission), payment_date = próximo día laboral; (3) se despachan jobs DeleteMembership (seguro) y ChangeDiscountSubscriptionStatus (descuentos); (4) si la empresa tiene exit_poll_reasons y allow_exit_poll = SI y la razón está en exit_poll_reasons, se envía encuesta de salida (SurveyShipping) al empleado; (5) si no aplica encuesta de salida pero el empleado tiene user, se revocan tokens y se elimina el user; (6) se hace soft delete del HighEmployee.
- **RN-04:** Si la baja es **programada** (fecha > hoy), solo se crea/actualiza el LowEmployee y se asocian company, location, department, area, position; no se hace delete del HighEmployee hasta que se ejecute la programada (fuera de este flujo o en otro proceso).
- **RN-05:** Encuesta de salida: se busca Survey con exit_poll = 'Sí' y (company_id de la empresa del empleado o company_id null). La razón de baja debe existir en exit_poll_reasons de la empresa y allow_exit_poll = 'SI' para enviar encuesta. Si no hay encuesta de salida configurada y la baja es inmediata con razón que exigiría encuesta, se rechaza con "No existe una encuesta de salida para enviar".
- **RN-06:** Al **editar** una baja ya registrada (TrashEdit o LowEmployeesController@update): se actualiza date, reason, comments, is_scheduled y snapshots (area, company, etc.). Si se cambia a NO PROGRAMADA se ejecuta la misma lógica de cierre de cuenta, receivable_account, encuesta, delete de HighEmployee que en Trash.
- **RN-07:** **Cancelar baja programada** (TrashCancel): se hace soft delete del LowEmployee; el HighEmployee sigue activo (no está onlyTrashed).
- **RN-08:** **Restaurar** (LowEmployeesController@restore): no restaura el HighEmployee borrado; crea un **nuevo** HighEmployee (reingreso) con los datos del formulario, historiales nuevos, opcionalmente cuenta nómina, y crea registro Readmission con previous_admission_date (del empleado dado de baja) y new_admission_date. El empleado anterior sigue soft-deleted; el "restaurar" es en realidad un reingreso con nuevo id.
- **RN-09:** Listado de bajas: solo empleados que están **soft-deleted** (onlyTrashed) y tienen al menos un low_employee. Scope por empresa si el usuario tiene company y no tiene high_employee_filters; si tiene filtros, scope por whereHas('filters', ...) sobre HighEmployee (igual que en Alta de Colaboradores).
- **RN-10:** En **edición de empleado dado de baja** (LowEmployeesController@update): si el usuario no es admin y la cuenta de nómina se cambia y tiene receivable_accounts PENDIENTE, se rechaza la edición.

---

## FLUJO PRINCIPAL

### Registrar baja (HighEmployeesController@Trash)

1. Obtener HighEmployee por high_employee_id.
2. Validar: no exista baja programada futura; si la baja es inmediata y aplica encuesta, que exista encuesta de salida; fecha de baja > admission_date.
3. Crear Log y asociar a user y company del usuario.
4. Crear LowEmployee (date, reason, comments, is_scheduled PROGRAMADA o NO PROGRAMADA según si date es futura).
5. Asociar low_employee a high_employee; asociar area, company, department, position, location al low_employee (snapshot).
6. Si is_scheduled == 'NO PROGRAMADA': cerrar account_state ACTIVO; si hay transacciones SALDO DEL SISTEMA crear ReceivableAccount; despachar DeleteMembership y ChangeDiscountSubscriptionStatus; si aplica, crear SurveyShipping de encuesta de salida y notificación; si no aplica encuesta pero hay user, revocar tokens y eliminar user; hacer $high_employee->delete() (soft delete).
7. Redirigir con mensaje de éxito.

### Editar baja programada (HighEmployeesController@TrashEdit)

- Misma validación de fecha y encuesta. Se obtiene el low_employee existente con $high_employee->low_employee()->first(), se actualiza date, reason, comments, is_scheduled y snapshots. Si se pasa a NO PROGRAMADA se ejecuta la misma lógica de cierre de cuenta, receivable, encuesta y delete que en Trash.

### Cancelar baja programada (HighEmployeesController@TrashCancel)

- Se obtiene el low_employee con $high_employee->low_employee()->first() y se hace $low_employee->delete() (soft delete). El HighEmployee no se toca (sigue activo).

### Listado de bajas (LowEmployeesController@getIndex)

1. Base: HighEmployee::onlyTrashed()->has('low_employee'). Scope: con company del usuario si no tiene filtros; si tiene high_employee_filters, filtrar por whereHas('filters', ...).
2. Paginar sobre LowEmployee donde high_employee_id esté en el subquery de high_employees filtrados (fromSub), orderBy id desc.
3. Selectores (companies, areas, positions, locations, departments) se construyen con leftJoin sobre la misma base de high_employees.

### Ver detalle de baja (LowEmployeesController@getView)

- Recibe high_employee_id. Busca HighEmployee::onlyTrashed()->where('id', high_employee_id). Muestra datos del empleado, productos, encuesta de salida (última recibida), historial laboral Palenca, documentos INE (facial, front, back). Bug: en el mensaje de error se usa variable $low_employee_id (no definida en la firma); debería ser $high_employee_id.

### Editar empleado dado de baja (LowEmployeesController@update)

- Actualiza el HighEmployee onlyTrashed (nombre, apellidos, email, móvil, fechas, catálogos, historiales, cuenta nómina si aplica). Valida unicidad email/móvil excluyendo el id del empleado actual. Valida fecha de baja > admission_date. No restaura el empleado (sigue borrado); solo modifica datos del registro soft-deleted.

### Reenviar encuesta de salida (LowEmployeesController@resendExitPoll)

- Obtiene el empleado onlyTrashed. Si ya tiene un envío de encuesta de salida: borra respuestas (SurveyResponse, SectionResponse, SurveyTotal) de ese empleado en ese envío; si el envío tiene otros receptores y está cerrada o vencida, crea un nuevo SurveyShipping y asocia al empleado; si no tenía envío o se crea nuevo, crea notificación y push. Bug: en mensaje de error se usa $low_employee_id en lugar de $high_employee_id.

### Restaurar / Reingreso (LowEmployeesController@restore)

- Crea un **nuevo** HighEmployee con los datos del formulario, historiales, business_name_history, payment_periodicity_history, opcionalmente cuenta nómina; si el empleado dado de baja tenía discount_subscription se reasigna al nuevo high_employee_id; crea Readmission con previous_admission_date y new_admission_date. No se elimina ni restaura el HighEmployee antiguo; queda como reingreso con nuevo id.

### Carga masiva de bajas (LowEmployeesController@lowMassive)

- Valida file required, mimes:xlsx. Importa con LowCollaboratorsImport (sin parámetros de empresa en el constructor). No se valida company del usuario (código que comprobaba user->company está comentado); quien tenga permiso trash_high_employees puede ejecutarla.

---

## VALIDACIONES

- **Trash/TrashEdit:** date en formato d/m/y (createFromFormat('d/m/y H:i:s', $request->date.' 00:00:00')); reason y comments opcionales.
- **LowEmployeesController@update:** name, apellidos, email/mobile (required_without), birthdate, max_amount, gross_salary, net_income, admission_date, account; date_low en formato d/m/Y; fecha de baja > admission_date.
- **LowEmployeesController@restore:** Mismas reglas que update (nombre, email, móvil, fechas, montos); no se valida CURP en restore en el fragmento revisado.
- **Carga masiva:** file required, mimes:xlsx.

---

## PERMISOS

- **view_low_employees:** getIndex, getList, getView, getFilters, resendExitPoll, generateExitPollReport, getRestore, restore, getEdit, update. Rutas admin/low_employees/* (excepto las que exigen trash_high_employees).
- **trash_high_employees:** getViewMassiveLow, getCollaboratorLowTemplate, lowMassive. La ejecución de la baja (Trash, TrashEdit, TrashCancel) está en rutas de high_employees y usa trash_high_employees.

---

## CASOS BORDE

- **Múltiples LowEmployee por HighEmployee:** El modelo HighEmployee define hasOne('low_employee'). La tabla low_employees no tiene unique en high_employee_id. En getList se usa $low_employee->low_employee()->get()->last(), por lo que si hubiera varios registros se muestra el último. En Trash siempre se crea uno nuevo; en TrashEdit se actualiza el primero. TrashCancel borra el primero. Flujo normal: una baja programada o una inmediata; si es programada y luego se edita a inmediata, se actualiza el mismo LowEmployee.
- **Fecha de baja en formato incorrecto:** Trash usa 'd/m/y' (año 2 dígitos). Si el front envía 'd/m/Y' (4 dígitos), createFromFormat puede fallar o interpretar mal.
- **Encuesta de salida:** Si exit_poll_reasons existe pero la razón enviada no está en la lista y allow_exit_poll = SI, la condición (doesntExist() || where('reason', $request->reason)->exists()) puede permitir seguir sin encuesta en algunos casos; si no hay encuesta global ni por empresa, se muestra "No existe una encuesta de salida para enviar" cuando la baja es inmediata.
- **Restore sin cuenta:** Si en restore se indica account y ya existe otra cuenta con ese número en un empleado no borrado, se rechaza. La variable $account se usa sin isset en el redirect: if(isset($account)) return redirect...; pero $account solo se asigna dentro de if(isset($request->account)); si no se envía account, $account no está definida y puede generar notice.

---

## BUGS E INCONSISTENCIAS

1. **getView y resendExitPoll:** En el mensaje de error se usa `$low_employee_id` pero el parámetro del método es `$high_employee_id`; la variable no definida puede generar notice o mensaje incorrecto.
2. **Formato de fecha en Trash:** createFromFormat('d/m/y H:i:s', ...) usa año de 2 dígitos; si el front envía 4 dígitos (d/m/Y) puede fallar.
3. **Carga masiva sin scope de empresa:** lowMassive() tiene comentado el bloque que comprueba user->company; LowCollaboratorsImport no recibe empresa ni catálogos. Cualquier usuario con trash_high_employees puede subir el Excel sin restricción por empresa.
4. **getCollaboratorLowTemplate:** Usa HighEmployee::all() para el total (incluye no borrados); el template se genera con ese count; no refleja solo empleados dados de alta activos por empresa.
5. **Restore: variable $account:** En restore() si no se envía request->account, la variable $account no se define; más abajo if(isset($account)) puede ser false, pero si en algún camino se usa $account sin isset podría haber notice. Revisado: $account se asigna solo dentro de if(isset($request->account)); el redirect que comprueba "Ya existe una cuenta..." usa if(isset($account)); si no se envió account, isset($account) es false. Correcto. Pero si se envía account y ya existe, $account está definida y se redirige. Ok.
6. **Relación hasOne vs varios registros:** high_employee->low_employee() es hasOne; la tabla permite varios low_employees por high_employee. TrashEdit y TrashCancel usan ->first(). Si en algún flujo se crearan dos bajas programadas, solo se vería/editaría una.
7. **TrashCancel:** Recibe high_employee_id como JSON: json_decode($request->high_employee_id). Si se envía como string numérico puede fallar json_decode; si se envía como número no es JSON válido. Inconsistente con el resto de rutas que reciben el id directo.

---

## PROBLEMAS TÉCNICOS

- **Lógica de baja duplicada:** La secuencia de cierre de account_state, creación de ReceivableAccount, envío de encuesta de salida y delete del HighEmployee está repetida en Trash y TrashEdit (y en TrashCancel no aplica). Sería un único servicio o método reutilizable.
- **LowEmployeesController muy grande:** Incluye getEdit/update con lógica casi idéntica a la edición de empleado activo (historiales, cuentas, validaciones), más getView, resendExitPoll, generateExitPollReport, restore (reingreso completo), getFilters con clonación de queries para selectores.
- **Paginación en getIndex:** Se usa fromSub($base_low_employees->select('id'), 'filtered_employees') para paginar LowEmployee; si la base es muy grande el subquery puede ser costoso.
- **Dependencia de colas:** DeleteMembership, ChangeDiscountSubscriptionStatus y notificaciones por push/email/sms; si las colas no se procesan, el estado de seguros/descuentos y notificaciones puede quedar desincronizado.

---

## MODELOS INVOLUCRADOS

- **LowEmployee** (App\Models\LowEmployee): SoftDeletes, fillable reason, date, is_scheduled, comments, high_employee_id, company_id, location_id, department_id, area_id, position_id, imss_last_update_date. Relaciones: high_employee (withTrashed), company, department, area, position, location (withTrashed), readmission.
- **HighEmployee:** Relación low_employee() hasOne. En listados de bajas se usa onlyTrashed()->has('low_employee').
- **Readmission:** Relaciona baja (low_employee) con reingreso (previous_admission_date, new_admission_date).
- **ReceivableAccount, AccountState, SurveyShipping, Survey, Notification:** Usados en el flujo de baja inmediata y encuesta de salida.

---

## MIGRACIONES

- **2020_02_26_070339_create_low_employees_table:** Crea low_employees (id, reason, date timestamp nullable, is_scheduled integer, high_employee_id FK high_employees cascade).
- **2020_05_06_150717_update_low_employees_table:** is_scheduled pasa de integer a string.
- **2021_04_05_142516_update_low_employees_2_table:** Añade comments longText nullable.
- **2021_05_05_153700_update_low_employees_3_table:** Añade softDeletes.
- **2023_08_14_083023_update_low_employees_4_table:** Añade company_id, location_id, position_id, department_id, area_id (FK a companies, locations, positions, departments, areas con cascade).
- **2024_03_19_091214_update_low_employees_5_table:** Añade imss_last_update_date date nullable.

---

## AMBIGÜEDADES

- **Ejecución de baja programada:** Cuando la fecha de la baja programada llega, no está claro en el código analizado si un job o cron ejecuta el "paso" a NO PROGRAMADA y el delete del HighEmployee, o si se hace manualmente. Trash y TrashEdit solo se ejecutan por petición del usuario.
- **Múltiples bajas por empleado:** La BD permite varios low_employees por high_employee; el modelo tiene hasOne. No queda documentado si en algún flujo se crean varias bajas (ej. programada y luego cancelada y otra programada).
- **readmissions:** Se crea en restore() con previous_admission_date y new_admission_date; no se revisó si Readmission tiene más campos o si se usa en reportes.

---

## DEUDA TÉCNICA

- **Restore = reingreso:** La acción se llama "restaurar" pero crea un nuevo HighEmployee; el historial del empleado queda en dos registros (el soft-deleted y el nuevo). Para reportes o antigüedad habría que considerar readmissions.
- **Bank id 23:** Excluido para no admin en getEdit y getRestore (mismo patrón que en Alta).
- **Validación de empresa en Trash/TrashEdit:** No se comprueba que el usuario pueda dar de baja a ese empleado (p. ej. que pertenezca a su empresa); solo permisos trash_high_employees.
- **Respuesta TrashCancel:** Devuelve response()->json(['action'=>'success', 'message'=>...]) mientras otras acciones del módulo hacen redirect()->route(...). Inconsistencia de tipo de respuesta.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
