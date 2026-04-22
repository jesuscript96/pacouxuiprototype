# Ficha técnica: Módulo Puesto (Legacy Paco) — PuestoResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **puestos**. Solo describe lo que existe en el código.

---

## MÓDULO: Puesto (PositionsController / equivalente PuestoResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona **puestos** por empresa: listado con filtro por empresa y búsqueda, alta, edición, vista detalle, baja (trash) y exportación a Excel. Cada puesto tiene **nombre**, **company_id** (opcional; "SIN ASIGNAR" si null), **general_position_id** (puesto general homologado; solo visible/editable para admin), **occupation_catalog_id** (catálogo de ocupaciones) y **area_catalog_id** (áreas/subáreas de ocupaciones). Los usuarios **high_user** solo ven y crean puestos de su empresa; los **admin** ven todos (o los restringidos por high_employee_filters). Controlador: `App\Http\Controllers\Admin\PositionsController`. Rutas bajo `admin/positions/*`; permisos: `view_positions`, `create_positions`, `edit_positions`, `trash_positions`.

---

## ENTIDADES

### Tabla: `positions`

- **PK:** id (bigint). **SoftDeletes** (deleted_at).
- **Campos:** name (string), company_id (unsignedBigInteger nullable, FK companies cascade), general_position_id (nullable, FK general_positions), occupation_catalog_id (nullable, añadido en migración posterior), area_catalog_id (nullable, añadido en migración posterior), timestamps, deleted_at.
- **Relaciones (modelo Position):** company() belongsTo Company; general_position() belongsTo GeneralPosition; occupation_catalog() belongsTo OccupationCatalog; area_catalog() belongsTo AreaCatalog; users(), high_employees(), low_employees(), product_filters(), high_employee_filters(), folders(), receivable_accounts(), position_histories().

### Catálogos

- **GeneralPosition:** catálogo de puestos generales (homologación); en create/edit (solo admin) select required.
- **OccupationCatalog:** catálogo de ocupaciones; en create/edit select opcional.
- **AreaCatalog:** áreas/subáreas de ocupaciones (code, denomination, full_name); en create/edit select opcional.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/positions | GET | PositionsController@getIndex | view_positions |
| admin/positions/get | POST | getList | view_positions |
| admin/positions/create | GET | getCreate | create_positions |
| admin/positions/create | POST | create | create_positions |
| admin/positions/edit/{position_id} | GET | getEdit | edit_positions |
| admin/positions/edit | POST | update | edit_positions |
| admin/positions/trash/{position_id} | GET | Trash | trash_positions |
| admin/positions/view/{position_id} | GET | getView | view_positions |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Puestos" (admin_positions) si el rol tiene al menos uno de: edit_positions, view_positions, trash_positions, create_positions.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance:** Si el usuario tiene **high_employee_filters** con position_id no null, solo ve los puestos cuyo id está en esos filtros. Si no tiene filtros y tiene **company** (high_user), solo ve puestos de su empresa (`$user->company->positions()`). Si no tiene filtros y no tiene company (admin), ve todos (`Position::query()`).
- **RN-02:** **Crear puesto:** name obligatorio. Si usuario es **high_user**, la nueva posición se asocia a su empresa (`$company->positions()->save($position)`). Si viene `request->general_position`, se asocia el puesto general (solo admin ve el select; en vista es required). Si viene `request->occupation_catalog`, se asocia con `$occupation_catalog->positions()->save($position)`. Si viene `request->area_catalog`, se asocia con `$area_catalog->positions()->save($position)`.
- **RN-03:** **Editar puesto:** name obligatorio. Si usuario es **high_user**, se fuerza `position->company_id = $user->company->id`. Si viene `request->general_position`, se actualiza general_position_id (solo admin). Si viene occupation_catalog o area_catalog, se hace `$catalog->positions()->save($position)` (actualiza los FK del puesto). **No se puede “desasignar”** occupation_catalog ni area_catalog desde el formulario: si no se envían, no se modifican (se mantienen los valores anteriores).
- **RN-04:** **Puesto general (general_position):** Solo admin ve y envía el campo en create/edit; en la vista es required. high_user no envía general_position.
- **RN-05:** **Empresa (company_id):** Asignación implícita en create para high_user y en update se reasigna para high_user. Admin no asigna empresa al crear (queda null).
- **RN-06:** **Trash:** No se puede eliminar si el puesto tiene high_employees, product_filters, high_employee_filters, users o folders. Mensaje: "No puede borrar un puesto con registros asignados." Eliminación con soft delete.
- **RN-07:** **Logs:** En create, update y Trash se crea un log con formato "nombre apellido_paterno (email)" y nombre del puesto con id; se asocia al usuario y a su company si tiene company_id.
- **RN-08:** **Listado y getList (POST):** getIndex pagina 10 y construye companies para filtro (incluye "SIN ASIGNAR"). getList: filtro por companies (valor "SIN ASIGNAR" para orWhereNull), búsqueda por id, name, company general_name, general_position name o literal "SIN ASIGNAR" (company_id/general_position_id null), ordenación por id, name, approved_position (general_position name) o company (general_name). Si action == 'getExcel', export PositionsExport a downloads/Puestos.xlsx y devuelve JSON con URL firmada (download_file). Si no, paginación y vista admin.positions.table.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

