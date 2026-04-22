# Prueba de migraciones en local

Flujo para probar Fase 1 y Fase 2 en local cuando Rafa aún no ha subido sus tablas. Se crean versiones mínimas locales de las tablas de Rafa y luego se ejecutan todas nuestras migraciones.

---

## Verificación de tablas en BD (última comprobación)

**Base de datos:** `paco_dev_db`  
**Total tablas:** 32

**Tablas que ya existen (Rafa/sistema):**

| Tabla | ¿La usamos nosotros? |
|-------|----------------------|
| empresas | ✅ Sí (Fase 1 y 2) |
| productos | ✅ Sí (empleado_producto) |
| razones_sociales | ✅ Sí (Fase 2 historiales) |
| industrias | — |
| sub_industrias | — |
| temas_voz_colaboradores | ⚠️ En BD; nuestras migraciones usan **temas_voz** |
| centro_de_costos, comisiones_rangos, configuracion_app, etc. | — |
| cache, cache_locks, failed_jobs, jobs, logs, migrations, password_reset_tokens, sessions | Laravel/sistema |

**Tablas que NO existen y necesitamos:**

- **bancos** — la crea nuestra migración `tablas_faltantes`
- **departamentos** — la crea nuestra migración `tablas_faltantes`
- **puestos** — la crea nuestra migración `tablas_faltantes`
- **ubicaciones, regiones, centros_pago, areas** — tablas_faltantes (ubicaciones, regiones, centros_pago); Rafa local (areas)
- **temas_voz** — la BD tiene `temas_voz_colaboradores`; nuestra Fase 1 (voz) referencia `temas_voz`. Opciones: crear `temas_voz` con la migración Rafa local, o cambiar la migración de voz a `temas_voz_colaboradores`

Para volver a listar tablas: ejecutar un script que haga `SHOW TABLES` o usar Tinker (ver sección Verificaciones).

---

## Requisitos previos

- `.env` configurado con BD local.
- Antes de ejecutar, verificar qué tablas ya existen (p. ej. si Rafa ya subió algo):

```bash
php artisan tinker
>>> array_filter(DB::select('SHOW TABLES'), fn($t) => in_array(current((array)$t), ['empresas','productos','temas_voz','bancos','departamentos','puestos']));
```

---

## Orden de ejecución

### PASO 1: Tablas de Rafa (versión local)

Crea solo las tablas que **no existan** (empresas, productos, temas_voz, razones_sociales, areas). Cuando Rafa suba sus cambios, esta migración no fallará porque usa `Schema::hasTable()`.

```bash
php artisan migrate --path=database/migrations/2026_03_02_090000_create_tablas_rafa_locales.php
```

### PASO 2: Nuestras tablas faltantes

Crea bancos, departamentos, puestos, ubicaciones, regiones, centros_pago (requieren `empresas`).

```bash
php artisan migrate --path=database/migrations/2026_03_02_100000_create_tablas_faltantes.php
```

### PASO 3: Fase 1

```bash
php artisan migrate --path=database/migrations/2026_02_26_100001_create_roles_permisos_tables.php
php artisan migrate --path=database/migrations/2026_02_26_100002_create_empleados_table.php
php artisan migrate --path=database/migrations/2026_02_26_100003_create_usuarios_table.php
php artisan migrate --path=database/migrations/2026_02_26_100004_create_auth_pivots_and_2fa_tables.php
php artisan migrate --path=database/migrations/2026_02_26_100005_create_oauth_tables.php
php artisan migrate --path=database/migrations/2026_02_26_100006_create_empleado_producto_and_filtros_tables.php
php artisan migrate --path=database/migrations/2026_02_26_100007_create_financiero_tables.php
php artisan migrate --path=database/migrations/2026_02_26_100008_create_chat_tables.php
php artisan migrate --path=database/migrations/2026_02_26_100009_create_voz_tables.php
php artisan migrate --path=database/migrations/2026_02_26_100010_create_otros_tables.php
```

### PASO 4: Fase 2

```bash
php artisan migrate --path=database/migrations/2026_02_23_100011_create_historiales_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100012_create_solicitudes_catalogos_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100013_create_solicitudes_and_approvals_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100014_create_encuestas_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100015_create_reconocimientos_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100016_create_notificaciones_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100017_create_documentos_empresa_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100018_create_mensajeria_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100019_create_capacitacion_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100020_create_integraciones_tables.php
php artisan migrate --path=database/migrations/2026_02_23_100021_create_adicionales_tables.php
```

**Alternativa:** Ejecutar todas las migraciones pendientes de una vez (si el orden por fecha/nombre es correcto):

```bash
php artisan migrate
```

---

## Verificaciones post-migración

### 1. Total de tablas

