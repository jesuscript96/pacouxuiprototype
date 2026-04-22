# Ficha técnica: Módulo Reconocimientos (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Reconocimientos (ReconocimientosResource / Acknowledgments)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite **gestionar el catálogo de reconocimientos** (tipos de reconocimiento que los empleados pueden enviarse entre sí) y **asignarlos a empresas**. Cada reconocimiento tiene nombre, descripción, **necessary_mentions** (cantidad de menciones necesarias para “desbloquearlo”), **is_shippable** (ENVIABLE | NO ENVIABLE) e **is_exclusive** (EXCLUSIVO: solo empresas elegidas; NO EXCLUSIVO: todas las empresas). La relación con empresas es N:M (tabla `acknowledgment_company`) con pivot **is_shippable** y **necessary_mentions** por empresa. Se pueden subir imágenes **inicial** y **final** (globales por reconocimiento o por empresa). Los empleados envían reconocimientos desde la app; cada envío es un **AcknowledgmentShipping** (remitente, destinatarios, mensaje, comentarios). En admin hay además **Reconocimientos enviados** (listado de envíos con filtros y export Excel). Al crear una empresa nueva se asocian automáticamente los reconocimientos no exclusivos (CompaniesController). Controlador: `AcknowledgmentsController`. Rutas bajo `admin/acknowledgments/*`; permisos: `view_acknowledgments`, `create_acknowledgments`, `edit_acknowledgments`, `trash_acknowledgments`, `view_sent_acknowledgments`.

---

## ENTIDADES

### Tabla: `acknowledgments`

- **PK:** id (bigint unsigned). SoftDeletes (update_acknowledgment_2_table).
- **Campos:** name (string), description (longText), is_shippable (string: ENVIABLE | NO ENVIABLE; migración original integer, luego change a string), is_exclusive (string: EXCLUSIVO | NO EXCLUSIVO; igual), necessary_mentions (integer). timestamps, deleted_at.
- **Relaciones (modelo Acknowledgment):** companies() belongsToMany Company con withPivot('is_shippable','necessary_mentions'); acknowledgment_shippings() hasMany AcknowledgmentShipping.

### Tabla pivot: `acknowledgment_company`

- **FK:** company_id → companies (cascade), acknowledgment_id → acknowledgments (cascade). **Campos pivot:** is_shippable (string), necessary_mentions (integer; añadido en update_acknowledgment_company_table). Permite por empresa: si el reconocimiento es enviable y cuántas menciones se requieren (pueden sobrescribir las del catálogo).
- **Uso:** EXCLUSIVO → solo las empresas attachadas ven el reconocimiento; NO EXCLUSIVO → se hace attach a todas las empresas al crear/actualizar.

### Tabla: `acknowledgment_shippings`

- **PK:** id. **FK:** acknowledgment_id → acknowledgments (cascade), high_employee_id → high_employees (cascade) (remitente).
- **Campos:** message (longText), filters (longText), comments (longText), date (timestamp nullable), belongs_capacitation (flag añadido en migración posterior). Cada envío tiene un remitente (high_employee_id) y N destinatarios vía pivot acknowledgment_high_employee.
- **Relaciones:** acknowledgment() belongsTo Acknowledgment, sender() belongsTo HighEmployee (high_employee_id), receivers() belongsToMany HighEmployee (pivot acknowledgment_high_employee con status), notifications() hasMany.

### Tabla pivot: `acknowledgment_high_employee`

- **FK:** acknowledgment_shipping_id, high_employee_id. **Campos:** status, reaction. Destinatarios del envío y estado (Unread/Read, etc.).

### Imágenes en disco

