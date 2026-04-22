# DOCUMENTACIÓN DEL MÓDULO DE USUARIOS - TECBEN-CORE

Documentación completa del módulo de usuarios para desarrollo, negocio, onboarding, soporte y QA.

---

## 1. VISIÓN GENERAL

### 1.1 Propósito del módulo

El módulo de usuarios centraliza la gestión de cuentas que acceden al panel de administración (Filament) y/o a la app, con tres tipos de rol funcional: **user** (panel sin empresa), **admin** (administrador de empresa, con reportes/Tableau/newsletter) y **employee** (empleado vinculado a un registro en `empleados`, acceso vía app). Incluye integración con WorkOS (SSO/Google), Shield (roles y permisos), límite de reportes por empresa y scope multitenant automático.

### 1.2 Tipos de usuario

| Tipo       | Descripción breve                          | Empresa | Roles panel | Uso principal        |
|-----------|---------------------------------------------|---------|-------------|-----------------------|
| **user**  | Usuario básico de panel                     | No      | Sí          | Admins globales       |
| **admin** | Administrador de empresa                   | Sí      | Sí          | RH / gerentes        |
| **employee** | Empleado con acceso a la app            | Sí      | No          | Colaboradores app    |

### 1.3 Arquitectura (tablas, modelos, recursos)

- **Tabla:** `usuarios` (modelo `App\Models\Usuario`).
- **Relaciones principales:** `empresa`, `empleado`, `roles` (Spatie).
- **Recurso Filament:** `App\Filament\Resources\Usuarios\UsuarioResource` (listado, creación, edición).
- **Página de perfil:** `App\Filament\Pages\Perfil` (datos personales y cambio de contraseña).
- **Política:** `App\Policies\UsuarioPolicy` (permisos Shield por acción).

---

## 2. ESTRUCTURA DE DATOS

### 2.1 Tabla `usuarios`

| Campo               | Tipo         | Nulable | Único | Descripción |
|---------------------|--------------|---------|-------|-------------|
| id                  | bigint       | No      | Sí (PK) | ID |
| workos_id           | string       | Sí      | Sí    | ID de WorkOS (SSO) |
| nombre              | string       | No      | No    | Nombre |
| apellido_paterno    | string       | Sí      | No    | Apellido paterno |
| apellido_materno    | string       | Sí      | No    | Apellido materno |
| email               | string       | No      | Sí    | Email (login) |
| password            | string       | Sí      | No    | Contraseña hasheada |
| avatar              | string       | Sí      | No    | URL avatar (p. ej. WorkOS) |
| telefono            | string(20)   | Sí      | No    | Teléfono |
| celular             | string(20)   | Sí      | No    | Celular |
| tipo                | string(50)   | No      | No    | `user`, `admin` o `employee` (default: `user`) |
| empresa_id          | bigint FK    | Sí      | No    | Empresa (admin/employee) |
| departamento_id     | bigint FK    | Sí      | No    | Departamento |
| puesto_id           | bigint FK    | Sí      | No    | Puesto |
| empleado_id         | bigint FK    | Sí      | No    | Empleado vinculado (solo tipo employee) |
| imagen              | string       | Sí      | No    | Foto subida (ruta) |
| view_reports        | boolean      | No      | No    | Acceso a reportes (solo admin, con límite por empresa) |
| user_tableau        | string       | Sí      | No    | Usuario Tableau (si empresa tiene analíticas) |
| receive_newsletter  | boolean      | No      | No    | Recibir newsletter (si empresa envía boletín) |
| email_verified_at   | timestamp    | Sí      | No    | Verificación email (WorkOS) |
| google2fa_secret    | string       | Sí      | No    | Secret 2FA (oculto en serialización) |
| enable_2fa          | boolean      | No      | No    | 2FA habilitado |
| verified_2fa_at     | timestamp    | Sí      | No    | Fecha verificación 2FA |
| remember_token      | string       | Sí      | No    | Token “recordarme” |
| created_at          | timestamp    | No      | No    | Creación |
| updated_at          | timestamp    | No      | No    | Actualización |

