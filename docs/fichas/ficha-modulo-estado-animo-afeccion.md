# Ficha técnica: Módulo Afecciones de Estado de Ánimo (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Afecciones de Estado de Ánimo (EstadoAnimoAfeccionResource / mood_disorders)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite listar, crear, editar, ver y eliminar **afecciones de estado de ánimo**. Cada afección tiene solo un nombre; se asocian a registros de estado de ánimo (moods) de empleados mediante tabla pivot `mood_disorder_mood`. No se puede eliminar una afección que tenga moods asignados. Comparte permisos con el módulo “Características” de estado de ánimo (mood_characteristics): view_moods, create_moods, edit_moods, trash_moods. En el sidebar aparece bajo “Estado de ánimo” → “Afecciones”. Controlador: `MoodDisordersController`.

---

## ENTIDADES

### Tabla principal: `mood_disorders`

- **PK:** `id` (bigint unsigned).
- **Campos:** `name` (string). `timestamps`. (La migración inicial tenía `initial_list` enum; la migración update_mood_disorders_table lo eliminó.)
- **Relaciones (modelo MoodDisorder):** `moods()` belongsToMany Mood vía pivot `mood_disorder_mood`.

### Tabla pivot: `mood_disorder_mood`

- **PK:** id. **FK:** mood_disorder_id → mood_disorders (cascade), mood_id → moods (cascade). `timestamps`. Permite N:M entre afecciones y registros de estado de ánimo (un mood puede tener varias afecciones y una afección puede estar en muchos moods).

### Contexto: tabla `moods`

- Registros de estado de ánimo por empleado (high_employee_id, tipo, valor, etc.). Relación N:M con mood_characteristics y con mood_disorders.

---

## REGLAS DE NEGOCIO

- **RN-01:** Nombre de la afección obligatorio (validación `name` required).
- **RN-02:** No se puede eliminar una afección que tenga al menos un registro de estado de ánimo asociado (`$mood_disorder->moods()->exists()`). Mensaje: "No puede borrar una afección con registros asignados."
- **RN-03:** Eliminación física (delete en modelo; MoodDisorder no usa SoftDeletes).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/mood_disorders | GET | MoodDisordersController@getIndex | view_moods |
| admin/mood_disorders/get | POST | MoodDisordersController@getTable | view_moods |
| admin/mood_disorders/create | GET/POST | getCreate / create | create_moods |
| admin/mood_disorders/edit/{id} | GET | getEdit | edit_moods |
| admin/mood_disorders/edit | POST | update | edit_moods |
| admin/mood_disorders/view/{id} | GET | getView | view_moods |
| admin/mood_disorders/trash/{id} | GET | Trash | trash_moods |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getTable)

1. getIndex: MoodDisorder::orderBy('id','desc')->paginate(10). Vista `admin.moods.disorders.list` que incluye la tabla (partial). Parámetros de request para take, orderBy, sortDir, page (por defecto 10, 0, desc, 1).
2. getTable (POST): usado por AJAX para filtrar/ordenar/paginar. Parámetros: take, search, orderBy, sortDir, page. Búsqueda por id o name (LIKE). Orden por id o name. Paginación en servidor. Devuelve HTML de la tabla (view table) para reemplazar en la página.

### Crear (getCreate / create)

1. getCreate: vista create sin datos adicionales (solo formulario nombre).
2. create: Validator name required. Se crea MoodDisorder con name, save(). Log de auditoría (usuario y opcionalmente company del usuario). Redirect a listado con mensaje "Afección creada exitosamente".

### Ver (getView)

1. Buscar afección por id; si no existe redirect a listado con mensaje. Vista view con nombre e id (solo lectura).

### Editar (getEdit / update)

1. getEdit: afección por id; si no existe redirect a listado. Vista edit con name y hidden mood_disorder_id.
2. update: Validator name required. Se actualiza name y save(). Log y redirect a edit con mensaje de éxito.

### Eliminar (Trash)

