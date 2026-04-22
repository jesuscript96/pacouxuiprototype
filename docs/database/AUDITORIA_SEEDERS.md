# Auditoría de seeders — tecben-core

## Paso 1: Inventario completo

| Archivo | Qué hace (1 línea) | Tablas que toca | Dependencias | Usa user vs usuarios? |
|---------|--------------------|-----------------|--------------|------------------------|
| **Inicial** | Crea usuario admin@paco.com y catálogos (productos, industrias, subindustrias, centros costo, notificaciones_incluidas). | users, productos, centro_de_costos, industrias, sub_industrias, notificaciones_incluidas | Ninguna | ✅ User (tabla user) |
| **EmpresaEjemploSeeder** | Crea una empresa de ejemplo con id y relaciones (config app, razones sociales, productos, reconocimientos, etc.). | empresas, configuracion_apps, razonsociales, y muchas pivotes/relacionadas | Industria, Subindustria, Producto, etc. (Inicial) | N/A |
| **SpatieRolesSeeder** | Crea rol super_admin (global), le asigna todos los permisos; crea admin_empresa, rh_empresa, empleado con company_id=1 (sin asignar permisos). | spatie_roles, role_has_permissions | Permisos existentes (shield:generate), Empresa id=1 opcional | N/A |
| **ShieldPermisosRolesSeeder** | Asigna permisos a admin_empresa y rh_empresa (lista fija Admin + Cliente: Empresa, CentroCosto, Departamento, etc.). | role_has_permissions | Empresa id=1, roles creados, permisos existentes | N/A |
| **ShieldPanelClienteSeeder** | Crea permisos panel Cliente (Departamento, DepartamentoGeneral), crea roles gestor_catalogos, consultor_catalogos, admin_empresa, rh_empresa con company_id=1, asigna permisos, crea usuario cliente@tecben.com. | permissions, spatie_roles, role_has_permissions, users, empresa_user | Empresa id=1 | ✅ User |
| **ShieldPermisosLegacySeeder** | Crea permisos “legacy” (snake_case convertido a PascalCase): custom por módulo (Empleados, Encuestas, Voz, etc.). | permissions | Ninguna | N/A |
| **BancoSeeder** | Inserta bancos de ejemplo (BBVA, Banamex, etc.) por ID. | bancos | Ninguna | N/A |
| **EstadoAnimoAfeccionSeeder** | Inserta afecciones de estado de ánimo si existe la tabla. | estado_animo_afecciones | Tabla existente | N/A |
| **EstadoAnimoCaracteristicaSeeder** | Inserta características de estado de ánimo si existe la tabla. | estado_animo_caracteristicas | Tabla existente | N/A |
| **ReconocimientosSeeder** | Inserta desde SQL acknowledgments si la tabla reconociimientos está vacía. | reconocimientos | Archivo SQL | N/A |
| **TemaVozColaboradoresSeeder** | Inserta desde SQL voice_employee_subjects si la tabla está vacía. | temas_voz_colaboradores | Archivo SQL | N/A |
| **DepartamentoSeeder** | Inserta muchos departamentos/departamentos generales desde array (mapeo name→nombre, company_id→empresa_id). | departamentos, departamentos_generales (implícito) | Ninguna | N/A |
| **WorkOSTestUserSeeder** | Crea usuario test@workos.com con rol super_admin para pruebas WorkOS. | users, model_has_roles | Rol super_admin existente | ✅ User |

---

## Paso 2: Clasificación

