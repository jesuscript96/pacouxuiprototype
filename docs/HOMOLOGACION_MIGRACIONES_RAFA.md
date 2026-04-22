# Homologación: migraciones de Rafa (commit 266ece6) + unificación en `users`

Referencia: commit de Rafa `266ece672458e897601f796e4cb5ee3457781ab8`.

**Decisiones aplicadas:**

1. **Sin defensas** (`hasTable`/barrido): Rafa limpiará su base; todas las migraciones son **create directo** como en Rafa.
2. **Una sola tabla de usuarios:** unificación en **`users`** (Laravel). Se añaden a `users` las columnas que tenía `usuarios`; no se crea la tabla `usuarios`. **Las migraciones asumen BD limpia** (no hay migración de datos ni de FKs desde `usuarios`).
3. **Acknowledgments:** se mantienen **dos tablas distintas** (catálogo reconocimientos de Rafa + tablas del módulo acknowledgments); no se unifican en una sola tabla de catálogo.
4. **Respetar FKs:** orden de migraciones y referencias coherentes.
5. **Fuera del código lo no usado:** migraciones que no formen parte del CORE se mueven a pospuestos o se eliminan; una sola migración `empresa_user` con **user_id → users** (Rafa).

---

## 1. Listado migraciones de Rafa (266ece6)

Igual que antes: 30 archivos en raíz, todo `Schema::create()` directo, **users** (no usuarios), **empresa_user** con **user_id → users**. Sin empleados, puestos, ubicaciones, regiones, centros_pago, roles/permisos app, Spatie en ese commit.

---

## 2. Set CORE final (homologado)

### 2.1 Migraciones de Rafa (exactas, create directo)

- Laravel base: 0001_01_01_000000 (users, sessions, password_reset_tokens), 0001_01_01_000001 (cache), 0001_01_01_000002 (jobs).
- Catálogos Rafa: industrias, sub_industrias, logs, productos, centro_costos, empresas, razonsocial, configuracion_app, comision_rangos, quincenas_personalizadas, empresas_productos, empresas_notificaciones_incluidas, empresas_centros_costos, **reconocimientos**, tema_voz_colaboradores, razon_encuesta_salidas, alias_tipo_transacciones, frecuencia_notificaciones, configuracion_retencion_nominas.
- empresas_reconocimientos, **bancos** (171552), estado_animo_afecciones, estado_animo_caracteristicas, **empresa_user** (230042, user_id → users), departamentos (004220), departamentos_generales, felicitaciones.

### 2.2 Migraciones propias que se añaden (orden por nombre)

- 2026_02_25_120000_add_workos_fields_to_users_table (alter users).
- 2026_02_26_099999: solo **puestos, ubicaciones, regiones, centros_pago** (create directo; departamentos lo crea 004220).
- 2026_02_26_100000_create_tablas_faltantes (no-op si se mantiene).
- 2026_02_26_100001_create_roles_permisos_tables (roles, permisos app).
- 2026_02_26_100002_create_empleados_table.
- **Eliminadas:** 100003 create_usuarios, 161940 add report/usuarios, 210000 add puesto/usuarios, 099998 create_bancos, 210000 add comision bancos, 211230 empresa_user (usuario_id).
- 2026_02_26_100004_create_auth_pivots_and_2fa_tables: **user_id → users** (rol_usuario, verify_2fa).
- 2026_02_26_100005_create_oauth_tables: **user_id → users**.
- 2026_02_26_100006_create_empleado_producto_and_filtros_tables.
- Spatie: 170439, 171432, 202139.
- **Nueva:** 2026_03_06_200000_add_usuario_fields_to_users_table (añade a users las columnas que tenía usuarios).
- **Nueva:** 2026_03_10_200003_drop_usuarios_table (elimina tabla usuarios si existiera; en BD limpia no existe).
- Reconocimientos (módulo app): **100015, 100030, 100033** están en **pospuestos/reconocimientos_app** (no en CORE); no se usan en lógica actual. En CORE solo tabla **reconocimientos** y pivot **empresas_reconocimientos** (Rafa).
- Traducciones: 100028, 100029 (si aplican).

### 2.3 Lo que sale del CORE (pospuestos o eliminado)

- 099998_create_bancos_table: eliminada (solo 171552 crea bancos).
- 210000_add_comision_and_soft_deletes_to_bancos: eliminada (171552 ya tiene comision y softDeletes).
- 100003_create_usuarios_table: eliminada (unificación en users).
- 161940_add_report_and_newsletter_fields_to_usuarios: eliminada (campos van a users en 200000).
- 210000_add_puesto_and_departamento_to_usuarios: eliminada (campos en 200000).
- 211230_create_empresa_user_table (usuario_id): eliminada; solo 230042 (user_id).

---

## 3. Unificación usuarios → users (BD limpia)

