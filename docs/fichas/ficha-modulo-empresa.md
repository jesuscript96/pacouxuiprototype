# Ficha técnica: Módulo Empresas (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Empresas (EmpresaResource / Companies)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Existe implementación en Filament (EmpresaResource, EmpresaService, EmpresaForm); esta ficha documenta solo el **legacy** Paco.

Permite **listar, crear, editar, ver y eliminar empresas** (clientes/tenants) en el panel Admin. Cada empresa tiene datos de contacto, industria/subindustria, contrato, **comisiones** (PERCENTAGE, FIXED_AMOUNT o MIXED con rangos), **productos** con precios y enable_from, **razones sociales** (BusinessName con RFC, domicilio), **centros de costo**, notificaciones excluidas, encuesta de salida, retenciones (PayrollWithholdingConfig), quincena personalizada, finiquito (URL), felicitaciones (allow_congratulation_notifications, congratulation_notifications_type), reconocimientos y temas de voz del colaborador, alias de transacciones, documentos/archivos, logo, foto, colores, app (name_app, app_download_link, ids de stores), y múltiples toggles (has_fourteen_monthly_payments, has_session_limit, has_settlement_date, has_download_capacitation, allow_exit_poll, has_nubarium_sign, has_sign_new_contract, send_newsletter, etc.). Controlador: `CompaniesController` (muy extenso, >2500 líneas). Rutas bajo `admin/companies/*`; permisos: `view_companies`, `create_companies`, `edit_companies`, `trash_companies`.

---

## ENTIDADES

### Tabla: `companies`

- **PK:** id (bigint unsigned). SoftDeletes (deleted_at).
- **Campos (resumen):** general_name, contact_name, contact_email, contact_phone, contact_mobile, billing_email, contract_start, contract_end, industry_id, sub_industry_id, commission_type (PERCENTAGE | FIXED_AMOUNT | MIXED), biweekly_commission, monthly_commission, fourteen_monthly_commission, weekly_commission, payment_gateway_commission, report_users, name_app, app_download_link, valid_days_messages, is_active, activation_date, has_sub_companies, first_color, second_color, third_color, fourth_color, logo, has_fourteen_monthly_payments, fourteen_monthly_next_payment_date, has_settlement_date, url_settlement_date, allow_exit_poll, has_nubarium_sign, has_sign_new_contract, has_download_capacitation, ios_app_id, android_app_id, huawei_app_id, has_analytics_by_location, transactions_with_imss, validate_accounts_automatically, send_newsletter, direct_debit_via_api, has_session_limit, allow_withholdings, withholding_day, allow_congratulation_notifications, congratulation_notifications_type (COMPANY | LOCATION), y otros añadidos en múltiples migraciones (update_companies_*). Relación con AppSetting (app_setting_id cuando hay app).
- **Relaciones (modelo Company):** industry(), sub_industry(), products() belongsToMany con pivot base_price, unit_price, enable_from, variation_margin; business_names() belongsToMany; cost_centers() belongsToMany; locations(), departments(), areas(), positions(), regions(), roles(), users(), high_employees(), low_employees(), festivities(), acknowledgments(), product_filters(), high_employee_filters(), folders(), exit_poll_reasons(), excluded_notifications(), payroll_withholding_configs(), personalized_fortnight() hasOne, notification_frequency() hasOne, app_setting(), commission_ranks(), transaction_type_aliases(), voice_employee_subjects() belongsToMany, logs(), etc.

### Tabla pivot: `company_product`

- **FK:** company_id, product_id. **Campos:** base_price, unit_price, enable_from, variation_margin. Asignación de productos a la empresa con precios y criterios de habilitación.

### Tabla: `business_names` y pivot con companies

- **BusinessName:** optional_business_name, rfc, cp, street, number, number_in, suburb, town, state. Una empresa puede tener varias razones sociales (attach en create/update).

