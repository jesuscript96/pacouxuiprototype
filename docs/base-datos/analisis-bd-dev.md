# Análisis BD dev y compatibilidad con Fase 1

Instrucciones para conectarte a la BD de dev (donde Rafa ya migró sus tablas), analizar la estructura y generar el reporte de compatibilidad con tus tablas de Fase 1.

---

## 1. Requisito: túnel SSH

La BD está detrás de SSH. Antes de cualquier comando, abre el túnel en una terminal y déjalo abierto:

```bash
ssh -L 3306:localhost:3306 pacodev@46.225.221.105
# Password SSH: (el que tengas en .env o el que te dio Rafa)
```

Con eso, `127.0.0.1:3306` redirige al MySQL del servidor de dev.

---

## 2. Analizar la BD de dev

Con el túnel abierto y el `.env` apuntando a la BD de dev (ya está configurado):

```bash
php artisan dev:db-analyze --output=storage/app/dev_db_analyze.json
```

- Si la conexión falla: revisa que el túnel esté abierto y que `DB_*` en `.env` coincida con las credenciales del servidor.
- Si funciona: se crea `storage/app/dev_db_analyze.json` con todas las tablas, columnas, FKs e índices.

---

## 3. Generar el reporte de compatibilidad

Cuando el JSON exista:

```bash
php artisan dev:db-report
```

Eso genera/actualiza el reporte en **`docs/base-datos/reporte-compatibilidad-rafa.md`** con:

- Listado de tablas en dev
- Para cada tabla de Rafa que nosotros referenciamos: DESCRIBE, FKs, índices
- Comparación con lo que esperan nuestras migraciones (tipos, nombres de columnas referenciadas)
- Incidencias y recomendaciones

---

## 4. Tablas de Rafa que afectan a Fase 1

Nuestras migraciones referencian estas tablas (deben existir en dev para que nuestras migraciones corran):

| Tabla (Rafa) | Usada en nuestras tablas |
|--------------|---------------------------|
| **empresas** | empleados, usuarios, chat_rooms, payroll_withholding_configs, digital_documents, folders, employee_filters |
| **departamentos** | usuarios |
| **puestos** | usuarios |
| **bancos** | cuentas_empleado |
| **temas_voz** | usuario_tema_voz, voces_empleado |
| **productos** | empleado_producto |

En el reporte habrá que comprobar:

- Que existan esas tablas.
- Que la columna referenciada sea la esperada (p. ej. `empresas.id`, `departamentos.id`, etc.).
- Que tipos/longitudes no generen conflictos (p. ej. `id` como BIGINT UNSIGNED).

---

## 5. Estructura del JSON de análisis

`dev_db_analyze.json` tiene esta forma:

```json
{
  "database": "paco_dev_db",
  "tables": {
    "nombre_tabla": {
      "columns": [ { "field", "type", "null", "key", "default", "extra" } ],
      "foreign_keys": [ { "column", "referenced_table", "referenced_column" } ],
      "indexes": [ { "name", "column", "unique" } ]
    }
  }
}
```

Puedes abrirlo para revisar a mano o usar `dev:db-report` para el markdown.

---

## 6. Si Rafa aún no sube código

Cuando tengas el reporte y sepas qué tablas/columnas hay en dev, puedes:

- Ajustar los stubs de modelos (Empresa, Departamento, Puesto, Banco, TemaVoz) con `$fillable` y tablas correctas si Rafa usa nombres distintos.
- Si alguna tabla de Rafa tiene otro nombre (ej. `catalogos_bancos` en lugar de `bancos`), habrá que adaptar la migración (nombre de tabla en `constrained()`) cuando Rafa suba el código o documentarlo en el reporte.

---

## Ver también

- [Reporte de compatibilidad Rafa](reporte-compatibilidad-rafa.md) — resultado de `php artisan dev:db-report`