### 2.2 Relaciones

- **Usuario → Empresa:** `belongsTo` (empresa_id). Solo usado en admin/employee.
- **Usuario → Empleado:** `belongsTo` (empleado_id). Solo en tipo employee; el empleado debe ser de la misma empresa.
- **Usuario → Roles (Shield):** `belongsToMany` vía Spatie (`model_has_roles`). Solo user/admin tienen roles de panel.
- **Empresa → Usuarios:** `hasMany` (empresa_id). Usado para límite de reportes (`empresa->usuarios()->where('view_reports', true)->count()`).

### 2.3 Campos específicos (reportes, Tableau, newsletter)

- **view_reports:** Boolean. Solo significativo para tipo `admin`. Al activarse se valida contra `empresas.num_usuarios_reportes`.
- **user_tableau:** String. Visible en formulario solo si tipo admin y `empresa.tiene_analiticas_por_ubicacion` es verdadero.
- **receive_newsletter:** Boolean. Visible en formulario solo si tipo admin y `empresa.enviar_boletin` es verdadero.

---

## 3. CRUD DE USUARIOS (FILAMENT)

### 3.1 Acceso

- **Ruta:** `/admin/usuarios`
- **Permiso necesario:** `ViewAny:Usuario` (p. ej. rol `super_admin` tiene todos los permisos del recurso).
- **Scope:** `super_admin` ve todos; usuarios con `empresa_id` ven solo usuarios de su empresa; usuarios sin empresa ven solo usuarios sin empresa (tipo user). Implementado en `UsuarioResource::getEloquentQuery()`.

### 3.2 Listado

**Columnas:**

- **id** (oculta por defecto, ordenable)
- **nombre** (buscable, ordenable)
- **apellido_paterno** / **apellido_materno** (ocultas por defecto)
- **email** (buscable, ordenable)
- **tipo** (badge: Usuario / Administrador / Empleado, colores gray/primary/success)
- **empresa.nombre** (placeholder "—" si no hay)
- **view_reports** (icono ojo / ojo tachado, oculta por defecto)
- **roles_count** (cantidad de roles, oculta por defecto)
- **created_at** (fecha/hora, oculta por defecto)

**Filtros:**

- Por **tipo:** Usuario | Administrador | Empleado
- Por **empresa:** relación `empresa.nombre` (placeholder "Todas")

**Acciones por fila:**

- Editar
- Eliminar (confirmación estándar de Filament)

### 3.3 Creación

**Campos comunes (todos los tipos):**

- Nombre, Apellido paterno, Apellido materno (requeridos)
- Email (requerido, único)
- Contraseña (requerida en creación), Confirmar contraseña
- Teléfono, Celular (opcionales)
- Foto (FileUpload, imagen, máx. 2MB, directorio `usuarios`)

**Sección "Tipo de usuario":**

- **Tipo:** Selector (Usuario básico panel | Administrador de empresa | Empleado app). Requerido, `live()` para mostrar/ocultar campos.
- **Empresa:** Selector (solo visible para admin/employee; requerido en esos tipos). Para no super_admin se fija con `Hidden` a la empresa del usuario actual.
- **Empleado:** Selector (solo tipo employee). Opciones: empleados de la empresa seleccionada; se usa `Empleado::nombre_completo`. Requerido y único por empleado para tipo employee.

**Sección "Configuración de administrador"** (solo tipo admin):

- Ver reportes (Toggle). Al activar se valida límite `empresas.num_usuarios_reportes`.
- Usuario Tableau (texto). Visible solo si la empresa tiene `tiene_analiticas_por_ubicacion`.
- Recibir newsletter (Toggle). Visible solo si la empresa tiene `enviar_boletin`.

**Sección "Roles":**

