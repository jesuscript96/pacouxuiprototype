# Análisis post-merge: rama de Rafa en test-rama-rafa

Análisis de los cambios de Rafa tras mergear su rama en la rama local, para identificar tablas nuevas, solapamientos, orden de migraciones y acciones recomendadas.

---

## 1. Resumen ejecutivo

- **Rafa aporta:** migraciones de catálogos y configuración (empresas, productos, razones_sociales, temas_voz_colaboradores, reconocimientos, centros de costo, notificaciones, comisiones, quincenas, etc.) y modelos Eloquent asociados.
- **Nosotros aportamos:** Fase 1 (empleados, usuarios, auth, financiero, chat, voz, otros), Fase 2 (historiales, solicitudes, encuestas, reconocimientos *acknowledgments*, notificaciones, documentos, mensajería, capacitación, integraciones, adicionales), tablas faltantes (bancos, departamentos, puestos, ubicaciones, regiones, centros_pago) y tablas Rafa locales (temas_voz, areas cuando no existan).
- **Conflictos directos:** ninguno que impida migrar. Hay **duplicidad conceptual** en reconocimientos (Rafa: `reconocimientos` / nosotros: `acknowledgments`) y en temas de voz (Rafa: `temas_voz_colaboradores` / nosotros: `temas_voz`).
- **Orden de migraciones:** el orden por fecha hace que primero corran las de Rafa (2026_02_24, 2026_02_25) y después las nuestras (2026_02_23 1000XX, 2026_03_02). Nuestra migración `090000_create_tablas_rafa_locales` solo crea tablas si no existen, así que no pisa nada de Rafa.

---

## 2. Migraciones de Rafa (listado)

| Migración | Tablas creadas | Dependencias |
|-----------|-----------------|--------------|
| 2026_02_24_230219 | industrias | — |
| 2026_02_24_230237 | sub_industrias | industrias |
| 2026_02_25_002716 | logs | — |
| 2026_02_25_011219 | productos | — |
| 2026_02_25_013432 | centro_de_costos | — |
| 2026_02_25_175836 | empresas | industrias, sub_industrias |
| 2026_02_25_181105 | razones_sociales, empresas_razones_sociales | empresas |
| 2026_02_25_194248 | configuracion_app | — |
| 2026_02_25_200211 | comisiones_rangos | empresas |
| 2026_02_25_201504 | quincenas_personalizadas | empresas |
| 2026_02_25_204433 | empresas_productos | empresas, productos |
| 2026_02_25_212639 | notificaciones_incluidas, empresas_notificaciones_incluidas | empresas |
| 2026_02_25_213248 | empresas_centros_costos | empresas, centro_de_costos |
| 2026_02_25_214718 | reconocimientos | — |
| 2026_02_25_220120 | temas_voz_colaboradores, empresas_temas_voz_colaboradores | empresas |
| 2026_02_25_222523 | razones_encuesta_salida | empresas |
| 2026_02_25_223031 | alias_tipo_transacciones | empresas |
| 2026_02_25_223811 | frecuencia_notificaciones | empresas |
| 2026_02_26_195003 | configuracion_retencion_nominas | empresas |
| 2026_03_02_185249 | empresas_reconocimientos | empresas, reconocimientos |

---

## 3. Estructura clave de Rafa

### 3.1 empresas

- **Migración:** `2026_02_25_175836_create_empresas_table.php`
- **Campos relevantes:** id, nombre, nombre_contacto, email_contacto, telefono_contacto, movil_contacto, industria_id, sub_industria_id, email_facturacion, fechas contrato, num_usuarios_reportes, **activo**, tipo_comision, comisiones (bisemanal, mensual, quincenal, semanal), comision_gateway, flags (tiene_pagos_catorcenales, transacciones_con_imss, validar_cuentas_automaticamente, etc.), softDeletes, timestamps.
- **Nuestras FKs:** empleados, usuarios, tablas_faltantes (departamentos, puestos, etc.), Fase 2 (surveys, notifications, messages, capacitations, …) referencian `empresas.id`. Compatible con esta estructura.

### 3.2 razones_sociales

