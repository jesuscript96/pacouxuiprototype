# Análisis exhaustivo: orden de migraciones en develop

**Objetivo:** Verificar que, al subir esta rama a `develop` y ejecutar `php artisan migrate` (o `migrate:fresh`), las migraciones se ejecuten en un orden que no provoque errores de tablas o FKs inexistentes.

**Criterio de Laravel:** Las migraciones se ejecutan en **orden lexicográfico del nombre del archivo**. No hay otro criterio (fecha del sistema, etc.).

---

## 1. Orden de ejecución real (59 archivos)

Listado completo en el orden en que Laravel los ejecutará:

| # | Archivo | Crea / modifica | Depende de (FKs) |
|---|---------|------------------|------------------|
| 1 | `0001_01_01_000000_create_users_table` | users | — |
| 2 | `0001_01_01_000001_create_cache_table` | cache, cache_locks | — |
| 3 | `0001_01_01_000002_create_jobs_table` | jobs | — |
| 4 | `2026_02_24_230219_create_industrias_table` | industrias | — |
| 5 | `2026_02_24_230237_create_sub_industrias_table` | sub_industrias | industrias |
| 6 | `2026_02_25_002716_create_logs_table` | logs | — |
| 7 | `2026_02_25_011219_create_productos_table` | productos | — |
| 8 | `2026_02_25_013432_create_centro_costos_table` | centro_de_costos | — |
| 9 | `2026_02_25_120000_add_workos_fields_to_users_table` | (modifica users) | users |
| 10 | `2026_02_25_175836_create_empresas_table` | empresas | industrias, sub_industrias |
| 11 | `2026_02_25_181105_create_razonsocial_table` | razones_sociales, empresas_razones_sociales | empresas |
| 12 | `2026_02_25_194248_create_configuracion_apps_table` | configuracion_app | — |
| 13 | `2026_02_25_200211_create_comision_rangos_table` | comisiones_rangos | empresas |
| 14 | `2026_02_25_201504_create_quincenas_personalizadas_table` | quincenas_personalizadas | — |
| 15 | `2026_02_25_204433_create_empresas_productos_table` | empresas_productos | empresas, productos |
| 16 | `2026_02_25_212639_create_empresas_notificaciones_incluidas_table` | notificaciones_incluidas, empresas_notificaciones_incluidas | empresas |
| 17 | `2026_02_25_213248_create_empresas_centros_costos_table` | empresas_centros_costos | empresas, centro_de_costos |
| 18 | `2026_02_25_214718_create_reconocimientos_table` | reconocimientos | — |
| 19 | `2026_02_25_220120_create_tema_voz_colaboradores_table` | temas_voz_colaboradores, empresas_temas_voz_colaboradores | empresas |
| 20 | `2026_02_25_222523_create_razon_encuesta_salidas_table` | razones_encuesta_salida | — |
| 21 | `2026_02_25_223031_create_alias_tipo_transaccions_table` | alias_tipo_transacciones | — |
| 22 | `2026_02_25_223811_create_frecuencia_notificaciones_table` | frecuencia_notificaciones | — |
| 23 | `2026_02_26_195003_create_configuracion_retencion_nominas_table` | configuracion_retencion_nominas | empresas |
| 24 | `2026_02_26_090000_create_tablas_rafa_locales` | empresas*, productos*, temas_voz, razones_sociales*, areas (*si no existen) | empresas (para razones_sociales/areas) |
| 25 | `2026_02_26_100000_create_tablas_faltantes` | bancos, departamentos, puestos, ubicaciones, regiones, centros_pago | empresas |
| 26 | `2026_02_26_100001_create_roles_permisos_tables` | roles, permisos | — |
| 27 | `2026_02_26_100002_create_empleados_table` | empleados | empresas |
| 28 | `2026_02_26_100003_create_usuarios_table` | usuarios | empresas, departamentos, puestos, empleados |
| 29 | `2026_02_26_100004_create_auth_pivots_and_2fa_tables` | rol_usuario, permiso_rol, password_resets, verify_2fa | usuarios, roles, permisos |
| 30 | `2026_02_26_100005_create_oauth_tables` | oauth_* | usuarios |
| 31 | `2026_02_26_100006_create_empleado_producto_and_filtros_tables` | empleado_producto, filtros_empleado | empleados, productos |
| 32 | `2026_02_26_100007_create_financiero_tables` | cuentas_empleado, estados_cuenta, transacciones, etc. | empleados, bancos, empresas |
| 33 | `2026_02_26_100008_create_chat_tables` | chat_rooms, chat_room_employees, chat_messages, etc. | empresas, empleados |
| 34 | `2026_02_26_100009_create_voz_tables` | usuario_tema_voz, voces_empleado, reiteraciones_voz, etc. | usuarios, empleados, temas_voz |
| 35 | `2026_02_26_100010_create_otros_tables` | employment_contracts_tokens, digital_documents, folders, employee_filters | usuarios, empresas, folders (misma migración) |
| 36 | `2026_03_02_185249_create_empresas_reconocimientos_table` | empresas_reconocimientos | empresas, reconocimientos |
| 37 | `2026_03_03_170439_create_permission_tables` | spatie (roles, permisos, pivotes) | — |
| 38 | `2026_03_03_171432_add_company_id_to_spatie_roles_table` | (columna company_id en spatie_roles) | empresas |
| 39 | `2026_03_03_202035_create_imports_table` | imports | users (Laravel) |
| 40 | `2026_03_03_202036_create_exports_table` | exports | users |
| 41 | `2026_03_03_202037_create_failed_import_rows_table` | failed_import_rows | imports |
| 42 | `2026_03_03_202139_add_display_name_and_description_to_spatie_roles_table` | (columnas en spatie_roles) | spatie_roles |
| 43 | `2026_03_04_161940_add_report_and_newsletter_fields_to_usuarios_table` | (columnas en usuarios) | usuarios |
| 44 | `2026_03_04_171552_create_bancos_table` | bancos (si no existe) o alter (comision, deleted_at) | — (idempotente) |
| 45 | `2026_03_04_184248_create_estado_animo_afecciones_table` | estado_animo_afecciones | — |
| 46 | `2026_03_04_184339_create_estado_animo_caracteristicas_table` | estado_animo_caracteristicas | — |
| 47 | `2026_03_04_200000_rename_our_fields_to_spanish_in_usuarios_table` | (rename columnas en usuarios) | usuarios |
| 48 | `2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing` | (columnas en bancos si faltan) | bancos |
| 49 | `2026_03_05_100011_create_historiales_tables` | location_histories, area_histories, position_histories, etc. | empleados, ubicaciones, areas, puestos, departamentos, razones_sociales, regiones |
| 50 | `2026_03_05_100012_create_solicitudes_catalogos_tables` | request_types, request_status, request_categories, approval_flow_stages | empresas (solo request_categories) |
| 51 | `2026_03_05_100013_create_solicitudes_and_approvals_tables` | requests, authorization_stage_approvers, status_histories | empleados, request_types, request_status, request_categories, approval_flow_stages, usuarios |
| 52 | `2026_03_05_100014_create_encuestas_tables` | survey_categories, surveys, survey_sections, survey_questions, etc. | usuarios, empresas, empleados |
| 53 | `2026_03_05_100015_create_reconocimientos_tables` | acknowledgments, acknowledgment_company, acknowledgment_shippings, etc. | empresas, empleados |
| 54 | `2026_03_05_100016_create_notificaciones_tables` | notifications, high_employee_notification, etc. | usuarios, empresas, empleados |
| 55 | `2026_03_05_100017_create_documentos_empresa_tables` | company_files, company_folder, digital_documents_*, etc. | empresas, folders, digital_documents, empleados |
| 56 | `2026_03_05_100018_create_mensajeria_tables` | messages, high_employee_message, message_response | usuarios, empresas, empleados |
| 57 | `2026_03_05_100019_create_capacitacion_tables` | capacitations, capacitation_modules, etc. | empresas, usuarios, empleados |
| 58 | `2026_03_05_100020_create_integraciones_tables` | belvo_*, imss_*, ine_*, voice_employees_tableu, messages_tableu | empleados, voces_empleado, messages |
| 59 | `2026_03_05_100021_create_adicionales_tables` | devices, device_locations, moods, festivities, etc. | empleados, usuarios, empresas |