- Selector múltiple de roles. Visible solo para tipo `user` o `admin`. Opciones según empresa del usuario actual (super_admin ve todos; resto ve roles de su empresa vía `SpatieRole::forCompany()`).

**Secciones solo lectura (si aplican):**

- WorkOS: workos_id, avatar, email_verified_at (visible si el usuario tiene workos_id).
- 2FA: enable_2fa, verified_2fa_at (visible si tiene secret o 2FA habilitado).

### 3.4 Edición

- Mismos campos que creación, con datos precargados.
- Contraseña y confirmación opcionales; solo se actualiza si se escribe nueva contraseña.
- Roles se cargan en `mutateFormDataBeforeFill()` y se sincronizan en `afterSave()`.
- Límite de reportes se valida excluyendo al usuario actual del conteo (`where('id', '!=', $this->record->id)`).

### 3.5 Eliminación

- Acción estándar `DeleteAction` en la página de edición.
- Permiso requerido: `Delete:Usuario`.
- En el código actual **no** hay restricciones adicionales por relaciones (voces, encuestas, mensajes, etc.); la eliminación es directa. Si en el futuro se añaden FKs o reglas de negocio que impidan borrar, se documentarán aquí y en la política/recurso.

---

## 4. TIPOS DE USUARIO (DETALLE)

### 4.1 Tipo `user` (usuario básico de panel)

- **Descripción:** Usuario de panel sin empresa asignada.
- **Casos de uso:** Administradores globales, supervisores sin empresa.
- **Campos visibles:** Datos personales + selector de tipo (sin empresa ni empleado) + Roles.
- **Roles disponibles:** Todos los roles (super_admin) o roles globales / de la empresa del usuario que crea, según `SpatieRole::forCompany($companyId)` con `company_id` null o del contexto.

### 4.2 Tipo `admin` (administrador de empresa)

- **Descripción:** Administrador asociado a una empresa.
- **Casos de uso:** Gerentes de RH, administradores de empresa.
- **Campos visibles:** Datos personales + Empresa + Ver reportes + Usuario Tableau (condicional) + Recibir newsletter (condicional) + Roles.
- **Límite de reportes:** Definido por `empresas.num_usuarios_reportes`. Al activar "Ver reportes" se comprueba que el número de usuarios de esa empresa con `view_reports = true` no supere el límite (en creación; en edición se excluye al propio usuario).
- **Roles disponibles:** Roles de la empresa del usuario actual (o todos si es super_admin).

### 4.3 Tipo `employee` (empleado con acceso a app)

- **Descripción:** Empleado vinculado a un registro en `empleados`.
- **Casos de uso:** Colaboradores que usan la app.
- **Campos visibles:** Datos personales + Empresa + Empleado (selector).
- **Vinculación:** `empleado_id` debe ser de un empleado de la empresa seleccionada; en el formulario se valida unicidad para tipo employee.
- **Roles:** No se muestran roles (acceso vía app, no panel).

---

## 5. PERFIL DE USUARIO

### 5.1 Acceso

- **Ruta:** `/admin/perfil`
- **Slug:** `perfil` (`Perfil::getSlug()`).
- **Navegación:** No se registra en el menú (`shouldRegisterNavigation = false`). Se puede enlazar desde el menú de usuario o cabecera.
- **Restricción:** Solo el usuario autenticado (abort 403 si no es `Usuario`).

### 5.2 Campos editables

- Nombre, Apellido paterno, Apellido materno
- Teléfono, Celular
- Foto (FileUpload)
- Email se muestra pero está deshabilitado (no se edita desde perfil).

### 5.3 Cambio de contraseña

- **Contraseña actual:** Obligatoria si se desea cambiar. Se valida con `Hash::check()`; si no coincide se muestra notificación de error y no se guarda.
- **Nueva contraseña:** Mínimo 8 caracteres (`minLength(8)`), debe coincidir con confirmación (`same('new_password_confirmation')`). Campos no deshidratados; solo se actualizan si se rellenan.
- **Confirmar nueva contraseña:** Debe coincidir con nueva contraseña.
- La sección es colapsable. Si no se rellena nueva contraseña, no se modifica la contraseña.

