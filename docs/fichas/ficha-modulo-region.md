# Ficha técnica: Módulo Región (Legacy Paco) — RegionResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **regiones**. Solo describe lo que existe en el código.

---

## MÓDULO: Región (RegionsController / equivalente RegionResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona **regiones** por empresa: listado con filtro por empresa y búsqueda, alta, edición, vista detalle, baja (trash) y exportación a Excel. Cada región tiene **nombre** y **company_id** (opcional; "SIN ASIGNAR" si null). No hay catálogo de homologación (general_region). Los usuarios **high_user** solo ven y crean regiones de su empresa; los **admin** ven todas (o las restringidas por high_employee_filters). Controlador: `App\Http\Controllers\Admin\RegionsController`. Rutas bajo `admin/regions/*`; permisos: `view_regions`, `create_regions`, `edit_regions`, `trash_regions`. No hay ruta getList: el listado se sirve por getIndex (primera carga) y getFilters (POST, recarga/Excel).

---

## ENTIDADES

### Tabla: `regions`

- **PK:** id (bigint). Sin SoftDeletes (eliminación física).
- **Campos:** name (string), company_id (unsignedBigInteger nullable, FK companies cascade), timestamps.
- **Relaciones (modelo Region):** company() belongsTo Company; high_employees() hasMany HighEmployee; region_histories() hasMany RegionHistory; product_filters() hasMany ProductFilter; high_employee_filters() hasMany HighEmployeeFilter. Las FK desde high_employees, product_filters, high_employee_filters, region_histories, etc. suelen tener onDelete set null o restrict; no se comprueba en Trash si hay registros asignados antes de borrar.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/regions | GET | RegionsController@getIndex | view_regions |
| admin/regions/filters | POST | getFilters | view_regions |
| admin/regions/create | GET | getCreate | create_regions |
| admin/regions/create | POST | create | create_regions |
| admin/regions/edit/{region_id} | GET | getEdit | edit_regions |
| admin/regions/edit | POST | update | edit_regions |
| admin/regions/trash/{region_id} | GET | Trash | trash_regions |
| admin/regions/view/{region_id} | GET | getView | view_regions |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Regiones" (admin_regions) si el rol tiene al menos uno de: edit_regions, view_regions, trash_regions, create_regions.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance:** Si el usuario tiene **high_employee_filters** con region_id no null, solo ve las regiones cuyo id está en esos filtros. Si no tiene filtros y tiene **company** (high_user), solo ve regiones de su empresa (`$user->company->regions()`). Si no tiene filtros y no tiene company (admin), ve todas (`Region::query()`).
- **RN-02:** **Crear región:** name obligatorio. Si usuario es **high_user**, la nueva región se asocia a su empresa (`$company->regions()->save($region)`). Admin no asigna empresa al crear (company_id queda null).
- **RN-03:** **Editar región:** name obligatorio. Si usuario es **high_user**, se fuerza `region->company_id = $user->company->id`. Admin no modifica company_id en update (no hay selector en el formulario).
- **RN-04:** **Empresa (company_id):** No hay selector de empresa en create/edit. Se asigna implícitamente en create para high_user y en update se reasigna para high_user. Admin no asigna ni cambia empresa.
- **RN-05:** **Trash:** No se comprueba si la región tiene high_employees, product_filters, high_employee_filters o region_histories. Se elimina con delete físico. La FK high_employees.region_id tiene onDelete('set null') en migración: al borrar la región, los empleados con esa region_id quedan con region_id null. No hay mensaje de error por “registros asignados”.
- **RN-06:** **Logs:** En create, update y Trash se crea un registro en logs (acción con usuario y nombre de la región) y se asocia al usuario y a su company si tiene company_id.
- **RN-07:** **Listado y filtros:** getIndex pagina por defecto 10 y construye lista de empresas para el filtro (leftJoin companies, IFNULL general_name "SIN ASIGNAR"). getFilters (POST): filtro por empresas (request->companies JSON; si incluye 0, orWhereNull company_id), búsqueda por id, name o general_name de empresa, ordenación por id, name o company (general_name), paginación, selectores (companies) en página 1 y action getTable. Export Excel cuando action == 'getExcel': genera downloads/Regiones.xlsx y devuelve JSON con URL firmada (download_file).

---

## FLUJO PRINCIPAL

### Listado (getIndex / getFilters)

- **getIndex:** Query base según high_employee_filters o company (ver RN-01). Companies para filtro; paginación 10. Solo admin ve panel "Filtrar por" (empresa). Vista list con tabla inicial (include admin.regions.table) y modal eliminar.
- **getFilters (POST):** Parámetros take, search, companies, orderBy, sortDir, page, action, name (activeFilter). Mismo alcance. Filtro por companies (0 = orWhereNull). Búsqueda por regions.id, name, company general_name. Selectors companies solo en page==1 y action getTable (y condiciones de activeFilter). Ordenación por id, name o company. Si action=='getExcel': get(), formatear company_name, RegionsExport a downloads/Regiones.xlsx, respuesta JSON con url firmada. Si no, paginate y formatear company_name (HTML con content-color-gray para "SIN ASIGNAR"); respuesta JSON con view (cleanView de admin.regions.table), filters, selectors.

### Crear (getCreate / create)