- **Globales (por reconocimiento):** `assets/acknowledgments/initial_image_{id}.png`, `assets/acknowledgments/final_image_{id}.png` (create/update en controlador; no se usa Storage::disk('uploads'), se guarda en path público). Máx. 20 MB, mimes jpg,jpeg,png,bmp.
- **Por empresa:** `assets/acknowledgments/{acknowledgment_id}/initial_image_{company_id}.png`, `final_image_{company_id}.png` (edit_company / updateCompany). La API y la app priorizan imagen por empresa si existe, si no la global.
- **Trash:** Se llama `Storage::disk('uploads')->delete('assets/acknowledgments/'.$acknowledgment_id.'.png')`; las rutas reales son initial_image_{id}.png y final_image_{id}.png, y no necesariamente en disco uploads (🔧 posible bug: no borra las imágenes correctas ni la carpeta por empresa).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/acknowledgments | GET | AcknowledgmentsController@getIndex | view_acknowledgments |
| admin/acknowledgments/get | GET | getList | view_acknowledgments |
| admin/acknowledgments/create | GET | getCreate | create_acknowledgments |
| admin/acknowledgments/create | POST | create | create_acknowledgments |
| admin/acknowledgments/edit/{acknowledgment_id} | GET | getEdit | edit_acknowledgments |
| admin/acknowledgments/edit | POST | update | edit_acknowledgments |
| admin/acknowledgments/view/{acknowledgment_id} | GET | getView | view_acknowledgments |
| admin/acknowledgments/trash/{acknowledgment_id} | GET | Trash | trash_acknowledgments |
| admin/acknowledgments/sent_view | GET | getSentview | view_sent_acknowledgments |
| admin/acknowledgments/sent_list | GET | getSentList | view_sent_acknowledgments |
| admin/acknowledgments/sent | POST | getSent | view_sent_acknowledgments |
| admin/acknowledgments/filter_companies | POST | filterCompanies | view_acknowledgments |
| admin/acknowledgments/list_companies/{acknowledgment_id} | GET | listCompanies | view_acknowledgments |
| admin/acknowledgments/edit_company/{acknowledgment_id}/{company_id} | GET | getEditCompany | edit_acknowledgments |
| admin/acknowledgments/edit_company | POST | updateCompany | edit_acknowledgments |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Reconocimientos" (admin_acknowledgments) si tiene view/edit/trash/create_acknowledgments; "Reconocimientos enviados" (admin_acknowledgments_sent_view) si tiene view_sent_acknowledgments.

---

## REGLAS DE NEGOCIO

- **RN-01:** name, description y necessary_mentions son **obligatorios** en create y update.
- **RN-02:** **is_exclusive:** Si EXCLUSIVO, el reconocimiento se asigna solo a las empresas seleccionadas (request->companies). Si NO EXCLUSIVO, se asigna a **todas** las empresas (Company::all()->pluck('id')). Al crear empresa nueva (CompaniesController) se hace attach de todos los reconocimientos no exclusivos.
- **RN-03:** **is_shippable:** ENVIABLE o NO ENVIABLE. Se guarda en el reconocimiento y se copia al pivot al hacer attach; por empresa se puede sobrescribir en edit_company (updateCompany).
- **RN-04:** **necessary_mentions:** En pivot por empresa puede diferir del valor global; en la API se usa pivot->necessary_mentions si != 0, si no el del reconocimiento. Define cuántas menciones necesita el empleado para poder enviar ese reconocimiento.
- **RN-05:** **Imágenes:** Opcionales. Globales: initial_image, final_image (create/update). Por empresa: initial_image, final_image en getEditCompany/updateCompany (ruta con acknowledgment_id y company_id). Validación: jpg,jpeg,png,bmp, max 20000 KB.
- **RN-06:** **Trash:** No se puede eliminar si tiene companies()->exists() o acknowledgment_shippings()->exists(). Mensaje: "No puede borrar un reconocimiento con registros asignados." Eliminación soft delete. Tras comprobar, se borra un archivo en disco (ruta que no coincide con la usada en create/update) y se hace delete del registro.
- **RN-07:** **Reconocimientos enviados (getSentview/getSent):** Si el usuario tiene high_employee_filters, se filtran los envíos por esos filtros (receivers que pertenecen a los filtros). Si no tiene filtros pero tiene company, solo envíos cuyos destinatarios sean de su empresa. Si no tiene company (admin), todos los envíos. Export Excel vía action getExcel (AcknowledgmentShippingsExport).
- **RN-08:** getCreate y getEdit exigen que existan empresas (Company::all()); si no, redirect back con error.

---

## FLUJO PRINCIPAL (resumen)

### Listado (getIndex / getList / filterCompanies)

- getIndex: Acknowledgment::query() con paginación; lista de companies para filtro (admin); por cada reconocimiento se formatea is_shippable/is_exclusive con estilos y lista de empresas (o "No aplica"). Vista list con tabla que puede recargar vía filterCompanies (POST) con filtro por empresas (solo admin), búsqueda y ordenación.
- getList: Acknowledgment::all(); mismo formato; respuesta JSON para DataTable. Botón "Ver empresas" → list_companies.
- filterCompanies: Filtro por companies (admin), búsqueda por id, name, is_shippable, is_exclusive, general_name de empresa; ordenación; paginación. Devuelve JSON con view (tabla HTML), filters y selectors.

### Crear (getCreate / create)

- getCreate: Company::all() ordenado; vista create con name, description, necessary_mentions, is_shippable, is_exclusive, empresas (multiselect si EXCLUSIVO), initial_image, final_image. create: validar name, description, necessary_mentions. Crear Acknowledgment. Si EXCLUSIVO y request->companies, attach a esas empresas; si NO EXCLUSIVO, attach a todas. Subir initial_image/final_image a assets/acknowledgments/ si vienen. Log y redirect.

### Ver / Editar (getView, getEdit, update)

