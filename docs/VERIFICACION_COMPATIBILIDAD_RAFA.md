# Verificación de compatibilidad para Rafa

**Objetivo:** Garantizar que, al hacer merge de la rama de Rafa (catálogos: empresas, productos, industrias, etc.) con nuestra rama (Fase 1, Fase 2, normalizaciones), ejecutar `php artisan migrate` en su entorno sea seguro: orden correcto, sin duplicar tablas/columnas y sin saltarse migraciones.

**Importante:** Rafa debe usar **solo** `php artisan migrate` (sin `--fresh` ni `migrate:fresh`) para **conservar sus datos**. El comando normal solo ejecuta las migraciones pendientes y añade tablas o columnas nuevas; **no borra ni trunca** tablas existentes. Toda la verificación y las migraciones idempotentes de este documento están pensadas para ese uso.

---

## 1. Resumen

- **Sí:** El conjunto de migraciones es seguro para ejecutar en el entorno de Rafa tras el merge.
- **Migraciones que modifican tablas que Rafa puede tener ya:** `users`, `spatie_roles`. Ambas están hechas **idempotentes** (comprueban tabla/columna antes de crear o añadir).
- **Tabla `usuarios`:** Es nuestra; las migraciones que la modifican (`add_report_and_newsletter_fields`, `add_puesto_and_departamento_to_usuarios_if_missing`) ya son idempotentes.
- **Tablas de catálogo de Rafa:** `empresas`, `productos`, `industrias`, `sub_industrias`, `temas_voz`, `razones_sociales`, `areas`. La migración `2026_02_26_090000_create_tablas_rafa_locales` solo crea cada tabla **si no existe** (`Schema::hasTable`), por lo que no hay conflicto si Rafa ya las tiene.

---

## 2. Migraciones que modifican tablas de Rafa

### Tabla: `users`

| Migración | Acción | Idempotente | Riesgo |
|-----------|--------|-------------|--------|
| `2026_02_25_120000_add_workos_fields_to_users_table` | Añade `workos_id`, `avatar`; hace `password` nullable | Sí: comprueba `hasTable('users')` y `hasColumn` para cada columna | Bajo |

- Si la tabla `users` no existe, la migración no hace nada (return temprano).
- Si `workos_id` o `avatar` ya existen, no se vuelven a añadir.
- El `ALTER TABLE ... MODIFY password` solo se ejecuta si existe la columna `password` y el driver es MySQL.

### Tabla: `spatie_roles`

| Migración | Acción | Idempotente | Riesgo |
|-----------|--------|-------------|--------|
| `2026_03_03_171432_add_company_id_to_spatie_roles_table` | Añade `company_id` (FK a `empresas`) | Sí: comprueba `hasTable('spatie_roles')` y `hasColumn('spatie_roles', 'company_id')` | Bajo |
| `2026_03_03_202139_add_display_name_and_description_to_spatie_roles_table` | Añade `display_name`, `description` | Sí: comprueba tabla y cada columna antes de añadir | Bajo |

- Si Rafa ya tiene `spatie_roles` (p. ej. por Spatie Permission) y ya tiene alguna de estas columnas, no se duplican.

### Tabla: `empresas` (y otras de catálogo)

- **No hay migraciones que añadan columnas a `empresas`** en nuestro conjunto (salvo las que crean tablas nuevas con FK a `empresas`).
- La migración `2026_02_26_090000_create_tablas_rafa_locales` **crea** `empresas`, `productos`, `temas_voz`, `razones_sociales`, `areas` **solo si no existen** (`if (! Schema::hasTable(...))`). Si Rafa ya las tiene, no se intenta crear de nuevo.

---

## 3. Orden de ejecución verificado

- Las migraciones se ejecutan en **orden lexicográfico del nombre del archivo**.
- **Laravel por defecto:** `0001_01_01_000000_create_users_table` (y cache, jobs) van primero.
- **Rafa (feb 24–26):** `create_industrias_table`, `create_sub_industrias_table`, `create_productos_table`, `create_empresas_table`, etc. tienen fechas anteriores a la mayoría de las nuestras; si Rafa tiene migraciones con las mismas fechas/nombres, Laravel las ejecutará una sola vez (registradas por nombre en `migrations`).
- **Nuestras migraciones (feb 26 en adelante):** Se ejecutan después; todas las tablas referenciadas por FKs (`empresas`, `usuarios`, `empleados`, `bancos`, etc.) se crean en migraciones previas o en la misma rama en orden correcto.
- **Bancos:** La migración `2026_02_26_099998_create_bancos_table` va **antes** que `2026_02_26_100007_create_financiero_tables` (que tiene FK a `bancos`). Es idempotente: si `bancos` ya existe, no hace nada.
- **Dependencia `spatie_roles` → `empresas`:** `add_company_id_to_spatie_roles_table` (2026_03_03_171432) se ejecuta después de `create_empresas_table` (2026_02_25_175836) y de `create_tablas_rafa_locales` (2026_02_26_090000), por lo que `empresas` existirá cuando se añada la FK.

