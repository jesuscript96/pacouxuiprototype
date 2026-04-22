# Plan: solo migraciones CORE (Login, Roles, Usuarios, Empleados + Rafa)

**Regla:** **Todo lo que haya sido editado o modificado por Rafa (migraciones) va en `database/migrations/` (CORE).** En CORE solo está la tabla `reconocimientos` y el pivot `empresas_reconocimientos` (Rafa). El módulo de reconocimientos de la app (acknowledgments, envíos, catálogo duplicado) está en pospuestos. El resto de migraciones no prioritarias (encuestas operativas, chat, voz, notificaciones operativas, documentos, solicitudes, capacitación, financiero operativo, reclutamiento, integraciones, adicionales no-Rafa) se mantiene en pospuestos.

---

## MIGRACIONES QUE SE QUEDAN (CORE)

| Archivo | Motivo |
|---------|--------|
| 0001_01_01_000000_create_users_table | users, sessions, password_reset_tokens |
| 0001_01_01_000001_create_cache_table | cache |
| 0001_01_01_000002_create_jobs_table | jobs (cola) |
| 2026_02_24_230219_create_industrias_table | Rafa |
| 2026_02_24_230237_create_sub_industrias_table | Rafa |
| 2026_02_25_120000_add_workos_fields_to_users_table | Login/WorkOS |
| 2026_02_25_175836_create_empresas_table | Rafa |
| 2026_02_25_181105_create_razonsocial_table | Rafa (razones_sociales) |
| 2026_02_25_204433_create_empresas_productos_table | Rafa (empresa-producto) |
| 2026_02_25_214718_create_reconocimientos_table | Rafa (reconocimientos) |
| 2026_02_25_220120_create_tema_voz_colaboradores_table | Rafa (temas_voz) |
| 2026_02_25_011219_create_productos_table | Rafa (productos) |
| 2026_02_25_213248_create_empresas_centros_costos_table | Rafa (empresas–centros costo) |
| 2026_02_25_194248_create_configuracion_apps_table | Rafa (configuración app) |
| 2026_02_25_200211_create_comision_rangos_table | Rafa (comisiones rangos) |
| 2026_02_25_201504_create_quincenas_personalizadas_table | Rafa (quincenas personalizadas) |
| 2026_02_25_223031_create_alias_tipo_transaccions_table | Rafa (alias tipo transacciones) |
| 2026_02_26_195003_create_configuracion_retencion_nominas_table | Rafa (config retención nómina) |
| 2026_02_25_002716_create_logs_table | Rafa (logs) |
| 2026_02_25_222523_create_razon_encuesta_salidas_table | Rafa (razones_encuesta_salida) |
| 2026_02_25_223811_create_frecuencia_notificaciones_table | Rafa (frecuencia_notificaciones) |
| 2026_02_25_212639_create_empresas_notificaciones_incluidas_table | CORE (notificaciones_incluidas, empresas_notificaciones_incluidas; panel admin) |
| 2026_02_26_099998_create_bancos_table | Rafa |
| 2026_02_26_099999_create_puestos_ubicaciones_regiones_centros_pago_table | Catálogos empleados (departamentos, puestos, ubicaciones, regiones, centros_pago) |
| 2026_02_26_100000_create_tablas_faltantes | No-op (mantener por historial) |
| 2026_02_26_100001_create_roles_permisos_tables | roles, permisos (app) |
| 2026_02_26_100002_create_empleados_table | empleados |
| 2026_02_26_100003_create_usuarios_table | usuarios |
| 2026_02_26_100004_create_auth_pivots_and_2fa_tables | rol_usuario, permiso_rol, password_resets, verify_2fa |
| 2026_02_26_100005_create_oauth_tables | oauth_* |
| 2026_02_26_100006_create_empleado_producto_and_filtros_tables | empleado_producto (high_employee_product), filtros_empleado |
| 2026_03_03_170439_create_permission_tables | Spatie (permissions, spatie_roles, model_has_*) |
| 2026_03_03_171432_add_company_id_to_spatie_roles_table | Spatie |
| 2026_03_03_202139_add_display_name_and_description_to_spatie_roles_table | Spatie |
| 2026_03_04_161940_add_report_and_newsletter_fields_to_usuarios_table | usuarios |
| 2026_03_04_171552_create_bancos_table | Rafa (bancos) |
| 2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing | Rafa (bancos) |
| 2026_03_05_004220_create_departamentos_table | departamentos (empleados) |
| 2026_03_05_210000_add_puesto_and_departamento_to_usuarios_if_missing | usuarios |
| 2026_03_04_184248_create_estado_animo_afecciones_table | CORE (estado de ánimo afecciones; panel admin) |
| 2026_03_04_184339_create_estado_animo_caracteristicas_table | CORE (estado de ánimo características; panel admin) |
| 2026_03_06_195203_create_felicitaciones_table | CORE (felicitaciones; panel admin) |
| 2026_03_05_152755_create_departamento_generals_table | CORE (departamentos_generales; recurso Cliente) |
| 2026_03_04_230042_create_empresa_user_table | Rafa (pivot empresa–user, user_id → users) |
| 2026_03_02_185249_create_empresas_reconocimientos_table | CORE (pivot empresa–reconocimiento; tabla reconocimientos de Rafa) |
| 2026_03_10_100028_translate_pivot_tables | empleado_producto → productos_empleado (solo si aplica) |
| 2026_03_10_100029_translate_role_tables | rol_usuario → usuario_rol |