- **getIndex:** Query base según high_employee_filters o company (ver RN-01). Companies desde positions (distinct company_id) más "SIN ASIGNAR"; paginación 10. Solo admin ve panel "Filtrar por" (empresa). Vista list con tabla (include admin.positions.table) y modal eliminar.
- **getList (POST):** Parámetros take, search, companies (JSON), orderBy, sortDir, page, action. Filtro por companies (in_array('SIN ASIGNAR', $filter) para orWhereNull company_id). Búsqueda por id, name, company general_name, general_position name, o "SIN ASIGNAR". Ordenación por id, name, approved_position (subquery GeneralPosition name), company (subquery Company general_name). Si action == 'getExcel': get(), PositionsExport, respuesta JSON con url. Si no, paginate y vista table.

### Crear (getCreate / create)

- **getCreate:** GeneralPosition::all() ordenado por name; OccupationCatalog ordenado por descripción; AreaCatalog con code, denomination, full_name ordenado por denomination. Vista create con name, (solo admin) general_position required, occupation_catalog y area_catalog opcionales.
- **create:** Validar name required. Crear Position con name; si high_user, company->positions()->save($position); si general_position, associate y save; si occupation_catalog, occupation_catalog->positions()->save($position); si area_catalog, area_catalog->positions()->save($position). Log; redirect a admin_positions.

### Ver (getView)

- Buscar Position por id; si no existe, redirect a admin_positions. Vista view solo lectura (id y nombre).

### Editar (getEdit / update)

- **getEdit:** Position por id; si no existe, redirect. GeneralPosition::all(), OccupationCatalog, AreaCatalog; vista edit con position_id, name, (solo admin) general_position, occupation_catalog, area_catalog.
- **update:** Validar name required. Si high_user, position->company_id = user->company->id. Si general_position, position->general_position_id = request->general_position. position->save(). Si occupation_catalog, occupation_catalog->positions()->save($position). Si area_catalog, area_catalog->positions()->save($position). No se comprueba si $position es null tras el find (si position_id inválido, error). No se limpia occupation_catalog_id ni area_catalog_id si no se envían. Log; redirect a admin_positions_edit.

### Trash (eliminar)

- Buscar Position por id. Se asigna `$message = "Se ha eliminado el puesto: ".$position->name` **antes** del `if (!$position)` → si no existe, error. Si no existe, redirect back "El puesto no existe." Si tiene high_employees, product_filters, high_employee_filters, users o folders, redirect back con error. Log; Position::where("id",$position_id)->delete() (soft delete); redirect a admin_positions.

---

## VALIDACIONES

- **create / update:** name required. Mensaje: "El nombre es requerido".
- general_position no se valida en servidor como obligatorio para admin; la vista lo marca required. occupation_catalog y area_catalog opcionales.

---

## VISTAS