No hay riesgo de FKs rotas por orden si ambas ramas se fusionan y se ejecuta `migrate` una vez.

---

## 4. Riesgo de “saltarse” migraciones

- Laravel identifica cada migración por su **nombre de archivo** (ej. `2026_02_25_120000_add_workos_fields_to_users_table`). Si la misma migración existe en ambas ramas con el **mismo nombre**, solo se ejecutará una vez (la que esté en la tabla `migrations`).
- Si en la rama de Rafa hay una migración con **nombre distinto** que ya añadió `workos_id`/`avatar` a `users`, al ejecutar la nuestra no se duplicarán columnas porque la migración nuestra ahora es idempotente.
- **Recomendación:** Tras el merge, revisar `php artisan migrate:status` y que no queden migraciones pendientes con error; en caso de conflicto de nombres (misma fecha/hora en ambas ramas), resolver el conflicto de merge manteniendo una sola versión del archivo y, si hace falta, asegurar que esa versión sea idempotente.

---

## 5. Instrucciones para Rafa

### Paso 1: Preparación

```bash
# 1. Backup de la BD actual (ajustar host, user, database)
mysqldump -h [host] -u [user] -p [database] > backup_rafa_pre_merge.sql

# 2. Actualizar su rama con main (o la rama donde están nuestras migraciones)
git checkout su-rama
git pull origin main   # o la rama acordada

# 3. Ver estado de migraciones antes del merge
php artisan migrate:status
```

### Paso 2: Ejecutar migraciones

```bash
# Ejecutar todas las migraciones pendientes
php artisan migrate
```

Si aparece algún error, no ejecutar más migraciones; anotar el mensaje y el archivo de migración que falla y revisar la sección “Si algo falla” más abajo.

### Paso 3: Verificar resultados

```bash
# Comprobar que no queden migraciones pendientes
php artisan migrate:status

# Comprobar tablas y columnas críticas (desde la raíz del proyecto)
php artisan tinker
```

En Tinker:

```php
Schema::hasTable('users');                    // true
Schema::hasColumn('users', 'workos_id');      // true (tras nuestra migración)
Schema::hasTable('empresas');                 // true (su tabla)
Schema::hasTable('empleados');                // true (nuestra tabla)
Schema::hasTable('usuarios');                 // true (nuestra tabla)
Schema::hasColumn('usuarios', 'ver_reportes'); // true si corre add_report_and_newsletter
```

**Verificación automática (recomendado):** usar el comando que compara migraciones con la BD:

```bash
php artisan migrate:verify
```

Este comando:
- Compara los archivos en `database/migrations/` con los registros de la tabla `migrations`: indica **pendientes** (archivos no ejecutados) y **huérfanas** (registros en BD sin archivo).
- Infiere qué tablas deberían existir a partir de las migraciones ya ejecutadas (`create_*_table`, `add_*_to_*_table`) y comprueba que existan en la BD.
- Si todo está coherente, muestra: `Resultado: OK — migraciones y esquema coherentes.`

Opciones:
- `php artisan migrate:verify --json` — salida en JSON (útil para scripts o CI).
- `php artisan migrate:verify --strict` — devuelve código de salida 1 si hay pendientes, huérfanas o tablas faltantes (útil en pipelines).

### Paso 4: Si algo falla

| Error | Posible causa | Solución |
|-------|----------------|----------|
| **Table already exists** | Una migración intenta crear una tabla que ya existe | Comprobar que la migración use `Schema::hasTable()` antes de `Schema::create`. Si es nuestra migración, debería estar ya protegida; si es de Rafa, añadir la comprobación. |
| **Duplicate column name** | Se intenta añadir una columna que ya existe | Comprobar que la migración use `Schema::hasColumn()` antes de añadir. Las que modifican `users` y `spatie_roles` ya están actualizadas. |
| **Foreign key constraint fails** | La tabla referenciada no existe o el orden de migraciones es distinto | Verificar que la migración que crea la tabla referida se ejecute antes (por nombre de archivo). Revisar `docs/base-datos/ANALISIS_ORDEN_MIGRACIONES_DEVELOP.md` si hace falta. |
| **SQLSTATE connection / access denied** | Credenciales o red | Revisar `.env` (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD). |