| Archivo | Clasificación | Motivo |
|---------|----------------|--------|
| **Inicial** | ⚠️ ACTUALIZAR | Esencial para catálogos. El usuario admin@paco.com no tiene rol super_admin asignado; conviene unificar con SuperAdmin (ver propuesta). |
| **EmpresaEjemploSeeder** | ✅ ESENCIAL | Base para desarrollo (empresa id=1). |
| **SpatieRolesSeeder** | 🔄 REEMPLAZADO / ⚠️ ACTUALIZAR | Crea roles y asigna todos los permisos a super_admin. La creación de permisos la cubren los comandos; este seeder sigue siendo útil para crear rol super_admin y asignarle permisos. Puede fusionarse con “SuperAdmin” (usuario + rol). |
| **ShieldPermisosRolesSeeder** | 🔄 REEMPLAZADO | Solo asigna permisos a roles; la lista está hardcodeada. Puede integrarse en un único seeder de “roles Cliente + asignación” que use permisos ya generados por los comandos. |
| **ShieldPanelClienteSeeder** | 🔄 REEMPLAZADO (parcial) | Crea permisos (redundante con `shield:generate-cliente`) y además roles + usuario cliente. Mantener solo: creación/actualización de roles Cliente y asignación de permisos + usuario cliente (sin crear permisos). |
| **ShieldPermisosLegacySeeder** | 🔄 REEMPLAZADO | Crea permisos con Permission::firstOrCreate (custom legacy). Esos permisos deberían generarse por config/recursos o no seedearse; candidato a eliminar o a sustituir por algo que no cree permisos. |
| **BancoSeeder** | ✅ ESENCIAL | Catálogo necesario para flujos que usan bancos. |
| **EstadoAnimoAfeccionSeeder** | 👤 DE RAFA / opcional | Módulo estado de ánimo (pospuestos). Condicionado a existencia de tabla. Mantener si se usa. |
| **EstadoAnimoCaracteristicaSeeder** | 👤 DE RAFA / opcional | Igual que el anterior. |
| **ReconocimientosSeeder** | ✅ ESENCIAL | Datos para reconocimientos; EmpresaEjemploSeeder depende de reconociimientos. |
| **TemaVozColaboradoresSeeder** | ✅ ESENCIAL | Datos para voz; EmpresaEjemploSeeder usa temas voz. |
| **DepartamentoSeeder** | 👤 DE RAFA | Muchos datos de departamentos/departamentos generales; posiblemente específicos de Rafa. Valorar si se mantiene para desarrollo. |
| **WorkOSTestUserSeeder** | 🗑️ DESECHABLE / 👤 DE RAFA | Usuario de prueba WorkOS (test@workos.com). Candidato a quitar del seed por defecto o dejar como seeder opcional. |

---

## Paso 3: Seeders reemplazados por comandos

| Archivo | Motivo de reemplazo |
|---------|----------------------|
| **ShieldPanelClienteSeeder** (parte permisos) | Crea permisos `ViewAny:Departamento`, etc. con `Permission::firstOrCreate`. Eso lo cubre `php artisan shield:generate-cliente`. El seeder debe limitarse a roles + asignación + usuario cliente. |
| **ShieldPermisosLegacySeeder** | Crea permisos custom (PascalCase desde snake_case). Son permisos que no vienen de recursos Filament; si se siguen usando, podrían estar en config y generarse por un comando o no seedearse. Redundante con enfoque “solo comandos generan permisos”. |
| **SpatieRolesSeeder** | No crea permisos; solo crea roles y asigna `Permission::all()` a super_admin. No reemplazado por comandos; sí candidato a unificarse con “SuperAdmin” (usuario + rol). |
| **ShieldPermisosRolesSeeder** | No crea permisos; solo `syncPermissions`. Sigue siendo necesario en concepto; conviene integrarlo en un único seeder de roles (Admin + Cliente) que asigne permisos ya generados. |

---

## Paso 4: DatabaseSeeder.php actual

```php
$this->call([
    Inicial::class,
    BancoSeeder::class,
    EstadoAnimoAfeccionSeeder::class,
    EstadoAnimoCaracteristicaSeeder::class,
    ReconocimientosSeeder::class,
    TemaVozColaboradoresSeeder::class,
    // EmpresaEjemploSeeder::class,  // Descomentar solo en desarrollo
    // ShieldPanelClienteSeeder::class,  // Permisos y roles panel Cliente; requiere empresa id=1
]);
```

- **Orden:** Inicial → catálogos → (Empresa y Shield comentados). Dependencias entre Inicial, Reconocimientos, TemaVoz y EmpresaEjemplo están bien si se descomenta Empresa.
- **Problemas:** No ejecuta permisos (comandos), no crea super admin con rol, no crea usuario cliente; seeders de permisos/roles están comentados o no existen en el orden deseado.

---

## Paso 5: Propuesta de DatabaseSeeder.php

