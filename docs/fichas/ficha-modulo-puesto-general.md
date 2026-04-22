# Ficha técnica: Módulo Puesto General (Legacy Paco) — PuestoGeneralResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **puestos generales** (catálogo de homologación). Solo describe lo que existe en el código.

---

## MÓDULO: Puesto General (GeneralPositionsController / equivalente PuestoGeneralResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona el **catálogo de puestos generales** para homologación: listado, alta, edición, vista detalle y baja (trash). Cada puesto general tiene solo **nombre**. No hay empresa ni alcance por usuario: todos los que tienen permiso ven el mismo listado (GeneralPosition::all()). Los **puestos** (por empresa) se asocian a un general_position_id en el módulo Puestos. Controlador: `App\Http\Controllers\Admin\GeneralPositionsController`. Rutas bajo `admin/general_positions/*`; permisos: `view_general_positions`, `create_general_positions`, `edit_general_positions`, `trash_general_positions`.

---

## ENTIDADES

### Tabla: `general_positions`

- **PK:** id (bigint). **SoftDeletes** (deleted_at).
- **Campos:** name (string), timestamps, deleted_at.
- **Relaciones (modelo GeneralPosition):** positions() hasMany Position. Los puestos por empresa tienen general_position_id FK a esta tabla; al eliminar un puesto general se comprueba si hay puestos asignados antes de permitir el borrado.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/general_positions | GET | GeneralPositionsController@getIndex | view_general_positions |
| admin/general_positions/get | GET | getList | view_general_positions |
| admin/general_positions/create | GET | getCreate | create_general_positions |
| admin/general_positions/create | POST | create | create_general_positions |
| admin/general_positions/edit/{general_position_id} | GET | getEdit | edit_general_positions |
| admin/general_positions/edit | POST | update | edit_general_positions |
| admin/general_positions/trash/{general_position_id} | GET | Trash | trash_general_positions |
| admin/general_positions/view/{general_position_id} | GET | getView | view_general_positions |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Puestos Generales" (admin_general_positions) si el rol tiene al menos uno de: edit_general_positions, view_general_positions, trash_general_positions, create_general_positions.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance:** No hay filtro por empresa ni por high_employee_filters. Cualquier usuario con permiso ve todos los puestos generales (GeneralPosition::all() en getList). Solo un listado global. En getList se llama User::getCurrent() pero no se usa para filtrar.
- **RN-02:** **Crear / Editar:** name obligatorio. No hay más campos. No hay unicidad explícita en el controlador (se pueden crear varios con el mismo nombre).
- **RN-03:** **Trash:** No se puede eliminar si existe al menos un registro en la relación positions() (puestos por empresa que referencian este puesto general). Mensaje: "No puede borrar un puesto con registros asignados." Eliminación con soft delete (modelo GeneralPosition usa SoftDeletes).
- **RN-04:** **Logs:** En create, update y Trash se crea un registro en tabla logs (acción con "puesto general para homologaciones" y nombre del usuario con formato "nombre / email"). Se asocia al usuario y a su company si tiene company_id.
- **RN-05:** **Uso en el sistema:** Los puestos (módulo Puestos / PositionsController) tienen general_position_id opcional; en create/edit de Puestos se muestra un select de GeneralPosition (required en la vista). Este módulo mantiene el catálogo que se usa allí.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

- **getIndex:** Devuelve vista `admin.general_positions.list` (DataTable que consume getList por AJAX).
- **getList:** GeneralPosition::all(). Por cada registro: id, name, acciones (Editar, Ver, Eliminar). Respuesta JSON `{ data: [...] }`. No se filtra por usuario ni empresa.

### Crear (getCreate / create)

- **getCreate:** Vista create sin datos adicionales (solo formulario name).
- **create:** Validar name required. Crear GeneralPosition con name; log; redirect a admin_general_positions con mensaje "Puesto general para homologaciones creado exitosamente".

### Ver (getView)

- Buscar GeneralPosition por id; si no existe, redirect a admin_general_positions con message_info. Vista view solo lectura (id y nombre).

### Editar (getEdit / update)

