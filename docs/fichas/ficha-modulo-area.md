# Ficha técnica: Módulo Área (Legacy Paco) — AreaResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **áreas**. Solo describe lo que existe en el código.

---

## MÓDULO: Área (AreasController / equivalente AreaResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona **áreas** por empresa: listado con filtro por empresa y búsqueda, alta, edición, vista detalle, baja (trash) y exportación a Excel. Cada área tiene **nombre**, **company_id** (opcional; "SIN ASIGNAR" si null) y **general_area_id** (área general homologada; solo visible/editable para rol admin). Los usuarios **high_user** solo ven y crean áreas de su empresa; los **admin** ven todas (o las restringidas por high_employee_filters). Controlador: `App\Http\Controllers\Admin\AreasController`. Rutas bajo `admin/areas/*`; permisos: `view_areas`, `create_areas`, `edit_areas`, `trash_areas`.

---

## ENTIDADES

### Tabla: `areas`

- **PK:** id (bigint). **SoftDeletes** (deleted_at).
- **Campos:** name (string), company_id (unsignedBigInteger nullable, FK companies cascade), general_area_id (unsignedBigInteger nullable, FK general_areas cascade), timestamps, deleted_at.
- **Relaciones (modelo Area):** company() belongsTo Company; general_area() belongsTo GeneralArea; high_employees() hasMany HighEmployee; low_employees() hasMany LowEmployee; product_filters() hasMany ProductFilter; high_employee_filters() hasMany HighEmployeeFilter; folders() belongsToMany Folder; receivable_accounts() hasMany ReceivableAccount; area_histories() hasMany AreaHistory.

### Tabla: `general_areas`

- Catálogo de áreas generales (homologación). En getCreate/getEdit se lista GeneralArea::all() ordenado por name para el select "Área general" (solo si el usuario tiene rol admin).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/areas | GET | AreasController@getIndex | view_areas |
| admin/areas/get | POST | getTable | view_areas |
| admin/areas/create | GET | getCreate | create_areas |
| admin/areas/create | POST | create | create_areas |
| admin/areas/edit/{area_id} | GET | getEdit | edit_areas |
| admin/areas/edit | POST | update | edit_areas |
| admin/areas/trash/{area_id} | GET | Trash | trash_areas |
| admin/areas/view/{area_id} | GET | getView | view_areas |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Áreas" (admin_areas) si el rol tiene al menos uno de: edit_areas, view_areas, trash_areas, create_areas.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance por tipo de usuario:** Si el usuario tiene **high_employee_filters** con area_id no null, solo ve las áreas cuyo id está en esos filtros. Si no tiene filtros y tiene **company** (high_user), solo ve áreas de su empresa (`$user->company->areas()`). Si no tiene filtros y no tiene company (admin), ve todas las áreas (`Area::query()`).
- **RN-02:** **Crear área:** name obligatorio. Si usuario es **high_user**, la nueva área se asocia a su empresa (`$company->areas()->save($area)`). Si viene `request->general_area`, se asocia la área general (solo el formulario admin muestra el select de área general; high_user no lo ve).
- **RN-03:** **Editar área:** name obligatorio. Si usuario es **high_user**, se fuerza `area->company_id = $user->company->id` (no puede sacar el área de su empresa). Si viene `request->general_area`, se actualiza general_area_id (solo admin ve el campo).
- **RN-04:** **Área general (general_area):** Solo usuarios con rol **admin** ven y envían el campo "Área general" en create/edit; en la vista es required. high_user no envía general_area; en create el controlador solo asocia si `isset($request->general_area)`.
- **RN-05:** **Empresa (company_id):** No hay selector de empresa en el formulario. Se asigna implícitamente: en create para high_user se asocia a su empresa; en update para high_user se reasigna a su empresa. Admin no asigna empresa al crear (queda null = "SIN ASIGNAR").
- **RN-06:** **Trash:** No se puede eliminar si el área tiene high_employees, product_filters, high_employee_filters o folders. Mensaje: "No puede borrar un area con registros asignados." Eliminación con soft delete (modelo Area usa SoftDeletes).
- **RN-07:** **Logs:** En create, update y Trash se crea un registro en tabla logs (acción con nombre del usuario y del área) y se asocia al usuario actual y a su company si tiene company_id (bloque duplicado en create y update: se guarda el log en company dos veces).
- **RN-08:** **Listado y tabla:** Filtro por empresas (solo admin; selector múltiple incluyendo "SIN ASIGNAR"). Búsqueda por id, name, general_name de empresa, o literal "SIN ASIGNAR" (equivale a company_id null o general_area_id null). Ordenación por id, name, área homologada (general_area name) o empresa (company general_name). Paginación (take 10/25/50/100). Export Excel vía action getExcel (getTable con action = 'getExcel'): genera archivo en Storage local y devuelve URL firmada (download_file).