- getView: Acknowledgment por id; vista view solo lectura. getEdit: Acknowledgment, Company::all(), companies_id (ids ya asignados). Vista edit con mismos campos; si EXCLUSIVO, empresas pre-seleccionadas. update: validaciones; actualizar name, description, necessary_mentions, is_shippable, is_exclusive. Si EXCLUSIVO, sync companies con request->companies y updateExistingPivot para nuevas con necessary_mentions e is_shippable; si NO EXCLUSIVO, sync con todas las empresas y updateExistingPivot para las que no estaban. Subir initial_image/final_image globales si vienen. Log y redirect.

### Empresas por reconocimiento (listCompanies, getEditCompany, updateCompany)

- listCompanies: Listado de empresas del reconocimiento con necessary_mentions, is_shippable y botón Editar → getEditCompany(acknowledgment_id, company_id). getEditCompany: Vista edit_company con reconocimiento, empresa y formulario (necessary_mentions, is_shippable, initial_image, final_image por empresa). updateCompany: Validar imágenes si hay archivo; guardar en assets/acknowledgments/{id}/initial_image_{company_id}.png y final_image_{company_id}.png; updateExistingPivot(company_id, necessary_mentions, is_shippable). Redirect a list_companies.

### Reconocimientos enviados (getSentview, getSentList, getSent)

- getSentview: Construye query de AcknowledgmentShipping con filtros por company/filtros de empleado; selectores (empresas, departamentos, áreas, ubicaciones, puestos, remitentes, categorías reconocimiento, etc.); paginación. Vista sent.list. getSentList: Listado simple para DataTable (fecha, remitente, destinatario, reconocimiento, comentario). getSent (POST): Misma lógica de filtros (fecha, remitente, destinatario, empresa, departamento, área, ubicación, puesto, categoría), búsqueda, ordenación; si action == getExcel, export Excel y devuelve URL firmada. Respuesta JSON con view (tabla), filters, selectors.

### Eliminar (Trash)

- Comprobar que exista el reconocimiento (pero en el código se usa $acknowledgment->name para $message antes del if !$acknowledgment → ⚠️ bug si no existe). Si tiene companies o acknowledgment_shippings, error. Borrar archivo en disco (ruta incorrecta). Acknowledgment::where("id",$acknowledgment_id)->delete() (soft delete). Log y redirect.

---

## VALIDACIONES

- **create/update:** name required, description required, necessary_mentions required. Mensajes: "El nombre es requerido", "La descripción es requerida", "La cantidad de menciones es requerida".
- **initial_image / final_image (si se suben):** file, mimes:jpg,jpeg,png,bmp, max:20000. Mensajes estándar de formato y 20 MB.
- **updateCompany:** necessary_mentions e is_shippable desde request; imágenes validadas igual. No se valida que company_id pertenezca al reconocimiento antes de updateExistingPivot.

---

## VISTAS

- **admin.acknowledgments.list:** Título "Reconocimientos". Filtro por empresa (solo admin). Tabla con columnas id, nombre, enviable, exclusivo, empresas, acciones (Editar, Ver, Eliminar, Ver empresas). Paginación y recarga vía filterCompanies.
- **admin.acknowledgments.create:** Formulario name, description, necessary_mentions, is_shippable, is_exclusive, empresas (si exclusivo), initial_image, final_image. action admin_acknowledgments_create.
- **admin.acknowledgments.edit:** Igual que create con datos del reconocimiento y companies_id para preselección. action admin_acknowledgments_update.
- **admin.acknowledgments.view:** Solo lectura del reconocimiento.
- **admin.acknowledgments.list_companies:** Listado de empresas del reconocimiento (nombre, necessary_mentions, is_shippable, Editar). Enlace a edit_company.
- **admin.acknowledgments.edit_company:** Formulario por empresa: necessary_mentions, is_shippable (toggle), initial_image, final_image. action admin_acknowledgments_update_company.
- **admin.acknowledgments.sent.list:** Listado de envíos con filtros (empresa, destinatario, remitente, categoría, fechas, etc.) y export Excel. Tabla recargada por getSent (JSON con view).

---

## USO EN OTROS MÓDULOS

- **CompaniesController (create):** Tras crear empresa, attach de Acknowledgment no exclusivos (is_shippable, necessary_mentions) para que la nueva empresa tenga el catálogo de reconocimientos.
- **Api\AuthController / Api\AcknowledgmentsController:** Listado de reconocimientos por empresa (wherePivot is_shippable ENVIABLE), envío de reconocimiento (crear AcknowledgmentShipping, notificaciones push), listado de recibidos por tipo, paginación. Imágenes desde assets/acknowledgments (global o por empresa).
- **HighEmployee:** sent_acknowledgments (hasMany AcknowledgmentShipping como remitente), received_acknowledgments (belongsToMany vía acknowledgment_high_employee). Envío desde app: un empleado elige reconocimiento, destinatarios y comentario; se crea shipping y se asocian receptores con status Unread.

