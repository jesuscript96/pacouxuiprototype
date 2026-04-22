# Ficha técnica: Módulo Departamento (Legacy Paco) — DepartamentoResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **departamentos**. Solo describe lo que existe en el código.

---

## MÓDULO: Departamento (DepartmentsController / equivalente DepartamentoResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona **departamentos** por empresa: listado con filtro por empresa y búsqueda, alta, edición, vista detalle, baja (trash) y exportación a Excel. Cada departamento tiene **nombre**, **company_id** (opcional; "SIN ASIGNAR" si null) y **general_department_id** (departamento general homologado; solo visible/editable para rol admin). Los usuarios **high_user** solo ven y crean departamentos de su empresa; los **admin** ven todos (o los restringidos por high_employee_filters). Controlador: `App\Http\Controllers\Admin\DepartmentsController`. Rutas bajo `admin/departments/*`; permisos: `view_departments`, `create_departments`, `edit_departments`, `trash_departments`.

---

## ENTIDADES

### Tabla: `departments`

- **PK:** id (bigint). **SoftDeletes** (deleted_at).
- **Campos:** name (string), company_id (unsignedBigInteger nullable, FK companies cascade), general_department_id (unsignedBigInteger nullable, FK general_departments cascade), timestamps, deleted_at.
- **Relaciones (modelo Department):** company() belongsTo Company; general_department() belongsTo GeneralDepartment; users() hasMany User; high_employees() hasMany HighEmployee; low_employees() hasMany LowEmployee; product_filters() hasMany ProductFilter; high_employee_filters() hasMany HighEmployeeFilter; folders() belongsToMany Folder; festivities() hasMany Festivity; receivable_accounts() hasMany ReceivableAccount; department_histories() hasMany DepartmentHistory.

### Tabla: `general_departments`

- Catálogo de departamentos generales (homologación). En getCreate/getEdit se lista GeneralDepartment::all() ordenado por name para el select "Departamento general" (solo si el usuario tiene rol admin).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/departments | GET | DepartmentsController@getIndex | view_departments |
| admin/departments/get | GET | getList | view_departments |
| admin/departments/filters | POST | getFilters | view_departments |
| admin/departments/create | GET | getCreate | create_departments |
| admin/departments/create | POST | create | create_departments |
| admin/departments/edit/{department_id} | GET | getEdit | edit_departments |
| admin/departments/edit | POST | update | edit_departments |
| admin/departments/trash/{department_id} | GET | Trash | trash_departments |
| admin/departments/view/{department_id} | GET | getView | view_departments |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Departamentos" (admin_departments) si el rol tiene al menos uno de: edit_departments, view_departments, trash_departments, create_departments.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance por tipo de usuario:** Si el usuario tiene **high_employee_filters** con department_id no null, solo ve los departamentos cuyo id está en esos filtros. Si no tiene filtros y tiene **company** (high_user), solo ve departamentos de su empresa (`$user->company->departments()`). Si no tiene filtros y no tiene company (admin), ve todos (`Department::query()`).
- **RN-02:** **Crear departamento:** name obligatorio. Si usuario es **high_user**, el nuevo departamento se asocia a su empresa (`$company->departments()->save($department)`). Si viene `request->general_department`, se asocia el departamento general (solo el formulario admin muestra el select; high_user no lo ve).
- **RN-03:** **Editar departamento:** name obligatorio. Si usuario es **high_user**, se fuerza `department->company_id = $user->company->id`. Si viene `request->general_department`, se actualiza general_department_id (solo admin ve el campo).
- **RN-04:** **Departamento general (general_department):** Solo usuarios con rol **admin** ven y envían el campo en create/edit; en la vista es required. high_user no envía general_department; en create el controlador solo asocia si `isset($request->general_department)`.
- **RN-05:** **Empresa (company_id):** No hay selector de empresa en el formulario. Se asigna implícitamente en create para high_user y en update se reasigna a su empresa para high_user. Admin no asigna empresa al crear (queda null = "SIN ASIGNAR").
- **RN-06:** **Trash:** No se puede eliminar si el departamento tiene high_employees, product_filters, high_employee_filters, **users** o folders. Mensaje: "No puede borrar un departamento con registros asignados." Eliminación con soft delete (modelo Department usa SoftDeletes).
- **RN-07:** **Logs:** En create, update y Trash se crea un registro en tabla logs (acción con nombre del usuario y del departamento; formato "nombre / email") y se asocia al usuario actual y a su company si tiene company_id.
- **RN-08:** **Listado y filtros:** getIndex pagina por defecto 10 y construye lista de empresas para el filtro (leftJoin companies, IFNULL general_name "SIN ASIGNAR"). getFilters (POST): filtro por empresas (request->companies JSON; si incluye 0 se añade orWhereNull company_id), búsqueda por id, name o general_name de empresa, ordenación por id, name, general_department (name) o company (general_name), paginación, selectores (companies) solo en página 1 y action getTable. Export Excel cuando action == 'getExcel': genera downloads/Departamentos.xlsx y devuelve JSON con URL firmada (download_file).
- **RN-09:** **Dos endpoints de listado:** getList (GET) devuelve JSON con data para DataTable; getFilters (POST) devuelve JSON con view (HTML tabla), filters y selectors para recarga dinámica con filtros/búsqueda/orden/Excel. La vista list incluye la tabla y scripts que llaman a getFilters.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList / getFilters)

