# Ficha técnica: Módulo Industrias (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Industrias (IndustriaResource / industries)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite listar, crear, editar, ver y eliminar **industrias**. Cada industria puede tener subindustrias (SubIndustry) y empresas (Company) asociadas. No se puede eliminar una industria que tenga empresas o subindustrias asignadas. Eliminación por soft delete. Catálogo global usado en el alta/edición de empresas y en el módulo de subindustrias.

---

## ENTIDADES

### Tabla principal: `industries`

- **PK:** `id` (bigint unsigned).
- **Campos:** `name` (string), `timestamps`, `deleted_at` (soft deletes).
- **Relaciones (modelo Industry):** `companies()` hasMany Company; `sub_industries()` hasMany SubIndustry.

### Tablas relacionadas

- **sub_industries:** FK industry_id → industries. Una industria tiene N subindustrias.
- **companies:** FK industry_id (y sub_industry_id). Las empresas pertenecen a una industria y una subindustria.

---

## REGLAS DE NEGOCIO

- **RN-01:** Nombre de la industria obligatorio (validación `name` required).
- **RN-02:** No se puede eliminar una industria que tenga empresas asignadas (`$industry->companies()->exists()`) o subindustrias asignadas (`$industry->sub_industries()->exists()`). Mensaje: "No puede borrar una industria con registros asignados."
- **RN-03:** Eliminación es soft delete (modelo usa SoftDeletes); el controlador usa `Industry::where("id",$industry_id)->delete()`, que en Laravel marca deleted_at.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/industries | GET | IndustriesController@getIndex | view_industries |
| admin/industries/get | GET | IndustriesController@getList | view_industries |
| admin/industries/create | GET/POST | getCreate / create | create_industries |
| admin/industries/edit/{id} | GET | getEdit | edit_industries |
| admin/industries/edit | POST | update | edit_industries |
| admin/industries/view/{id} | GET | getView | view_industries |
| admin/industries/trash/{id} | GET | Trash | trash_industries |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

1. getIndex: devuelve vista `admin.industries.list` (DataTable que consume getList por AJAX).
2. getList: `Industry::all()` (con SoftDeletes excluye eliminados). Se serializa para DataTable: id, name, botones Editar/Ver/Eliminar. Sin paginación en servidor; paginación en el cliente (DataTable).

### Crear (getCreate / create)

1. getCreate: vista `admin.industries.create` sin datos adicionales (solo formulario nombre).
2. create: Validator name required. Se crea Industry con name, save(). Log de auditoría (usuario y opcionalmente company del usuario). Redirect a listado con mensaje de éxito.

### Ver (getView)

1. Buscar industria por id; si no existe redirect a listado con mensaje. Vista `admin.industries.view` con industria (nombre e id).

### Editar (getEdit / update)

1. getEdit: buscar industria por id; si no existe redirect a listado. Vista edit con name e hidden industry_id.
2. update: Validator name required. Se actualiza name y save(). Log y redirect a edit con mensaje de éxito.

### Eliminar (Trash)

1. Buscar industria por id; si no existe redirect back "La industria no existe." Si `companies()->exists() || sub_industries()->exists()` redirect back "No puede borrar una industria con registros asignados." Log y `Industry::where("id",$industry_id)->delete()` (soft delete). Redirect a listado con mensaje.

---

## VALIDACIONES

- **name:** required (mensaje: "El nombre es requerido").
- No hay validación de unicidad de nombre.

---

## VISTAS

- **admin.industries.list:** DataTable (id `dataTables-industries`), AJAX a get_admin_industries. Columnas: N°, Nombre, acciones (Editar, Ver, Eliminar). Modal confirmación eliminar. Botón Crear. Subtítulo: "Administra las industrias dadas de alta en Paco."
- **admin.industries.create:** Formulario con un campo: nombre (text, required). action admin_industries_create.
- **admin.industries.edit:** Formulario name, hidden industry_id. panel-heading "Industria". action admin_industries_update.
- **admin.industries.view:** Muestra id (#) y nombre. Solo lectura.

---

## USO EN OTROS MÓDULOS

- **CompaniesController (getCreate):** Requiere `Industry::has('sub_industries')->get()` para mostrar el formulario de alta de empresa; si no hay industrias con subindustrias, redirect con error. En create/update de empresa se asigna industry_id y sub_industry_id.
- **SubIndustriesController:** getCreate y getEdit cargan `Industry::all()` (ordenado por name); si no hay industrias no se puede crear/editar subindustria. Al crear subindustria se asocia a una industria con `$industry->sub_industries()->save($sub_industry)`.
- **industriesQuery (SubIndustriesController):** POST admin/sub_industries/query recibe industry id y devuelve subindustrias de esa industria para formularios de empresas.

---

## MODELOS INVOLUCRADOS

- **Industry** (App\Models\Industry): tabla industries, SoftDeletes, fillable name. Relaciones: companies() hasMany Company, sub_industries() hasMany SubIndustry.
- **SubIndustry:** industry_id FK; industry() belongsTo Industry.
- **Company:** industry_id (y sub_industry_id); industry() belongsTo Industry.

---

## MIGRACIONES

- **2019_09_13_184134_create_industries_table:** Crea industries (id, name string, timestamps).
- **2021_11_01_114151_update_industries_table:** Añade softDeletes() a industries.

---

## PERMISOS (Legacy)

- **view_industries:** listar, ver detalle, getList.
- **create_industries:** getCreate, create.
- **edit_industries:** getEdit, update.
- **trash_industries:** Trash.

Catálogo global; en sidebar bajo "Industrias". Según documentación de roles: view_industria, create_industria, update_industria, delete_industria (catálogos).

---

## CASOS BORDE

- **Eliminar con empresas o subindustrias:** Se impide con mensaje claro. Las empresas y subindustrias mantienen la FK a la industria; si en otros listados se filtra por industrias no eliminadas (Industry::all() o sin withTrashed), el comportamiento es coherente.
- **Listado:** getList usa Industry::all() que excluye soft-deleted. No hay pantalla en el CRUD para ver o restaurar industrias eliminadas.
- **Editar solo nombre:** No se valida que el nombre sea único; pueden existir dos industrias con el mismo nombre.

---

## AMBIGÜEDADES

- **Unicidad de nombre:** No se comprueba en create ni en update. Duplicados posibles si el negocio no lo impide por otro medio.

---

## DEUDA TÉCNICA

- **Paginación en servidor:** getList devuelve todos los registros; con muchas industrias la respuesta puede ser pesada. La paginación es solo en el cliente (DataTable).
- **Logs:** Se registra acción, usuario y opcionalmente company del usuario; no hay campo estructurado de recurso afectado (ej. industry_id).

---

## DIFERENCIAS CON TECBEN-CORE (si aplica)

- No verificado en este análisis. Al implementar: mantener RN-01 a RN-03, soft delete, bloqueo de eliminación si hay companies o sub_industries; valorar validación de unicidad de nombre si el negocio lo exige.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