---

## MODELOS INVOLUCRADOS

- **Acknowledgment:** tabla acknowledgments, SoftDeletes, fillable name, description, is_shippable, is_exclusive, necessary_mentions, company_id. companies() belongsToMany con withPivot('is_shippable','necessary_mentions'); acknowledgment_shippings() hasMany.
- **AcknowledgmentShipping:** acknowledgment_id, high_employee_id (sender), message, comments, date, belongs_capacitation. acknowledgment(), sender(), receivers() con pivot status, notifications().
- **Company:** acknowledgments() belongsToMany con pivot is_shippable, necessary_mentions.
- **HighEmployee:** sent_acknowledgments() hasMany AcknowledgmentShipping; received_acknowledgments() belongsToMany AcknowledgmentShipping (pivot acknowledgment_high_employee).

---

## MIGRACIONES

- **create_acknowledgments_table:** id, name, description (longText), is_shippable (integer), is_exclusive (integer), necessary_mentions (integer), timestamps.
- **create_acknowledgment_company_table:** id, company_id, acknowledgment_id (nullable), FKs, timestamps. Luego update: is_shippable (integer), necessary_mentions (integer); después is_shippable change a string.
- **create_acknowledgment_shippings_table:** id, message, filters, comments, date, acknowledgment_id, high_employee_id, FKs, timestamps.
- **create_acknowledgment_high_employee_table:** acknowledgment_shipping_id, high_employee_id, status, reaction, FKs.
- **update_acknowledgments_table:** is_shippable e is_exclusive a string. **update_acknowledgment_2_table:** softDeletes en acknowledgments.

---

## PERMISOS LEGACY

- **view_acknowledgments:** getIndex, getList, filterCompanies, listCompanies, getView.
- **create_acknowledgments:** getCreate, create.
- **edit_acknowledgments:** getEdit, update, getEditCompany, updateCompany.
- **trash_acknowledgments:** Trash.
- **view_sent_acknowledgments:** getSentview, getSentList, getSent (incluye export Excel).

---

## CASOS BORDE

- **Trash:** Se asigna `$message = "Se ha eliminado...".$acknowledgment->name` antes de `if (!$acknowledgment)`; si el id no existe, $acknowledgment es null y se produce error. Además se borra `assets/acknowledgments/{id}.png` en disco uploads; las imágenes reales son initial_image_{id}.png y final_image_{id}.png y pueden estar en public; la carpeta por empresa assets/acknowledgments/{id}/ no se borra.
- **filterCompanies (admin):** Join con alias tack1; cuando se filtra por companies incluyendo 0 (SIN ASIGNAR), se hace orWhereNull('tack1.company_id'). La construcción del query con select puede sobrescribir columnas; verificar que acknowledgments.id siga en el select para paginación/agrupación.
- **getHighEmployeeAcknowledgments:** Usa `$employee->compnay_id` (typo); debería ser company_id. Si no se corrige, fallo al obtener la empresa.
- **listCompanies:** Si el reconocimiento no tiene empresas, $acknowledgment->companies()->get() es vacío; la vista list_companies muestra tabla vacía. getEditCompany con company_id inexistente para ese reconocimiento: companies()->where('company_id',$company_id)->first() devuelve null; la vista edit_company podría fallar al acceder a $company.

---

## AMBIGÜEDADES

- **necessary_mentions:** No está documentado en el código el significado exacto (¿menciones de otros empleados para desbloquear el reconocimiento? ¿número de destinatarios por envío?). La API usa el valor para mostrarlo al cliente.
- **Pivot is_shippable por empresa:** Permite que un reconocimiento sea ENVIABLE para unas empresas y NO ENVIABLE para otras cuando es exclusivo; en updateCompany se puede cambiar solo para una empresa.

---

## DEUDA TÉCNICA

- Trash: orden de comprobación !$acknowledgment y ruta de borrado de imágenes incorrecta; no se elimina carpeta assets/acknowledgments/{id}/ ni archivos initial/final_image.
- Typo compnay_id en getHighEmployeeAcknowledgments.
- Trash por GET. Respuesta getSent con HTML en JSON (cleanView) para tabla.
- filterCompanies y getSent: queries complejos con múltiples join y clone; difícil mantenimiento.

---

## DIFERENCIAS CON TECBEN-CORE

Por definir (no verificado en este análisis). Si en tecben-core existe catálogo de reconocimientos, comparar: exclusivo vs todas las empresas, pivot is_shippable/necessary_mentions por empresa, imágenes globales y por empresa, y flujo "Reconocimientos enviados" con filtros y export.
