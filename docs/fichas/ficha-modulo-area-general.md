# Ficha técnica: Módulo Área General (Legacy Paco) — AreaGeneralResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **áreas generales** (catálogo de homologación). Solo describe lo que existe en el código.

---

## MÓDULO: Área General (GeneralAreasController / equivalente AreaGeneralResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona el **catálogo de áreas generales** para homologación: listado, alta, edición, vista detalle y baja (trash). Cada área general tiene solo **nombre**. No hay empresa ni alcance por usuario: todos los que tienen permiso ven el mismo listado (GeneralArea::all()). Las **áreas** (por empresa) se asocian a un general_area_id en el módulo Áreas. Controlador: `App\Http\Controllers\Admin\GeneralAreasController`. Rutas bajo `admin/general_areas/*`; permisos: `view_general_areas`, `create_general_areas`, `edit_general_areas`, `trash_general_areas`.

---

## ENTIDADES

### Tabla: `general_areas`

- **PK:** id (bigint). **SoftDeletes** (deleted_at).
- **Campos:** name (string), timestamps, deleted_at.
- **Relaciones (modelo GeneralArea):** areas() hasMany Area. Las áreas (por empresa) tienen general_area_id FK a esta tabla; al eliminar un área general se comprueba si hay áreas asignadas antes de permitir el borrado.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/general_areas | GET | GeneralAreasController@getIndex | view_general_areas |
| admin/general_areas/get | GET | getList | view_general_areas |
| admin/general_areas/create | GET | getCreate | create_general_areas |
| admin/general_areas/create | POST | create | create_general_areas |
| admin/general_areas/edit/{general_area_id} | GET | getEdit | edit_general_areas |
| admin/general_areas/edit | POST | update | edit_general_areas |
| admin/general_areas/trash/{general_area_id} | GET | Trash | trash_general_areas |
| admin/general_areas/view/{general_area_id} | GET | getView | view_general_areas |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** "Áreas Generales" (admin_general_areas) si el rol tiene al menos uno de: edit_general_areas, view_general_areas, trash_general_areas, create_general_areas.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance:** No hay filtro por empresa ni por high_employee_filters. Cualquier usuario con permiso ve todas las áreas generales (GeneralArea::all()). Solo un listado global.
- **RN-02:** **Crear / Editar:** name obligatorio. No hay más campos. No hay unicidad explícita en el controlador (se pueden crear varias con el mismo nombre).
- **RN-03:** **Trash:** No se puede eliminar si existe al menos un registro en la relación areas() (áreas por empresa que referencian esta área general). Mensaje: "No puede borrar un area con registros asignados." Eliminación con soft delete (modelo GeneralArea usa SoftDeletes).
- **RN-04:** **Logs:** En create, update y Trash se crea un registro en tabla logs (acción con "area general para homologaciones" y nombre del usuario/catálogo) y se asocia al usuario y a su company si tiene company_id.
- **RN-05:** **Uso en el sistema:** Las áreas (módulo Áreas) tienen general_area_id opcional; en create/edit de Áreas (solo admin) se muestra un select de GeneralArea. Este módulo mantiene el catálogo que se usa allí.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

- **getIndex:** Devuelve vista `admin.general_areas.list` (DataTable que consume getList por AJAX).
- **getList:** GeneralArea::all(). Por cada registro: id, name, acciones (Editar, Ver, Eliminar). Respuesta JSON `{ data: [...] }`.

### Crear (getCreate / create)

- **getCreate:** Vista create sin datos adicionales (solo formulario name).
- **create:** Validar name required. Crear GeneralArea con name; log; redirect a admin_general_areas con mensaje "Area general creado exitosamente".

### Ver (getView)

- Buscar GeneralArea por id; si no existe, redirect a admin_general_areas con message_info. Vista view solo lectura (id y nombre).

### Editar (getEdit / update)

- **getEdit:** GeneralArea por id; si no existe, redirect a admin_general_areas con message_info. Vista edit con general_area_id y name.
- **update:** Validar name required. Se obtiene $general_area = GeneralArea::where("id",$general_area_id)->first() pero **no se comprueba si es null** antes de asignar $general_area->name; si general_area_id no existe, se produce error. save(); log; redirect a admin_general_areas_edit con mensaje éxito.

### Trash (eliminar)

- Buscar GeneralArea por id. En el código se asigna `$message = "Se ha eliminado el area general para homologaciones: ".$general_area->name` **antes** del `if (!$general_area)` → si el id no existe, $general_area es null y se produce error. Si no existe, redirect back con error "El area no existe." Si $general_area->areas()->exists(), redirect back con error. Log; GeneralArea::where("id",$general_area_id)->delete() (soft delete); redirect a admin_general_areas con message_info.

---

## VALIDACIONES

- **create / update:** name required. Mensaje: "El nombre es requerido".
- No se valida unicidad de nombre ni longitud máxima.

---

## VISTAS

- **admin.general_areas.list:** Título "Áreas Generales". Botón Crear. DataTable id dataTables-areas con ajax get_admin_general_areas. Columnas: N°, Nombre, acciones. Modal confirmación eliminar.
- **admin.general_areas.create:** Formulario name (required). action admin_general_areas_create.
- **admin.general_areas.edit:** Formulario general_area_id (hidden), name (required). action admin_general_areas_update.
- **admin.general_areas.view:** Solo lectura: id y nombre. Enlace Regresar a admin_general_areas.

---

## USO EN OTROS MÓDULOS

- **AreasController (módulo Áreas):** getCreate y getEdit cargan GeneralArea::all() ordenado por name para el select "Área general" / "Área homologada". Las áreas por empresa tienen general_area_id opcional; solo los usuarios admin ven y envían ese campo.
- **Modelo Area:** general_area() belongsTo GeneralArea.

---

## MODELOS INVOLUCRADOS

- **GeneralArea (App\Models\GeneralArea):** tabla general_areas, SoftDeletes, fillable name. areas() hasMany Area.
- **Area:** general_area_id (nullable), belongsTo GeneralArea.
- **User, Company, Log:** Log asociado al usuario y a su company.

---

## MIGRACIONES

- **create_general_areas_table:** id, name, timestamps.
- **update_general_areas_table:** softDeletes() en general_areas.

---

## PERMISOS LEGACY

- **view_general_areas:** getIndex, getList, getView.
- **create_general_areas:** getCreate, create.
- **edit_general_areas:** getEdit, update.
- **trash_general_areas:** Trash.

---

## CASOS BORDE

- **Trash con general_area_id inexistente:** Se construye `$message` con `$general_area->name` antes de comprobar `if (!$general_area)`; si no existe, $general_area es null y se produce error.
- **Update con general_area_id inexistente:** Tras la validación de name se hace $general_area = GeneralArea::where(...)->first() pero no se comprueba si $general_area es null antes de usar $general_area->name y $general_area->save(); si el id no existe, se produce error.

---

## AMBIGÜEDADES

- **Unicidad de nombre:** No se valida que el nombre sea único; se pueden crear varias áreas generales con el mismo nombre. No queda claro si es intencional (permite duplicados para homologación) o omisión.

---

## DEUDA TÉCNICA

- Orden en Trash: comprobar existencia de $general_area antes de usar $general_area->name en $message.
- Update: comprobar que $general_area exista después de obtenerlo por general_area_id y antes de usarlo; si no existe, redirect con error en lugar de permitir el fallo.

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Áreas Generales (catálogo de homologación), conviene contrastar: unicidad de nombre, validación de existencia en update, soft delete y bloqueo de borrado cuando hay áreas asignadas. No se ha verificado implementación actual en tecben-core en este análisis.