---

## 2. Verificación de dependencias críticas

Para cada migración que referencia otras tablas, se comprueba que esas tablas existan **en una migración anterior** (número de orden menor).

| Migración (#) | Tabla(s) referenciada(s) | ¿Creada en # anterior? | Estado |
|---------------|---------------------------|------------------------|--------|
| 5 | industrias | #4 | ✅ |
| 10 | industrias, sub_industrias | #4, #5 | ✅ |
| 11 | empresas | #10 | ✅ |
| 13 | empresas | #10 | ✅ |
| 15 | empresas, productos | #10, #7 (o #24) | ✅ |
| 16 | empresas | #10 | ✅ |
| 17 | empresas, centro_de_costos | #10, #8 | ✅ |
| 19 | empresas | #10 | ✅ |
| 23 | empresas | #10 | ✅ |
| 24 | empresas (para razones_sociales/areas) | #10 (ya existe por Rafa) | ✅ |
| 25 | empresas | #10 | ✅ |
| 27 | empresas | #10 | ✅ |
| 28 | empresas, departamentos, puestos, empleados | #10, #25, #25, #27 | ✅ |
| 29 | usuarios, roles, permisos | #28, #26 | ✅ |
| 30 | usuarios | #28 | ✅ |
| 31 | empleados, productos | #27, #7 (o #24) | ✅ |
| 32 | empleados, bancos, empresas | #27, #25 (o #44), #10 | ✅ |
| 33 | empresas, empleados | #10, #27 | ✅ |
| 34 | usuarios, empleados, temas_voz | #28, #27, #24 (temas_voz en tablas_rafa_locales) | ✅ |
| 35 | usuarios, empresas; folders (misma migración) | #28, #10; folders se crean en #35 antes de usarse | ✅ |
| 36 | empresas, reconocimientos | #10, #18 | ✅ |
| 38 | empresas | #10 | ✅ |
| 43 | usuarios | #28 | ✅ |
| 47 | usuarios | #28 | ✅ |
| 48 | bancos | #25 o #44 | ✅ |
| 49 | empleados, ubicaciones, areas, puestos, departamentos, razones_sociales, regiones | #27, #25, #24, #25, #25, #11 o #24, #25 | ✅ |
| 50 | empresas | #10 | ✅ |
| 51 | empleados, request_types, request_status, request_categories, approval_flow_stages, usuarios | #27, #50, #50, #50, #50, #28 | ✅ |
| 52 | usuarios, empresas, empleados | #28, #10, #27 | ✅ |
| 53 | empresas, empleados | #10, #27 | ✅ |
| 54 | usuarios, empresas, empleados | #28, #10, #27 | ✅ |
| 55 | empresas, folders, digital_documents, empleados | #10, #35, #35, #27 | ✅ |
| 56 | usuarios, empresas, empleados | #28, #10, #27 | ✅ |
| 57 | empresas, usuarios, empleados (+ tablas internas misma migración) | #10, #28, #27 | ✅ |
| 58 | empleados, voces_empleado, messages | #27, #34, #56 | ✅ |
| 59 | empleados, usuarios, empresas, devices (misma migración) | #27, #28, #10; devices en #59 | ✅ |

---

## 3. Casos especiales ya resueltos

### 3.1 Tabla `bancos`

- **Creación:** La crea primero `2026_02_26_100000_create_tablas_faltantes` (#25) con estructura mínima (nombre, codigo, timestamps).
- **Duplicado:** `2026_03_04_171552_create_bancos_table` (#44) no vuelve a crear si ya existe; si existe, añade `comision` y `deleted_at` si faltan.
- **Ajuste posterior:** `2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing` (#48) garantiza esas columnas cuando la tabla vino solo de #25.
- **Conclusión:** No hay error "Table 'bancos' already exists" y la tabla queda con la estructura esperada por el modelo y BancoSeeder.

### 3.2 Tabla `temas_voz`

- **Uso:** `2026_02_26_100009_create_voz_tables` (#34) referencia `temas_voz` en usuario_tema_voz y voces_empleado.
- **Creación:** `2026_02_26_090000_create_tablas_rafa_locales` (#24) crea `temas_voz` con `if (! Schema::hasTable('temas_voz'))`.
- **Orden:** #24 (090000) se ejecuta **antes** que #34 (100009). ✅

### 3.3 Tablas `departamentos` y `puestos`

- **Uso:** `2026_02_26_100003_create_usuarios_table` (#28) referencia departamentos y puestos.
- **Creación:** `2026_02_26_100000_create_tablas_faltantes` (#25) crea ambas.
- **Orden:** #25 se ejecuta **antes** que #28. ✅

### 3.4 Fase 2 (historiales, solicitudes, encuestas, etc.)

- Antes estaban en `2026_02_23_*` y se ejecutaban antes que empresas/empleados/usuarios.
- Ahora están en **`2026_03_05_100011` … `2026_03_05_100021`**, es decir **después** de todas las migraciones de Rafa y Fase 1.
- Todas sus dependencias (empresas, empleados, usuarios, departamentos, puestos, ubicaciones, regiones, areas, razones_sociales, folders, digital_documents, voces_empleado, messages, etc.) están creadas en migraciones anteriores. ✅

### 3.5 `tablas_rafa_locales` y `tablas_faltantes`

- Renombradas a **2026_02_26_090000** y **2026_02_26_100000** para que corran **después** de las migraciones de Rafa (empresas ya existe en #10) y **antes** de roles, empleados, usuarios y el resto de Fase 1.
- Así existen departamentos, puestos, bancos, temas_voz, areas, etc. cuando se necesitan. ✅

---

## 4. Conclusión

- **Al subir a `develop` y ejecutar `php artisan migrate` (o `migrate:fresh`), las migraciones se ejecutarán en el orden del apartado 1.**
- **Todas las dependencias comprobadas en el apartado 2 se satisfacen** por migraciones con número de orden menor.
- **No hay dependencias circulares** ni tablas referenciadas antes de ser creadas.
- **Casos especiales** (bancos, temas_voz, departamentos/puestos, Fase 2) están cubiertos por el reorden y las migraciones idempotentes aplicadas.

**Veredicto:** Sí se puede subir a develop y correr las migraciones en este orden sin errores de esquema o de FKs. El orden actual del repositorio es correcto para `develop`.

---

## 5. Comando de verificación recomendado

Antes de hacer merge a develop, en una rama o copia local:

```bash
php artisan migrate:fresh --no-interaction
```

Si termina sin errores, el estado del análisis se cumple en ese entorno. En develop, quien haga pull y ejecute `php artisan migrate` solo correrá las migraciones pendientes; el orden será el mismo (por nombre de archivo).

---

*Documento generado: 2026-03-04. Coherente con `docs/ANALISIS_POST_MERGE_MIGRACIONES.md` y `docs/REGISTRO_CAMBIOS_2026_03_04.md`.*
