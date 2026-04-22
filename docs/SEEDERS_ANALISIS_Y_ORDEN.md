# Análisis de seeders – cuáles ejecutar y en qué orden

## Resumen de seeders

| Seeder | Qué hace | Depende de |
|--------|----------|------------|
| **Inicial** | User (admin@paco.com) en `users`; Producto, CentroCosto, Industria, Subindustria, NotificacionesIncluidas (catálogos base de Rafa). | Tablas: users, productos, centro_de_costos, industrias, sub_industrias, notificaciones_incluidas |
| **BancoSeeder** | Listado de bancos (BBVA, Banamex, Santander, etc.) con código y comisión. | Tabla `bancos` |
| **EstadoAnimoAfeccionSeeder** | Catálogo de afecciones para estado de ánimo (Salud, Estrés, Familia, etc.). | Tabla `estado_animo_afecciones` |
| **EstadoAnimoCaracteristicaSeeder** | Catálogo de características de estado de ánimo (Enojo, Calma, Felicidad, etc.) con `lista_inicial`. | Tabla `estado_animo_caracteristicas` |
| **ReconocimientosSeeder** | Inserta datos desde `acknowledgments_202603012159.sql` en `reconocimientos`. | Archivo SQL en `database/seeders/` y tabla `reconocimientos` |
| **TemaVozColaboradoresSeeder** | Inserta datos desde `voice_employee_subjects_202603012229.sql` en `temas_voz_colaboradores`. | Archivo SQL en `database/seeders/` y tabla `temas_voz_colaboradores` |
| **EmpresaEjemploSeeder** (Rafa) | Una empresa de ejemplo con configuración, razones sociales, productos, notificaciones, comisiones, centros de costo, reconocimientos, temas de voz, alias transacciones, etc. | Inicial (Industria, Subindustria, Producto, NotificacionesIncluidas, CentroCosto); opcionalmente ReconocimientosSeeder y TemaVozColaboradoresSeeder (si no hay datos, crea uno de ejemplo) |
| **SpatieRolesSeeder** | Roles: super_admin (global) y admin_empresa, rh_empresa, empleado para empresa id 1. | Tablas Spatie (permisos/roles); Empresa id 1 para roles por empresa |
| **ShieldPermisosLegacySeeder** | Permisos adicionales por módulo (Empleados, Encuestas, Voz, Reconocimientos, etc.) en formato PascalCase para Filament Shield. | `php artisan shield:generate` (o que existan permisos en BD) |
| **ShieldPermisosRolesSeeder** | Asigna permisos a admin_empresa y rh_empresa (empresa id 1). | Empresa id 1; SpatieRolesSeeder; permisos de Shield generados |
| **WorkOSTestUserSeeder** | Usuario de prueba en `usuarios` (test@workos.com) con rol super_admin para panel admin. | Tabla `usuarios`; tablas Spatie (roles) |

---

## Orden recomendado para ejecutar

### 1. Solo catálogos (sin empresa ni usuarios de panel)

Para tener datos de catálogo y poder probar módulos que dependen de ellos:

```bash
php artisan db:seed --class=Inicial
php artisan db:seed --class=BancoSeeder
php artisan db:seed --class=EstadoAnimoAfeccionSeeder
php artisan db:seed --class=EstadoAnimoCaracteristicaSeeder
php artisan db:seed --class=ReconocimientosSeeder
php artisan db:seed --class=TemaVozColaboradoresSeeder
```

### 2. Catálogos + empresa de ejemplo (Rafa)

Para tener una empresa lista con todas sus relaciones:

```bash
# Primero los del punto 1, luego:
php artisan db:seed --class=EmpresaEjemploSeeder
```

### 3. Panel admin (Filament + Shield) con usuario de prueba

Para entrar al panel con un super_admin:

```bash
# Después de migrar y (opcional) punto 1 o 2:
php artisan shield:generate --all
php artisan db:seed --class=ShieldPermisosLegacySeeder
php artisan db:seed --class=SpatieRolesSeeder
php artisan db:seed --class=ShieldPermisosRolesSeeder
php artisan db:seed --class=WorkOSTestUserSeeder
```

- Si no hay empresa id 1, SpatieRolesSeeder y ShieldPermisosRolesSeeder no crean roles por empresa (no fallan).
- WorkOSTestUserSeeder crea el usuario test@workos.com y le asigna super_admin.