- **Migración:** `2026_02_25_181105_create_razonsocial_table.php`
- **Estructura:** id, nombre, rfc, cp, calle, numero_exterior, numero_interior, colonia, alcaldia, estado, registro_patronal, timestamps. **Sin empresa_id**; la relación con empresas es por pivot `empresas_razones_sociales`.
- **Nuestro uso:** Fase 2 `business_names_histories` tiene `razon_social_id` → `razones_sociales`. La tabla existe y tiene `id`, no hay conflicto. Nuestra migración `090000` no crea `razones_sociales` si ya existe (Rafa va antes).

### 3.3 temas_voz_colaboradores

- **Migración:** `2026_02_25_220120_create_tema_voz_colaboradores_table.php`
- **Tablas:** `temas_voz_colaboradores` (id, nombre, descripcion, exclusivo_para_empresa, softDeletes, timestamps), `empresas_temas_voz_colaboradores` (empresa_id, tema_voz_colaborador_id).
- **Nuestro uso:** Nuestras tablas de voz (usuario_tema_voz, voces_empleado) referencian **temas_voz**, no `temas_voz_colaboradores`. La migración `090000_create_tablas_rafa_locales` crea **temas_voz** solo si no existe, así que seguimos teniendo una tabla `temas_voz` para nuestras FKs. En BD quedan dos catálogos: `temas_voz` (nuestro, para voz) y `temas_voz_colaboradores` (Rafa). Decisión pendiente: más adelante unificar (p. ej. apuntar nuestras FKs a `temas_voz_colaboradores` y dejar de crear `temas_voz`) o mantener ambos.

### 3.4 reconocimientos (Rafa) vs acknowledgments (nosotros)

- **Rafa:** `reconocimientos` (id, nombre, descripcion, es_enviable, es_exclusivo, menciones_necesarias, softDeletes, timestamps) y pivot `empresas_reconocimientos` (empresa_id, reconocimiento_id, es_enviable, menciones_necesarias).
- **Nosotros:** `acknowledgments`, `acknowledgment_company`, `acknowledgment_shippings`, `acknowledgment_high_employee` (nombres en inglés, flujo de envíos por empleado).
- **Conclusión:** Son dos diseños en paralelo. No hay conflicto de nombres ni de FKs. Si en negocio “reconocimientos” y “acknowledgments” son lo mismo, en Fase 3 habría que decidir: unificar en una sola fuente (p. ej. usar `reconocimientos` + `empresas_reconocimientos` de Rafa y adaptar nuestro flujo) o mantener ambos y mapear en aplicación.

### 3.5 productos

- **Rafa:** `productos` (id, nombre, descripcion, softDeletes, timestamps). Nuestra Fase 1 `empleado_producto` ya apunta a `productos`. Compatible.

### 3.6 Otras tablas de Rafa (sin choque con las nuestras)

- **centro_de_costos** ≠ nuestros **centros_pago** (tablas distintas).
- **configuracion_app**, **comisiones_rangos**, **quincenas_personalizadas**, **frecuencia_notificaciones**, **configuracion_retencion_nominas**, **alias_tipo_transacciones**, **razones_encuesta_salida**, **notificaciones_incluidas**, **empresas_notificaciones_incluidas**, **empresas_centros_costos**, **empresas_productos**: todas de Rafa; nosotros no las creamos, no hay conflicto.

---

## 4. Orden de ejecución de migraciones (resumen)

Orden aproximado por fecha/nombre:

1. Laravel base: users, cache, jobs, etc.
2. 2026_02_24: industrias, sub_industrias
3. 2026_02_25: productos, centro_de_costos, **empresas**, **razones_sociales**, empresas_razones_sociales, configuracion_app, comisiones_rangos, quincenas_personalizadas, empresas_productos, notificaciones_incluidas, empresas_notificaciones_incluidas, empresas_centros_costos, **reconocimientos**, **temas_voz_colaboradores**, empresas_temas_voz_colaboradores, razones_encuesta_salida, alias_tipo_transacciones, frecuencia_notificaciones, logs
4. 2026_02_26 (Rafa): configuracion_retencion_nominas
5. 2026_03_02 (Rafa): empresas_reconocimientos
6. 2026_03_02_090000: **tablas_rafa_locales** (solo crea temas_voz y areas si no existen; empresas, productos, razones_sociales ya existen)
7. 2026_03_02_100000: **tablas_faltantes** (bancos, departamentos, puestos, ubicaciones, regiones, centros_pago)
8. 2026_02_26_100001–100010: Fase 1
9. 2026_02_23_100011–100021: Fase 2