- **getEdit:** GeneralPosition por id; si no existe, redirect a admin_general_positions con message_info. Vista edit con general_position_id y name.
- **update:** Validar name required. Se obtiene $general_position = GeneralPosition::where("id",$general_position_id)->first() pero **no se comprueba si es null** antes de asignar $general_position->name; si general_position_id no existe, se produce error. save(); log; redirect a admin_general_positions_edit con mensaje éxito.

### Trash (eliminar)

- Buscar GeneralPosition por id. En el código se asigna `$message = "Se ha eliminado el puesto general para homologaciones: ".$general_position->name` **antes** del `if (!$general_position)` → si el id no existe, $general_position es null y se produce error. Si no existe, redirect back con error "El puesto no existe." Si $general_position->positions()->exists(), redirect back con error. Log; GeneralPosition::where("id",$general_position_id)->delete() (soft delete); redirect a admin_general_positions con message_info.

---

## VALIDACIONES

- **create / update:** name required. Mensaje: "El nombre es requerido".
- No se valida unicidad de nombre ni longitud máxima.

---

## VISTAS

- **admin.general_positions.list:** Título "Puestos Generales". Botón Crear. DataTable id dataTables-positions con ajax get_admin_general_positions. Columnas: N°, Nombre, acciones. Modal confirmación eliminar.
- **admin.general_positions.create:** Formulario name (required). action admin_general_positions_create.
- **admin.general_positions.edit:** Formulario general_position_id (hidden), name (required). action admin_general_positions_update.
- **admin.general_positions.view:** Solo lectura: id y nombre. Enlace Regresar a admin_general_positions.

---

## USO EN OTROS MÓDULOS

- **PositionsController (módulo Puestos):** getCreate y getEdit cargan GeneralPosition::all() ordenado por name para el select "Puesto general" / homologado. Los puestos por empresa tienen general_position_id; en create/edit el select es required. Búsqueda y ordenación por general_position name en listado/filtros.
- **Modelo Position:** general_position() belongsTo GeneralPosition.
- **Exports (Excel posiciones):** Muestran general_position->name cuando existe.

---

## MODELOS INVOLUCRADOS

- **GeneralPosition (App\Models\GeneralPosition):** tabla general_positions, SoftDeletes, fillable name. positions() hasMany Position.
- **Position:** general_position_id (nullable en migración; en vistas de Puestos el select es required), belongsTo GeneralPosition.
- **User, Company, Log:** Log asociado al usuario y a su company.

---

## MIGRACIONES

- **create_general_positions_table:** id, name, timestamps.
- **update_general_positions_table:** softDeletes() en general_positions.
- **update_positions_2_table:** general_position_id (nullable FK general_positions) en positions.

---

## PERMISOS LEGACY

- **view_general_positions:** getIndex, getList, getView.
- **create_general_positions:** getCreate, create.
- **edit_general_positions:** getEdit, update.
- **trash_general_positions:** Trash.

---

## CASOS BORDE

- **Trash con general_position_id inexistente:** Se construye `$message` con `$general_position->name` antes de comprobar `if (!$general_position)`; si no existe, $general_position es null y se produce error.
- **Update con general_position_id inexistente:** Tras la validación de name se usa $general_position sin comprobar si es null antes de $general_position->name y save(); si el id no existe, se produce error.

---

## AMBIGÜEDADES

- **Unicidad de nombre:** No se valida que el nombre sea único; se pueden crear varios puestos generales con el mismo nombre. No queda claro si es intencional o omisión.

---

## DEUDA TÉCNICA

- Orden en Trash: comprobar existencia de $general_position antes de usar $general_position->name en $message.
- Update: comprobar que $general_position exista después de obtenerlo por general_position_id y antes de usarlo; si no existe, redirect con error.
- getList obtiene User::getCurrent() pero no lo utiliza; se puede eliminar la variable si no hay uso previsto.

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Puestos Generales (catálogo de homologación), conviene contrastar: unicidad de nombre, validación de existencia en update, soft delete y bloqueo de borrado cuando hay puestos asignados. No se ha verificado implementación actual en tecben-core en este análisis.