Nota: 100028 y 100029 traducen tablas que sí usamos (pivotes y roles). Se dejan; si alguna tabla no existe, son idempotentes.

---

## MIGRACIONES QUE SE MUEVEN A POSPUESTOS

### reconocimientos_app (módulo app; no usado en lógica actual)
- 2026_03_05_100015_create_reconocimientos_tables (acknowledgments, acknowledgment_company, acknowledgment_shippings, acknowledgment_high_employee)
- 2026_03_10_100030_translate_acknowledgment_tables
- 2026_03_10_100033_use_rafa_reconocimientos_drop_duplicate_catalog

### encuestas
- 2026_03_05_100014_create_encuestas_tables
- 2026_03_05_220000_rename_encuesta_tables_and_columns_to_spanish
- 2026_03_10_100034_rename_encuesta_tables_start_with_encuesta

### chat
- 2026_02_26_100008_create_chat_tables
- 2026_03_10_100001_translate_chat_tables

### mensajeria
- 2026_03_05_100018_create_mensajeria_tables
- 2026_03_10_100002_translate_messaging_tables

### voz (no Rafa; Rafa temas_voz se queda)
- 2026_02_26_100009_create_voz_tables
- 2026_03_10_100019_translate_tableu_tables
- 2026_03_10_100021_translate_onesignal_tables

### notificaciones
- 2026_03_05_100016_create_notificaciones_tables
- 2026_03_10_100020_translate_notification_tables

### documentos
- 2026_02_26_100010_create_otros_tables (tiene employment_contracts_tokens, digital_documents, folders, employee_filters)
- 2026_03_05_100017_create_documentos_empresa_tables
- 2026_03_05_220001_rename_documentos_empresa_tables_to_spanish
- 2026_03_10_100022_translate_document_tables
- 2026_03_10_100023_translate_contract_tables
- 2026_03_10_100024_translate_folder_tables

### solicitudes
- 2026_03_05_100011_create_historiales_tables
- 2026_03_05_100012_create_solicitudes_catalogos_tables
- 2026_03_05_100013_create_solicitudes_and_approvals_tables
- 2026_03_05_220002_rename_solicitudes_catalogos_tables_to_spanish
- 2026_03_10_100011_translate_history_tables
- 2026_03_10_100012_translate_employee_history_tables
- 2026_03_10_100013_translate_status_history_tables

### capacitacion
- 2026_03_05_100019_create_capacitacion_tables
- 2026_03_10_100003_translate_capacitation_tables
- 2026_03_10_100004_translate_skills_tables

### financiero
- 2026_02_26_100007_create_financiero_tables
- 2026_03_10_100005_translate_financial_tables
- 2026_03_10_100006_translate_payroll_tables
- 2026_03_10_100007_translate_netpay_tables
- 2026_03_10_100008_translate_commission_tables
- 2026_03_10_100009_translate_services_tables
- 2026_03_10_100010_translate_excluded_tables

### reclutamiento
- 2026_03_10_100025_translate_recruitment_tables

### integraciones
- 2026_03_05_100020_create_integraciones_tables
- 2026_03_10_100015_translate_belvo_tables
- 2026_03_10_100016_translate_nubarium_tables
- 2026_03_10_100017_translate_nomipay_tables
- 2026_03_10_100018_translate_payment_tables

### adicionales / misc
- 2026_03_03_202035_create_imports_table
- 2026_03_03_202036_create_exports_table
- 2026_03_03_202037_create_failed_import_rows_table
- 2026_03_05_100021_create_adicionales_tables
- 2026_03_10_100014_translate_readmission_tables
- 2026_03_10_100026_translate_misc_tables
- 2026_03_10_100027_translate_user_tables
- 2026_03_10_100031_translate_remaining_business_tables

---

## DEPENDENCIA CRÍTICA

**100010_create_otros_tables** crea también **employment_contracts_tokens, digital_documents, folders, employee_filters**. Si la movemos, no existirán esas tablas (correcto para core). Pero **100010** no crea nada que use empleados/usuarios core; solo crea tablas de otros módulos. Por tanto se mueve entera a pospuestos/documentos.

---

## ORDEN TRAS LIMPIEZA

Las migraciones que queden en `database/migrations/` se ejecutarán en orden alfabético. No debe faltar ninguna dependencia: empleados → empresas; usuarios → roles; empresa_user → usuarios, empresas; etc. Todas las de Rafa y las de auth/roles/usuarios/empleados permanecen.