### 5.4 2FA

- Se muestran **enable_2fa** y **verified_2fa_at** en solo lectura.
- Visible si el usuario tiene `google2fa_secret` o `enable_2fa` en true. La gestión/activación de 2FA se hace en otra sección si existe.

---

## 6. ROLES Y PERMISOS (SHIELD)

### 6.1 Permisos del recurso Usuario (formato Action:Usuario)

- ViewAny:Usuario  
- View:Usuario  
- Create:Usuario  
- Update:Usuario  
- Delete:Usuario  
- ForceDelete:Usuario  
- ForceDeleteAny:Usuario  
- Restore:Usuario  
- RestoreAny:Usuario  
- Replicate:Usuario  
- Reorder:Usuario  

Generados con `php artisan shield:generate --all --panel=admin` (o `--resource=Usuario`). El rol `super_admin` debe tener asignados estos permisos (p. ej. vía seeder o panel Shield).

### 6.2 Roles por defecto (referencia)

| Rol            | Descripción breve     | Uso típico                          |
|----------------|------------------------|-------------------------------------|
| super_admin   | Acceso total          | Ver/crear/editar/eliminar todos los usuarios |
| admin_empresa | Administrador empresa | Gestionar usuarios de su empresa    |
| rh_empresa    | RH                    | Ver usuarios de su empresa          |

Los roles con `company_id` se filtran por empresa en el selector de roles del formulario (`SpatieRole::forCompany($companyId)`).

### 6.3 Asignación de roles

- En creación/edición de usuario (tipos `user` y `admin`), selector múltiple de roles.
- Opciones según usuario autenticado: super_admin ve todos los roles (sin scope); resto ve roles de su empresa (`forCompany($user->empresa_id)`).
- Se guardan con `syncRoles($roleIds)` en `afterCreate()` / `afterSave()`.

---

## 7. REGLAS DE NEGOCIO

### 7.1 Email único

- Validación en formulario: `unique(ignoreRecord: true)` en creación y edición.
- No se permiten dos usuarios con el mismo email.

### 7.2 Contraseña

- Mínimo 8 caracteres en formulario de usuario y en perfil (nueva contraseña).
- En edición de usuario: solo se actualiza si el campo contraseña tiene valor; si está vacío no se modifica.
- En perfil: se exige contraseña actual correcta para poder cambiar.

### 7.3 Límite de reportes

- Campo `empresas.num_usuarios_reportes` define el máximo de usuarios con `view_reports = true` por empresa.
- Al activar "Ver reportes" en un admin se valida en `CreateUsuario::validateReportLimit()` y `EditUsuario::validateReportLimit()`.
- En edición se excluye al usuario actual del conteo. Mensaje de error: "Se alcanzó el límite de usuarios con acceso a reportes para esta empresa."

### 7.4 Scope por empresa (multitenant)

- **super_admin:** ve todos los usuarios (sin filtro en `getEloquentQuery()`).
- **Usuario con empresa_id:** solo ve usuarios con su mismo `empresa_id`.
- **Usuario sin empresa:** solo ve usuarios con `empresa_id` null.

Implementado en `UsuarioResource::getEloquentQuery()`, no en la política. La política solo comprueba permisos (ViewAny:Usuario, etc.).

### 7.5 Comportamiento por tipo

- **user:** sin empresa; puede tener roles de panel.
- **admin:** empresa obligatoria; puede tener view_reports, user_tableau, receive_newsletter y roles.
- **employee:** empresa y empleado obligatorios; empleado debe ser de la empresa; sin roles en el formulario.

---

## 8. FLUJOS DE TRABAJO (EJEMPLOS)

### 8.1 Crear un admin con reportes