Orden que respeta dependencias y usa comandos para permisos:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Catálogos base (productos, industrias, bancos, reconocimientos, temas voz, etc.)
        $this->call([
            Inicial::class,
            BancoSeeder::class,
            EstadoAnimoAfeccionSeeder::class,
            EstadoAnimoCaracteristicaSeeder::class,
            ReconocimientosSeeder::class,
            TemaVozColaboradoresSeeder::class,
        ]);

        // 2. Empresa de ejemplo (id=1) para desarrollo
        $this->call(EmpresaEjemploSeeder::class);

        // 3. Permisos (comandos; no seeders)
        Artisan::call('shield:generate', ['--panel' => 'admin', '--option' => 'permissions', '--all' => true]);
        Artisan::call('shield:generate-cliente');

        // 4. Roles y asignación de permisos
        $this->call(SpatieRolesSeeder::class);           // super_admin + roles por empresa (admin_empresa, rh_empresa, empleado)
        $this->call(ShieldPermisosRolesSeeder::class);    // asigna permisos Admin+Cliente a admin_empresa y rh_empresa
        $this->call(RolesClienteSeeder::class);          // gestor_catalogos, consultor_catalogos; asigna permisos Cliente (ver nota)

        // 5. Usuarios de prueba
        $this->call(SuperAdminSeeder::class);            // usuario super_admin para /admin (crear si no existe)
        $this->call(ClienteEjemploSeeder::class);        // usuario cliente@tecben.com para /cliente (crear si no existe)
    }
}
```

**Nota:** Hoy `ShieldPanelClienteSeeder` hace: crear permisos Cliente (redundante), crear roles gestor/consultor/admin_empresa/rh y asignar permisos, y crear usuario cliente. Propuesta:

- **RolesClienteSeeder:** Nuevo seeder (o renombrar/refactorizar ShieldPanelClienteSeeder) que solo cree/actualice roles con company_id=1 (admin_empresa, gestor_catalogos, consultor_catalogos, rh_empresa) y haga `syncPermissions` con los permisos ya existentes en BD (generados por `shield:generate-cliente`). No crear permisos.
- **ClienteEjemploSeeder:** Extraer de ShieldPanelClienteSeeder la creación/asignación del usuario cliente@tecben.com (empresa 1, rol admin_empresa). O mantener esa lógica dentro de “RolesClienteSeeder” y no tener seeder aparte “ClienteEjemploSeeder” si se prefiere un solo punto de entrada.
- **SuperAdminSeeder:** Crear (o extender Inicial) para asegurar usuario con email conocido (ej. admin@paco.com) y asignarle rol super_admin. SpatieRolesSeeder ya crea el rol y le asigna todos los permisos; SuperAdminSeeder solo asegura el usuario y la asignación del rol.

**Nombres reales a usar:** SpatieRolesSeeder, ShieldPermisosRolesSeeder, EmpresaEjemploSeeder, Inicial, BancoSeeder, etc. RolesClienteSeeder y SuperAdminSeeder/ClienteEjemploSeeder hay que crearlos o refactorizar desde ShieldPanelClienteSeeder e Inicial según se acuerde.

**shield:generate interactivo:** El comando `shield:generate` puede pedir confirmación si no se pasan opciones. Con `--panel=admin --option=permissions --all=true` debería ejecutarse sin prompts; conviene comprobarlo en local.

---

## Paso 6: Seeders eliminados (candidatos) y por qué

| Archivo | Motivo |
|---------|--------|
| **ShieldPermisosLegacySeeder** | Crea permisos custom manualmente; el proyecto usa comandos para permisos. Eliminar del run o eliminar archivo si se confirma que esos permisos no se usan. |
| **WorkOSTestUserSeeder** | Usuario de prueba WorkOS; no necesario para entorno estándar. Quitar del DatabaseSeeder (o eliminar) salvo que el equipo lo quiera como seeder opcional. |
| **DepartamentoSeeder** | Opcional; muchos datos, posiblemente de Rafa. Eliminar del run por defecto; mantener archivo como seeder opcional si se desea. |

No se elimina ningún seeder sin tu aprobación; solo se proponen y se documentan en este archivo y en `database/seeders/README.md`.
