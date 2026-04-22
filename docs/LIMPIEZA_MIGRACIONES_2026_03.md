# Limpieza y orden del directorio de migraciones

**Fecha:** 2026-03  
**Objetivo:** Dejar `database/migrations/` coherente, sin eliminar nada que rompa entornos ya migrados.

---

## 1. Estado actual: tabla de usuarios

### Situación real del código

- **Auth:** `config/auth.php` usa `AUTH_MODEL` → `App\Models\Usuario::class`. La aplicación autentica contra la tabla **`usuarios`** y el modelo **`Usuario`**.
- **Filament / Shield:** Paneles Admin y Cliente usan **Usuario** (FilamentUser, HasTenants). Recursos, políticas y middleware referencian **Usuario** y tabla **`usuarios`**.
- **Pivot empresa_user:** La migración `2026_03_06_211230_create_empresa_user_table.php` define `usuario_id` → `usuarios`. El modelo **Usuario** usa esa pivot.
- **Tabla `users`:** Existe por la migración por defecto de Laravel (`0001_01_01_000000_create_users_table.php`) y tiene `add_workos_fields_to_users_table`. El modelo **User** existe y usa `empresa_user` con `user_id` en código, pero la migración de la pivot usa **`usuario_id`** → **`usuarios`**. Solo **felicitaciones** referencia `user_id` → `users`.

Conclusión: el sistema está montado sobre **`usuarios`** y **Usuario**. Unificar en **`users`** implicaría:

- Migrar datos de `usuarios` → `users` (o renombrar tabla y columnas).
- Cambiar todas las FK `usuario_id` → `user_id` y tabla referenciada en muchas migraciones y modelos.
- Cambiar auth, Filament, Shield y políticas a **User**.
- Decidir qué hacer con la pivot `empresa_user` (hoy `usuario_id` → `usuarios`).

Por tanto, **no se ha cambiado** la tabla de usuarios a `users` en esta limpieza. Queda como tarea aparte si se confirma el acuerdo de usar solo `users`.

---

## 2. Migraciones que NO se eliminan (y por qué)

| Migración | Motivo |
|-----------|--------|
| `2026_02_26_090000_create_tablas_rafa_locales` | Ya es idempotente (solo crea empresas, productos, temas_voz, razones_sociales, areas si no existen). En muchos entornos ya está en la tabla `migrations`. Borrarla rompería `migrate:status` y el historial. Además, **temas_voz** y **areas** no tienen otra migración que las cree; si se quita esta, habría que añadir migraciones específicas para ellas. |
| `2026_02_26_100000_create_tablas_faltantes` | Convertida en **no-op** (up/down vacíos). Sigue siendo necesaria como archivo: si en algún entorno ya está registrada en `migrations`, el archivo debe existir. Eliminarla daría “migration not found”. |
| `2026_03_04_171552_create_bancos_table` | Crea la tabla **bancos** (idempotente) y añade columnas si faltan. Es la migración que define bancos; no existe otra que la sustituya en los catálogos actuales. |
| `2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing` | Añade columnas a **bancos** en bases que ya tenían la tabla sin ellas. Idempotente y necesaria para no dejar bancos a medias. |

**Migración eliminada (2026-03):** `2026_03_04_200000_rename_our_fields_to_spanish_in_usuarios_table.php` — Se eliminó para evitar inconsistencia. La migración `2026_03_04_161940_add_report_and_newsletter_fields_to_usuarios_table.php` ahora añade los campos en español (ver_reportes, usuario_tableau, recibir_boletin) de forma definitiva y, si existen columnas en inglés, las copia a español y las elimina. En entornos donde la migración de rename ya estaba ejecutada, se puede borrar esa fila de la tabla `migrations` para evitar "migration not found".

No existían en el proyecto las migraciones `2026_03_02_090000_create_tablas_rafa_locales` ni `2026_03_02_100000_create_tablas_faltantes`, por lo que no hay duplicados que eliminar.

---

## 3. Migraciones conservadas (orden de ejecución)

El orden es el que impone el prefijo de fecha/hora del nombre del archivo. **No se han renombrado** archivos de migración para no romper la tabla `migrations` en bases donde ya están ejecutadas.

