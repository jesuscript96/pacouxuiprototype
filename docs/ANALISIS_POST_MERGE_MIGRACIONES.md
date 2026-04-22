# ANÁLISIS POST-MERGE: CONFLICTOS Y RECOMENDACIONES

**Rama mergeada:** Rafa (catálogos y empresas) + test (Fase 1, Fase 2, CRUD usuarios)  
**Fecha de análisis:** 2026-03-04  
**Objetivo:** Identificar problemas que impidan ejecutar `php artisan migrate` en orden.

---

## 1. RESUMEN EJECUTIVO

| Concepto | Valor |
|----------|--------|
| **Estado general** | ❌ **No migrable** sin correcciones |
| **Conflictos críticos** | 5 |
| **Conflictos menores** | 4 |
| **Recomendación principal** | Reordenar migraciones Fase 2 (2026_02_23_*) y asegurar que tablas base (empresas, empleados, usuarios, departamentos, puestos, bancos, temas_voz) existan antes de las que las referencian. Resolver duplicado de tabla `bancos`. |

---

## 2. CONFLICTOS DETECTADOS

### 2.1 Conflictos críticos (impiden migración)

| # | Tipo | Descripción | Solución |
|---|------|-------------|----------|
| 1 | **Orden migraciones** | Las migraciones de Fase 2 (`2026_02_23_100011` … `2026_02_23_100021`) se ejecutan **antes** que las de Rafa (`2026_02_24_*`, `2026_02_25_*`) y que Fase 1 (`2026_02_26_100001` …). Dependen de `empresas`, `empleados`, `usuarios`, `departamentos`, `puestos`, `ubicaciones`, `regiones`, `areas`, `razones_sociales`. | Renombrar migraciones Fase 2 a una fecha **posterior** a Fase 1 y a `tablas_faltantes` / `tablas_rafa_locales`, p. ej. `2026_03_05_100011_…` … `2026_03_05_100021_…`. |
| 2 | **FK inexistente** | `usuarios` (2026_02_26_100003) tiene `departamento_id` → `departamentos` y `puesto_id` → `puestos`. Esas tablas se crean en `2026_03_02_100000_create_tablas_faltantes`, que corre **después**. | Mover la creación de `departamentos` y `puestos` a una migración con fecha anterior a `2026_02_26_100003`, o retrasar la migración de `usuarios` (p. ej. después de `tablas_faltantes`). |
| 3 | **FK inexistente** | `cuentas_empleado` (2026_02_26_100007) tiene `banco_id` → `bancos`. La tabla `bancos` se crea en `2026_03_02_100000` (tablas_faltantes), que corre **después**. | Crear `bancos` antes de `2026_02_26_100007` (p. ej. en una migración 2026_02_26_100000_create_bancos) o mover financiero a una fecha posterior a `tablas_faltantes`. |
| 4 | **FK inexistente** | `usuario_tema_voz` y `voces_empleado` (2026_02_26_100009) referencian `temas_voz`. Rafa crea `temas_voz_colaboradores`, no `temas_voz`. La tabla `temas_voz` solo se crea en `2026_03_02_090000_create_tablas_rafa_locales` (y solo si no existe), que corre **después**. | Crear `temas_voz` antes de 2026_02_26_100009 (p. ej. migración 2026_02_26_100008_ensure_temas_voz) o retrasar la migración de voz para que corra después de `tablas_rafa_locales`. Alternativa: unificar concepto y usar solo `temas_voz_colaboradores` (cambio de código y FKs). |
| 5 | **Tabla duplicada** | `bancos` se crea en dos migraciones: `2026_03_02_100000_create_tablas_faltantes` y `2026_03_04_171552_create_bancos_table`. La segunda falla con “Table 'bancos' already exists”. Estructuras distintas (una con `codigo` nullable string, otra con `codigo` integer y `comision`). | Dejar una sola migración que cree `bancos`. Eliminar la creación de `bancos` de una de las dos (recomendado: quitar de `tablas_faltantes` y usar solo `create_bancos_table` si se quiere la estructura con comisión; o al revés). Ajustar la que se mantenga para que tenga las columnas que usa el dominio. |

### 2.2 Conflictos menores (no bloqueantes una vez orden correcto)

