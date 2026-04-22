# Ficha técnica: Módulo Características de Estado de Ánimo (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Características de Estado de Ánimo (EstadoAnimoCaracteristicaResource / mood_characteristics)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite listar, crear, editar, ver y eliminar **características de estado de ánimo**. Cada característica tiene nombre y opcionalmente una **lista inicial** (normal, mal, muy mal, bien, muy bien) que agrupa la opción en la app. Las características se asocian a registros de estado de ánimo (moods) de empleados mediante tabla pivot; no se puede eliminar una característica que tenga moods asignados. Comparte permisos con el módulo “Afecciones” (mood_disorders): view_moods, create_moods, edit_moods, trash_moods.

---

## ENTIDADES

### Tabla principal: `mood_characteristics`

- **PK:** `id` (bigint unsigned).
- **Campos:** `name` (string), `initial_list` (enum nullable: 'normal','bad','very_bad','well','very_well'). `timestamps`.
- **Relaciones (modelo MoodCharacteristic):** `moods()` belongsToMany Mood vía pivot `mood_characteristic_mood`.

### Tabla pivot: `mood_characteristic_mood`

- **PK:** id. **FK:** mood_characteristic_id → mood_characteristics (cascade), mood_id → moods (cascade). `timestamps`. Permite N:M entre características y registros de estado de ánimo (un mood puede tener varias características y una característica puede estar en muchos moods).

### Contexto: tabla `moods`

- Registros de estado de ánimo por empleado (high_employee_id, tipo, valor, etc.). Relación N:M con mood_characteristics y con mood_disorders (afecciones).

---

## REGLAS DE NEGOCIO

- **RN-01:** Nombre de la característica obligatorio (validación `name` required).
- **RN-02:** Lista inicial opcional; valores permitidos: normal, bad, very_bad, well, very_well. Se muestran en UI como Normal, Mal, Muy mal, Bien, Muy bien. Si no se asigna, en listado/vista se muestra "SIN ASIGNAR".
- **RN-03:** No se puede eliminar una característica que tenga al menos un registro de estado de ánimo asociado (`$mood_characteristic->moods()->exists()`). Mensaje: "No puede borrar una característica con registros asignados."
- **RN-04:** Eliminación física (delete en modelo; no se usa SoftDeletes en MoodCharacteristic).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/mood_characteristics | GET | MoodCharacteristicsController@getIndex | view_moods |
| admin/mood_characteristics/get | POST | MoodCharacteristicsController@getTable | view_moods |
| admin/mood_characteristics/create | GET/POST | getCreate / create | create_moods |
| admin/mood_characteristics/edit/{id} | GET | getEdit | edit_moods |
| admin/mood_characteristics/edit | POST | update | edit_moods |
| admin/mood_characteristics/view/{id} | GET | getView | view_moods |
| admin/mood_characteristics/trash/{id} | GET | Trash | trash_moods |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getTable)

1. getIndex: query MoodCharacteristic::orderBy('id','desc') con select que añade `list_value` (CASE para traducir initial_list a texto: Mal, Muy mal, Bien, Muy bien, Normal, SIN ASIGNAR). Paginación 10 por defecto. Vista `admin.moods.characteristics.list` que incluye la tabla (partial).
2. getTable (POST): usado por AJAX para filtrar/ordenar/paginar. Parámetros: take, search, orderBy, sortDir, page. Búsqueda por id, name o lista inicial (mapeo texto → valor: Normal→normal, Mal→bad, etc.). Orden por id, name o list_value (vía list_value en el CASE). Devuelve HTML de la tabla (view table) para reemplazar en la página.

### Crear (getCreate / create)

1. getCreate: lista fija `$lists = ['normal'=>'Normal','bad'=>'Mal','very_bad'=>'Muy mal','well'=>'Bien','very_well'=>'Muy bien']`. Vista create con nombre (text) e initial_list (select múltiple con data-max-options="1").
2. create: Validator name required. Se crea MoodCharacteristic con name y, si viene, initial_list. Log de auditoría (usuario y opcionalmente company). Redirect a listado con mensaje de éxito.

### Ver (getView)

1. Buscar característica por id; si no existe redirect a listado con mensaje. Se pasa $lists para mostrar texto de initial_list. Vista view con nombre y lista inicial (o SIN ASIGNAR).

### Editar (getEdit / update)

1. getEdit: misma $lists; característica por id; si no existe redirect. Vista edit con name e initial_list (select múltiple, una opción).
2. update: Validator name required. Se actualiza name e initial_list (isset($request->initial_list) ? $request->initial_list : null). Log y redirect a edit con mensaje.

### Eliminar (Trash)

1. Buscar característica por id; si no existe redirect back "La característica no existe." Si moods()->exists() redirect back "No puede borrar una característica con registros asignados." Log y $mood_characteristic->delete(). Redirect a listado.

---

## VALIDACIONES