1. **add_usuario_fields_to_users_table:** añade a `users` las columnas de negocio: apellido_paterno, apellido_materno, telefono, celular, tipo, empresa_id, departamento_id, puesto_id, empleado_id, imagen, ver_reportes, usuario_tableau, recibir_boletin, google2fa_secret, enable_2fa, verified_2fa_at.
2. **drop_usuarios_table:** elimina la tabla `usuarios` si existiera (en instalación limpia no se crea nunca; queda como limpieza por si acaso).

No hay migración de datos ni de FKs: se asume que la base está limpia y solo se usa la tabla `users`.

---

## 4. Orden de ejecución y FKs

- **users** (0001_01_01_000000) antes que empresa_user (230042) y que add_usuario_fields (200000).
- **empresas** antes que departamentos (004220), empleados (100002), empresa_user (230042).
- **departamentos, puestos** (099999 + 004220) antes de add_usuario_fields (users necesita departamento_id, puesto_id).
- **empleados** (100002) antes de add_usuario_fields (users necesita empleado_id).
- **roles** (100001) antes de rol_usuario (100004).
- **users** con columnas nuevas (200000) antes de cualquier uso; **drop_usuarios** (200003) al final (no-op en BD limpia).

---

## 5. Resumen

| Qué | Cómo |
|-----|------|
| **Rafa** | 30 migraciones create directo; users; empresa_user con user_id. |
| **Homologación** | Sin defensas; mismas migraciones Rafa + WorkOS, 099999 (solo puestos/ubicaciones/regiones/centros_pago), empleados, roles, auth (user_id), oauth (user_id), Spatie, add_usuario_fields, migrate data, update FKs, drop usuarios. |
| **Acknowledgments** | Módulo app (100015, 100030, 100033) en **pospuestos/reconocimientos_app**; en CORE solo reconocimientos + empresas_reconocimientos (Rafa). |
| **empresa_user** | Solo 230042 (user_id → users). |
| **Usuario en código** | Sustituido por User; modelo Usuario y recurso Usuario eliminados o deprecados. |

---

## 6. Verificación commit 266ece6 (Rafa) vs CORE actual

Verificación realizada comparando el árbol del commit `266ece672458e897601f796e4cb5ee3457781ab8` con las migraciones y modelos actuales.

**Migraciones de Rafa:** 0001 (users), 230042 (empresa_user), 214718 (reconocimientos), 185249 (empresas_reconocimientos), 171552 (bancos), 004220 (departamentos), 195203 (felicitaciones), 013432 (centro_de_costos), 213248 (empresas_centros_costos) — todas homologadas o idénticas. La migración **2026_03_04_230042_create_empresa_user_table** faltaba y fue añadida (user_id → users, empresa_id → empresas).

**Modelos:** User (empresas() con empresa_user), Reconocmiento, Empresa, Felicitacion — compatibles con Rafa. **FelicitacionesTable** (Filament): mismo contenido que en el commit (columnas titulo, tipo, empresa, departamento, es_urgente, user.name, requiere_respuesta).

**Conclusión:** Las migraciones y modelos de Rafa en 266ece6 están homologados. Única corrección: añadir 230042 (empresa_user) para que exista la tabla que usa `User::empresas()` y el panel por tenant.

---

## 7. Pasos para correr migraciones con BD limpia

Si tú o Rafa tienen la base de datos vacía (o quieren empezar de cero):

- **Requisito:** `.env` con DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD (y DB_HOST/DB_PORT si aplica).
- **Orden:** Laravel ejecuta solo las migraciones de `database/migrations/` (raíz), en orden por nombre de archivo. Las de `pospuestos/` no se ejecutan por defecto.
- **BD vacía (primera vez):** `php artisan migrate`
- **Borrar todo y recrear:** `php artisan migrate:fresh`
- **Recrear y poblar datos:** `php artisan migrate:fresh --seed`

No hace falta correr migraciones en un orden especial: con BD limpia, un solo `migrate` o `migrate:fresh` es suficiente.

---

## 8. Análisis final: migrate:fresh sin referencias rotas

Para que **Rafa (o tú)** pueda ejecutar **Borrar todo y recrear** (`php artisan migrate:fresh`) sin referencias dañadas:

- **Migraciones CORE:** Todas usan **users** y **user_id** (rol_usuario, empresa_user, oauth, add_usuario_fields, drop_usuarios). Ninguna crea tabla `usuarios` ni FK usuario_id.
- **Modelos:** User, Empresa (usuarios() → User), SpatieRole (usuarios() → User), Rol, Felicitacion, Log usan **users** / user_id. Config (auth, shield) y Gate usan User.
- **Seeders:** Inicial y WorkOSTestUserSeeder usan User. MigrateVerifyCommand actualizado para verificar columnas en tabla **users** (no usuarios).
- **Pospuestos:** Las migraciones en pospuestos siguen con usuario_id/usuarios; no se ejecutan con migrate:fresh. Si se reactiva un módulo, habría que cambiarlas a user_id → users.

**Conclusión:** Con la configuración actual, `migrate:fresh` + `db:seed` deja el CORE homologado y sin referencias rotas ni lógica corrompida.