- **getIndex:** Query base según high_employee_filters o company (ver RN-01). Companies para filtro vía leftJoin e IFNULL(companies.general_name, "SIN ASIGNAR"); paginación 10. Solo admin ve panel "Filtrar por" (empresa). Vista list con tabla inicial y modal eliminar.
- **getList (GET):** Mismo alcance; Department::whereIn(...)->get() o company->departments()->get() o Department::all(). Por cada departamento: id, name, (admin) general_department_name, acciones, (admin) company_name. Respuesta JSON { data: [...] }.
- **getFilters (POST):** Parámetros take, search, companies, orderBy, sortDir, page, action, name (activeFilter). Mismo alcance. Filtro por companies (0 = orWhereNull company_id). Búsqueda por departments.id, name, company general_name. Si page==1 y action==getTable y (name != 'company_filter' o no viene companies), se recalculan selectors companies. Ordenación por id, name, general_department (subquery GeneralDepartment name), company (subquery Company general_name). Si action=='getExcel': get(), formatear general_department_name y company_name, DepartmentsExport a downloads/Departamentos.xlsx, respuesta JSON con url firmada. Si no, paginate y formatear igual; respuesta JSON con view (cleanView de admin.departments.table), filters, selectors.

### Crear (getCreate / create)

- **getCreate:** GeneralDepartment::all() ordenado por name; vista create con name y (solo admin) select general_department required.
- **create:** Validar name required. Crear Department con name; si high_user, asociar a su company; si request->general_department, asociar general_department. Log (y en company si aplica). Redirect a admin_departments con mensaje éxito.

### Ver (getView)

- Buscar Department por id; si no existe, redirect a admin_departments. Vista view solo lectura (id y nombre).

### Editar (getEdit / update)

- **getEdit:** Department por id; si no existe, redirect a admin_departments. GeneralDepartment::all() ordenado por name; vista edit con name y (solo admin) select general_department.
- **update:** Validar name required. Si high_user, department->company_id = user->company->id. Si request->general_department, department->general_department_id = request->general_department. save(); log (y en company si aplica). Redirect a admin_departments_edit con mensaje éxito.

### Trash (eliminar)

- Buscar Department por id. En el código se asigna `$message = "Se ha eliminado el departamento: ".$department->name` **antes** del `if (!$department)` → si el id no existe, $department es null y se produce error. Si no existe, redirect back con error "El departamento no existe." Si tiene high_employees, product_filters, high_employee_filters, users o folders, redirect back con error. Log (y en company si aplica); Department::where("id",$department_id)->delete() (soft delete). Redirect a admin_departments con message_info.

---

## VALIDACIONES

- **create / update:** name required. Mensaje: "El nombre es requerido".
- El select "Departamento general" es required en vista solo para admin; el controlador no valida general_department en servidor (solo isset).

---

## VISTAS

- **admin.departments.list:** Título "Departamentos"; subtítulo administración de departamentos. Botón Crear. Solo admin: panel "Filtrar por" con multiselect Empresa y "Borrar filtros". Incluye admin.departments.table y modal confirmación eliminar. Scripts recargan tabla vía POST admin_departments_filters (búsqueda, paginación, orden, filtro empresas, Excel).
- **admin.departments.table:** Tabla con columnas: N°, Nombre; (solo admin) Departamento Homologado, Empresa; acciones (Editar, Ver, Eliminar). Botón "Descargar a excel". Inputs ocultos name, orderBy, sortDir, currentPage; select "Mostrar N departamentos"; búsqueda. general_department_name y company_name vienen formateados del controlador (HTML con content-color-gray para "Sin asignar"/"SIN ASIGNAR").
- **admin.departments.create:** Formulario name; si admin, select general_department (required). action admin_departments_create.
- **admin.departments.edit:** Igual que create con department_id y datos precargados. action admin_departments_update.
- **admin.departments.view:** Solo lectura: id y nombre. Enlace Regresar a admin_departments.