---

## 6. Conclusión

- Rafa puede hacer merge de su rama con la nuestra y ejecutar `php artisan migrate` con seguridad.
- Las tablas que él ya tenga (`users`, `empresas`, `productos`, `spatie_roles`, etc.) no se duplican: las que crean tablas usan `hasTable`; las que modifican `users` y `spatie_roles` usan `hasColumn`/`hasTable`.
- Los campos nuevos se añaden correctamente y de forma idempotente.
- El orden de migraciones respeta las dependencias (bancos antes de financiero, empresas antes de spatie_roles company_id, etc.).

Para más detalle sobre orden y dependencias, ver `docs/base-datos/ANALISIS_ORDEN_MIGRACIONES_DEVELOP.md` y `docs/LIMPIEZA_MIGRACIONES_2026_03.md`.

---

## 7. Seeders nuestros: posibles conflictos con Rafa

Si Rafa ejecuta `php artisan db:seed` después del merge, **algunos de nuestros seeders pueden chocar** con datos que él ya tenga en tablas de catálogo (mismos IDs o mismos valores únicos).

| Seeder | Tablas que toca | Riesgo | Notas |
|--------|------------------|--------|--------|
| **Inicial** | `users`, `productos`, `industrias`, `sub_industrias`, `centro_de_costos`, `notificaciones_incluidas` | **Alto** | Usa `insert()` con IDs fijos. Si Rafa ya tiene industrias, productos, etc. con esos IDs → error de clave duplicada. Crea también `admin@paco.com` en `users`. |
| **BancoSeeder** | `bancos` | **Alto** | `insert()` con IDs fijos (1, 2, 4, …). Si Rafa ya tiene bancos → clave duplicada. |
| **EstadoAnimoAfeccionSeeder** | `estado_animo_afecciones` | Bajo | Tabla nuestra. Riesgo solo si se ejecuta dos veces (IDs 1–29). |
| **EstadoAnimoCaracteristicaSeeder** | `estado_animo_caracteristicas` | Bajo | Igual, tabla nuestra. |
| **ReconocimientosSeeder** | `reconocimientos` | **Medio** | SQL con INSERT; si el dump tiene IDs que ya existen en Rafa → conflicto. |
| **TemaVozColaboradoresSeeder** | `temas_voz_colaboradores` | **Medio** | SQL con INSERT; Rafa puede tener ya esa tabla con datos. |
| **EmpresaEjemploSeeder** | `empresas`, razones_sociales, productos, etc. | **Alto** | Ahora está **comentado** en `DatabaseSeeder`. Si se descomenta, crea empresa de ejemplo y puede chocar con datos de Rafa. |
| **SpatieRolesSeeder** | `spatie_roles` | Bajo | Usa `firstOrCreate` → idempotente. Asume empresa id 1. |
| **ShieldPermisosRolesSeeder** | permisos de roles | Bajo | Solo asigna permisos a roles existentes. Asume empresa id 1. |
| **WorkOSTestUserSeeder** | `usuarios` | Bajo | `firstOrCreate` por email → idempotente. |

**Recomendación para Rafa:** No ejecutar `php artisan db:seed` a ciegas tras el merge. Si quiere datos iniciales: ejecutar solo los seeders que necesite (p. ej. `php artisan db:seed --class=BancoSeeder`) y comprobar antes si esas tablas tienen ya datos; o usar BD vacía y luego `db:seed` (p. ej. tras `migrate:fresh` en desarrollo).

En esta rama, **Inicial**, **BancoSeeder**, **EstadoAnimoAfeccionSeeder** y **EstadoAnimoCaracteristicaSeeder** están hechos idempotentes (solo insertan filas cuyo ID no existe ya), de modo que Rafa puede ejecutar `php artisan db:seed` sin errores de clave duplicada en esas tablas. ReconocimientosSeeder y TemaVozColaboradoresSeeder siguen usando SQL con INSERT; si la tabla ya tiene datos con los mismos IDs, pueden fallar — en ese caso ejecutarlos solo en BD vacía o revisar el contenido del SQL.