```bash
php artisan tinker
>>> $tablas = DB::select('SHOW TABLES');
>>> echo "Total tablas: " . count($tablas) . "\n";
>>> foreach($tablas as $t) { echo current((array)$t) . "\n"; }
```

### 2. Tablas clave Fase 1

```php
>>> $claves_fase1 = ['roles', 'permisos', 'empleados', 'usuarios', 'cuentas_empleado', 'chat_rooms', 'voces_empleado'];
>>> foreach($claves_fase1 as $tabla) { echo Schema::hasTable($tabla) ? "✅ $tabla\n" : "❌ $tabla\n"; }
```

### 3. Tablas clave Fase 2

```php
>>> $claves_fase2 = ['location_histories', 'requests', 'surveys', 'acknowledgments', 'notifications', 'messages', 'capacitations'];
>>> foreach($claves_fase2 as $tabla) { echo Schema::hasTable($tabla) ? "✅ $tabla\n" : "❌ $tabla\n"; }
```

### 4. Foreign keys

```php
>>> $fks = DB::select("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
>>> echo "Total FKs: " . count($fks) . "\n";
```

### 5. Inserción de prueba

Estructura real de `empleados`: `id`, `empresa_id`, `numero_empleado`, `nombre`, `apellido_paterno`, `apellido_materno`, `email`, `telefono`, `celular`, `curp`, `rfc`, `fecha_nacimiento`, `fecha_ingreso`, `estado`, `timestamps`, `deleted_at`.

```php
>>> DB::table('empresas')->insert(['nombre' => 'Empresa Test', 'activa' => true, 'created_at' => now(), 'updated_at' => now()]);
>>> $empresa_id = DB::getPdo()->lastInsertId();
>>> DB::table('empleados')->insert(['empresa_id' => $empresa_id, 'nombre' => 'Juan', 'apellido_paterno' => 'Perez', 'apellido_materno' => 'Garcia', 'email' => 'juan@test.com', 'numero_empleado' => 'EMP001', 'fecha_ingreso' => '2023-01-01', 'created_at' => now(), 'updated_at' => now()]);
>>> echo "Empresa y empleado insertados.\n";
```

---

## Posibles errores y soluciones

| Error | Causa | Solución |
|-------|--------|----------|
| `SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint` | Tabla referenciada no existe | Ejecutar migraciones en el orden indicado; asegurar que `empresas` existe antes de tablas_faltantes y Fase 1. |
| `SQLSTATE[42S02]: Base table or view not found` | Tabla no existe | Ejecutar la migración que crea esa tabla (ver orden arriba). |
| `Duplicate column name` / table already exists | Tabla o columna ya existe | No volver a ejecutar la misma migración; o hacer rollback y volver a migrar. |
| `temas_voz` no existe | Nuestra voz usa `temas_voz` | La migración 090000 crea `temas_voz` (no `temas_voz_colaboradores`). |

---

## Reporte de resultados (plantilla)

```markdown
# REPORTE DE PRUEBA DE MIGRACIONES - LOCAL

## 1. Migraciones ejecutadas
- Tablas Rafa locales: 5 (empresas, productos, temas_voz, razones_sociales, areas)
- Tablas faltantes: 6 (bancos, departamentos, puestos, ubicaciones, regiones, centros_pago)
- Fase 1: 10 migraciones
- Fase 2: 11 migraciones
- **Total tablas: ~115**

## 2. Resultados por módulo
| Módulo        | Estado |
|---------------|--------|
| Auth          | ✅ / ❌ |
| Empleados     | ✅ / ❌ |
| Financiero    | ✅ / ❌ |
| Chat          | ✅ / ❌ |
| Voz           | ✅ / ❌ |
| Historiales   | ✅ / ❌ |
| Solicitudes   | ✅ / ❌ |
| Encuestas     | ✅ / ❌ |
| Reconocimientos | ✅ / ❌ |
| Notificaciones | ✅ / ❌ |
| Documentos   | ✅ / ❌ |
| Mensajería   | ✅ / ❌ |
| Capacitación | ✅ / ❌ |
| Integraciones | ✅ / ❌ |
| Adicionales  | ✅ / ❌ |

## 3. Problemas detectados
- [Lista de errores si hay]

## 4. Conclusiones
- [Resumen]
- ¿Listo para cuando Rafa suba sus tablas?
```

---

Cuando Rafa suba sus tablas (empresas, productos, temas_voz, etc.), no es necesario eliminar las tablas locales: la migración `2026_03_02_090000_create_tablas_rafa_locales` no vuelve a crear una tabla que ya exista. Si se quiere un entorno “limpio” con solo las tablas de Rafa, hacer rollback de esa migración en local y luego ejecutar solo las migraciones de Rafa (o restaurar BD) y después `php artisan migrate` para el resto.