1. Ir a `/admin/usuarios` → "Nuevo usuario".
2. Tipo: "Administrador de empresa".
3. Completar datos personales y email.
4. Seleccionar empresa.
5. Activar "Ver reportes" (comprobar que no se supere el límite de la empresa).
6. Asignar roles (p. ej. admin_empresa).
7. Guardar.

### 8.2 Crear un employee vinculado a empleado

1. Ir a `/admin/usuarios` → "Nuevo usuario".
2. Tipo: "Empleado (app)".
3. Completar datos personales y email.
4. Seleccionar empresa.
5. Seleccionar empleado del listado (solo empleados de esa empresa).
6. Guardar.

### 8.3 Probar límite de reportes

1. Configurar en la empresa `num_usuarios_reportes = 2`.
2. Crear dos admins de esa empresa con "Ver reportes" activado.
3. Al intentar activar "Ver reportes" en un tercer admin de la misma empresa, debe mostrarse el error de límite.

### 8.4 Cambiar contraseña desde perfil

1. Ir a `/admin/perfil`.
2. En "Cambiar contraseña": introducir contraseña actual, nueva contraseña (≥ 8 caracteres) y confirmación.
3. Guardar. Si la actual es incorrecta, se muestra notificación y no se guarda.

### 8.5 Asignar roles a usuario

1. Editar usuario tipo user o admin.
2. En sección "Roles", seleccionar uno o varios roles del selector múltiple.
3. Guardar. Los roles se sincronizan con `syncRoles()`.

---

## 9. INTEGRACIONES

### 9.1 WorkOS

- **Campos en usuarios:** workos_id, avatar, email_verified_at.
- En el recurso Usuario se muestran en sección "WorkOS" solo lectura cuando el usuario tiene workos_id (deshabilitados y no deshidratados).

### 9.2 2FA

- **Campos:** enable_2fa, google2fa_secret (oculto), verified_2fa_at.
- En recurso y en perfil: solo lectura. La habilitación/deshabilitación de 2FA se gestiona en otro flujo si existe.

### 9.3 Tableau

- Campo **user_tableau**. Visible en formulario solo para tipo admin y si la empresa tiene `tiene_analiticas_por_ubicacion` verdadero.

### 9.4 Newsletter

- Campo **receive_newsletter**. Visible en formulario solo para tipo admin y si la empresa tiene `enviar_boletin` verdadero.

---

## 10. PRUEBAS SUGERIDAS

### 10.1 Checklist QA (resumen)

- [ ] Crear usuario tipo user (sin empresa, con roles).
- [ ] Crear usuario tipo admin (con empresa, reportes, roles); verificar límite de reportes.
- [ ] Crear usuario tipo employee (empresa + empleado).
- [ ] Editar usuario: cambiar tipo, empresa, roles; contraseña opcional.
- [ ] Eliminar usuario (con permiso Delete:Usuario).
- [ ] Perfil: editar datos y cambiar contraseña (contraseña actual correcta/incorrecta).
- [ ] Scope: como no super_admin solo se ven usuarios de la misma empresa (o sin empresa si el usuario no tiene empresa).
- [ ] Email único: no permitir duplicado en creación ni edición.
- [ ] Selector de empleados: solo de la empresa seleccionada; unicidad para employee.

### 10.2 Casos de error

- Límite de reportes: activar "Ver reportes" cuando ya se alcanzó el límite → mensaje claro.
- Email duplicado → validación de unicidad.
- Contraseña actual incorrecta en perfil → notificación y no guardar nueva contraseña.

---

## 11. PREGUNTAS FRECUENTES

**¿Cómo crear un usuario sin empresa?**  
Seleccionar tipo "Usuario básico (panel)". No se muestra el campo empresa.

**¿Qué pasa si alcanzo el límite de reportes?**  
No podrás activar "Ver reportes" en más admins de esa empresa hasta aumentar `num_usuarios_reportes` en la empresa o desactivar reportes en otros usuarios.