### Tablas relacionadas (resumen)

- **cost_centers:** relación many-to-many con companies (company_cost_center); centros BELVO, EMIDA, STP.
- **excluded_notifications:** notificaciones deshabilitadas para la empresa (Adelanto nómina, Validación cuenta, etc.).
- **exit_poll_reasons:** razones de encuesta de salida (ABANDONO, RENUNCIA, DESPIDO, etc.) cuando allow_exit_poll = SI.
- **payroll_withholding_configs:** retenciones por periodicidad (MENSUAL, SEMANAL, CATORCENAL, QUINCENAL) con emails, fecha o días.
- **personalized_fortnights:** quincena personalizada (start_day, end_day) por empresa.
- **commission_ranks:** rangos de comisión cuando commission_type = MIXED (price_from, price_until, fixed_amount, percentage).
- **notification_frequency:** recordatorio estados de ánimo (days, next_date, type).
- **transaction_type_aliases:** alias para tipos de transacción (ADELANTO DE NOMINA, PAGO DE SERVICIO, RECARGA).
- **acknowledgments:** reconocimientos no exclusivos se asocian automáticamente al crear empresa.
- **voice_employee_subjects:** temas de voz no exclusivos o exclusivos de la empresa se asocian al crear.
- **AppSetting:** opcional; relación cuando se indican android_app_id o ios_app_id.

### Archivos en disco

