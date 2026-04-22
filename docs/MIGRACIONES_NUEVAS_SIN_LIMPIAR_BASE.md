# Migraciones: correr nuevas sin limpiar la base

## 1. ¿Tengo que limpiar la base para correr migraciones nuevas?

**No.** No hace falta limpiar la base para ejecutar migraciones nuevas.

- Laravel ejecuta **solo** las migraciones que **no** están registradas en la tabla `migrations`.
- Cada archivo de migración se ejecuta **una sola vez**; tras ejecutarse, se inserta su nombre en `migrations` y no se vuelve a correr.
- Al hacer `php artisan migrate`, solo se ejecutan las **pendientes** (las que no están en `migrations`). El resto de la base no se toca.

Por tanto, puedes seguir haciendo `php artisan migrate` cuando añadas o recibas nuevas migraciones; no se re-ejecutan migraciones ya aplicadas ni se sobrescribe la base por el hecho de correr migrate.

## 2. ¿Alguna migración puede sobrescribir lo que ya está aplicado?

**No**, en el estado actual del proyecto.

Se ha revisado que las migraciones que podían crear la **misma tabla** en más de un sitio (o en distinto orden según el entorno) estén **idempotentes** o sean **no-op**, de modo que no fallen con "Table already exists" ni sobrescriban tablas ya creadas:

| Migración | Comportamiento |
|-----------|----------------|
| `2026_02_26_099999_create_puestos_ubicaciones_regiones_centros_pago_table` | Crea **departamentos**, puestos, ubicaciones, regiones, centros_pago (idempotente). Debe correr antes de create_usuarios_table (FK departamento_id, puesto_id). La migración 2026_03_05 solo añade `departamento_general_id` si la tabla ya existe. |
| `2026_02_26_100000_create_tablas_faltantes` | **No-op:** `up()` y `down()` no hacen nada. Se mantiene el archivo para no romper el historial. Las tablas (bancos, departamentos, puestos, etc.) se crean en otras migraciones o ya existen. |
| `2026_03_04_171552_create_bancos_table` | **Idempotente:** solo hace `Schema::create('bancos', ...)` si `!Schema::hasTable('bancos')`. Si la tabla existe, añade columnas faltantes (`comision`, `deleted_at`) si no están. |
| `2026_03_05_004220_create_departamentos_table` | **Idempotente:** si la tabla `departamentos` existe, solo añade la columna `departamento_general_id` si falta y termina. Si no existe, la crea. |
| `2026_02_26_090000_create_tablas_rafa_locales` | **Idempotente:** cada tabla (empresas, productos, temas_voz, razones_sociales, areas) se crea solo si `!Schema::hasTable(...)`. |
| `2026_03_02_185249_create_empresas_reconocimientos_table` | **Idempotente:** si `Schema::hasTable('empresas_reconocimientos')`, hace `return` sin crear. |
| `2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing` | **Idempotente:** solo actúa si existe la tabla `bancos`; añade columnas solo si no existen (`comision`, `deleted_at`). |
| `2026_03_05_210000_add_puesto_and_departamento_to_usuarios_if_missing` | **Idempotente:** si la tabla `usuarios` existe pero le faltan `puesto_id` o `departamento_id`, las añade. Si ya las tiene (p. ej. create_usuarios reciente), no hace nada. |

El resto de migraciones hacen `Schema::create(...)` **sin** comprobar si la tabla existe; eso es correcto porque cada una crea **tablas con nombres únicos** y Laravel solo las ejecuta una vez. No hay otra lógica que “sobrescriba” migraciones ya interpuestas.

## 3. Resumen

- **Correr migraciones nuevas:** `php artisan migrate`. No es necesario limpiar la base.
- **No se re-ejecutan** migraciones ya registradas en `migrations`.
- Las migraciones que tocaban tablas que podían existir de antes (bancos, departamentos, tablas_rafa, tablas_faltantes, empresas_reconocimientos, bancos comision/soft deletes) están en modo idempotente o no-op, así que **no sobrescriben** ni provocan "table already exists" en entornos donde esas tablas ya existen.

## 4. Errores potenciales y mitigaciones

| Situación | Error posible | Mitigación actual |
|-----------|----------------|-------------------|
| Base sin `puestos` / `departamentos` al correr `create_usuarios_table` | "Failed to open the referenced table 'puestos'" o "'departamentos'" | La migración **099999** crea idempotentemente `departamentos`, `puestos`, `ubicaciones`, `regiones`, `centros_pago` **antes** de 100003 (usuarios). |
| Rafa (u otro) tenía `puestos`/`departamentos` por el antiguo `tablas_faltantes` | Ninguno: 099999 comprueba `hasTable()` y no vuelve a crear. | Comportamiento idempotente; no se generan más errores. |
| Orden de migraciones: `departamentos` se creaba en 2026_03_05, después de `usuarios` (100003) | En BD limpia, 100003 fallaba por FK a `departamentos`. | 099999 ahora crea `departamentos` antes; 2026_03_05 solo añade columna si la tabla ya existe. |
| Rollback parcial (solo 099999) | No se hace `down()` de `departamentos` en 099999 para no romper FK desde `usuarios`. | La migración 2026_03_05 se encarga de `dropIfExists('departamentos')` en su propio `down()`. |