| # | Tipo | Descripción | Recomendación |
|---|------|-------------|----------------|
| 1 | Nomenclatura | Rafa: tabla `reconocimientos` (catálogo). Nosotros: tabla `acknowledgments` + `acknowledgment_company`, etc. Son tablas distintas; Rafa tiene además `empresas_reconocimientos` (pivot). | Mantener ambas: `reconocimientos` como catálogo de Rafa, `acknowledgments` como módulo de envíos/estado. En código, `acknowledgment_company.reconocimiento_id` apunta a `acknowledgments` (no a `reconocimientos`); si en negocio debe apuntar al catálogo de Rafa, habría que cambiar la FK a `reconocimientos`. |
| 2 | Modelo / tabla | `temas_voz` (nuestro) vs `temas_voz_colaboradores` (Rafa). Modelos `TemaVoz` (tabla `temas_voz`) y `TemaVozColaborador` (tabla `temas_voz_colaboradores`). | Si se mantienen las dos tablas: `tablas_rafa_locales` sigue creando `temas_voz` cuando no exista; nuestros modelos Voz/UsuarioTemaVoz siguen usando `TemaVoz`/`temas_voz`. Si se unifica solo en `temas_voz_colaboradores`, habría que cambiar FKs y modelos (VozEmpleado, UsuarioTemaVoz) a `TemaVozColaborador` y `tema_voz_colaborador_id`. |
| 3 | Typo en modelo | Clase `Reconocmiento` (falta 'ie') en `app/Models/Reconocmiento.php`. | Renombrar a `Reconocimiento` y actualizar referencias (p. ej. en `Empresa.php`). |
| 4 | Inconsistencia FK Rafa | En `empresas`, Rafa usa `$table->foreignId('industria_id')->constrained('industrias')->nullable();` — el orden correcto en Laravel es `->nullable()->constrained(...)` para que la columna sea nullable. | Corregir en la migración de empresas para evitar problemas en DB estricta. |

---

## 3. VERIFICACIÓN POR TABLA

### 3.1 Tablas de Rafa necesarias para nosotros

| Tabla Rafa | ¿Existe en migraciones Rafa? | Nuestra migración que la referencia | Estado |
|------------|------------------------------|-------------------------------------|--------|
| empresas | ✅ 2026_02_25_175836 | empleados, usuarios, chat_rooms, surveys, notifications, etc. | ✅ OK (se crea antes en orden si se corrige Fase 2) |
| productos | ✅ 2026_02_25_011219 | empleado_producto | ✅ OK |
| razones_sociales | ✅ 2026_02_25_181105 | business_names_histories (historiales) | ✅ OK |
| industrias / sub_industrias | ✅ 2026_02_24_* | empresas (Rafa) | ✅ OK |
| reconocimientos | ✅ 2026_02_25_214718 | empresas_reconocimientos (Rafa) | ✅ OK |
| temas_voz_colaboradores | ✅ 2026_02_25_220120 | Empresa ↔ TemaVozColaborador | ✅ OK (Rafa) |
| temas_voz | ❌ (Rafa no la crea) | usuario_tema_voz, voces_empleado | ❌ Solo la crea `tablas_rafa_locales` después → ver conflicto #4 |
| departamentos | ❌ (Rafa no la crea) | usuarios, department_histories | ❌ Creada en tablas_faltantes (después de usuarios) → conflicto #2 |
| puestos | ❌ (Rafa no la crea) | usuarios, position_histories | ❌ Idem |
| bancos | ❌ (Rafa no la crea) | cuentas_empleado | ❌ Creada en tablas_faltantes después → conflicto #3; además duplicado con create_bancos_table |
| ubicaciones | ❌ | location_histories | ❌ tablas_faltantes (después de Fase 2) |
| regiones | ❌ | region_histories | ❌ tablas_faltantes |
| centros_pago | ❌ | (empleados no la referencian en migración actual) | Creada en tablas_faltantes |
| areas | ❌ | area_histories | ❌ Solo en tablas_rafa_locales (2026_03_02_090000) |

### 3.2 Orden de ejecución actual (por nombre de archivo)

Laravel ejecuta por orden lexicográfico del nombre del archivo. Orden relevante:

```
0001_01_01_000000_create_users_table
0001_01_01_000001_create_cache_table
0001_01_01_000002_create_jobs_table
2026_02_23_100011_create_historiales_tables      ← Necesita: empleados, ubicaciones, areas, puestos, departamentos, razones_sociales, regiones → FALLA
2026_02_23_100012_create_solicitudes_catalogos_tables
2026_02_23_100013_create_solicitudes_and_approvals_tables  ← Necesita: empleados, usuarios, request_* → FALLA
2026_02_23_100014_create_encuestas_tables         ← Necesita: usuarios, empresas, empleados → FALLA
2026_02_23_100015_create_reconocimientos_tables   ← Necesita: empresas, empleados → FALLA
2026_02_23_100016_create_notificaciones_tables    ← Necesita: usuarios, empresas, empleados → FALLA
2026_02_23_100017_create_documentos_empresa_tables ← Necesita: empresas, folders, empleados, digital_documents → FALLA
2026_02_23_100018_create_mensajeria_tables        ← Necesita: usuarios, empresas, empleados → FALLA
2026_02_23_100019_create_capacitacion_tables      ← Necesita: empresas, usuarios, empleados → FALLA
2026_02_23_100020_create_integraciones_tables    ← Necesita: empleados, voces_empleado, messages → FALLA
2026_02_23_100021_create_adicionales_tables       ← Necesita: empleados, usuarios, empresas → FALLA
2026_02_24_230219_create_industrias_table
2026_02_24_230237_create_sub_industrias_table
2026_02_25_002716_create_logs_table
2026_02_25_011219_create_productos_table
2026_02_25_013432_create_centro_costos_table
2026_02_25_120000_add_workos_fields_to_users_table
2026_02_25_175836_create_empresas_table
2026_02_25_181105_create_razonsocial_table         ← razones_sociales
2026_02_25_194248_create_configuracion_apps_table
2026_02_25_200211_create_comision_rangos_table
2026_02_25_201504_create_quincenas_personalizadas_table
2026_02_25_204433_create_empresas_productos_table
2026_02_25_212639_create_empresas_notificaciones_incluidas_table
2026_02_25_213248_create_empresas_centros_costos_table
2026_02_25_214718_create_reconocimientos_table
2026_02_25_220120_create_tema_voz_colaboradores_table
2026_02_25_222523_create_razon_encuesta_salidas_table
2026_02_25_223031_create_alias_tipo_transaccions_table
2026_02_25_223811_create_frecuencia_notificaciones_table
2026_02_26_195003_create_configuracion_retencion_nominas_table
2026_02_26_100001_create_roles_permisos_tables
2026_02_26_100002_create_empleados_table            ← Necesita: empresas ✅
2026_02_26_100003_create_usuarios_table             ← Necesita: empresas ✅, departamentos ❌, puestos ❌
2026_02_26_100004_create_auth_pivots_and_2fa_tables
2026_02_26_100005_create_oauth_tables
2026_02_26_100006_create_empleado_producto_and_filtros_tables
2026_02_26_100007_create_financiero_tables          ← Necesita: empleados ✅, bancos ❌
2026_02_26_100008_create_chat_tables
2026_02_26_100009_create_voz_tables                 ← Necesita: usuarios ✅, empleados ✅, temas_voz ❌
2026_02_26_100010_create_otros_tables
2026_03_02_090000_create_tablas_rafa_locales        ← Crea empresas/productos/temas_voz/razones_sociales/areas si no existen
2026_03_02_100000_create_tablas_faltantes          ← Crea bancos, departamentos, puestos, ubicaciones, regiones, centros_pago
2026_03_02_185249_create_empresas_reconocimientos_table
2026_03_03_* (Spatie, imports/exports, etc.)
2026_03_04_171552_create_bancos_table               ← Duplicado con tablas_faltantes
```

---

## 4. ORDEN DE MIGRACIONES RECOMENDADO

1. **Laravel base:** cache, jobs, users (0001_01_01_*).
2. **Rafa catálogos y empresas:** 2026_02_24_* y 2026_02_25_* (industrias, sub_industrias, logs, productos, centro_costos, empresas, razones_sociales, configuracion_app, comisiones_rangos, quincenas_personalizadas, empresas_productos, notificaciones_incluidas, empresas_centros_costos, reconocimientos, temas_voz_colaboradores, razon_encuesta_salida, alias_tipo_transacciones, frecuencia_notificaciones, configuracion_retencion_nominas).
3. **Tablas “locales” que dependen de Rafa:** ejecutar `2026_03_02_090000_create_tablas_rafa_locales` para crear `temas_voz` (y otras) si no existen — **pero con fecha anterior** a la migración de voz (p. ej. renombrar a `2026_02_25_220121_create_tablas_rafa_locales` o crear una migración específica `2026_02_26_100008_ensure_temas_voz` que solo cree `temas_voz` si no existe).
4. **Tablas faltantes (catálogos nuestros):** `departamentos`, `puestos`, `bancos`, `ubicaciones`, `regiones`, `centros_pago` — deben existir **antes** de `usuarios` y de `cuentas_empleado`. Opción: migración `2026_02_26_100000_create_catalogos_faltantes` que cree solo estas tablas (y no duplique `bancos`).
5. **Fase 1 (roles, empleados, usuarios, auth, oauth, empleado_producto, financiero, chat, voz, otros):** 2026_02_26_100001 … 2026_02_26_100010.
6. **Fase 2 (historiales, solicitudes, encuestas, reconocimientos/acknowledgments, notificaciones, documentos, mensajería, capacitación, integraciones, adicionales):** renombrar a algo como `2026_03_05_100011_…` … `2026_03_05_100021_…` para que corran después de Fase 1 y de tablas_faltantes/tablas_rafa_locales.
7. **Rafa empresas_reconocimientos:** 2026_03_02_185249.
8. **Resto (Spatie, imports, bancos si se mantiene una sola creación):** 2026_03_03_*, 2026_03_04_*.