- **getCreate:** Vista create sin datos adicionales (solo formulario name).
- **create:** Validar name required. Crear Region con name; si high_user, asociar a su company con $company->regions()->save($region). Log; redirect a admin_regions con mensaje "Región creada exitosamente".

### Ver (getView)

- Buscar Region por id; si no existe, redirect a admin_regions con message_info. Vista view solo lectura (id y nombre).

### Editar (getEdit / update)

- **getEdit:** Region por id; si no existe, redirect a admin_regions con message_info. Vista edit con region_id y name.
- **update:** Validar name required. Se obtiene $region = Region::where("id",$region_id)->first() pero **no se comprueba si es null** antes de usar $region->name y asignar company_id para high_user; si region_id no existe, se produce error. save(); log; redirect a admin_regions_edit con mensaje éxito.

### Trash (eliminar)

- Buscar Region por id. En el código se asigna `$message = "Se ha eliminado la región: ".$region->name` **antes** del `if (!$region)` → si el id no existe, $region es null y se produce error. Si no existe, redirect back con error "La región no existe." Log; Region::where("id",$region_id)->delete() (físico); redirect a admin_regions con message_info.

---

## VALIDACIONES

- **create / update:** name required. Mensaje: "El nombre es requerido".
- No se valida unicidad de nombre por empresa ni longitud máxima.

---

## VISTAS

- **admin.regions.list:** Título "Regiones"; subtítulo administración de regiones de la empresa. Botón Crear. Solo admin: panel "Filtrar por" con multiselect Empresa y "Borrar filtros". Incluye admin.regions.table y modal confirmación eliminar. Scripts recargan tabla vía POST admin_regions_filters (búsqueda, paginación, orden, filtro empresas, Excel).
- **admin.regions.table:** Tabla con columnas: N°, Nombre, acciones (Editar, Ver, Eliminar), (solo admin) Empresa. Botón "Descargar a excel". Inputs ocultos name, orderBy, sortDir, currentPage; select "Mostrar N regiones"; búsqueda. company_name formateado en el controlador.
- **admin.regions.create:** Formulario name (required). action admin_regions_create.
- **admin.regions.edit:** Formulario region_id (hidden), name (required). action admin_regions_update.
- **admin.regions.view:** Solo lectura: id y nombre. Enlace Regresar a admin_regions.

---

## USO EN OTROS MÓDULOS

- **HighEmployeesController:** Selector de regiones en alta y edición de colaboradores (region_id).
- **SurveysController, MessagesController, NotificationPushController, PersonalizedMessagesController:** Regiones para destinatarios y filtros.
- **ProductFilter, HighEmployeeFilter:** region_id en filtros.
- **RegionHistory, RegionsExport, RegionSheet (export colaboradores):** Historial y exportaciones.

---

## MODELOS INVOLUCRADOS

- **Region (App\Models\Region):** tabla regions, fillable name, company_id. company(), high_employees(), region_histories(), product_filters(), high_employee_filters(). Sin SoftDeletes.
- **Company:** regions() hasMany Region.
- **User, Log:** Log asociado al usuario y a su company.

---

## MIGRACIONES

- **create_regions_table:** id, name, company_id (nullable FK companies cascade), timestamps.
- **update_high_employees_24_table:** region_id (nullable FK regions) en high_employees con onDelete('set null').
- Otras tablas (product_filters, high_employee_filters, region_histories, survey_shippings, etc.) añaden region_id con FK a regions.

---

## PERMISOS LEGACY

- **view_regions:** getIndex, getFilters (incluye export Excel), getView.
- **create_regions:** getCreate, create.
- **edit_regions:** getEdit, update.
- **trash_regions:** Trash.

---

## CASOS BORDE

- **Trash con region_id inexistente:** Se construye `$message` con `$region->name` antes de comprobar `if (!$region)`; si no existe, $region es null y se produce error.
- **Update con region_id inexistente:** Tras validar name se usa $region sin comprobar si es null; si el id no existe, error al acceder a $region->name y al guardar.
- **Filtro empresas:** getFilters usa in_array(0, $companies_filter) para orWhereNull('regions.company_id'). Columnas para high_user no incluyen company (índice 3); si high_user envía orderBy=3 podría haber índice inexistente en $columns.
- **Trash con regiones con empleados:** No hay validación; high_employees.region_id tiene onDelete set null, por lo que el borrado deja a los empleados con region_id null. No se informa al usuario.

---

## AMBIGÜEDADES

- **high_user editando región de otra empresa:** getEdit/getView no comprueban que la región pertenezca a la empresa del usuario; en update se fuerza company_id para high_user, por lo que podría “reasignar” una región ajena a su empresa.

---

## DEUDA TÉCNICA

- Orden en Trash: comprobar existencia de $region antes de usar $region->name en $message.
- Update: comprobar que $region exista después del find y, si no, redirect con error.
- Trash: valorar comprobar si existen high_employees (u otras relaciones) antes de borrar y mostrar error o advertencia, en lugar de depender solo del set null en BD.

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Regiones (Filament o similar), conviene contrastar: alcance por empresa/filtros de empleado, soft delete vs físico, comprobación de registros asignados antes de borrar, export Excel y permisos. No se ha verificado implementación actual en tecben-core en este análisis.