### 4. Todo en uno (como DatabaseSeeder actual)

El `DatabaseSeeder` actual hace:

```php
Inicial, BancoSeeder, EstadoAnimoAfeccionSeeder, EstadoAnimoCaracteristicaSeeder,
ReconocimientosSeeder, TemaVozColaboradoresSeeder
```

**EmpresaEjemploSeeder está comentado.** Para incluirlo:

```php
$this->call([
    Inicial::class,
    BancoSeeder::class,
    EstadoAnimoAfeccionSeeder::class,
    EstadoAnimoCaracteristicaSeeder::class,
    ReconocimientosSeeder::class,
    TemaVozColaboradoresSeeder::class,
    EmpresaEjemploSeeder::class,  // descomentar
]);
```

Luego:

```bash
php artisan db:seed
```

---

## Cuáles conviene ejecutar (recomendación)

| Objetivo | Seeders a ejecutar |
|----------|--------------------|
| Desarrollo local con datos de ejemplo completos | **Inicial** → **BancoSeeder** → **EstadoAnimoAfeccionSeeder** → **EstadoAnimoCaracteristicaSeeder** → **ReconocimientosSeeder** → **TemaVozColaboradoresSeeder** → **EmpresaEjemploSeeder** |
| Entrar al panel admin (Filament) | Después de lo anterior (o solo migraciones): **shield:generate** → **ShieldPermisosLegacySeeder** → **SpatieRolesSeeder** → **ShieldPermisosRolesSeeder** → **WorkOSTestUserSeeder** |
| Solo catálogos, sin empresa ni usuarios panel | Inicial, BancoSeeder, EstadoAnimoAfeccionSeeder, EstadoAnimoCaracteristicaSeeder, ReconocimientosSeeder, TemaVozColaboradoresSeeder |
| Reproducir lo que hace hoy `db:seed` | Dejar DatabaseSeeder como está y ejecutar `php artisan db:seed` (sin EmpresaEjemploSeeder). |

---

## Notas importantes

1. **ReconocimientosSeeder** y **TemaVozColaboradoresSeeder** requieren los archivos SQL en `database/seeders/` (ya están en el repo). Si faltan, el seeder avisa y no inserta nada.
2. **Inicial** crea un usuario en la tabla **users** (Laravel), no en **usuarios**. El panel admin usa **usuarios**; para eso sirve **WorkOSTestUserSeeder**.
3. **EmpresaEjemploSeeder** usa Empresa, ConfiguracionApp, Razonsocial, Producto, NotificacionesIncluidas, ComisionRango, ConfiguracionRetencionNomina, CentroCosto, Reconocmiento, TemaVozColaborador, AliasTipoTransaccion, FrecuenciaNotificaciones, QuincenasPersonalizadas, RazonEncuestaSalida. Si falta algún catálogo, puede fallar o crear datos mínimos (reconocimiento/tema voz) según el código.
4. **SpatieRolesSeeder** y **ShieldPermisosRolesSeeder** asumen empresa id **1**. Si quieres roles por empresa, crea primero una empresa (p. ej. con EmpresaEjemploSeeder) o ajusta el `$empresaId` en esos seeders.

---

## Orden completo sugerido (una sola secuencia)

Para “formatear, migrar y dejar listo para desarrollar con empresa de ejemplo y usuario admin”:

```bash
php artisan migrate:fresh
php artisan db:seed --class=Inicial
php artisan db:seed --class=BancoSeeder
php artisan db:seed --class=EstadoAnimoAfeccionSeeder
php artisan db:seed --class=EstadoAnimoCaracteristicaSeeder
php artisan db:seed --class=ReconocimientosSeeder
php artisan db:seed --class=TemaVozColaboradoresSeeder
php artisan db:seed --class=EmpresaEjemploSeeder
php artisan shield:generate --all
php artisan db:seed --class=ShieldPermisosLegacySeeder
php artisan db:seed --class=SpatieRolesSeeder
php artisan db:seed --class=ShieldPermisosRolesSeeder
php artisan db:seed --class=WorkOSTestUserSeeder
```

Resumen: **conviene ejecutar** los 6 del `DatabaseSeeder` actual + **EmpresaEjemploSeeder** para datos de ejemplo; y para el panel admin, los de Shield y **WorkOSTestUserSeeder** en el orden indicado.