---

## FLUJO PRINCIPAL

### Listado (getIndex / getTable)

- **getIndex:** Determina query base según filtros de empleado y company (ver RN-01). Pagina 10 por defecto; obtiene companies_ids de las áreas resultantes y arma lista de empresas para el filtro (incluye "SIN ASIGNAR"). Solo usuarios admin ven el panel de filtros (empresa). Devuelve vista `admin.areas.list` con areas, request y filters.
- **getTable (POST):** Parámetros: take, search, companies (JSON), orderBy, sortDir, page, action. Si action == 'getExcel', aplica mismos filtros/orden, get() sin paginar, genera Excel (AreasExport) en downloads/Areas.xlsx y devuelve JSON con url (signedRoute download_file). Si no, pagina y devuelve vista `admin.areas.table` (HTML de la tabla) para recargar el listado vía AJAX.

### Crear (getCreate / create)

- **getCreate:** GeneralArea::all() ordenado por name; vista create con name y (solo admin) select de general_area required.
- **create:** Validar name required. Crear Area con name; si high_user, asociar a su company; si request->general_area, asociar general_area. Log (y log en company si aplica, dos veces). Redirect a admin_areas con mensaje éxito.

### Ver (getView)

- Buscar Area por id; si no existe, redirect a admin_areas con mensaje. Vista view solo lectura (nombre e id).

### Editar (getEdit / update)

- **getEdit:** Area por id; si no existe, redirect a admin_areas. GeneralArea::all() ordenado por name; vista edit con name y (solo admin) select general_area.
- **update:** Validar name required. Si high_user, area->company_id = user->company->id. Si request->general_area, area->general_area_id = request->general_area. save(); log (y en company dos veces). Redirect a admin_areas_edit con mensaje éxito.

### Trash (eliminar)

- Buscar Area por id. En el código se asigna `$message = "Se ha eliminado el area: ".$area->name` **antes** del `if (!$area)` → si el id no existe, $area es null y se produce error. Si no existe, redirect back con error "El area no existe." Si tiene high_employees, product_filters, high_employee_filters o folders, redirect back con error. Log (y en company dos veces); Area::where("id",$area_id)->delete() (soft delete). Redirect a admin_areas con message_info.

---

## VALIDACIONES

- **create / update:** name required. Mensaje: "El nombre es requerido".
- En la vista create/edit, el select "Área general" es required solo para admin; el controlador no valida general_area en servidor (solo isset).

---

## VISTAS

- **admin.areas.list:** Título "Áreas"; subtítulo administración de áreas de la empresa. Botón Crear. Solo admin: panel "Filtrar por" con multiselect Empresa (incl. "SIN ASIGNAR") y "Borrar filtros". Incluye tabla (admin.areas.table) y modal de confirmación para eliminar. Scripts recargan tabla vía POST get_admin_areas (búsqueda, paginación, orden, filtro empresas, botón Excel).
- **admin.areas.table:** Tabla con columnas: N°, Nombre; (solo admin) Área Homologada, Empresa; acciones (Editar, Ver, Eliminar). Botón "Descargar a excel" (dispara action getExcel). Inputs ocultos orderBy, sortDir, currentPage; select "Mostrar N áreas"; búsqueda.
- **admin.areas.create:** Formulario name; si admin, select general_area (required). action admin_areas_create.
- **admin.areas.edit:** Igual que create con area_id y datos precargados. action admin_areas_update.
- **admin.areas.view:** Solo lectura: id y nombre. Enlace Regresar a admin_areas.

