# Ficha técnica: Módulo Subindustrias (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Subindustrias (sub_industries / SubindustriaResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite listar, crear, editar, ver y eliminar subindustrias. Cada subindustria pertenece a una industria (Industry). Las empresas (Company) tienen FK `sub_industry_id`; al eliminar una subindustria se valida que no tenga empresas asignadas. Endpoint auxiliar `industriesQuery` devuelve subindustrias por industria (usado en formularios de empresas).

---

## ENTIDADES

### Tabla principal: `sub_industries`

- **PK:** `id` (bigint unsigned).
- **Campos:** `name` (string), `industry_id` (unsignedBigInteger nullable, FK a industries cascade). `timestamps`, `deleted_at` (soft deletes).
- **Relaciones (modelo SubIndustry):** `industry()` belongsTo Industry; `companies()` hasMany Company.

### Tabla relacionada: `industries`

- **PK:** id. **Campos:** name. SoftDeletes. Relación `sub_industries()` hasMany SubIndustry.

### Uso en empresas

- **companies.sub_industry_id** → FK a sub_industries. Una empresa pertenece a una subindustria (y por tanto a una industria).

---

## REGLAS DE NEGOCIO

- **RN-01:** Para acceder a crear o editar subindustrias debe existir al menos una industria; si no hay industrias se redirige con error "No hay industrias creadas".
- **RN-02:** Nombre de subindustria obligatorio (validación `name` required).
- **RN-03:** Toda subindustria debe pertenecer a una industria (en create se asocia con `$industry->sub_industries()->save($sub_industry)`; en update se asigna `industry_id`). El formulario exige industria (required en HTML); el controlador en create no valida `industry` en el Validator (solo name) — si no se envía industry, `Industry::find($data['industry'])` puede ser null y provocar error al llamar `->save($sub_industry)`.
- **RN-04:** No se puede eliminar una subindustria que tenga empresas asignadas (`$sub_industry->companies()->exists()`). Mensaje: "No puede borrar una subindustria con registros asignados."
- **RN-05:** Eliminación es soft delete (modelo usa SoftDeletes); el controlador usa `SubIndustry::where("id",...)->delete()` que en Laravel con SoftDeletes marca deleted_at.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/sub_industries | GET | SubIndustriesController@getIndex | view_sub_industries |
| admin/sub_industries/get | GET | SubIndustriesController@getList | view_sub_industries |
| admin/sub_industries/create | GET/POST | getCreate / create | create_sub_industries |
| admin/sub_industries/edit/{id} | GET | getEdit | edit_sub_industries |
| admin/sub_industries/edit | POST | update | edit_sub_industries |
| admin/sub_industries/view/{id} | GET | getView | view_sub_industries |
| admin/sub_industries/trash/{id} | GET | Trash | trash_sub_industries |
| admin/sub_industries/query | POST | industriesQuery | create_companies O edit_companies |

Middleware: `logged`, `2fa`, `Permissions` (and/or según ruta).

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

1. getIndex: vista `admin.sub_industries.list` (DataTable que consume getList por AJAX).
2. getList: `SubIndustry::all()` (incluye soft deleted si no se usa scope; en Laravel all() no excluye por defecto los soft deleted — el modelo tiene SoftDeletes, así que all() sí excluye). Se serializa para DataTable: id, name, industria (nombre de industry), botones Editar/Ver/Eliminar.

### Crear (getCreate / create)

1. getCreate: `Industry::all()`; si count < 1 redirect back con "No hay industrias creadas". Industrias ordenadas por nombre (sortBy('name')). Vista `admin.sub_industries.create` con nombre e industria (select).
2. create: Validator solo `name` required. Se crea SubIndustry con name, save(); luego `Industry::find($data['industry'])` y `$industry->sub_industries()->save($sub_industry)` (asigna industry_id). Log de auditoría (usuario y opcionalmente company del usuario). Redirect a listado con mensaje de éxito.

### Ver (getView)

1. Buscar subindustria por id; si no existe redirect a listado con mensaje. Vista `admin.sub_industries.view` con nombre e industria (lectura).

### Editar (getEdit / update)

1. getEdit: Industrias todas; si no hay industrias redirect back con error. Subindustria por id; si no existe redirect a listado. Industrias ordenadas por nombre. Vista `admin.sub_industries.edit` con name e industry (select).
2. update: Validator name required. Se actualiza name e industry_id y save(). Log y redirect a edit con mensaje.

### Eliminar (Trash)