- **name:** required (mensaje: "El nombre es requerido").
- **initial_list:** no validado en backend; en BD es enum nullable. La vista usa select con opciones normal, bad, very_bad, well, very_well.

---

## VISTAS

- **admin.moods.characteristics.list:** Título "Características de estado de animo", botón Crear, include de table, modal confirmación eliminar. DataTable con paginación/búsqueda/orden vía AJAX a getTable.
- **admin.moods.characteristics.table:** Tabla con columnas N°, Nombre, Lista inicial (list_value), acciones (Editar, Ver, Eliminar). Select "Mostrar N características", input búsqueda, paginación.
- **admin.moods.characteristics.create:** Formulario nombre (required) e initial_list (select múltiple, max 1 opción). "Seleccione si desea adjuntar a una lista inicial."
- **admin.moods.characteristics.edit:** Igual que create; hidden mood_characteristic_id; valor inicial de initial_list desde $mood_characteristic->initial_list. (En el nav-tab dice "Área General" por error de copia.)
- **admin.moods.characteristics.view:** Muestra nombre y lista inicial (o SIN ASIGNAR usando $lists).

---

## USO EN OTROS MÓDULOS

- **API MoodsController:** getCharacteristics(get_mood_characteristics) filtra por initial_list y búsqueda por nombre; getOtherCharacteristics con paginación. Al crear un mood (post moods/create) se valida mood_characteristics (array de ids) y se hace attach a la relación mood_characteristics.
- **Mood (modelo):** mood_characteristics() belongsToMany MoodCharacteristic; getMoodCharacteristicAttribute devuelve la primera característica asociada.
- **HighEmployee:** moods() hasMany Mood; los empleados registran estado de ánimo con características y afecciones asociadas.

---

## MODELOS INVOLUCRADOS

- **MoodCharacteristic** (App\Models\MoodCharacteristic): tabla mood_characteristics, fillable name, initial_list. Relación moods() belongsToMany Mood con pivot mood_characteristic_mood.
- **Mood** (App\Models\Mood): tabla moods, relaciones mood_characteristics(), mood_disorders() (N:M).
- **HighEmployee:** moods() hasMany Mood.

---

## MIGRACIONES

- **2024_05_03_121930_create_mood_characteristics_table:** Crea mood_characteristics (id, name string, initial_list enum nullable 'normal','bad','very_bad','well','very_well', timestamps).
- **2024_05_28_092641_create_mood_characteristic_mood_table:** Crea pivot mood_characteristic_mood (mood_characteristic_id, mood_id, FKs cascade, timestamps).

---

## PERMISOS (Legacy)

- **view_moods:** listar, ver detalle, getTable (características y también afecciones / mood_disorders).
- **create_moods:** getCreate, create.
- **edit_moods:** getEdit, update.
- **trash_moods:** Trash.

Catálogo global; mismo permiso para "Características" y "Afecciones" de estado de ánimo. En el sidebar: Estado de ánimo → Características / Afecciones.

---

## CASOS BORDE

- **initial_list como array:** El formulario create/edit usa select múltiple (data-max-options="1"). Si se envía una sola opción, en algunos clientes el request puede llegar como initial_list[] = "normal". En create se asigna `$request->initial_list` directamente; si llega como array, la asignación a una columna enum puede guardar "Array" o el primer elemento según driver/cast. Conviene en backend normalizar a string (ej. is_array ? $request->initial_list[0] : $request->initial_list).
- **Editar y quitar lista inicial:** En update se usa isset($request->initial_list) ? $request->initial_list : null; si el usuario desmarca la opción, puede no enviarse la key y se guarda null. Coherente.
- **Búsqueda por "SIN ASIGNAR":** En getTable, si el usuario busca "SIN ASIGNAR", $lists["SIN ASIGNAR"] no existe (las keys son Normal, Mal, etc.); search_value queda null y el where usa LIKE sobre IFNULL(initial_list,'SIN ASIGNAR'), que puede devolver filas con initial_list null.

---

## AMBIGÜEDADES

- **Nav-tab en edit:** La pestaña dice "Área General" en lugar de "Característica"; parece copia de otra vista.
- **Orden por lista inicial:** Se ordena por list_value (texto traducido); el orden alfabético en español puede no coincidir con un orden lógico de intensidad (mal → muy mal → normal → bien → muy bien).

---

## DEUDA TÉCNICA

- **Normalizar initial_list en create/update:** Si el front envía initial_list como array (select multiple), el controlador debería tomar el primer elemento antes de asignar al modelo.
- **Lista de list_value duplicada:** El CASE que traduce initial_list a texto está en getIndex y getTable; podría extraerse a un atributo del modelo o a un enum/helper.

---

## DIFERENCIAS CON TECBEN-CORE (si aplica)

- No verificado en este análisis. Al implementar: mantener RN-01 a RN-04, lista inicial con los mismos valores, validación de no eliminar si hay moods asociados; corregir posible envío de initial_list como array.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