Si aparecen nuevos errores de "referenced table 'X' doesn't exist" al hacer `migrate`, comprobar que la tabla `X` se cree en una migración con **fecha/número anterior** a la que la referencia (p. ej. en 099999 o en una migración previa a 100003).

## 5. Cambios de esquema cuando la tabla ya existe (regla de oro)

**Problema:** Si una migración ya se ejecutó en un entorno, Laravel **no la vuelve a ejecutar**. Si después añadimos columnas (o cambios) editando ese mismo archivo de migración, las bases que ya tenían la migración aplicada **nunca** verán esos cambios.

**Solución:**

1. **No editar migraciones ya aplicadas.** Una vez que un archivo de migración está en la tabla `migrations`, no modificar su contenido para añadir columnas o tablas; eso solo afecta a instalaciones nuevas.
2. **Crear una migración nueva** que aplique el cambio (añadir columna, índice, etc.).
3. **Hacerla idempotente** cuando la tabla pueda existir con o sin el cambio:
   - Comprobar `Schema::hasTable('nombre_tabla')` antes de tocar la tabla.
   - Comprobar `Schema::hasColumn('nombre_tabla', 'nombre_columna')` antes de añadir una columna.
   - Así la misma migración sirve para: (a) bases nuevas donde la tabla ya tiene la columna, (b) bases antiguas donde falta la columna.

**Ejemplo en el proyecto:** `2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing.php` y `2026_03_05_210000_add_puesto_and_departamento_to_usuarios_if_missing.php` (añaden columnas solo si no existen).

**Resumen:** Cambio de esquema = nueva migración + `hasColumn`/`hasTable` cuando quieras que funcione en todos los entornos sin fallar ni duplicar.

## 6. ¿Con qué otras migraciones podría ocurrir lo mismo?

El mismo problema (la migración ya corrió → Laravel la salta → las columnas nuevas nunca se aplican) puede darse en:

### 6.1 Tablas con “doble origen” de creación

Algunas tablas pueden existir con **dos esquemas distintos** según qué migración las creó primero:

| Tabla | Origen 1 | Origen 2 | Riesgo |
|-------|----------|----------|--------|
| **empresas** | `create_empresas_table` (175836) – esquema completo | `create_tablas_rafa_locales` (090000) – esquema mínimo (id, nombre, activa, timestamps, softDeletes) | Si en un entorno corrió antes 090000, `empresas` queda con pocas columnas. Cualquier columna nueva que se añada editando 175836 no llegará a esas bases. |
| **productos** | `create_productos_table` (011219) | `create_tablas_rafa_locales` (090000) – mínimo | Mismo patrón: tabla puede existir con esquema reducido. |
| **temas_voz** | Solo en 090000 (mínimo) | - | Si más adelante se crea un `create_temas_voz` con más columnas y alguien ya tiene la tabla por 090000, habría que añadir columnas en una migración nueva. |
| **razones_sociales** | `create_razonsocial` (181105) | 090000 – mínimo | Igual: doble origen, esquema mínimo posible. |
| **areas** | Solo en 090000 (mínimo) | - | Mismo riesgo que temas_voz. |

**Solución:** No editar las migraciones `create_*` ya aplicadas. Para nuevas columnas en estas tablas, crear **siempre** una migración nueva y usar `Schema::hasTable` + `Schema::hasColumn` para que sea idempotente.

### 6.2 Cualquier migración que solo hace `Schema::create()` (sin `hasTable`)

En **cualquier** migración que haga `Schema::create('nombre_tabla', ...)` **sin** comprobar si la tabla existe:

- Si esa migración **ya se ejecutó** en un entorno, Laravel **no la vuelve a ejecutar**.
- Si más adelante **editas ese archivo** para añadir columnas (o índices, etc.), los entornos que ya tenían la migración aplicada **nunca** verán esos cambios.

Afecta por ejemplo a:

- **empleados** (`create_empleados_table`, 100002)
- **create_historiales_tables** (100011)
- **create_oauth_tables**, **create_chat_tables**, **create_financiero_tables**, etc.

**Solución:** Para cualquier cambio de esquema en esas tablas, usar **una migración nueva** que haga `Schema::table(...)` y, si la tabla puede tener esquemas mezclados, comprobar con `hasColumn` antes de añadir.

### 6.3 Resumen práctico

| Situación | Acción |
|-----------|--------|
| Añadir columna a una tabla que puede existir con esquema antiguo (usuarios, bancos, departamentos, empresas, productos, etc.) | Nueva migración + `hasTable` / `hasColumn`; no editar la migración `create_*` original. |
| Añadir columna a una tabla que solo se crea en una migración `create_*` (empleados, historiales, etc.) | Nueva migración con `Schema::table`; opcionalmente `hasColumn` si quieres que sea idempotente en todos los entornos. |
| Crear una tabla que podría existir ya (p. ej. por tablas_rafa o tablas_faltantes) | Migración idempotente con `if (! Schema::hasTable(...)) { Schema::create(...) }` (como 099999 o create_bancos). |