1. Buscar afección por id; si no existe redirect back "La afección no existe." Si moods()->exists() redirect back "No puede borrar una afección con registros asignados." Log y $mood_disorder->delete() (eliminación física). Redirect a listado.

---

## VALIDACIONES

- **name:** required (mensaje: "El nombre es requerido").
- No hay validación de unicidad de nombre.

---

## VISTAS

- **admin.moods.disorders.list:** Título "Afecciones de estado de animo", botón Crear, include de table, modal confirmación eliminar. DataTable con paginación/búsqueda/orden vía AJAX a getTable (POST).
- **admin.moods.disorders.table:** Tabla con columnas N°, Nombre, acciones (Editar, Ver, Eliminar). Select "Mostrar N afecciones", input búsqueda, paginación.
- **admin.moods.disorders.create:** Formulario solo nombre (required). action admin_mood_disorders_create.
- **admin.moods.disorders.edit:** Formulario name, hidden mood_disorder_id. action admin_mood_disorders_update.
- **admin.moods.disorders.view:** Muestra id (#) y nombre. Solo lectura.

---

## USO EN OTROS MÓDULOS

- **API MoodsController:** getDisorders devuelve afecciones; al crear un mood (post moods/create) se puede enviar mood_disorders (array de ids) y se hace attach a la relación mood_disorders.
- **Mood (modelo):** mood_disorders() belongsToMany MoodDisorder con pivot mood_disorder_mood.
- **HighEmployee:** moods() hasMany Mood; los empleados registran estado de ánimo con características y afecciones asociadas.

---

## MODELOS INVOLUCRADOS

- **MoodDisorder** (App\Models\MoodDisorder): tabla mood_disorders, fillable name. Relación moods() belongsToMany Mood con pivot mood_disorder_mood. No usa SoftDeletes.
- **Mood** (App\Models\Mood): tabla moods, relaciones mood_characteristics(), mood_disorders() (N:M).
- **HighEmployee:** moods() hasMany Mood.

---

## MIGRACIONES

- **2024_05_03_123006_create_mood_disorders_table:** Crea mood_disorders (id, name string, initial_list enum nullable 'normal','bad','very_bad','well','very_well', timestamps).
- **2024_05_29_102906_update_mood_disorders_table:** Elimina la columna initial_list de mood_disorders.
- **2024_05_29_102617_create_mood_disorder_mood_table:** Crea pivot mood_disorder_mood (mood_disorder_id, mood_id, FKs cascade, timestamps).

---

## PERMISOS (Legacy)

- **view_moods:** listar, ver detalle, getTable (compartido con Características de estado de ánimo).
- **create_moods:** getCreate, create.
- **edit_moods:** getEdit, update.
- **trash_moods:** Trash.

Catálogo global; mismo permiso para "Características" y "Afecciones" de estado de ánimo. En el sidebar: Estado de ánimo → Características / Afecciones.

---

## CASOS BORDE

- **Eliminar con moods asociados:** Se impide con mensaje claro. Eliminación es física; no hay soft delete ni pantalla para restaurar.
- **Unicidad de nombre:** No se valida; pueden existir dos afecciones con el mismo nombre.

---

## AMBIGÜEDADES

- **Columna initial_list eliminada:** La tabla se creó con initial_list (igual que mood_characteristics); luego se eliminó en update_mood_disorders_table. El modelo solo tiene fillable name; no hay lista inicial en afecciones en el flujo actual.

---

## DEUDA TÉCNICA

- **Logs:** Se registra acción, usuario y opcionalmente company del usuario; no hay campo estructurado de recurso afectado (ej. mood_disorder_id).
- **Typo en comentario del controlador:** getTable tiene comentario "Ordenamiento por nombre o id de affeción" (affeción en lugar de afección).

---

## DIFERENCIAS CON TECBEN-CORE (si aplica)

- No verificado en este análisis. Al implementar: mantener RN-01 a RN-03, validación de no eliminar si hay moods asociados; valorar unicidad de nombre si el negocio lo exige.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