---

## 5. ACCIONES CORRECTIVAS REQUERIDAS

### Inmediatas (antes de migrar)

- [ ] **Reordenar Fase 2:** Renombrar `2026_02_23_100011` … `2026_02_23_100021` a fechas posteriores (p. ej. `2026_03_05_100011` … `2026_03_05_100021`) para que se ejecuten después de Fase 1 y de las tablas de soporte.
- [ ] **Crear catálogos antes de usuarios y financiero:** Asegurar que `departamentos`, `puestos`, `bancos`, `ubicaciones`, `regiones`, `centros_pago` (y si aplica `areas`) se creen en una migración con fecha anterior a `2026_02_26_100003` y `2026_02_26_100007`. Opciones: (a) Nueva migración `2026_02_26_100000_create_catalogos_faltantes` que cree solo esas tablas, o (b) mover el bloque de creación de esas tablas desde `tablas_faltantes` a esa migración anterior y dejar en `tablas_faltantes` solo lo que no genere conflicto.
- [ ] **Asegurar tabla `temas_voz` antes de voz:** Crear `temas_voz` en una migración con fecha anterior a `2026_02_26_100009` (o retrasar la migración de voz). La opción más simple es una migración tipo `2026_02_26_100008_ensure_temas_voz` que haga `Schema::create('temas_voz', ...)` solo si `!Schema::hasTable('temas_voz')`, manteniendo compatibilidad con `tablas_rafa_locales`.
- [ ] **Eliminar duplicado de `bancos`:** Quitar la creación de `bancos` de una de las dos migraciones (`tablas_faltantes` o `create_bancos_table`) y unificar estructura (nombre, codigo, comision, soft deletes según se use en la app).
- [ ] **Opcional:** Corregir en la migración de `empresas` de Rafa el uso de `->nullable()->constrained('industrias')` para industria_id/sub_industria_id.

### A corto plazo (después de migrar)

- [ ] Renombrar modelo `Reconocmiento` → `Reconocimiento` y actualizar referencias.
- [ ] Decidir si `acknowledgment_company.reconocimiento_id` debe apuntar a `reconocimientos` (catálogo de Rafa) en lugar de `acknowledgments` y, si sí, añadir migración que cambie la FK y el código.
- [ ] Revisar si se unifica todo en `temas_voz_colaboradores` y se deja de usar `temas_voz` en voz (cambio de FKs y modelos).

---

## 6. MODELOS Y RELACIONES

- **TemaVoz** (`temas_voz`): Usado por `VozEmpleado`, `UsuarioTemaVoz`. La tabla debe existir antes de la migración de voz; actualmente la crea solo `tablas_rafa_locales` (más tarde).
- **TemaVozColaborador** (`temas_voz_colaboradores`): De Rafa; pivot `empresas_temas_voz_colaboradores`. Sin conflicto de nombre con `temas_voz`.
- **Reconocmiento** (typo) → tabla `reconocimientos`. Relación con empresas vía `empresas_reconocimientos`. OK.
- **Empresa:** Relaciones con reconocimientos y temas_voz_colaboradores correctas; depende de que existan las tablas de Rafa.
- **Empleado / Usuario:** Dependen de empresas; Usuario además de departamentos y puestos — deben crearse después de esos catálogos.

---

## 7. CONCLUSIÓN

- **¿Se puede migrar ahora?** **No.** Sin los cambios de orden y de creación de tablas (departamentos, puestos, bancos, temas_voz) y sin eliminar el duplicado de `bancos`, la migración fallará en la primera migración que use una tabla aún no creada (historiales o, si se retrasa Fase 2, en usuarios/financiero/voz).
- **Riesgos principales:** Orden de ejecución por fecha; FKs a tablas creadas en migraciones posteriores; tabla `bancos` creada dos veces con distinta estructura.
- **Próximo paso recomendado:** Aplicar las acciones correctivas inmediatas (reordenar Fase 2, adelantar creación de catálogos y de `temas_voz`, unificar creación de `bancos`), luego ejecutar `php artisan migrate` en un entorno de prueba y corregir cualquier fallo residual (p. ej. industria_id nullable en empresas).