---

## USO EN OTROS MÓDULOS

- **HighEmployeesController, LowEmployeesController:** Selector de departamentos en alta/baja de colaboradores.
- **FolderController, OnlineWellnessController, SurveysController, MessagesController, NotificationPushController, PersonalizedMessagesController:** Departamentos para destinatarios, filtros y carpetas.
- **ProductManagementController, HighEmployeeFilter:** Relaciones con Department.
- **User:** department_id en usuarios; Trash comprueba department->users()->exists().
- **ReceivableAccount, DepartmentHistory, Festivity, ProductFilter:** Relaciones con Department.
- **DepartmentsExport, DepartmentalStructureLCT/VR (commands/jobs):** Export Excel y estructura departamental.

---

## MODELOS INVOLUCRADOS

- **Department (App\Models\Department):** tabla departments, SoftDeletes, fillable name, company_id, general_department_id. company(), general_department(), users(), high_employees(), low_employees(), product_filters(), high_employee_filters(), folders(), festivities(), receivable_accounts(), department_histories().
- **GeneralDepartment (App\Models\GeneralDepartment):** catálogo para select create/edit.
- **Company:** departments() hasMany Department.
- **User, Log:** Log asociado al usuario y a su company.

---

## MIGRACIONES

- **create_departments_table:** id, name, timestamps.
- **update_departments_table:** company_id (nullable FK companies cascade).
- **update_departments_2_table:** general_department_id (nullable FK general_departments cascade).
- **update_departments_3_table:** softDeletes() en departments.

---

## PERMISOS LEGACY

- **view_departments:** getIndex, getList, getFilters (incluye export Excel).
- **create_departments:** getCreate, create.
- **edit_departments:** getEdit, update.
- **trash_departments:** Trash.

---

## CASOS BORDE

- **Trash con department_id inexistente:** Se construye `$message` con `$department->name` antes de comprobar `if (!$department)`; si no existe, $department es null y se produce error.
- **Filtro empresas:** getFilters usa `in_array(0, $companies_filter)` para orWhereNull('departments.company_id'); el valor 0 representa "SIN ASIGNAR". En getIndex la lista de companies viene del leftJoin y puede incluir filas con id null (name "SIN ASIGNAR"); el front debe enviar 0 para sin asignar si el backend espera 0.
- **Columnas según rol:** getFilters define $columns distinto si el usuario tiene company (high_user) o no (admin): admin tiene columnas id, name, general_department, company; high_user no tiene company en el array (índice 4 falta), por lo que orderBy con índice 4 podría fallar si high_user envía orderBy=4.

---

## AMBIGÜEDADES

- **general_department requerido en vista pero no en controlador:** Igual que en Áreas; no se valida que general_department sea id válido de GeneralDepartment.
- **high_user editando departamento de otra empresa:** getEdit/getView no comprueban que el departamento pertenezca a la empresa del usuario; en update se fuerza company_id para high_user, por lo que podría "reasignar" un departamento ajeno.
- **getList vs getFilters:** getList (GET) devuelve data para DataTable; getFilters (POST) devuelve view + filters + selectors. No queda claro si la lista inicial usa getList o getFilters; la vista list incluye la tabla con @include table y scripts que llaman getFilters, por lo que la carga inicial viene de getIndex (que ya pasa departments y companies) y las recargas de getFilters.

---

## DEUDA TÉCNICA

- Orden en Trash: comprobar existencia de $department antes de usar $department->name en $message.
- Vista create.blade.php y edit.blade.php: `}` suelto dentro del @foreach de general_departments (después del </option>), posible error de sintaxis Blade.
- cleanView en getFilters elimina saltos de línea y tabs del HTML; puede afectar legibilidad del markup devuelto.

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Departamentos (Filament o similar), conviene contrastar: alcance por empresa/filtros de empleado, obligatoriedad de departamento general, soft delete, bloqueo de borrado si tiene users (además de empleados, filtros, carpetas), export Excel y permisos. No se ha verificado implementación actual en tecben-core en este análisis.
