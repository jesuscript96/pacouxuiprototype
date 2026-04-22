# Verificación: tablas en inglés y necesidad de traducción

Regla del proyecto: **español** para tablas/columnas de negocio; **inglés** solo para términos técnicos, convenciones Laravel y nombres de paquetes.

---

## Resumen por tabla

| Tabla que ves | ¿Traducir? | Nombre final / Nota |
|---------------|------------|---------------------|
| **employee_filters** | ✅ Sí | `filtros_empleado_guardados` |
| **acknowledgments** | ✅ Ya hecho | Migración 100030 → `reconocimientos` |
| **exports** | ✅ Sí | `exportaciones` + columnas a español |
| **failed_import_rows** | ❌ No | Técnico (errores de importación) |
| **failed_jobs** | ❌ No | Laravel Queue |
| **imports** | ✅ Sí | `importaciones` + columnas a español |
| **job_batches** | ❌ No | Laravel Queue |
| **jobs** | ❌ No | Laravel Queue |
| **migrations** | ❌ No | Laravel |
| **model_has_permissions** | ❌ No | Spatie Permission (convención paquete) |
| **model_has_roles** | ❌ No | Spatie Permission (convención paquete) |
| **nom35_sections** | ✅ Sí | `secciones_nom35` |
| **nom35_sections_responses** | ✅ Sí | `respuestas_secciones_nom35` |
| **oauth_*** | ❌ No | Laravel Passport / OAuth estándar |
| **password_reset_tokens** / **password_resets** | ❌ No | Laravel auth |
| **permissions** | ❌ No | Spatie Permission (config del paquete) |
| **readmissions** | ✅ Sí | `reingresos` |
| **readmission_histories** | ✅ Ya hecho | Migración 100014 → `historiales_reingreso` |
| **role_has_permissions** | ❌ No | Spatie Permission (convención paquete) |
| **sessions** | ❌ No | Laravel auth |

---

## Detalle por tabla

### ✅ Traducir (negocio)

- **employee_filters**  
  Filtros guardados por usuario/empresa (nombre_filtro, criterios_json).  
  → Tabla: `filtros_empleado_guardados`. Columnas ya en español.

- **exports**  
  Exportaciones de datos (quién, archivo, filas, etc.).  
  → Tabla: `exportaciones`.  
  Columnas a español: `file_name` → `nombre_archivo`, `file_disk` → `disco_archivo`, `exporter` → `exportador`, `processed_rows` → `filas_procesadas`, `total_rows` → `filas_totales`, `successful_rows` → `filas_exitosas`.  
  Se dejan en inglés: `user_id`, `completed_at`, `id`, `timestamps`.

- **imports**  
  Importaciones de datos.  
  → Tabla: `importaciones`.  
  Columnas a español: `file_name` → `nombre_archivo`, `file_path` → `ruta_archivo`, `importer` → `importador`, `processed_rows` → `filas_procesadas`, `total_rows` → `filas_totales`, `successful_rows` → `filas_exitosas`.  
  Se dejan en inglés: `user_id`, `completed_at`, `id`, `timestamps`.

- **readmissions**  
  Reingresos de empleados (fecha_reingreso, motivo).  
  → Tabla: `reingresos`. Columnas ya en español.

- **nom35_sections**  
  Secciones de la norma NOM-35 (nombre, descripcion).  
  → Tabla: `secciones_nom35`. NOM35 se mantiene como nombre de norma.

- **nom35_sections_responses**  
  Respuestas por sección NOM-35.  
  → Tabla: `respuestas_secciones_nom35`. Columnas ya en español (respuestas, puntaje, empleado_id, seccion_id).

### ❌ No traducir

- **failed_import_rows**  
  Filas fallidas de importación. Uso técnico/operativo; convención habitual en inglés.

- **failed_jobs**, **job_batches**, **jobs**  
  Tablas del sistema de colas de Laravel. No son dominio de negocio.

- **migrations**  
  Tabla interna de Laravel para el historial de migraciones.

- **model_has_permissions**, **model_has_roles**, **role_has_permissions**  
  Convención del paquete Spatie Permission. Cambiarlas exigiría tocar config y posiblemente código del paquete.

- **permissions**  
  Tabla de Spatie; el nombre viene de `config('permission.table_names')`. Mantener en inglés evita romper el paquete.

- **oauth_***  
  Estándar OAuth y tablas de Laravel Passport. Términos técnicos globales.

- **password_reset_tokens**, **password_resets**, **sessions**  
  Autenticación y sesión de Laravel. Convenciones del framework.

---

## Migración aplicada

La migración **`2026_03_10_100031_translate_remaining_business_tables.php`** renombra solo las tablas (y columnas) marcadas como “Traducir” en esta verificación. El resto se deja en inglés por ser técnico o de paquetes.