- **Foto:** `assets/companies/photos/{company_id}.png` (redimensionada 150x150).
- **Logo:** `assets/companies/logos/{company_id}_{time}.png` en disco `uploads` (create/update).
- **Documentos:** `assets/companies/files/{company_id}/` (PDFs; subida en create/update; removeFile, updateFile).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/companies | GET | CompaniesController@getIndex | view_companies |
| admin/companies/get | GET | getList | view_companies |
| admin/companies/create | GET | getCreate | create_companies |
| admin/companies/create | POST | create | create_companies |
| admin/companies/edit/{company_id} | GET | getEdit | edit_companies |
| admin/companies/edit | POST | update | edit_companies |
| admin/companies/view/{company_id} | GET | getView | view_companies |
| admin/companies/trash/{company_id} | GET | Trash | trash_companies |
| admin/companies/removefile | POST | removeFile | edit_companies |
| admin/companies/updatefile | POST | updateFile | edit_companies |
| admin/companies/query | POST | rfcQuery | (sin permiso en ruta; middleware logged) |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}` según la ruta. rfcQuery no exige permiso de companies (se usa para cargar datos al cambiar empresa en otro formulario).

---

## REGLAS DE NEGOCIO (resumen)

- **RN-01:** getCreate exige que existan industrias con subindustrias, subindustrias, productos, puestos y departamentos; si no, redirect back con error. getEdit exige industrias con subindustrias.
- **RN-02:** **Create:** general_name, contact_name, contact_email, contact_phone, contact_mobile, billing_email, contract_start, contract_end, commission_type (PERCENTAGE | FIXED_AMOUNT | MIXED), report_users obligatorios. Si commission_type ≠ MIXED: biweekly, monthly, fourteen_monthly, weekly, payment_gateway_commission requeridos (required_unless). Si MIXED: commission_rank array con al menos un rango (price_from, price_until, fixed_amount, percentage); price_until > price_from; percentage ≤ 100. Razones sociales: optional_business_name.*, rfc.*, cp.*, street.*, number.*, suburb.*, town.*, state.* requeridos; productos con unit_price, base_price, enable_from, variation_margin (regex precios con coma de miles).
- **RN-03:** **Quincena personalizada:** si set_custom_fortnight, start_day y end_day obligatorios; start_day no puede ser mayor que end_day.
- **RN-04:** **Finiquito:** si has_settlement_date, url_settlement_date no puede estar vacía.
- **RN-05:** **Encuesta de salida:** si allow_exit_poll, debe seleccionarse al menos una razón (reasons).
- **RN-06:** **Retenciones (allow_withholdings):** se crean PayrollWithholdingConfig por cada periodicidad enviada (payroll_withholding_monthly_day, payroll_withholding_weekday, payroll_withholding_fourteen_monthly_day, payroll_withholding_biweekly_day). Emails común para todas.
- **RN-07:** **Comisión MIXED:** tras guardar la empresa se crean CommissionRank por cada elemento de commission_rank y se asocian a la empresa.
- **RN-08:** **Productos:** se hace attach de cada producto elegido con base_price, unit_price (JSON), enable_from, variation_margin. industry y sub_industry se asocian después de guardar.
- **RN-09:** **Admin:** puede marcar is_active (SI/NO) y activation_date al crear; no-admin deja is_active = NO. Solo admin puede activar empresa.
- **RN-10:** **Trash:** No se puede eliminar una empresa que tenga locations, areas, departments, positions, roles, folders, users o high_employees. Mensaje: "No puede borrar una empresa con registros asignados." Se borran business_names (delete), products (detach), foto en disco, directorio de archivos, logo en disco; luego Company::where("id",$company_id)->delete() (soft delete).
- **RN-11:** **getEdit:** Se cargan sub_industries de la industria actual de la empresa (`$company->industry->sub_industries()`). Documentos desde Storage en `assets/companies/files/{company_id}`.
- **RN-12:** **Felicitaciones:** allow_congratulation_notifications (boolean) y congratulation_notifications_type (COMPANY | LOCATION) se guardan en create/update; no hay validación de tipo si el toggle está activo.

---

## FLUJO PRINCIPAL (resumen)

### Listado (getIndex / getList)

- getIndex: vista `admin.companies.list` (DataTable que consume getList por AJAX).
- getList: Company::all() (con SoftDeletes excluye eliminadas). Para cada empresa: id, nombre con foto, industria, subindustria, contact_email, productos (spans), botones Editar / Ver / Eliminar. Respuesta JSON `{ data: companies_list }`.

### Crear (getCreate / create)

- getCreate: Carga industries (con sub_industries), sub_industries, products, positions, departments, notificaciones y razones de encuesta de salida, cost_centers (BELVO, EMIDA, STP), withholding_days, week_days. Vista create con formulario multipestaña/secciones (datos generales, contacto, contrato, comisiones, productos, razones sociales, centros de costo, notificaciones, retenciones, quincena, finiquito, encuesta de salida, colores, app, logo, photo, documentos, etc.). create: validaciones (RN-02 a RN-05), creación Company, asignación de comisiones (fijas o MIXED con CommissionRank), notification_frequency si mood_notification_frequency, personalized_fortnight si aplica, PayrollWithholdingConfig si allow_withholdings, AppSetting si app ids, ExcludedNotification para notificaciones no elegidas (solo admin), BusinessName y attach, CostCenter attach, Product attach con pivot, industry/sub_industry save, documentos en disco, foto (Image resize 150x150), attach de Acknowledgment no exclusivos, attach VoiceEmployeeSubject, ExitPollReason si allow_exit_poll, TransactionTypeAlias (adelanto, servicio, recarga), Log, redirect a admin_companies con "Empresa creada exitosamente".

### Ver (getView)

- Buscar company por id; si no existe redirect a listado. Documentos = archivos en `assets/companies/files/{company_id}`. Vista view con company y documents.

### Editar (getEdit / update)

- getEdit: Industries, products, departments, positions; company por id; sub_industries de la industria de la empresa; documents desde disco; products_attached con pivot; notifications y reasons; excluded_notifications, exit_poll_reasons, personalized_fortnight, payroll_withholding_configs, commission_ranks, etc. Vista edit con mismos bloques que create. update: validaciones análogas a create; actualización de todos los campos y relaciones (business_names, products pivot, cost_centers, excluded_notifications, exit_poll_reasons, retenciones, quincena, commission_ranks para MIXED, logo, photo, documentos, felicitaciones, etc.); Log; redirect a admin_companies_edit con "Empresa actualizada exitosamente".

### Eliminar (Trash)

- Comprobar que no existan locations, areas, departments, positions, roles, folders, users, high_employees. business_names()->delete(); products()->detach(); borrar foto y directorio de archivos y logo del disco; Company::where("id",$company_id)->delete() (soft delete). Log y redirect con mensaje.

### removeFile / updateFile

- removeFile: borrar archivo en `assets/companies/files/{id}/{filename}` (disco uploads). updateFile: mover archivo subido a esa carpeta. rfcQuery: devolver datos (locations, areas, departments, positions, business_names) para una empresa según permisos/filtros del usuario (high_employee_filters); usado en otros formularios.

---

## VALIDACIONES (resumen)

- general_name, contact_name, contact_email (email), contact_phone (numeric, digits_between 0,10), contact_mobile (igual), billing_email (email), contract_start/end (date), commission_type (in PERCENTAGE,FIXED_AMOUNT,MIXED), report_users (numeric). Comisiones según tipo (required_unless MIXED o required_if MIXED para rangos). Razones sociales y productos con reglas por índice. Logo/photo: mimes jpg,jpeg,png,bmp, logo max 5000 KB, photo max 20000 KB. documents.*: mimes pdf, max 20000 KB. Quincena: start_day ≤ end_day. has_settlement_date implica url_settlement_date no vacía. allow_exit_poll implica al menos una razón.

---

## VISTAS

- **admin.companies.list:** Listado con DataTable (AJAX get_admin_companies). Columnas: N°, Nombre (con foto), Industria, SubIndustria, Correo, Productos, Acciones (Editar, Ver, Eliminar). Modal confirmación eliminar. Botón Crear.
- **admin.companies.create:** Formulario extenso por pestañas/secciones: datos generales, contacto, contrato, comisiones (tipo + fijas o repeater MIXED), productos (con precios y enable_from), razones sociales (repeater con RFC, domicilio), centros de costo, notificaciones excluidas, retenciones, quincena personalizada, finiquito, encuesta de salida, colores, app, logo, foto, documentos, toggles varios. action admin_companies_create.
- **admin.companies.edit:** Estructura análoga a create con datos de la empresa; action admin_companies_update. Incluye gestión de archivos (subir/eliminar).
- **admin.companies.view:** Vista solo lectura de la empresa y listado de documentos (enlaces a archivos).

---

## USO EN OTROS MÓDULOS

- **Empresa** es el núcleo del multi-tenant: usuarios, high_employees, locations, departments, areas, positions, regions, roles, productos, festividades, reconocimientos, voz del colaborador, encuestas, notificaciones, retenciones, nómina, etc. dependen de company_id.
- **ProductManagementController:** listado de empresas con productos para segmentación.
- **CarouselManagementController:** listado de empresas para carrusel por empresa.
- **HighEmployeeFiltersController:** listado de empresas para filtros por empresa.
- **rfcQuery:** usado desde otros formularios para cargar locations/areas/departments/positions/business_names al elegir empresa.

---

## MODELOS INVOLUCRADOS

- **Company:** tabla companies, SoftDeletes, fillable con decenas de campos. Relaciones: industry, sub_industry, products (pivot), business_names (pivot), cost_centers (pivot), locations, departments, areas, positions, regions, roles, users, high_employees, festivities, acknowledgments (pivot), product_filters, high_employee_filters, folders, exit_poll_reasons, excluded_notifications, payroll_withholding_configs, personalized_fortnight, notification_frequency, app_setting, commission_ranks, transaction_type_aliases, voice_employee_subjects (pivot), logs, etc. Accessors: photo, logo_url, festivity_logo_url.
- **Industry, SubIndustry, Product, BusinessName, CostCenter, Department, Position, Area, Location, Region, Role, User, HighEmployee, Festivity, Acknowledgment, ExitPollReason, ExcludedNotification, PayrollWithholdingConfig, PersonalizedFortnight, NotificationFrequency, AppSetting, CommissionRank, TransactionTypeAlias, VoiceEmployeeSubject, Log,** etc.

---

## MIGRACIONES

- **create_companies_table** y numerosas **update_companies_*_table** (update_companies_2 a 37 y otras) que añaden o modifican columnas (industry_id, sub_industry_id, commission_type, colores, app, flags de funcionalidad, allow_congratulation_notifications, congratulation_notifications_type, etc.). Tablas relacionadas: company_product, business_name_company, company_cost_center, company_voice_employee_subject, create_payroll_withholding_configs, create_commission_ranks, create_personalized_fortnights, exit_poll_reasons, excluded_notifications, notification_templates, transaction_type_aliases, etc.

---

## PERMISOS LEGACY

- **view_companies:** getIndex, getList, getView.
- **create_companies:** getCreate, create.
- **edit_companies:** getEdit, update, removeFile, updateFile.
- **trash_companies:** Trash.

---

## CASOS BORDE

- **Trash:** business_names()->delete() elimina los registros de BusinessName asociados; si en otro contexto se usaran razones sociales compartidas entre empresas, podría no ser deseable. En el legacy parecen por empresa.
- **getEdit:** Si la empresa no tiene industry_id o industry eliminada, $company->industry->sub_industries() podría fallar; se asume que industry existe.
- **Comisión MIXED en update:** La lógica de actualización de commission_ranks (borrar anteriores y crear nuevos según request) debe coincidir con la de create; revisar en el bloque update del controlador.
- **rfcQuery:** No exige permiso view_companies ni edit_companies; cualquier usuario logueado podría consultar datos de cualquier empresa pasando id por request.

---

## AMBIGÜEDADES

- **Orden de guardado:** En create se guarda la company y luego se asocian industry y sub_industry (industry->companies()->save($company)); hasta ese momento industry_id y sub_industry_id podrían no estar asignados en el modelo si no se asignan antes del save. En el código se hace $company->save() tras rellenar campos y luego industry->companies()->save($company) y sub_industry->companies()->save($company), que actualizan las FKs; por tanto el orden es correcto.
- **Documentos:** updateFile hace move del archivo a la carpeta de la empresa; removeFile borra por nombre. La subida masiva en create/update usa storeAs con nombre fecha+índice; la interfaz de edición puede permitir renombrar o reemplazar vía updateFile.

---

## DEUDA TÉCNICA

- **CompaniesController** muy largo (>2500 líneas); crear/actualizar concentran mucha lógica que podría extraerse a servicios o formularios request.
- **Trash por GET:** debería ser POST/DELETE.
- **rfcQuery sin permiso:** exposición de datos de empresa por id sin verificación de permiso.
- Duplicación de reglas y mensajes entre create y update; podría unificarse en FormRequest o servicio.

---

## DIFERENCIAS CON TECBEN-CORE

La ficha existente `ficha-modulo-empresas.md` describe la implementación en tecben-core (Filament EmpresaResource, EmpresaService, EmpresaForm) con toggles tipo_comision, permitir_retenciones, tiene_pagos_catorcenales, tiene_quincena_personalizada, activar_finiquito, permitir_encuesta_salida, etc. En legacy, los nombres y estructura son distintos (commission_type PERCENTAGE/FIXED_AMOUNT/MIXED, allow_withholdings, set_custom_fortnight, has_settlement_date, allow_exit_poll, etc.). Al migrar o homologar, hay que mapear campos y reglas entre legacy y tecben-core (por ejemplo tipo_comision vs commission_type, permitir_retenciones vs allow_withholdings, tablas configuracion_retencion_nominas vs payroll_withholding_configs).
