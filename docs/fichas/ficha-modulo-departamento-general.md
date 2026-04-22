# Ficha técnica: Módulo Departamento General (Legacy Paco) — DepartamentoGeneralResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **departamentos generales** (catálogo de homologación). Solo describe lo que existe en el código.

---

## MÓDULO: Departamento General (GeneralDepartmentsController / equivalente DepartamentoGeneralResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona el **catálogo de departamentos generales** para homologación: listado, alta, edición, vista detalle y baja (trash). Cada departamento general tiene solo **nombre**. No hay empresa ni alcance por usuario: todos los que tienen permiso ven el mismo listado (GeneralDepartment::all()). Los **departamentos** (por empresa) se asocian a un general_department_id en el módulo Departamentos. Controlador: `App\Http\Controllers\Admin\GeneralDepartmentsController`. Rutas bajo `admin/general_departments/*`; permisos: `view_general_departments`, `create_general_departments`, `edit_general_departments`, `trash_general_departments`.

---

## ENTIDADES

### Tabla: `general_departments`

- **PK:** id (bigint). **SoftDeletes** (deleted_at).
- **Campos:** name (string), timestamps, deleted_at.
- **Relaciones (modelo GeneralDepartment):** departments() hasMany Department. Los departamentos por empresa tienen general_department_id FK a esta tabla; al eliminar un departamento general se comprueba si hay departamentos asignados antes de permitir el borrado.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/general_departments | GET | GeneralDepartmentsController@getIndex | view_general_departments |
| admin/general_departments/get | GET | getList | view_general_departments |
| admin/general_departments/create | GET | getCreate | create_general_departments |
| admin/general_departments/create | POST | create | create_general_departments |
| admin/general_departments/edit/{general_department_id} | GET | getEdit | edit_general_departments |
| admin/general_departments/edit | POST | update | edit_general_departments |
| admin/general_departments/trash/{general_department_id} | GET | Trash | trash_general_departments |
| admin/general_departments/view/{general_department_id} | GET | getView | view_general_departments |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Departamentos Generales" (admin_general_departments) si el rol tiene al menos uno de: edit_general_departments, view_general_departments, trash_general_departments, create_general_departments.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance:** No hay filtro por empresa ni por high_employee_filters. Cualquier usuario con permiso ve todos los departamentos generales (GeneralDepartment::all() en getList). Solo un listado global.
- **RN-02:** **Crear / Editar:** name obligatorio. No hay más campos. No hay unicidad explícita en el controlador (se pueden crear varios con el mismo nombre).
- **RN-03:** **Trash:** No se puede eliminar si existe al menos un registro en la relación departments() (departamentos por empresa que referencian este departamento general). Mensaje: "No puede borrar un departamento con registros asignados." Eliminación con soft delete (modelo GeneralDepartment usa SoftDeletes).
- **RN-04:** **Logs:** En create, update y Trash se crea un registro en tabla logs (acción con "departamento general" / "departamento general para homologaciones" y nombre del usuario con formato "nombre apellido_paterno (email)" más id del catálogo). Se asocia al usuario y a su company si tiene company_id. En update el log se guarda en la company **dos veces** (dos bloques idénticos if(isset($user->company_id))).
- **RN-05:** **Uso en el sistema:** Los departamentos (módulo Departamentos) tienen general_department_id opcional; en create/edit de Departamentos (solo admin) se muestra un select de GeneralDepartment. Este módulo mantiene el catálogo que se usa allí.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

- **getIndex:** Devuelve vista `admin.general_departments.list` (DataTable que consume getList por AJAX).
- **getList:** GeneralDepartment::all(). Por cada registro: id, name, acciones (Editar, Ver, Eliminar). Respuesta JSON `{ data: [...] }`. No se filtra por usuario ni empresa.

### Crear (getCreate / create)

- **getCreate:** Vista create sin datos adicionales (solo formulario name).
- **create:** Validar name required. Crear GeneralDepartment con name; log (incluyendo id del departamento general); redirect a admin_general_departments con mensaje "Departamento general para homologaciones creado exitosamente".

### Ver (getView)

