# Análisis: compatibilidad de seeders con migraciones y nomenclatura unificada (colaborador)

**Alcance:** Análisis estático. No se ha ejecutado ningún seeder ni comando.

---

## 1. Referencias a empleado_id, numero_empleado, rol 'empleado', modelos/tablas inexistentes

| Seeder | ¿empleado_id / numero_empleado / rol 'empleado'? | Notas |
|--------|--------------------------------------------------|--------|
| Inicial | No columnas; solo texto en `productos.nombre`/`descripcion` ("Voz del Empleado", "empleados") | Texto mostrable únicamente |
| EmpresaEjemploSeeder | No | Usa solo modelos actuales |
| ClienteEjemploSeeder | No | User con `tipo` => 'cliente', rol admin_empresa |
| SuperAdminSeeder | No | Asigna rol super_admin |
| WorkOSTestUserSeeder | No | User tipo 'administrador' |
| SpatieRolesSeeder | No | Crea rol **'colaborador'** (línea 36), no 'empleado' |
| ShieldPermisosLegacySeeder | No | Permisos `upload_archivo_colaborador`, `view_baja_colaborador` (PascalCase en BD) |
| ShieldPermisosRolesSeeder | No | Solo admin_empresa, rh_empresa |
| ShieldPanelClienteSeeder | No | Roles y usuario cliente |
| RolesClienteSeeder | No | admin_empresa, rh_empresa, gestor_catalogos, consultor_catalogos |
| TemaVozColaboradoresSeeder | No | Inserta en `temas_voz_colaboradores`; SQL tiene "empleado" solo en texto descripcion |
| OcupacionesSeeder | No | Inserta en `ocupaciones` (id, descripcion, timestamps) |
| BancoSeeder | No | Tabla `bancos` |
| DepartamentoSeeder | No | Tabla `departamentos`; sin empleado_id/numero_empleado |
| EstadoAnimoAfeccionSeeder | No | Tabla `estado_animo_afecciones` |
| EstadoAnimoCaracteristicaSeeder | No | Tabla `estado_animo_caracteristicas` |
| ReconocimientosSeeder | No | Tabla `reconocimientos`; SQL con "empleados" en texto |
| SegmentacionProductosTestSeeder | No | Usa `Colaborador::create` con **numero_colaborador** (línea 248) |

Ningún seeder escribe en columnas `empleado_id` o `numero_empleado`, ni crea el rol `empleado`.

---

## 2. Registros en tablas con campos renombrados

- **colaboradores:** La única columna renombrada fue `numero_empleado` → `numero_colaborador`.  
  **SegmentacionProductosTestSeeder** ya usa `numero_colaborador`.  
  Ningún otro seeder inserta en `colaboradores` en el flujo por defecto.
- **users:** `empleado_id` → `colaborador_id`; ningún seeder asigna `empleado_id` ni escribe en esa columna.
- **spatie_roles / permissions:** La migración de unificación actualiza nombres; los seeders crean rol `colaborador` y permisos con nombres actuales.

No hay inserts que usen nombres de columnas viejos.

---

## 3. Comandos Artisan en DatabaseSeeder

- **`shield:generate --panel=admin --option=permissions --all=true`**  
  Depende de: paneles Filament registrados, recursos del panel Admin, tablas `permissions` y `spatie_roles` (creadas por migraciones).  
  Tras `migrate:fresh` las tablas existen; el comando crea permisos en BD. No requiere datos previos. ✅

- **`shield:generate-cliente`**  
  Depende de: panel `cliente`, recursos del panel Cliente, tablas de permisos.  
  Usa `Permission::firstOrCreate`; con BD recién migrada funciona. No requiere filas previas. ✅

Ninguno de los dos comandos depende de seeders previos para tablas o datos; sí asumen que las migraciones ya se ejecutaron.

---

## 4. Orden de ejecución en DatabaseSeeder.php