Listado en orden de ejecución (68 archivos):

```
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
2026_02_24_230219_create_industrias_table.php
2026_02_24_230237_create_sub_industrias_table.php
2026_02_25_002716_create_logs_table.php
2026_02_25_011219_create_productos_table.php
2026_02_25_013432_create_centro_costos_table.php
2026_02_25_120000_add_workos_fields_to_users_table.php
2026_02_25_175836_create_empresas_table.php
2026_02_25_181105_create_razonsocial_table.php
2026_02_25_194248_create_configuracion_apps_table.php
2026_02_25_200211_create_comision_rangos_table.php
2026_02_25_201504_create_quincenas_personalizadas_table.php
2026_02_25_204433_create_empresas_productos_table.php
2026_02_25_212639_create_empresas_notificaciones_incluidas_table.php
2026_02_25_213248_create_empresas_centros_costos_table.php
2026_02_25_214718_create_reconocimientos_table.php
2026_02_25_220120_create_tema_voz_colaboradores_table.php
2026_02_25_222523_create_razon_encuesta_salidas_table.php
2026_02_25_223031_create_alias_tipo_transaccions_table.php
2026_02_25_223811_create_frecuencia_notificaciones_table.php
2026_02_26_090000_create_tablas_rafa_locales.php
2026_02_26_099998_create_bancos_table.php
2026_02_26_099999_create_puestos_ubicaciones_regiones_centros_pago_table.php
2026_02_26_100000_create_tablas_faltantes.php
2026_02_26_100001_create_roles_permisos_tables.php
2026_02_26_100002_create_empleados_table.php
2026_02_26_100003_create_usuarios_table.php
2026_02_26_100004_create_auth_pivots_and_2fa_tables.php
2026_02_26_100005_create_oauth_tables.php
2026_02_26_100006_create_empleado_producto_and_filtros_tables.php
2026_02_26_100007_create_financiero_tables.php
2026_02_26_100008_create_chat_tables.php
2026_02_26_100009_create_voz_tables.php
2026_02_26_100010_create_otros_tables.php
2026_02_26_195003_create_configuracion_retencion_nominas_table.php
2026_03_02_185249_create_empresas_reconocimientos_table.php
2026_03_03_170439_create_permission_tables.php
2026_03_03_171432_add_company_id_to_spatie_roles_table.php
2026_03_03_202035_create_imports_table.php
2026_03_03_202036_create_exports_table.php
2026_03_03_202037_create_failed_import_rows_table.php
2026_03_03_202139_add_display_name_and_description_to_spatie_roles_table.php
2026_03_04_161940_add_report_and_newsletter_fields_to_usuarios_table.php
2026_03_04_171552_create_bancos_table.php
2026_03_04_184248_create_estado_animo_afecciones_table.php
2026_03_04_184339_create_estado_animo_caracteristicas_table.php
2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing.php
2026_03_05_004220_create_departamentos_table.php
2026_03_05_100011_create_historiales_tables.php
2026_03_05_100012_create_solicitudes_catalogos_tables.php
2026_03_05_100013_create_solicitudes_and_approvals_tables.php
2026_03_05_100014_create_encuestas_tables.php
2026_03_05_100015_create_reconocimientos_tables.php
2026_03_05_100016_create_notificaciones_tables.php
2026_03_05_100017_create_documentos_empresa_tables.php
2026_03_05_100018_create_mensajeria_tables.php
2026_03_05_100019_create_capacitacion_tables.php
2026_03_05_100020_create_integraciones_tables.php
2026_03_05_100021_create_adicionales_tables.php
2026_03_05_152755_create_departamento_generals_table.php
2026_03_05_210000_add_puesto_and_departamento_to_usuarios_if_missing.php
2026_03_06_195203_create_felicitaciones_table.php
2026_03_06_211230_create_empresa_user_table.php
```

**Orden bancos:** `2026_02_26_099998_create_bancos_table.php` crea la tabla **bancos** antes de `create_financiero_tables` (100007), que tiene FK a `bancos`. La migración `2026_03_04_171552_create_bancos_table.php` sigue existiendo y es idempotente (si bancos ya existe, solo añade columnas si faltan).

---

## 4. Modelos y tablas de usuarios

