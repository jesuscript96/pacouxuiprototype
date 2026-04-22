# Comprobación final: orden de migraciones para Rafa

Cuando Rafa ejecute `php artisan migrate`, las migraciones se ejecutan **en orden alfabético por nombre de archivo**. Este documento confirma que el orden es correcto y que no hay sobrescrituras.

---

## 1. Orden de ejecución (resumido)

| Fase | Migraciones | Qué hace |
|------|-------------|----------|
| **Laravel base** | `0001_01_01_*` | users, cache, jobs |
| **Rafa / catálogos** | `2026_02_24_*` … `2026_02_26_*` | industrias, empresas, empleados, **reconocimientos** (tabla de Rafa), bancos, etc. |
| **App** | `2026_03_02_*` … `2026_03_06_*` | permisos, imports/exports, encuestas, **acknowledgments** (módulo app), notificaciones, etc. |
| **Renombres 03-05** | `2026_03_05_220000/220001/220002` | encuestas, documentos empresa, solicitudes → español |
| **Traducción 03-10** | `2026_03_10_100001` … `2026_03_10_100031` | chat, mensajería, capacitación, … reconocimientos app, resto negocio |

Las de **traducción (2026_03_10_*)** van **siempre después** de todas las que crean tablas. Ninguna migración posterior vuelve a crear tablas en inglés que nuestras migraciones hayan renombrado.

---

## 2. Conflicto evitado: dos tablas “reconocimientos”

- **`2026_02_25_214718_create_reconocimientos_table`** crea la tabla **`reconocimientos`** (catálogo de Rafa).
- **`2026_03_05_100015_create_reconocimientos_tables`** crea **`acknowledgments`** (módulo de reconocimientos de la app).

Si renombráramos `acknowledgments` → `reconocimientos`, chocaría con la tabla de Rafa. Por eso la migración **`2026_03_10_100030_translate_acknowledgment_tables`** renombra:

- `acknowledgments` → **`catalogo_reconocimientos`** (no a `reconocimientos`).
- `acknowledgment_company` → `reconocimientos_empresa`
- `acknowledgment_shippings` → `envios_reconocimiento`
- `acknowledgment_high_employee` → `empleado_reconocimiento`

Así quedan:

- **`reconocimientos`**: tabla de Rafa (2026_02_25_214718). **Es la única tabla de catálogo de reconocimientos.**
- La migración **`2026_03_10_100033_use_rafa_reconocimientos_drop_duplicate_catalog`** elimina el catálogo duplicado de la app (`catalogo_reconocimientos`) y hace que `reconocimientos_empresa` y `envios_reconocimiento` referencien **`reconocimientos`** (Rafa).

No se sobrescribe ni se duplica la tabla de Rafa. El módulo de la app (reconocimientos por empresa, envíos, empleado_reconocimiento) usa únicamente la tabla **reconocimientos** de Rafa.

---

## 3. Idempotencia

Las migraciones de traducción usan comprobaciones como:

- `Schema::hasTable('nombre_ingles') && !Schema::hasTable('nombre_espanol')` antes de renombrar.
- `Schema::hasColumn(...)` antes de renombrar columnas.

Si una tabla ya está en español (por un run anterior o por otra migración), no se vuelve a renombrar. Eso evita errores si se ejecuta dos veces o en distintos entornos.

---

## 4. Resumen para Rafa

- **Catálogos de Rafa:** “Catálogos de Rafa” = **todo lo que Rafa tenía en Catálogos Admin**. No se quita nada de lo que Rafa tenía; las migraciones de empresas_centros_costos, configuracion_app, comision_rangos, quincenas_personalizadas, alias_tipo_transacciones y configuracion_retencion_nominas están en CORE junto con el resto de tablas de Rafa.
- **Orden:** Correcto. Primero se crean todas las tablas (incluidas las de Rafa y las de la app), luego las de renombre 03-05, luego las de traducción 03-10.
- **Sobrescrituras:** No. Ninguna migración posterior crea de nuevo tablas como `acknowledgments`, `chat_rooms`, etc., que ya se renombran en 2026_03_10_*.
- **Tabla `reconocimientos`:** Es la de Rafa y es la única. La migración 100033 elimina el catálogo duplicado de la app y deja que el módulo use solo `reconocimientos` (Rafa).
- **Ejecución:** `php artisan migrate` es suficiente; no hace falta ningún paso extra.