---

## USO EN OTROS MÓDULOS

- **HighEmployeesController, LowEmployeesController:** Selector de áreas (areas) en alta/baja de colaboradores.
- **PositionsController:** area_catalogs en create/edit de puestos.
- **FolderController, OnlineWellnessController:** Áreas en carpetas y bienestar.
- **SurveysController, MessagesController, NotificationPushController, PersonalizedMessagesController:** Áreas para destinatarios y filtros.
- **ProductManagementController:** Áreas en edición de producto.
- **HighEmployeeFilter:** area_id en filtros de empleado (y restringe listado de áreas en getIndex/getTable).
- **ReceivableAccount, AreaHistory, ProductFilter:** Relaciones con Area.
- **Panel dashboard (panels.blade.php):** Enlace "Ver Detalles" a admin_areas.

---

## MODELOS INVOLUCRADOS

- **Area (App\Models\Area):** tabla areas, SoftDeletes, fillable name, company_id, general_area_id. company(), general_area(), high_employees(), low_employees(), product_filters(), high_employee_filters(), folders(), receivable_accounts(), area_histories().
- **GeneralArea (App\Models\GeneralArea):** catálogo de áreas generales; usado en select create/edit.
- **Company:** areas() hasMany Area.
- **User, Log:** Log asociado al usuario y a su company.

---

## MIGRACIONES

- **create_areas_table:** id, name, company_id (nullable FK companies cascade), timestamps.
- **update_areas_table:** general_area_id (nullable FK general_areas cascade).
- **update_areas_2_table:** softDeletes() en areas.

---

## PERMISOS LEGACY

- **view_areas:** getIndex, getTable (incluye export Excel).
- **create_areas:** getCreate, create.
- **edit_areas:** getEdit, update.
- **trash_areas:** Trash.

---

## CASOS BORDE

- **Trash con area_id inexistente:** Se construye `$message` con `$area->name` antes de comprobar `if (!$area)`; si el área no existe, $area es null y se produce error.
- **Filtro empresas:** En getTable, `$filter = json_decode($request->companies)`; si no se envía o es inválido, puede ser null; el código usa `if(!empty($filter))` para filtrar; si "SIN ASIGNAR" está en el filtro, se hace orWhereNull('company_id'). El valor "SIN ASIGNAR" en el select de list tiene value "SIN ASIGNAR" (string), no null; hay que comprobar que el backend trate bien ese valor (en getTable se usa in_array('SIN ASIGNAR', $filter) para añadir orWhereNull).
- **Admin sin general_area en request:** En update, si request->general_area no viene (p. ej. alta desde otra herramienta), no se actualiza general_area_id; se mantiene el valor anterior. La vista edit para admin tiene el select required, por lo que en uso normal siempre llega.

---

## AMBIGÜEDADES

- **general_area requerido en vista pero no en controlador:** Para admin el select es required en HTML; el controlador no valida que general_area exista o que sea id válido de GeneralArea. Si se envía un id inexistente, la FK podría fallar o quedar inconsistente.
- **high_user editando área de otra empresa:** Si un high_user obtiene un area_id de otra empresa (p. ej. manipulando la URL), getEdit no comprueba que el área pertenezca a su empresa; al hacer update se fuerza company_id a su empresa, "robando" el área. No hay política de autorización por empresa en getEdit/getView.

---

## DEUDA TÉCNICA

- En create y update el log se asocia a la company del usuario **dos veces** (dos bloques idénticos `if(isset($user->company_id)) { $company_user->logs()->save($log); }`).
- Orden en Trash: comprobar existencia de $area antes de usar $area->name en $message.
- Vista create.blade.php (y edit): hay un `}` suelto dentro del @foreach de general_areas (`}}` seguido de `}` en la línea del option); puede ser error de sintaxis Blade.

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Áreas (Filament o similar), conviene contrastar: alcance por empresa/filtros de empleado, obligatoriedad de área general, soft delete, export Excel y permisos view/create/edit/trash. No se ha verificado implementación actual en tecben-core en este análisis.