**¿Puedo cambiar el tipo de usuario después de creado?**  
Sí. En edición se puede cambiar el tipo; los campos condicionales se muestran/ocultan según el tipo seleccionado. Al cambiar a employee hay que asignar empleado; al cambiar a user/admin se pueden asignar roles.

**¿Cómo se asigna un empleado a un usuario?**  
Solo en tipo "employee". Primero se elige empresa; el selector "Empleado" muestra empleados de esa empresa (por `nombre_completo`). Cada empleado solo puede estar vinculado a un usuario tipo employee (validación de unicidad).

**¿Por qué no veo todos los usuarios en el listado?**  
El recurso aplica scope por empresa: si no eres super_admin, solo ves usuarios de tu empresa (o usuarios sin empresa si tú no tienes empresa).

**¿Qué permisos necesita un usuario para ver el listado?**  
`ViewAny:Usuario`. El rol super_admin lo tiene por defecto si se le han asignado los permisos del recurso Usuario.

---

## 12. REFERENCIA TÉCNICA

### 12.1 Archivos principales

```
app/Models/Usuario.php
app/Models/Empresa.php
app/Models/Empleado.php
app/Models/SpatieRole.php

app/Filament/Resources/Usuarios/
├── UsuarioResource.php
├── Schemas/UsuarioForm.php
├── Tables/UsuariosTable.php
└── Pages/
    ├── ListUsuarios.php
    ├── CreateUsuario.php
    └── EditUsuario.php

app/Filament/Pages/Perfil.php
app/Policies/UsuarioPolicy.php

database/migrations/
├── 2026_02_26_100003_create_usuarios_table.php
└── 2026_03_04_161940_add_report_and_newsletter_fields_to_usuarios_table.php
```

### 12.2 Métodos clave

**Usuario.php**

- `getNameAttribute()`: nombre para mostrar (nombre + apellidos o email).
- `empresa()`, `empleado()`, `roles()`: relaciones.
- `rolesDisponibles()`: roles según empresa del usuario (super_admin ve todos; resto vía `SpatieRole::forCompany($companyId)`).

**CreateUsuario.php / EditUsuario.php**

- `mutateFormDataBeforeCreate()` / `mutateFormDataBeforeSave()`: excluyen password_confirmation y roles; validan límite de reportes; contraseña opcional en edición.
- `afterCreate()` / `afterSave()`: sincronizan roles con `syncRoles($roleIds)`.
- `validateReportLimit()`: comprueba `empresas.num_usuarios_reportes` frente a usuarios con view_reports; en edición excluye al propio usuario.

**UsuarioResource.php**

- `getEloquentQuery()`: aplica scope multitenant (super_admin sin filtro; con empresa → mismo empresa_id; sin empresa → empresa_id null).

**UsuarioForm.php**

- `empresaSelect()`: devuelve Hidden si no es super_admin y el usuario tiene empresa; si no, Select de empresas.
- `rolesOptions()`: roles para el selector (super_admin sin scope; resto forCompany).
- `empresaTieneAnaliticas()` / `empresaEnviaBoletin()`: condicionales para mostrar user_tableau y receive_newsletter.

**UsuarioPolicy.php**

- Métodos por acción (viewAny, view, create, update, delete, restore, forceDelete, etc.) que delegan en `$user->can('Action:Usuario')`.

**Perfil.php**

- `fillForm()`: rellena con datos del usuario autenticado.
- `save()`: actualiza datos personales; si hay nueva contraseña, valida actual con `Hash::check()` y asigna la nueva (hasheada por cast del modelo).

### 12.3 Eventos

No hay eventos específicos del módulo (listeners de creación/edición de usuario) documentados en el código actual.

---

## 13. HISTORIAL DE CAMBIOS

| Fecha     | Versión | Cambios              | Autor   |
|----------|---------|----------------------|--------|
| 2026-03-04 | 1.0   | Documentación inicial | Sistema |