| Paso | Qué se ejecuta | Dependencias |
|------|----------------|--------------|
| 1 | Inicial, BancoSeeder, EstadoAnimo*, ReconocimientosSeeder, TemaVozColaboradoresSeeder | Tablas creadas por migraciones |
| 2 | EmpresaEjemploSeeder | Industria, Subindustria, Producto, NotificacionesIncluidas, CentroCosto, Reconocimiento, TemaVozColaborador (del paso 1 o Inicial) |
| 3 | shield:generate (admin), shield:generate-cliente | Tablas permissions/spatie_roles (migraciones); sin datos obligatorios |
| 4 | SpatieRolesSeeder, ShieldPermisosRolesSeeder, RolesClienteSeeder | Empresa id=1 (paso 2), permisos (paso 3) |
| 5 | SuperAdminSeeder, ClienteEjemploSeeder | User admin@paco.com (Inicial), roles (paso 4), Empresa 1 |
| 6 | TemaVozColaboradoresSeeder, OcupacionesSeeder | Tablas existentes; idempotentes |

**Orden de ejecución:** ✅ Correcto. Las dependencias (catálogos → empresa → permisos → roles → usuarios) se respetan. No se crean `empresa_user` ni usuarios de ejemplo sin empresa o roles previos.

---

## 5. Permisos con nombres viejos (UploadArchivoEmpleado, ViewBajaEmpleado, rol 'empleado')

- **SpatieRolesSeeder:** Crea el rol **colaborador**, no `empleado`. ✅  
- **ShieldPermisosLegacySeeder:** Define `upload_archivo_colaborador`, `view_baja_colaborador` (y equivalentes en PascalCase en BD). No usa nombres antiguos. ✅  
- **ShieldPermisosRolesSeeder / RolesClienteSeeder:** Solo asignan permisos por nombre; no crean permisos con nombres legacy. ✅  
- **DatabaseSeeder** no invoca `ShieldPermisosLegacySeeder`; si se ejecuta a mano, crea permisos ya alineados con la nomenclatura actual.

Ningún seeder crea permisos `UploadArchivoEmpleado` o `ViewBajaEmpleado`, ni el rol `empleado`.

---

## SEEDERS OK

| Seeder | Estado |
|--------|--------|
| Inicial | OK (solo texto "empleado" en productos; sin columnas) |
| BancoSeeder | OK |
| EstadoAnimoAfeccionSeeder | OK |
| EstadoAnimoCaracteristicaSeeder | OK |
| ReconocimientosSeeder | OK |
| TemaVozColaboradoresSeeder | OK (tabla y columnas actuales) |
| EmpresaEjemploSeeder | OK |
| SpatieRolesSeeder | OK (rol colaborador) |
| ShieldPermisosRolesSeeder | OK |
| RolesClienteSeeder | OK |
| ShieldPermisosLegacySeeder | OK (nombres colaborador) |
| ShieldPanelClienteSeeder | OK (no se usa en db:seed por defecto) |
| SuperAdminSeeder | OK |
| ClienteEjemploSeeder | OK |
| WorkOSTestUserSeeder | OK |
| OcupacionesSeeder | OK |
| DepartamentoSeeder | OK (no referencias empleado; no se ejecuta por defecto) |
| SegmentacionProductosTestSeeder | OK (numero_colaborador; no se ejecuta por defecto) |

---

## SEEDERS CON PROBLEMAS

| Seeder | Problema | Línea/referencia |
|--------|----------|-------------------|
| — | Ninguno en código PHP | — |

**Documentación (no bloquea ejecución):**

| Archivo | Problema |
|---------|----------|
| database/seeders/README.md | Línea 23: indica que SpatieRolesSeeder crea el rol "empleado (company_id=1)"; en código el rol es **colaborador**. Documentación desactualizada. |

---

## ORDEN DE EJECUCIÓN EN DatabaseSeeder

✅ **Correcto.** Catálogos → Empresa de ejemplo → permisos (comandos) → roles y asignación → usuarios de prueba. No hay dependencias invertidas (p. ej. empresa_user sin users/empresas).

---

## VEREDICTO

**🟢 Seeders pasarán limpio** con el estado actual de migraciones y nomenclatura unificada (colaborador).

- No hay uso de `empleado_id`, `numero_empleado` ni rol `empleado` en seeders.
- No hay inserts en columnas renombradas con nombres viejos.
- Los comandos `shield:generate` y `shield:generate-cliente` solo requieren migraciones ejecutadas.
- El orden en `DatabaseSeeder.php` respeta dependencias.

**Ajuste recomendado (opcional):** Actualizar `database/seeders/README.md` línea 23: cambiar "empleado (company_id=1)" por "colaborador (company_id=1)" para alinear la documentación con `SpatieRolesSeeder`.