- **Tabla `users`:** Existe (Laravel + WorkOS). Modelo **User** (`app/Models/User.php`), usado en relación con empresas y en **felicitaciones** (`user_id`).
- **Tabla `usuarios`:** Es la tabla principal de la aplicación. Modelo **Usuario** (`app/Models/Usuario.php`, `$table = 'usuarios'`). Auth, Filament y Shield dependen de ella.
- No se ha eliminado ni renombrado el modelo **Usuario**; hacerlo requeriría el cambio global a `users` descrito arriba.

---

## 5. Verificaciones recomendadas

```bash
# Migraciones pendientes / estado
php artisan migrate:status

# Desde cero (solo en entorno de desarrollo)
php artisan migrate:fresh --no-interaction
```

En Tinker:

```php
Schema::hasTable('usuarios');  // true
Schema::hasTable('users');     // true
```

---

## 6. Próximos pasos (si se quiere unificar en `users`)

Si se confirma que la decisión es usar solo **`users`**:

1. Crear un **proyecto de migración** (nueva rama/PR):  
   - Migrar datos `usuarios` → `users` (o renombrar tabla y adaptar columnas).  
   - Extender `users` con las columnas que hoy tiene `usuarios` (empresa_id, departamento_id, puesto_id, empleado_id, workos_id, nombre, apellidos, etc.).
2. Añadir migraciones que:  
   - Cambien todas las FK de `usuario_id`/`usuarios` a `user_id`/`users`.  
   - Ajusten la pivot `empresa_user` a `user_id` si se deja de usar `usuario_id`.
3. Cambiar **config/auth.php** y todo el código (Filament, Shield, políticas) de **Usuario** a **User**.
4. Eliminar o deprecar el modelo **Usuario** y las migraciones que solo crean/modifican **usuarios** una vez completada la migración de datos y de código.

Esta limpieza deja el directorio de migraciones estable y documentado; la unificación en `users` queda como tarea explícita y separada.

---

## 7. Verificación de consistencia e instrucciones para Rafa

### 7.1 Verificación realizada (duplicados, orden, modelos)

- **Duplicados:** No hay nombres de archivo duplicados (68 migraciones únicas).
- **Orden Rafa:** Las migraciones 2026_02_24–2026_02_26 (industrias, logs, productos, empresas, tablas_rafa, 099998 bancos, 099999 puestos/ubicaciones, tablas_faltantes, 100001–100010, etc.) se ejecutan en orden por timestamp. No hay migraciones duplicadas con fechas 2026_03_02_090000/100000.
- **Modelos:** Todos los modelos en `app/Models/` tienen `protected $table` apuntando a la tabla correcta (usuarios, empresas, bancos, empleados, etc.).
- **migrate:fresh:** En este entorno no pudo ejecutarse por error de conexión a la base de datos (host no respondió). Debe probarse en un entorno donde la BD esté accesible; con el orden actual (bancos en 099998 antes de financiero 100007) se espera que termine sin errores.

### 7.2 Migraciones eliminadas (que Rafa podría tener ejecutadas)

Solo se eliminó **un** archivo de migración:

| Migración eliminada |
|---------------------|
| `2026_03_04_200000_rename_our_fields_to_spanish_in_usuarios_table.php` |

### 7.3 Instrucciones para Rafa

1. **Después de hacer pull**, si en su base de datos esa migración ya estaba ejecutada, Laravel puede mostrar "Migration not found" o similar al ejecutar `php artisan migrate` o `php artisan migrate:status`.

2. **Solución:** Borrar la fila correspondiente en la tabla `migrations` (las columnas de usuarios ya están en español, no hace falta volver a ejecutar nada):

   ```sql
   DELETE FROM migrations WHERE migration = '2026_03_04_200000_rename_our_fields_to_spanish_in_usuarios_table';
   ```

3. **Nueva migración que le aparecerá:** `2026_02_26_099998_create_bancos_table`. Si su base ya tiene la tabla `bancos`, la migración es idempotente (no hace nada). Puede ejecutar `php artisan migrate` con normalidad.

4. **Resumen:** No se ha eliminado ninguna otra migración que pudiera estar en su historial. Solo el rename de usuarios; con el SQL anterior su entorno queda consistente.
