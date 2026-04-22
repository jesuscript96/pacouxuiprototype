# Tests

## Ejecutar tests

```bash
php artisan test
```

## Base de datos de test (MySQL)

Los tests de Feature usan la base de datos configurada en `phpunit.xml` con `APP_ENV=testing`:
- **DB_DATABASE**: `paco_test_db`
- **DB_HOST**: 192.168.1.51

Si los tests fallan con errores como:
- `Table 'users' already exists`
- `Table 'migrations' doesn't exist`

la BD de test está en un estado inconsistente. **Solución:**

1. **En el servidor MySQL** (192.168.1.51), resetea la base de datos de test:
   ```sql
   DROP DATABASE IF EXISTS paco_test_db;
   CREATE DATABASE paco_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Luego en el proyecto** ejecuta las migraciones para testing:
   ```bash
   php artisan migrate --env=testing --force
   ```

3. **Vuelve a lanzar los tests:**
   ```bash
   php artisan test
   ```

No ejecutes tests en paralelo (varios procesos a la vez) contra la misma BD `paco_test_db`, o pueden aparecer conflictos de tablas.

## Usar SQLite en memoria (opcional)

Si tienes la extensión PHP **pdo_sqlite** instalada, puedes usar SQLite en memoria para los tests y evitar depender del MySQL de test. En `phpunit.xml` cambia:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

y comenta o elimina las variables `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`. Así cada ejecución de tests usa una BD en memoria nueva.