1. Buscar subindustria por id; si no existe redirect back "La subindustria no existe." Si `$sub_industry->companies()->exists()` redirect back "No puede borrar una subindustria con registros asignados." Log y `SubIndustry::where("id",...)->delete()` (soft delete). Redirect a listado con mensaje.

### industriesQuery (flujo secundario)

- POST admin/sub_industries/query con `id` (industry_id). Devuelve JSON: `sub_industries` (array de {id, name}), `action`: "get_sub_industries", y si viene `sub_industry` en request, `selected`. Usado desde formularios de empresas para cargar subindustrias al elegir industria.

---

## VALIDACIONES

- **name:** required (mensaje: "El nombre es requerido").
- **industry:** no validado en create/update en el Validator; el formulario lo marca required en HTML. Si se omite en create, Industry::find(null) y ->save() pueden fallar.

---

## VISTAS

- **admin.sub_industries.list:** DataTable (id `dataTables-sub_industries`), AJAX a get_admin_sub_industries. Columnas: N°, Nombre, Industria, acciones. Modal confirmación eliminar. Botón Crear.
- **admin.sub_industries.create:** Formulario nombre (text) e industry (select con industries ordenadas). required en ambos.
- **admin.sub_industries.edit:** Formulario name e industry (select); hidden sub_industry_id. action admin_sub_industries_update.
- **admin.sub_industries.view:** Muestra nombre e industria (sub_industry->industry->name).

---

## SERVICIOS/ENDPOINTS INVOLUCRADOS

- **getList:** Devuelve JSON para DataTable (lista de subindustrias con industria).
- **industriesQuery:** POST; recibe industry id; devuelve subindustrias de esa industria en JSON (usado por módulo Empresas).

---

## MODELOS INVOLUCRADOS

- **SubIndustry** (App\Models\SubIndustry): tabla `sub_industries`, SoftDeletes, fillable name, industry_id. Relaciones: industry() belongsTo Industry, companies() hasMany Company.
- **Industry** (App\Models\Industry): tabla industries, SoftDeletes, fillable name. Relaciones: sub_industries() hasMany SubIndustry, companies() hasMany Company.
- **Company:** sub_industry_id FK; relación sub_industry() belongsTo SubIndustry.

---

## MIGRACIONES

- **2019_09_13_184708_create_sub_industries_table:** Crea sub_industries (id, name, timestamps). Sin industry_id.
- **2019_10_02_173800_update_sub_industries_table:** Añade industry_id nullable, FK a industries cascade.
- **2021_11_01_120016_update_sub_industries_2_table:** Añade softDeletes() a sub_industries.

---

## PERMISOS (Legacy)

- **view_sub_industries:** listar, ver detalle, getList.
- **create_sub_industries:** getCreate, create.
- **edit_sub_industries:** getEdit, update.
- **trash_sub_industries:** Trash.
- **industriesQuery:** create_companies O edit_companies (permisos_or).

Catálogo global; disponible para roles con permisos de catálogos.

---

## CASOS BORDE

- **Crear sin industrias:** Redirect con error; no se muestra formulario.
- **Crear con industry no enviado o inválido:** No hay validación en backend; Industry::find($data['industry']) puede ser null y causar error al llamar ->save($sub_industry).
- **Editar cambiando industria:** Permitido; solo se actualiza industry_id.
- **Eliminar con empresas asignadas:** Se impide con mensaje claro. Eliminación es soft delete; empresas con esa sub_industry_id seguirían referenciando el id (FK); si en otro código se filtra por subindustrias no eliminadas, coherente.

---

## AMBIGÜEDADES

- **Listado y soft deletes:** getList usa SubIndustry::all() que con SoftDeletes excluye eliminados. No hay opción en el CRUD para ver o restaurar subindustrias eliminadas.
- **Unicidad nombre:** No se valida unicidad de nombre (global o por industria). Pueden existir dos subindustrias con el mismo nombre en la misma industria.

---

## DEUDA TÉCNICA

- **Validación de industry en create:** El controlador no incluye 'industry' => 'required|exists:industries,id' en el Validator; depende del required del HTML. Conviene validar en backend.
- **Vista create.blade.php:** Hay un `}` suelto en el @foreach de industries (línea ~61); podría ser error de sintaxis Blade según versión.
- **industriesQuery sin permiso de vista subindustrias:** La ruta query usa permisos create_companies o edit_companies; no comprueba view_sub_industries; es coherente para uso en formulario de empresas.

---

## DIFERENCIAS CON TECBEN-CORE (si aplica)

- No verificado en este análisis. Al implementar en tecben-core: mantener RN-01 a RN-05, validar industry en create/update, considerar unicidad nombre por industria si negocio lo exige.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
