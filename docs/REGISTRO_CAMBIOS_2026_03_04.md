# Registro de cambios – Post-merge y ajustes (2026-03-04)

Documentación de todos los cambios realizados tras el merge de la rama de Rafa (catálogos y empresas) con la rama test (Fase 1, Fase 2, CRUD usuarios unificado).

---

## Índice

1. [Gitignore](#1-gitignore)
2. [Análisis post-merge y orden de migraciones](#2-análisis-post-merge-y-orden-de-migraciones)
3. [Renombrado de campos de usuarios a español](#3-renombrado-de-campos-de-usuarios-a-español)
4. [Migraciones de soporte (orden, bancos)](#4-migraciones-de-soporte-orden-bancos)
5. [Seeders y migración bancos](#5-seeders-y-migración-bancos)
6. [Login WorkOS y panel](#6-login-workos-y-panel)
7. [Modelo SpatieRole](#7-modelo-spatierole)
8. [Logout WorkOS](#8-logout-workos)
9. [Documentación creada o actualizada](#9-documentación-creada-o-actualizada)

---

## 1. Gitignore

**Archivo:** `.gitignore`

**Cambio:** Se añadieron entradas para no versionar assets publicados por paquetes (regenerables con `php artisan vendor:publish`):

- `/lang/vendor/`
- `/resources/views/vendor/`
- `/public/vendor/`

**Motivo:** Reducir el ruido de ~3k archivos sin seguimiento en la rama de Rafa (traducciones y vistas de Filament).

---

## 2. Análisis post-merge y orden de migraciones

**Documento:** `docs/ANALISIS_POST_MERGE_MIGRACIONES.md`

**Contenido:** Análisis de conflictos entre migraciones de Rafa y nuestras (tablas duplicadas, FKs inexistentes, orden de ejecución). Incluye:

- Conflictos críticos y menores
- Tablas que dependen de otras
- Orden de ejecución recomendado
- Acciones correctivas

**Cambios aplicados en código (ver sección 4):** Reorden de migraciones y ajuste de tabla `bancos`.

---

## 3. Renombrado de campos de usuarios a español

**Objetivo:** Alinear nomenclatura con Rafa (español en tablas/campos de negocio).

### 3.1 Migración

**Archivo:** `database/migrations/2026_03_04_200000_rename_our_fields_to_spanish_in_usuarios_table.php`

- `view_reports` → `ver_reportes`
- `user_tableau` → `usuario_tableau`
- `receive_newsletter` → `recibir_boletin`

Se ejecuta después de `2026_03_04_161940_add_report_and_newsletter_fields_to_usuarios_table.php`.

### 3.2 Modelo

**Archivo:** `app/Models/Usuario.php`

- **fillable:** `ver_reportes`, `usuario_tableau`, `recibir_boletin` (sustituyen los nombres en inglés).
- **casts:** `ver_reportes` y `recibir_boletin` como `boolean`.

### 3.3 Filament (formulario y tabla)

**Archivos:**

- `app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php`  
  Toggle `ver_reportes`, TextInput `usuario_tableau`, Toggle `recibir_boletin`.
- `app/Filament/Resources/Usuarios/Tables/UsuariosTable.php`  
  IconColumn `ver_reportes` (label "Reportes").
- `app/Filament/Resources/Usuarios/Pages/CreateUsuario.php`  
  Validación de límite de reportes usando `ver_reportes` y `data.ver_reportes`.
- `app/Filament/Resources/Usuarios/Pages/EditUsuario.php`  
  Misma validación en edición.

---

## 4. Migraciones de soporte (orden, bancos)

Para que `php artisan migrate` (o `migrate:fresh`) funcione en el orden correcto y sin duplicar `bancos`, se aplicaron estos cambios.

### 4.1 Orden de migraciones

- **Fase 2 (historiales, solicitudes, encuestas, etc.):**  
  Archivos renombrados de `2026_02_23_100011` … `100021` a **`2026_03_05_100011`** … **`2026_03_05_100021`** para que se ejecuten después de empleados, usuarios y tablas de catálogo.

- **Tablas de soporte:**  
  - `2026_03_02_090000_create_tablas_rafa_locales.php` → **`2026_02_26_090000_create_tablas_rafa_locales.php`**  
  - `2026_03_02_100000_create_tablas_faltantes.php` → **`2026_02_26_100000_create_tablas_faltantes.php`**  

  Así `departamentos`, `puestos`, `bancos`, `temas_voz`, etc. se crean antes de `usuarios` y del módulo de voz.

### 4.2 Tabla `bancos`

**Archivo:** `database/migrations/2026_03_04_171552_create_bancos_table.php`

- Si la tabla `bancos` **no existe:** se crea con `nombre`, `codigo`, `comision`, `softDeletes`, `timestamps`.
- Si **ya existe** (p. ej. creada por `tablas_faltantes`): se añaden las columnas `comision` y `deleted_at` si no existen.

Con esto se evita el error "Table 'bancos' already exists" al subir a develop (Rafa u otros pueden correr migraciones sin conflicto).

**Archivo:** `database/migrations/2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing.php`

- Migración adicional que asegura `comision` y `deleted_at` en `bancos` cuando la tabla fue creada solo por `tablas_faltantes` (estructura mínima). Necesaria para que `BancoSeeder` funcione en esas bases.

---

## 5. Seeders y migración bancos

**Documento:** `docs/SEEDERS_ANALISIS_Y_ORDEN.md`

- Listado de seeders, dependencias y orden recomendado.
- Orden completo para entorno de desarrollo (catálogos + empresa de ejemplo + Shield + usuario de prueba).

**Comandos ejecutados en la sesión (tras aplicar migración de `bancos`):**

```bash
php artisan db:seed --class=Inicial
php artisan db:seed --class=BancoSeeder
php artisan db:seed --class=EstadoAnimoAfeccionSeeder
php artisan db:seed --class=EstadoAnimoCaracteristicaSeeder
php artisan db:seed --class=ReconocimientosSeeder
php artisan db:seed --class=TemaVozColaboradoresSeeder
php artisan db:seed --class=EmpresaEjemploSeeder
php artisan shield:generate --all --panel=admin --no-interaction
php artisan db:seed --class=ShieldPermisosLegacySeeder
php artisan db:seed --class=SpatieRolesSeeder
php artisan db:seed --class=ShieldPermisosRolesSeeder
php artisan db:seed --class=WorkOSTestUserSeeder
```

Usuario de prueba para el panel: **test@workos.com** / **password123** (super_admin). Empresa de ejemplo: ID 1.

---

## 6. Login WorkOS y panel

### 6.1 Provider duplicado

**Archivo:** `app/Providers/Filament/AdminPanelProvider.php`

- **Problema:** `use App\Filament\Auth\WorkOsLogin` estaba duplicado (líneas 5 y 16), lo que provocaba error al ejecutar Artisan.
- **Cambio:** Se eliminó el `use` duplicado.

### 6.2 Página de login no era la de WorkOS

**Archivo:** `app/Providers/Filament/AdminPanelProvider.php`

- **Problema:** Se llamaba `->login(WorkOsLogin::class)` y después `->login()` sin argumentos, quedando la página de login por defecto de Filament (sin botón WorkOS).
- **Cambio:** Se eliminó la segunda llamada `->login()`. Se mantuvo `->login(WorkOsLogin::class)`, colores, favicon, brandName y brandLogo.

**Resultado:** En `/admin` se muestra la vista `resources/views/filament/auth/workos-login.blade.php` con el botón "Iniciar sesión con WorkOS" que apunta a `route('workos.login')` (`/auth/workos`).

---

## 7. Modelo SpatieRole

**Archivo:** `app/Models/SpatieRole.php`

- **Problema:** En `findByParam()` se usaba `static::withoutGlobalScopes()->query()`. `withoutGlobalScopes()` devuelve un `Builder`, que no tiene método `query()`, provocando `BadMethodCallException` al guardar/editar usuario (p. ej. al sincronizar roles).
- **Cambio:** Se reemplazó por `$query = static::withoutGlobalScopes();` y se usa ese builder para el resto de la consulta.

---

## 8. Logout WorkOS

**Problema:** Al cerrar sesión con un usuario que entró por WorkOS, la app redirigía a la página de logout de WorkOS; allí se cargan scripts de Segment (cdn.segment.com). Si un bloqueador los bloquea (ERR_BLOCKED_BY_CLIENT), WorkOS muestra "Couldn't sign in" en lugar de redirigir bien.

### 8.1 Configuración

**Archivo:** `config/services.php`

- Nueva opción en `workos`: **`skip_logout_redirect`** (por defecto `true`).
- Variable de entorno: **`WORKOS_SKIP_LOGOUT_REDIRECT`** (por defecto no definida; el valor por defecto en código es `true`).

### 8.2 Respuesta de logout

**Archivo:** `app/Http/Responses/WorkOsLogoutResponse.php`

- Si `config('services.workos.skip_logout_redirect', true)` es `true`: se redirige directamente a `route('filament.admin.auth.login')` sin llamar a la URL de logout de WorkOS.
- Si es `false`: se mantiene el comportamiento anterior (redirigir a WorkOS con SID y `returnTo`).

**Resultado:** Por defecto el logout es solo local y el usuario vuelve al login del panel sin pasar por la página de WorkOS, evitando el error de Segment.

---

## 9. Documentación creada o actualizada

| Documento | Descripción |
|-----------|-------------|
| `docs/ANALISIS_POST_MERGE_MIGRACIONES.md` | Conflictos de migraciones post-merge, orden y acciones correctivas. |
| `docs/SEEDERS_ANALISIS_Y_ORDEN.md` | Análisis de seeders, dependencias y orden recomendado de ejecución. |
| `docs/REGISTRO_CAMBIOS_2026_03_04.md` | Este archivo: resumen de todos los cambios de la sesión. |

---

## Resumen de archivos tocados

- **Config:** `config/services.php`
- **Providers:** `app/Providers/Filament/AdminPanelProvider.php`
- **Modelos:** `app/Models/Usuario.php`, `app/Models/SpatieRole.php`
- **Filament:** `app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php`, `Tables/UsuariosTable.php`, `Pages/CreateUsuario.php`, `Pages/EditUsuario.php`
- **Auth/WorkOS:** `app/Http/Responses/WorkOsLogoutResponse.php`
- **Migraciones:** `2026_03_04_200000_rename_our_fields_to_spanish_in_usuarios_table.php`, `2026_03_04_171552_create_bancos_table.php`, `2026_03_04_210000_add_comision_and_soft_deletes_to_bancos_if_missing.php`
- **Renombres de archivos de migración:** Fase 2 (`2026_02_23_*` → `2026_03_05_*`), `tablas_rafa_locales` y `tablas_faltantes` (→ `2026_02_26_090000` y `2026_02_26_100000`)
- **Raíz:** `.gitignore`
- **Docs:** `docs/ANALISIS_POST_MERGE_MIGRACIONES.md`, `docs/SEEDERS_ANALISIS_Y_ORDEN.md`, `docs/REGISTRO_CAMBIOS_2026_03_04.md`

---

*Última actualización: 2026-03-04*