- **admin.positions.list:** Título "Puestos"; subtítulo administración de puestos. Botón Crear. Solo admin: panel "Filtrar por" (empresa multiselect). Incluye admin.positions.table y modal eliminar. Scripts recargan tabla vía POST get_admin_positions (filtros, búsqueda, paginación, Excel).
- **admin.positions.table:** Tabla: N°, Nombre; (solo admin) Puesto Homologado, Empresa; acciones (Editar, Ver, Eliminar). Botón "Descargar a excel". Label "Mostrar N **ubicaciones**" (texto posiblemente erróneo; debería ser puestos). general_position y company con "SIN ASIGNAR" en gris si no hay.
- **admin.positions.create:** Formulario name; (solo admin) general_position (required); occupation_catalog y area_catalog (opcionales). action admin_positions_create. Typo en vista: "form-grou" en un div (debería ser form-group).
- **admin.positions.edit:** Igual que create con position_id y datos precargados. action admin_positions_update.
- **admin.positions.view:** Solo lectura: id y nombre. Enlace Regresar a admin_positions.

---

## USO EN OTROS MÓDULOS

- **HighEmployeesController, LowEmployeesController:** Selector de puestos en alta/baja de colaboradores.
- **FolderController, SurveysController, MessagesController, NotificationPushController, PersonalizedMessagesController, ProductManagementController:** Puestos para destinatarios, filtros y carpetas.
- **HighEmployeeFilter, ProductFilter:** position_id en filtros.
- **User, ReceivableAccount, PositionHistory, Folder (pivot):** Relaciones con Position.
- **PositionsExport:** Export Excel de puestos (incluye general_position name).

---

## MODELOS INVOLUCRADOS

- **Position (App\Models\Position):** tabla positions, SoftDeletes, fillable name, company_id, general_position_id, occupation_catalog_id, area_catalog_id. company(), general_position(), occupation_catalog(), area_catalog(), users(), high_employees(), low_employees(), product_filters(), high_employee_filters(), folders(), receivable_accounts(), position_histories().
- **GeneralPosition:** positions() hasMany Position.
- **OccupationCatalog, AreaCatalog:** positions() hasMany (o relación que permite save($position)).
- **Company:** positions() hasMany Position.

---

## MIGRACIONES

- **create_positions_table:** id, name, timestamps.
- **update_positions_table:** company_id (nullable FK companies cascade).
- **update_positions_2_table:** general_position_id (nullable FK general_positions).
- **update_positions_3_table:** softDeletes() en positions.
- **add_occupation_catalog_field_to_positions:** occupation_catalog_id (nullable).
- **add_area_catalog_field_to_positions:** area_catalog_id (nullable).

---

## PERMISOS LEGACY

- **view_positions:** getIndex, getList (incluye export Excel), getView.
- **create_positions:** getCreate, create.
- **edit_positions:** getEdit, update.
- **trash_positions:** Trash.

---

## CASOS BORDE

- **Trash con position_id inexistente:** $message se construye con $position->name antes de comprobar !$position; si no existe, error.
- **Update con position_id inexistente:** Tras validar name se usa $position sin comprobar null; si el id no existe, error al acceder a $position->name y save().
- **Desasignar catálogos en edición:** No hay opción para dejar occupation_catalog o area_catalog en blanco; si no se envían, se mantienen los valores actuales.
- **getList:** Usa Storage::disk('local') sin que aparezca use Illuminate\Support\Facades\Storage en los imports del controlador (🔧 posible falta de import).

---

## AMBIGÜEDADES

- **high_user editando puesto de otra empresa:** getEdit/getView no comprueban que el puesto pertenezca a la empresa del usuario; en update se fuerza company_id para high_user.
- **occupation_catalog / area_catalog en update:** Se usa catalog->positions()->save($position); si el catálogo tiene hasMany positions, save() sobre un position existente actualiza el FK en el position. No se actualiza position.occupation_catalog_id ni position.area_catalog_id directamente; si no se envía ninguno, los FK no se tocan. Para “limpiar” haría falta lógica explícita.

---

## DEUDA TÉCNICA

- Orden en Trash: comprobar existencia de $position antes de usar $position->name en $message.
- Update: comprobar que $position exista tras el find.
- Añadir `use Illuminate\Support\Facades\Storage` en PositionsController si falta (getList usa Storage).
- Vista create: typo "form-grou" en lugar de "form-group".
- Tabla: label "Mostrar N ubicaciones" podría ser "puestos".

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Puestos (Filament o similar), conviene contrastar: alcance por empresa/filtros de empleado, campos occupation_catalog y area_catalog, posibilidad de desasignar catálogos en edición, soft delete, bloqueo de borrado (high_employees, users, folders, etc.) y export Excel. No se ha verificado implementación actual en tecben-core en este análisis.
