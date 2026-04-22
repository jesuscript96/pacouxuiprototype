# Verificación de referencias a tablas antiguas

**Fecha:** 2026-03  
**Contexto:** Tras la normalización de traducciones (encuestas, documentos empresa, solicitudes), se ha buscado en todo el proyecto referencias a los nombres antiguos de tablas/columnas.

---

## 1. Resumen

- **Total referencias encontradas en código de aplicación:** **0**
- **Por tipo de archivo:** Ninguna en modelos, controladores, recursos Filament, vistas, rutas, seeders, tests ni config.
- **En migraciones:** Solo en archivos que deben usar esos nombres (creación inicial o renombrado); **correcto**.
- **En documentación:** Aparecen nombres antiguos en docs de contexto/legacy como descripción histórica; **no es código ejecutable**.

---

## 2. Referencias encontradas por categoría

### Modelos

| Archivo | Línea | Referencia | Acción necesaria |
|---------|-------|------------|------------------|
| — | — | Ninguna | Ninguna |

No existen modelos que usen `$table = 'survey_*'`, `company_files`, `request_types`, etc.

### Controladores

| Archivo | Línea | Referencia | Acción necesaria |
|---------|-------|------------|------------------|
| — | — | Ninguna | Ninguna |

### Recursos Filament

| Archivo | Línea | Referencia | Acción necesaria |
|---------|-------|------------|------------------|
| — | — | Ninguna | Ninguna |

No hay SurveyResource, RequestResource ni recursos que referencien las tablas antiguas.

### Vistas

| Archivo | Línea | Referencia | Acción necesaria |
|---------|-------|------------|------------------|
| — | — | Ninguna | Ninguna |

(No se encontraron vistas que referencien survey, request_types, company_file, etc.)

### Migraciones

Las únicas apariciones están en:

- **create_encuestas_tables** (100014): crea `survey_categories`, `surveys`, etc. → correcto.
- **create_documentos_empresa_tables** (100017): crea `company_files`, `company_folder`, etc. → correcto.
- **create_solicitudes_catalogos_tables** (100012): crea `request_types`, `request_status`, etc. → correcto.
- **create_solicitudes_and_approvals_tables** (100013): FKs a `request_types`, `approval_flow_stages`, etc. → correcto (las tablas existen con ese nombre hasta que corre la migración de renombrado).
- **rename_encuesta_tables_and_columns_to_spanish** (220000): referencia nombres antiguos para renombrarlos y en `down()` para revertir → correcto.
- **rename_documentos_empresa_tables_to_spanish** (220001): idem → correcto.
- **rename_solicitudes_catalogos_tables_to_spanish** (220002): idem → correcto.

**Conclusión:** No hay que modificar migraciones; el flujo create → rename es el esperado.

### Configuración y seeders

Ninguna referencia en `config/` ni en `database/seeders/`.

### Tests

Ninguna referencia en `tests/` a las tablas antiguas.

---

## 3. Referencias que no requieren corrección (falsos positivos / contexto)

- **public/index.php** — `Request::capture()` es la clase HTTP `Illuminate\Http\Request` de Laravel, no la tabla `requests`. No cambiar.
- **docs/** — En `ANALISIS_BD_LEGACY_PACO.md`, `ANALISIS_DETALLADO_TABLAS_LEGACY.md`, `fase2/tablas-faltantes-y-migraciones.md`, `ANALISIS_ORDEN_MIGRACIONES_DEVELOP.md` y `NORMALIZACION_TRADUCCIONES_MIGRACIONES.md` se citan nombres antiguos como contexto, legacy o listado de lo que crean las migraciones. No es código; opcionalmente se puede añadir en esos docs una nota tipo: “Estas tablas se renombran a español en las migraciones 220000–220002”.

---

## 4. Acciones correctivas requeridas

**En código de aplicación:** ninguna. No hay referencias obsoletas en modelos, controladores, recursos, vistas, rutas, seeders, tests ni config.

**Opcional en documentación:** en los documentos de análisis/legacy que listan tablas en inglés, se puede indicar que los nombres actuales en BD son los españoles (según `NORMALIZACION_TRADUCCIONES_MIGRACIONES.md`).

---

## 5. Verificación final

Tras la normalización, las migraciones se han ejecutado correctamente:

```bash
php artisan migrate:fresh --no-interaction
# Completado sin errores (71 migraciones)
```

Al crear en el futuro modelos para encuestas, solicitudes o documentos de empresa, usar las **tablas en español** (encuestas, categorias_encuesta, solicitudes, tipos_solicitud, archivos_empresa, etc.) según `docs/NORMALIZACION_TRADUCCIONES_MIGRACIONES.md` y `docs/REGLA_TRADUCCION_MIGRACIONES.md`.
