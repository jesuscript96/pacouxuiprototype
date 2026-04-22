# Análisis: migrate:fresh contra base vacía (solo desde código)

**Alcance:** Análisis estático de `database/migrations/`. No se ha ejecutado nada contra dev/Aiven.

---

## 1. Orden de migraciones (por timestamp de archivo)

Las migraciones se ejecutan en orden lexicográfico del nombre del archivo (= orden por timestamp).

| # | Timestamp | Archivo | Crea tabla(s) | Altera tabla | FKs a | ¿Destino existe? |
|---|-----------|---------|----------------|--------------|-------|------------------|
| 1 | 0001_01_01_000000 | create_users_table | users, password_reset_tokens, sessions | — | — | — |
| 2 | 0001_01_01_000001 | create_cache_table | cache, cache_locks | — | — | — |
| 3 | 0001_01_01_000002 | create_jobs_table | jobs, job_batches, failed_jobs | — | — | — |
| 4 | 2026_02_24_230219 | create_industrias_table | industrias | — | — | — |
| 5 | 2026_02_24_230237 | create_sub_industrias_table | sub_industrias | — | — | — |
| 6 | 2026_02_25_002716 | create_logs_table | logs | — | — | — |
| 7 | 2026_02_25_011219 | create_productos_table | productos | — | — | — |
| 8 | 2026_02_25_013432 | create_centro_costos_table | centro_de_costos | — | — | — |
| 9 | 2026_02_25_120000 | add_workos_fields_to_users_table | — | users | — | users (#1) ✅ |
| 10 | 2026_02_25_175836 | create_empresas_table | empresas | — | industrias, sub_industrias | ✅ (#4,#5) |
| 11 | 2026_02_25_181105 | create_razonsocial_table | razones_sociales, empresas_razones_sociales | — | empresas, razones_sociales | ✅ (#10, misma mig) |
| 12 | 2026_02_25_194248 | create_configuracion_apps_table | configuracion_app | — | — | — |
| 13 | 2026_02_25_200211 | create_comision_rangos_table | comisiones_rangos | — | empresas | ✅ (#10) |
| 14 | 2026_02_25_201504 | create_quincenas_personalizadas_table | quincenas_personalizadas | — | empresas | ✅ (#10) |
| 15 | 2026_02_25_204433 | create_empresas_productos_table | empresas_productos | — | empresas, productos | ✅ (#10,#7) |
| 16 | 2026_02_25_212639 | create_empresas_notificaciones_incluidas_table | notificaciones_incluidas, empresas_notificaciones_incluidas | — | empresas, notificaciones_incluidas | ✅ (#10, misma mig) |
| 17 | 2026_02_25_213248 | create_empresas_centros_costos_table | empresas_centros_costos | — | empresas, centro_de_costos | ✅ (#10,#8) |
| 18 | 2026_02_25_214718 | create_reconocimientos_table | reconocimientos | — | — | — |
| 19 | 2026_02_25_220120 | create_tema_voz_colaboradores_table | temas_voz_colaboradores, empresas_temas_voz_colaboradores | — | empresas, temas_voz_colaboradores | ✅ (#10, misma mig) |
| 20 | 2026_02_25_222523 | create_razon_encuesta_salidas_table | razones_encuesta_salida | — | empresas | ✅ (#10) |
| 21 | 2026_02_25_223031 | create_alias_tipo_transaccions_table | alias_tipo_transacciones | — | empresas | ✅ (#10) |
| 22 | 2026_02_25_223811 | create_frecuencia_notificaciones_table | frecuencia_notificaciones | — | empresas | ✅ (#10) |
| 23 | 2026_02_26_099998 | create_puestos_table | puestos | — | empresas | ✅ (#10) |
| 24 | 2026_02_26_100000 | create_tablas_faltantes | — | — | no-op | — |
| 25 | 2026_02_26_100001 | create_roles_permisos_tables | roles, permisos | — | — | — |
| 26 | 2026_02_26_100004 | create_auth_pivots_and_2fa_tables | rol_usuario, permiso_rol, password_resets, verify_2fa | — | users, roles | ✅ (#1,#25) |
| 27 | 2026_02_26_100005 | create_oauth_tables | oauth_* | — | users | ✅ (#1) |
| 28 | 2026_02_26_195003 | create_configuracion_retencion_nominas_table | configuracion_retencion_nominas | — | empresas | ✅ (#10) |
| 29 | 2026_03_02_185249 | create_empresas_reconocimientos_table | empresas_reconocimientos | — | empresas, reconocimientos | ✅ (#10,#18) |
| 30 | 2026_03_03_170439 | create_permission_tables | permissions, **spatie_roles** (config), model_has_*, role_has_permissions | — | — | — |
| 31 | 2026_03_03_171432 | add_company_id_to_spatie_roles_table | — | spatie_roles | empresas | ✅ (#10); tabla spatie_roles (#30) ✅ |
| 32 | 2026_03_03_202139 | add_display_name_and_description_to_spatie_roles_table | — | spatie_roles | — | tabla (#30) ✅ |
| 33 | 2026_03_04_171552 | create_bancos_table | bancos | — | — | — |
| 34 | 2026_03_04_184248 | create_estado_animo_afecciones_table | estado_animo_afecciones | — | — | — |
| 35 | 2026_03_04_184339 | create_estado_animo_caracteristicas_table | estado_animo_caracteristicas | — | — | — |
| 36 | 2026_03_04_230042 | create_empresa_user_table | empresa_user | — | empresas, users | ✅ (#10,#1) |
| 37 | 2026_03_05_004220 | create_departamentos_table | departamentos | — | empresas | ✅ (#10) |
| 38 | 2026_03_05_152755 | create_departamento_generals_table | departamentos_generales | — | — | — |
| 39 | 2026_03_06_195203 | create_felicitaciones_table | felicitaciones | — | empresas, users, departamentos | ✅ (#10,#1,#37) |
| 40 | 2026_03_10_012446 | add_usuario_fields_to_users_table | — | users | empresas, departamentos, puestos; **colaborador_id solo columna (sin FK)** | ✅ (#10,#37,#23) |
| 41 | 2026_03_10_012548 | drop_usuarios_table | — | — | drop si existe | — |
| 42 | 2026_03_10_190906 | create_ubicaciones_table | ubicaciones, razones_sociales_ubicaciones | — | empresas, razones_sociales, ubicaciones | ✅ (#10,#11, misma mig) |
| 43 | 2026_03_10_212941 | homologar_tipo_usuario_en_users_table | — | users | — | users (#1) ✅ |
| 44 | 2026_03_11_153115 | create_centro_pagos_table | centros_pagos | — | empresas | ✅ (#10) |
| 45 | 2026_03_11_164844 | create_areas_generales_table | areas_generales | — | empresas | ✅ (#10) |
| 46 | 2026_03_11_164849 | create_areas_table | areas | — | areas_generales, empresas | ✅ (#45,#10) |
| 47 | 2026_03_11_181350 | create_regions_table | regiones | — | empresas | ✅ (#10) |
| 48 | 2026_03_11_181401 | create_colaboradores_table | colaboradores | — | empresas, ubicaciones, departamentos, areas, puestos, regiones, centros_pagos, razones_sociales | ✅ (#10,#42,#37,#46,#23,#47,#44,#11) |
| 49 | 2026_03_11_181402 | create_beneficiarios_colaborador_table | beneficiarios_colaborador | — | colaboradores | ✅ (#48) |
| 50 | 2026_03_11_181403 | create_cuentas_nomina_table | cuentas_nomina | — | colaboradores, bancos | ✅ (#48,#33) |
| 51 | 2026_03_11_181404 | create_historial_ubicaciones_table | historial_ubicaciones | — | colaboradores, ubicaciones | ✅ (#48,#42) |
| 52 | 2026_03_11_181405 | create_historial_departamentos_table | historial_departamentos | — | colaboradores, departamentos | ✅ (#48,#37) |
| 53 | 2026_03_11_181406 | create_historial_areas_table | historial_areas | — | colaboradores, areas | ✅ (#48,#46) |
| 54 | 2026_03_11_181407 | create_historial_puestos_table | historial_puestos | — | colaboradores, puestos | ✅ (#48,#23) |
| 55 | 2026_03_11_181408 | create_historial_regiones_table | historial_regiones | — | colaboradores, regiones | ✅ (#48,#47) |
| 56 | 2026_03_11_181409 | create_historial_razones_sociales_table | historial_razones_sociales | — | colaboradores, razones_sociales | ✅ (#48,#11) |
| 57 | 2026_03_11_181410 | create_historial_periodicidades_pago_table | historial_periodicidades_pago | — | colaboradores | ✅ (#48) |
| 58 | 2026_03_11_181411 | create_colaborador_producto_table | colaborador_producto | — | colaboradores, productos | ✅ (#48,#7) |
| 59 | 2026_03_11_182633 | create_puestos_generales_table | puestos_generales | — | empresas | ✅ (#10) |
| 60 | 2026_03_11_183741 | create_ocupaciones_table | ocupaciones | — | — | — |
| 61 | 2026_03_11_183742 | add_columns_to_puesto_table | — | puestos | puestos_generales, ocupaciones, areas_generales | ✅ (#23,#60,#45) |
| 62 | 2026_03_11_194801 | create_importaciones_table | importaciones | — | empresas, users | ✅ (#10,#1) |
| 63 | 2026_03_11_194802 | create_errores_importacion_table | errores_importacion | — | importaciones | ✅ (#62) |
| 64 | 2026_03_12_195118 | create_usuarios_temas_voz_colaboradores_table | usuarios_temas_voz_colaboradores | — | users, temas_voz_colaboradores | ✅ (#1,#19) |
| 65 | 2026_03_13_170610 | create_filtro_productos_table | filtros_productos | — | empresas, productos, areas, departamentos, ubicaciones, puestos, regiones | ✅ (#10,#7,#46,#37,#42,#23,#47) |
| 66 | 2026_03_17_160646 | unificar_nomenclatura_empleado_a_colaborador | — | users, colaboradores | ver sección unificación | ver abajo |
| 67 | 2026_03_17_170850 | change_estado_to_string_in_colaborador_producto_table | — | colaborador_producto | — | tabla (#58) ✅ |

---

## 2. Problemas detectados

Ningún caso en el que una FK apunte a una tabla con timestamp **posterior**.  
Ningún `Schema::table()` sobre una tabla que aún no exista.  
Ningún `renameColumn`/`dropColumn` sobre una columna que no exista en ese punto del orden (en fresh: `colaboradores.numero_empleado` existe por #48; `users.colaborador_id` existe por #40 como columna; la unificación no hace rename en users en la rama “colaborador_id ya existe”, solo añade FK).

| Migración | Problema | Tipo |
|-----------|----------|------|
| — | Ninguno | — |

---

## 3. Migración de unificación (2026_03_17_160646) en base vacía

- **renameColumn colaboradores (numero_empleado → numero_colaborador)**  
  La tabla `colaboradores` existe desde la migración #48 (2026_03_11_181401) y tiene la columna `numero_empleado`.  
  **Conclusión:** ✅ OK.

- **UPDATE spatie_roles SET name='colaborador' WHERE name='empleado'**  
  La tabla `spatie_roles` existe (creada en #30 como `$tableNames['roles']` = `spatie_roles`).  
  En migrate:fresh no se ha ejecutado seeder; no hay filas con `name='empleado'`.  
  **Comportamiento:** UPDATE afecta 0 filas; en MySQL no es error.  
  **Conclusión:** ✅ Sin efecto, no da error.

- **UPDATE permissions (UploadArchivoEmpleado → …, ViewBajaEmpleado → …)**  
  La tabla `permissions` existe (#30). En base vacía no hay esos nombres.  
  **Comportamiento:** 0 filas actualizadas; no es error.  
  **Conclusión:** ✅ Sin efecto, no da error.

- **FK users.colaborador_id → colaboradores**  
  En fresh: la migración #40 ya creó la columna `colaborador_id` en `users` (sin FK). La unificación entra en `elseif (Schema::hasColumn('users', 'colaborador_id'))` y ejecuta `Schema::table('users', ...)` añadiendo la FK a `colaboradores`.  
  `users` existe (#1), `colaboradores` existe (#48).  
  **Conclusión:** ✅ OK.

---

## 4. Resumen

- **Orden de migraciones:** 67 archivos; todas las FKs y alters referencian tablas/columnas creadas en migraciones anteriores.
- **Problemas detectados:** Ninguno (FK, alter, rename/drop, datos).
- **Unificación en base vacía:** renameColumn ✅, UPDATE spatie_roles ✅ sin efecto, UPDATE permissions ✅ sin efecto, FK users→colaboradores ✅.

---

## VEREDICTO

**🟢 migrate:fresh pasará limpio** en una base completamente vacía (sin ejecutar contra dev/Aiven).

No se han detectado fallos de orden de tablas, FKs, alters ni renames; los UPDATE de la unificación en tablas vacías no lanzan error.