- Buscar GeneralDepartment por id; si no existe, redirect a admin_general_departments con message_info. Vista view solo lectura (id y nombre).

### Editar (getEdit / update)

- **getEdit:** GeneralDepartment por id; si no existe, redirect a admin_general_departments con message_info. Vista edit con general_department_id y name.
- **update:** Validar name required. Se obtiene $general_department = GeneralDepartment::where("id",$general_department_id)->first() pero **no se comprueba si es null** antes de asignar $general_department->name; si general_department_id no existe, se produce error. save(); log (dos veces en company); redirect a admin_general_departments_edit con mensaje éxito.

### Trash (eliminar)

- Buscar GeneralDepartment por id. En el código se asigna `$message = "Se ha eliminado el departamento general para homologaciones: ".$general_department->name` **antes** del `if (!$general_department)` → si el id no existe, $general_department es null y se produce error. Si no existe, redirect back con error "El departamento no existe." Si $general_department->departments()->exists(), redirect back con error. Log; GeneralDepartment::where("id",$general_department_id)->delete() (soft delete); redirect a admin_general_departments con message_info.

---

## VALIDACIONES

- **create / update:** name required. Mensaje: "El nombre es requerido".
- No se valida unicidad de nombre ni longitud máxima.

---

## VISTAS

- **admin.general_departments.list:** Título "Departamentos Generales". Botón Crear. DataTable id dataTables-departments con ajax get_admin_general_departments. Columnas: N°, Nombre, acciones. Modal confirmación eliminar.
- **admin.general_departments.create:** Formulario name (required). action admin_general_departments_create.
- **admin.general_departments.edit:** Formulario general_department_id (hidden), name (required). action admin_general_departments_update.
- **admin.general_departments.view:** Solo lectura: id y nombre. Enlace Regresar a admin_general_departments.

---

## USO EN OTROS MÓDULOS

- **DepartmentsController (módulo Departamentos):** getCreate y getEdit cargan GeneralDepartment::all() ordenado por name para el select "Departamento general" / "Departamento homologado". Los departamentos por empresa tienen general_department_id opcional; solo los usuarios admin ven y envían ese campo.
- **Modelo Department:** general_department() belongsTo GeneralDepartment.

---

## MODELOS INVOLUCRADOS

- **GeneralDepartment (App\Models\GeneralDepartment):** tabla general_departments, SoftDeletes, fillable name. departments() hasMany Department.
- **Department:** general_department_id (nullable), belongsTo GeneralDepartment.
- **User, Company, Log:** Log asociado al usuario y a su company.

---

## MIGRACIONES

- **create_general_departments_table:** id, name, timestamps.
- **update_general_departments_table:** softDeletes() en general_departments.

---

## PERMISOS LEGACY

- **view_general_departments:** getIndex, getList, getView.
- **create_general_departments:** getCreate, create.
- **edit_general_departments:** getEdit, update.
- **trash_general_departments:** Trash.

---

## CASOS BORDE

- **Trash con general_department_id inexistente:** Se construye `$message` con `$general_department->name` antes de comprobar `if (!$general_department)`; si no existe, $general_department es null y se produce error.
- **Update con general_department_id inexistente:** Tras la validación de name se usa $general_department sin comprobar si es null antes de $general_department->name y save(); si el id no existe, se produce error.

---

## AMBIGÜEDADES

- **Unicidad de nombre:** No se valida que el nombre sea único; se pueden crear varios departamentos generales con el mismo nombre. No queda claro si es intencional o omisión.

---

## DEUDA TÉCNICA

- Orden en Trash: comprobar existencia de $general_department antes de usar $general_department->name en $message.
- Update: comprobar que $general_department exista después de obtenerlo por general_department_id y antes de usarlo; si no existe, redirect con error.
- En update el log se asocia a la company del usuario **dos veces** (dos bloques idénticos); eliminar el duplicado.

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Departamentos Generales (catálogo de homologación), conviene contrastar: unicidad de nombre, validación de existencia en update, soft delete, bloqueo de borrado cuando hay departamentos asignados y formato de log (nombre apellido (email) + id). No se ha verificado implementación actual en tecben-core en este análisis.
