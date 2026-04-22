# Reporte de compatibilidad: BD dev vs Fase 1 (tablas de Rafa)

Generado a partir de `dev_db_analyze.json` (BD: **paco_dev_db**).

---

## 1. Tablas existentes en dev

Total: **32** tablas.

| Tabla | Columnas | FKs | Índices |
|-------|----------|-----|---------|
| alias_tipo_transacciones | 6 | 1 | 2 |
| cache | 3 | 0 | 2 |
| cache_locks | 3 | 0 | 2 |
| centro_de_costos | 12 | 0 | 1 |
| comisiones_rangos | 9 | 1 | 2 |
| configuracion_app | 13 | 0 | 1 |
| configuracion_retencion_nominas | 9 | 1 | 2 |
| empresas | 58 | 2 | 3 |
| empresas_centros_costos | 5 | 2 | 3 |
| empresas_notificaciones_incluidas | 5 | 2 | 3 |
| empresas_productos | 9 | 2 | 3 |
| empresas_razones_sociales | 5 | 2 | 3 |
| empresas_reconocimientos | 5 | 2 | 3 |
| empresas_temas_voz_colaboradores | 5 | 2 | 3 |
| failed_jobs | 7 | 0 | 2 |
| frecuencia_notificaciones | 7 | 1 | 2 |
| industrias | 5 | 0 | 1 |
| job_batches | 10 | 0 | 1 |
| jobs | 7 | 0 | 2 |
| logs | 7 | 0 | 1 |
| migrations | 3 | 0 | 1 |
| notificaciones_incluidas | 6 | 0 | 1 |
| password_reset_tokens | 3 | 0 | 1 |
| productos | 6 | 0 | 1 |
| quincenas_personalizadas | 6 | 1 | 2 |
| razones_encuesta_salida | 5 | 1 | 2 |
| razones_sociales | 13 | 0 | 1 |
| reconocimientos | 9 | 0 | 1 |
| sessions | 6 | 0 | 3 |
| sub_industrias | 6 | 1 | 2 |
| temas_voz_colaboradores | 7 | 0 | 1 |
| users | 8 | 0 | 2 |

---

## 2. Tablas de Rafa requeridas por Fase 1

Nuestras migraciones referencian las siguientes tablas. Deben existir y tener columna `id` (BIGINT UNSIGNED) para las FK.

### empresas

**Estado:** Existe.

**Columnas (DESCRIBE):**

| Campo | Tipo | Null | Key | Default | Extra |
|-------|------|------|-----|---------|-------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment |
| nombre | varchar(255) | NO | - | NULL |  |
| nombre_contacto | varchar(255) | NO | - | NULL |  |
| email_contacto | varchar(255) | NO | - | NULL |  |
| telefono_contacto | varchar(255) | NO | - | NULL |  |
| movil_contacto | varchar(255) | NO | - | NULL |  |
| industria_id | bigint unsigned | NO | MUL | NULL |  |
| sub_industria_id | bigint unsigned | NO | MUL | NULL |  |
| email_facturacion | varchar(255) | NO | - | NULL |  |
| fecha_inicio_contrato | date | NO | - | NULL |  |
| fecha_fin_contrato | date | NO | - | NULL |  |
| num_usuarios_reportes | int | NO | - | NULL |  |
| activo | tinyint(1) | NO | - | NULL |  |
| fecha_activacion | datetime | YES | - | NULL |  |
| nombre_app | varchar(255) | YES | - | NULL |  |
| link_descarga_app | varchar(255) | YES | - | NULL |  |
| app_android_id | varchar(255) | YES | - | NULL |  |
| app_ios_id | varchar(255) | YES | - | NULL |  |
| app_huawei_id | varchar(255) | YES | - | NULL |  |
| color_primario | varchar(255) | YES | - | NULL |  |
| color_secundario | varchar(255) | YES | - | NULL |  |
| color_terciario | varchar(255) | YES | - | NULL |  |
| color_cuarto | varchar(255) | YES | - | NULL |  |
| logo_url | varchar(255) | YES | - | NULL |  |
| tipo_comision | enum('PERCENTAGE','FIXED_AMOUNT','MIXED') | YES | - | NULL |  |
| comision_bisemanal | decimal(10,2) | NO | - | NULL |  |
| comision_mensual | decimal(10,2) | NO | - | NULL |  |
| comision_quincenal | decimal(10,2) | NO | - | NULL |  |
| comision_semanal | decimal(10,2) | NO | - | NULL |  |
| tiene_pagos_catorcenales | tinyint(1) | NO | - | NULL |  |
| fecha_proximo_pago_quincenal | date | YES | - | NULL |  |
| tiene_sub_empresas | tinyint(1) | NO | - | NULL |  |
| comision_gateway | decimal(10,2) | NO | - | NULL |  |
| transacciones_con_imss | tinyint(1) | NO | - | NULL |  |
| validar_cuentas_automaticamente | tinyint(1) | NO | - | NULL |  |
| tiene_analiticas_por_ubicacion | tinyint(1) | NO | - | NULL |  |
| version_android | varchar(255) | YES | - | NULL |  |
| version_ios | varchar(255) | YES | - | NULL |  |
| tiene_limite_de_sesiones | tinyint(1) | NO | - | NULL |  |
| tiene_firma_nubarium | tinyint(1) | NO | - | NULL |  |
| enviar_boletin | tinyint(1) | NO | - | NULL |  |
| permitir_encuesta_salida | tinyint(1) | NO | - | NULL |  |
| configuracion_app_id | int | YES | - | NULL |  |
| activar_finiquito | tinyint(1) | NO | - | NULL |  |
| url_finiquito | varchar(255) | YES | - | NULL |  |
| domiciliación_via_api | tinyint(1) | NO | - | NULL |  |
| ha_firmado_nuevo_contrato | tinyint(1) | NO | - | NULL |  |
| vigencia_mensajes_urgentes | int | YES | - | NULL |  |
| permitir_notificaciones_felicitaciones | tinyint(1) | NO | - | NULL |  |
| segmento_notificaciones_felicitaciones | enum('COMPANY','LOCATION') | YES | - | NULL |  |
| permitir_retenciones | tinyint(1) | NO | - | NULL |  |
| dias_vencidos_retencion | int | NO | - | NULL |  |
| pertenece_pepeferia | tinyint(1) | NO | - | NULL |  |
| tipo_registro | varchar(255) | YES | - | NULL |  |
| descargar_cursos | tinyint(1) | NO | - | NULL |  |
| deleted_at | timestamp | YES | - | NULL |  |
| created_at | timestamp | YES | - | NULL |  |
| updated_at | timestamp | YES | - | NULL |  |