No se detectan ciclos ni FKs rotas por este orden.

---

## 5. Modelos de Rafa (referencia)

En `app/Models/` aparecen, entre otros:

- **Empresa** (empresas), **Producto**, **Razonsocial** (razones_sociales), **Industria**, **Subindustria**
- **TemaVozColaborador**, **Reconocmiento** (typo: falta “i” en Reconocimiento)
- **CentroCosto**, **ComisionRango**, **QuincenasPersonalizadas**, **ConfiguracionApp**, **ConfiguracionRetencionNomina**
- **NotificacionesIncluidas**, **FrecuenciaNotificaciones**, **AliasTipoTransaccion**, **RazonEncuestaSalida**
- **Log**

**Empresa** tiene relaciones con industria, subindustria, razonesSociales, productos (empresas_productos), notificacionesIncluidas, comisionesRangos, centrosCostos, reconocimientos (empresas_reconocimientos), temasVozColaboradores (empresas_temas_voz_colaboradores), etc., y eventos para Log en created/updated/deleted.

---

## 6. Posibles errores o mejoras en migraciones de Rafa

- **create_empresas_table:** `industria_id` y `sub_industria_id` usan `->constrained(...)->nullable()`. En Laravel lo habitual es `->nullable()->constrained(...)`. Revisar si en tu versión de Laravel el orden afecta (normalmente no).
- **create_reconocimientos_table (214718):** En `down()` se hace `Schema::dropIfExists('empresas_reconocimientos')` pero esa tabla la crea otra migración (185249). Al hacer rollback de 214718 se intentaría borrar una tabla que no creó esta migración. Mejor que en el `down()` de 214718 solo se haga `Schema::dropIfExists('reconocimientos')`; el `down()` de 185249 ya se encarga de `empresas_reconocimientos`.
- **Modelo Reconocmiento:** Nombre con typo; recomendable renombrar a `Reconocimiento` cuando se toque ese código.

---

## 7. Acciones recomendadas

| Prioridad | Acción |
|-----------|--------|
| Alta | Nada bloqueante para migrar. Ejecutar `php artisan migrate` en un entorno con la rama mergeada y comprobar que todo corre (Rafa + nuestras migraciones). |
| Media | Corregir `down()` de la migración de reconocimientos (214718) para no dropear `empresas_reconocimientos`. |
| Media | Decidir en Fase 3 si se unifica “temas de voz” en `temas_voz_colaboradores` (y se deja de crear `temas_voz`) o se mantienen ambos catálogos. |
| Media | Decidir en Fase 3 si reconocimientos (Rafa) y acknowledgments (nosotros) se unifican o se mantienen ambos y se mapean en lógica de negocio. |
| Baja | Renombrar modelo `Reconocmiento` → `Reconocimiento` y actualizar referencias (p. ej. en `Empresa::reconocimientos()`). |

---

## 8. Conclusión

El merge de la rama de Rafa en `test-rama-rafa` es coherente con nuestras Fases 1 y 2: las tablas de Rafa (empresas, productos, razones_sociales, temas_voz_colaboradores, reconocimientos, etc.) se crean antes que las nuestras y nuestras FKs siguen siendo válidas. La migración `090000` solo rellena lo que falta (temas_voz, areas) sin pisar tablas de Rafa. No hay conflictos de nombres ni de integridad referencial; las únicas “duplicidades” son de diseño (reconocimientos vs acknowledgments, temas_voz vs temas_voz_colaboradores) y se pueden resolver en Fase 3 con decisiones de negocio y, si aplica, pequeñas correcciones en migraciones o modelos.
