# Seeders — Entorno de desarrollo

## Setup completo (desde cero)

```bash
php artisan migrate
php artisan db:seed
```

## Qué ejecuta `db:seed` — Orden de ejecución

| # | Paso | Tipo | Qué hace |
|---|------|------|----------|
| 1 | Inicial | Seeder | Usuario admin@paco.com + catálogos (productos, industrias, subindustrias, centros costo, notificaciones) |
| 2 | BancoSeeder | Seeder | Bancos de ejemplo |
| 3 | EstadoAnimoAfeccionSeeder | Seeder | Afecciones estado de ánimo (si existe tabla) |
| 4 | EstadoAnimoCaracteristicaSeeder | Seeder | Características estado de ánimo (si existe tabla) |
| 5 | ReconocimientosSeeder | Seeder | Reconocimientos desde SQL |
| 6 | TemaVozColaboradoresSeeder | Seeder | Temas voz desde SQL |
| 7 | EmpresaEjemploSeeder | Seeder | Empresa de ejemplo y relaciones |
| 8 | shield:generate --panel=admin | Comando | Permisos panel Admin |
| 9 | shield:generate-cliente | Comando | Permisos panel Cliente |
| 10 | SpatieRolesSeeder | Seeder | Roles super_admin, admin_empresa, rh_empresa, colaborador (company_id=1) |
| 11 | ShieldPermisosRolesSeeder | Seeder | Asigna permisos Admin+Cliente a admin_empresa y rh_empresa |
| 12 | RolesClienteSeeder | Seeder | Roles Cliente (gestor_catalogos, consultor_catalogos) y asignación de permisos |
| 13 | SuperAdminSeeder | Seeder | Asigna rol super_admin a admin@paco.com |
| 14 | ClienteEjemploSeeder | Seeder | Ficha en `colaboradores` + usuario cliente@tecben.com para /cliente (rol admin_empresa, empresa id=1) |

**Estado:** Implementado. `php artisan db:seed` ejecuta el orden anterior (incluye comandos Shield).

## Credenciales de prueba

| Rol | Email | Contraseña | Panel | Empresa |
|-----|-------|------------|-------|---------|
| Super Admin | admin@paco.com | password | /admin | N/A |
| Cliente ejemplo | cliente@tecben.com | password | /cliente | Empresa Ejemplo (id=1) |
| Test WorkOS (opcional) | test@workos.com | password123 | /admin | N/A |

## Cuándo ejecutar cada cosa

- **Agregaste un nuevo módulo al panel Cliente** → `php artisan shield:generate-cliente`
- **Agregaste un nuevo recurso al panel Admin** → `php artisan shield:generate --panel=admin --option=permissions --all`
- **Necesitas regenerar todo** → `php artisan migrate:fresh --seed` (o `php artisan migrate:fresh && php artisan db:seed`)

## Seeders no ejecutados por defecto (eliminados del run)

| Archivo | Motivo |
|---------|--------|
| **ShieldPanelClienteSeeder** | Eliminado. Permisos vía `shield:generate-cliente`; roles vía `RolesClienteSeeder`; usuario demo vía `ClienteEjemploSeeder` (incluye ficha en `colaboradores`). |
| **ShieldPermisosLegacySeeder** | Crea permisos custom manualmente; el proyecto usa comandos para permisos. Reemplazado por shield:generate. |
| **WorkOSTestUserSeeder** | Usuario de prueba WorkOS; no esencial. Ejecutar solo si se necesita: `php artisan db:seed --class=WorkOSTestUserSeeder`. |
| **DepartamentoSeeder** | Datos masivos de departamentos; opcional. |

## Referencia

- Auditoría completa: `docs/database/AUDITORIA_SEEDERS.md`
- Agregar módulo al panel Cliente: `docs/acceso-por-empresa/AGREGAR_MODULO_PANEL_CLIENTE.md`