**Foreign keys (salientes):**

- `industria_id` → industrias.id
- `sub_industria_id` → sub_industrias.id

**Índices:**

- PRIMARY (id) UNIQUE
- empresas_industria_id_foreign (industria_id)
- empresas_sub_industria_id_foreign (sub_industria_id)

**Compatibilidad FK Fase 1:** La columna `id` existe (tipo: bigint unsigned). Nuestras migraciones usan `foreignId(...)->constrained('empresas')` → correcto.

### departamentos

**Estado:** NO existe en la BD de dev. Las migraciones de Fase 1 que referencian esta tabla fallarán hasta que Rafa la cree.

**Acción:** Esperar a que Rafa suba el código o crear la tabla en dev con al menos `id` (bigint unsigned, PK).

### puestos

**Estado:** NO existe en la BD de dev. Las migraciones de Fase 1 que referencian esta tabla fallarán hasta que Rafa la cree.

**Acción:** Esperar a que Rafa suba el código o crear la tabla en dev con al menos `id` (bigint unsigned, PK).

### bancos

**Estado:** NO existe en la BD de dev. Las migraciones de Fase 1 que referencian esta tabla fallarán hasta que Rafa la cree.

**Acción:** Esperar a que Rafa suba el código o crear la tabla en dev con al menos `id` (bigint unsigned, PK).

### temas_voz

**Estado:** NO existe en la BD de dev. Las migraciones de Fase 1 que referencian esta tabla fallarán hasta que Rafa la cree.

**Acción:** Esperar a que Rafa suba el código o crear la tabla en dev con al menos `id` (bigint unsigned, PK).

### productos

**Estado:** Existe.

**Columnas (DESCRIBE):**

| Campo | Tipo | Null | Key | Default | Extra |
|-------|------|------|-----|---------|-------|
| id | bigint unsigned | NO | PRI | NULL | auto_increment |
| nombre | varchar(255) | NO | - | NULL |  |
| descripcion | varchar(255) | YES | - | NULL |  |
| deleted_at | timestamp | YES | - | NULL |  |
| created_at | timestamp | YES | - | NULL |  |
| updated_at | timestamp | YES | - | NULL |  |

**Índices:**

- PRIMARY (id) UNIQUE

**Compatibilidad FK Fase 1:** La columna `id` existe (tipo: bigint unsigned). Nuestras migraciones usan `foreignId(...)->constrained('productos')` → correcto.

---

## 3. Resumen de compatibilidad

- **Tablas faltantes en dev:** departamentos, puestos, bancos, temas_voz.
- Ejecutar las migraciones de Fase 1 en esta BD fallará hasta que existan.

---

*Reporte generado por `php artisan dev:db-report`. Actualizar tras volver a ejecutar `dev:db-analyze` si Rafa cambia la BD.*
