# Análisis del CRUD de usuarios en Legacy (Paco)

**Objetivo:** Extraer del legacy el comportamiento del CRUD de usuarios (panel admin) para replicar o mejorar en tecben-core sin perder funcionalidades críticas.  
**Alcance:** Controlador principal `UsersController` (usuarios tipo `user`). El CRUD de **High Users** (`HighUsersController`) es un módulo distinto y gestiona usuarios con `company_id` y `high_employee_id`; se menciona al final como referencia.

---

## 1. Controlador principal

- **Ruta:** `app/Http/Controllers/Admin/UsersController.php`
- **Métodos encontrados:**

| Método | Ruta (name) | Descripción |
|--------|-------------|-------------|
| `getIndex()` | admin_users | Vista listado (DataTable) |
| `getList()` | get_admin_users | JSON para DataTable (solo type='user') |
| `getView()` | get_admin_users_view | JSON alternativo para vista (misma lógica que getList) |
| `getCreate()` | admin_users_create | Formulario de creación |
| `create()` | admin_users_create (POST) | Guardar nuevo usuario |
| `getUserView($user_id)` | admin_users_view | Vista detalle (ver usuario) |
| `getEdit($user_id)` | admin_users_edit | Formulario de edición |
| `update()` | admin_users_update (POST) | Actualizar usuario |
| `getEditProfile($user_id)` | admin_users_edit_profile | Editar perfil (solo el usuario logueado) |
| `updateProfile()` | admin_users_update_profile (POST) | Guardar perfil |
| `Trash($user_id)` | admin_users_trash | Eliminar usuario (borrado físico) |
| `getVerifySession()` | admin_users_session | Verificar si la sesión expiró (límite por empresa) |
| `updatePassword()` | admin_users_update_password (POST) | Cambiar contraseña (AJAX/API) |

**Importante:** Este CRUD trabaja **únicamente con usuarios de tipo `user`**. No crea ni edita `company_id` ni `high_employee_id`; esos campos quedan vacíos/null. Los usuarios tipo `high_user` (con empresa y opcionalmente empleado vinculado) se gestionan en **HighUsersController** (`admin/high_users`).

---

## 2. Validaciones (inline, sin Form Requests)

No existen `StoreUserRequest` ni `UpdateUserRequest`. Todas las validaciones se hacen con `Validator::make()` dentro del controlador.

### Creación (`create`)

```php
'name' => 'required',
'email' => 'required|email',
'mother_last_name' => 'required',
'paternal_last_name' => 'required',
'password' => 'required',
'confirm_password' => 'required',
```

- **Email único (a nivel aplicación):**  
  `User::where("email", $data["email"])->whereIn('type', ['user', 'high_user'])->first()`  
  Si existe → error "Ya existe un usuario registrado con ese correo electrónico".
- **Contraseña:**  
  - Debe coincidir con `confirm_password`.  
  - Mínimo 5 caracteres (`strlen($password) < 5`).  
  - Se hashea con `Hash::make()`.
- **Foto (opcional):**  
  Si viene `photo`: `file|mimes:jpg,jpeg,png,bmp|max:20000` (20MB).  
  Se sube a S3: `users/profile_images/`. Resize 150x150, aspect ratio, PNG.

### Edición (`update`)

```php
'name' => 'required',
'mother_last_name' => 'required',
'paternal_last_name' => 'required',
'email' => 'required|email',
```

- **Email único:** Mismo check que en creación, excluyendo el usuario actual:  
  `$check_user->id != $user_id`.
- **Contraseña:** Opcional. Si se envía: misma regla de coincidencia y mínimo 5 caracteres.  
  Si se actualiza contraseña también se setea `update_password = 'SI'` y `last_password_update = now()`.
- **Foto:** Misma regla que en creación; si ya existe imagen en `users/profile_images` se reutiliza el mismo path.

### Perfil (`updateProfile`)

- Mismas reglas que edición (nombre, apellidos, email, contraseña opcional, foto).
- **Restricción:** Solo el propio usuario puede editar su perfil:  
  `$edit_user->id != $current_user->id` → redirect con mensaje "El usuario que intenta editar no coincide con el logueado actualmente".

### Cambio de contraseña (`updatePassword`)

- Respuesta JSON.
- Reglas: contraseñas deben coincidir, mínimo 5 caracteres.
- **La nueva contraseña no puede ser igual a la actual:**  
  `Hash::check($password, $edit_user->password)` → error "La contraseña nueva no puede ser igual a la anterior".
- Al actualizar: `update_password = 'NO'`, `last_password_update = now()`.

---

## 3. Vistas principales

| Vista | Uso |
|-------|-----|
| `resources/views/admin/users/list.blade.php` | Listado con DataTable; botón Crear; modal de confirmación de eliminación. |
| `resources/views/admin/users/create.blade.php` | Formulario creación: datos personales, contraseña/confirmación, roles (switches), foto. |
| `resources/views/admin/users/edit.blade.php` | Formulario edición: datos en hidden + modal "Editar" para cambiar nombre/email/apellidos; contraseña opcional; roles; foto. |
| `resources/views/admin/users/view.blade.php` | Solo lectura: datos personales y tabla de roles. |
| `resources/views/admin/users/edit_profile.blade.php` | Perfil del usuario logueado: datos, contraseña opcional, roles (solo si es admin). Bloque 2FA (Activar/Desactivar) solo si `Auth::user()->hasRoles('admin')`; los formularios 2FA no tienen `action` en la vista (se manejan por JS/ruta externa, p. ej. Google2FAController). |

- **Layout:** `layouts.main`; sección `content` y `scripts`.
- **Componentes:** No se usan componentes Blade reutilizables; formularios por vista.
- **Roles:** Listados con `Role::get()` (todos los roles, sin filtrar por `company_id`). Switches con `name="roles[{{ $role->name }}]"`.
- **Paginación:** DataTable con `pageLength: 10`, orden por columna 0 desc; datos vía AJAX a `get_admin_users`.

---

## 4. Campos y relaciones destacadas

### Campos que usa el CRUD (UsersController)

- **Siempre guardados en create/update:**  
  `name`, `email`, `mother_last_name`, `paternal_last_name`, `phone` (vacío), `mobile` (vacío), `type` ('user'), `has_report_user` ('SI' en creación), `image` (path S3 o vacío), `update_password` ('SI' en creación; en update solo si se cambia contraseña).
- **Solo en creación:** `password` (hasheado).
- **Solo en update cuando se cambia contraseña:** `password`, `update_password`, `last_password_update`.
- **No se tocan en este CRUD:** `company_id`, `high_employee_id`, `position_id`, `department_id`, `area_id`, `user_tableau`, `receive_newsletter`, `google2fa_secret`, `verified_2fa_at`, `enable_2fa`, `token_batch`, `email_verified_at`.

### Relaciones utilizadas

- **User → roles:** `$user->roles` (Entrust, belongsToMany). Asignación con `attachRole($role->id)`; en update primero `detachRoles($edit_user->roles)` y luego attach de los nuevos.
- **User → logs:** Para auditoría: `$user_current->logs()->save($log)` y opcionalmente `$company_user->logs()->save($log)` si el usuario tiene `company_id`.
- **User → high_employee:** En el modelo con `withTrashed()`; no se usa en este CRUD.
- **User → company, department, position, area:** En el modelo; no se asignan en UsersController.

### Modelo User (`app/User.php`)

- **Fillable:** name, email, password, mother_last_name, paternal_last_name, phone, mobile, image, type, has_report_user, notification_voice_employees, position_id, department_id, company_id, high_employee_id, user_tableau, receive_newsletter, update_password, last_password_update, google2fa_secret, verified_2fa_at, enable_2fa, token_batch.
- **Accessors:** `getFullNameAttribute`, `getImageUrlAttribute` (local o S3 con URL temporal).
- **Métodos:** `getCurrentRol()`, `hasRoles($roles)`, `getCurrent()` (Auth::user()).

---

## 5. Flujos específicos

### Creación

1. getCreate: se cargan todos los roles (`Role::get()`).
2. create: validar datos → comprobar email único (type user/high_user) → validar y hashear contraseña → crear User con type 'user', update_password 'SI', phone/mobile vacíos, has_report_user 'SI'.
3. Si hay foto: validar mime/size → resize → subir a S3 → guardar path en `user->image`.
4. Asignar roles desde `$request->roles` (keys = role name): `Role::whereIn('name', array_keys($roles))->get()` y `$user->attachRole($role->id)`.
5. Crear Log asociado al usuario actual y opcionalmente a la empresa del usuario actual; redirect a listado con mensaje de éxito.

### Edición

1. getEdit: cargar usuario por id; si no existe redirect a listado con mensaje. Cargar todos los roles.
2. update: validar → email único excluyendo el usuario actual → si hay password, validar y actualizar password + update_password + last_password_update en un `User::where()->update()`.
3. Actualizar en el modelo: name, email, apellidos, phone, mobile, type. Si hay foto: misma lógica que en creación (reutilizar path si ya es users/profile_images).
4. detachRoles + attachRoles según request.
5. Log y redirect a admin_users_edit con mensaje de éxito.

### Eliminación (Trash)

1. Comprobar que el usuario existe.
2. **Restricción:** No se puede borrar si tiene:
   - `comments_attended()` (voz atendidos),
   - `sent_surveys()`,
   - `created_surveys()`,
   - `sent_messages()`,
   - `voice_employee_subjects()`,
   - `read_comments()`,
   - `high_employee_filters()`.
   Si alguno existe → redirect back con error "No puede borrar un usuario con registros asignados."
3. Crear Log.
4. `$user->detachRoles($user->roles)`.
5. Si `file_exists($user->image)` borrar de disco `uploads` (no S3 en este paso).
6. `User::where("id", $user_id)->delete()` → **borrado físico** (no soft delete en User).

### Perfil

- getEditProfile: solo permite editar si `$edit_user->id == $current_user->id`.
- updateProfile: misma comprobación; mismas validaciones y lógica que update (datos, contraseña opcional, foto, roles). En la vista de perfil, la sección de roles solo se muestra si el usuario es admin; la sección 2FA también solo para admin (los formularios 2FA se manejan fuera de esta vista).

### Cambio de contraseña (updatePassword)

- Usa el usuario logueado; valida coincidencia, longitud mínima 5 y que la nueva no sea igual a la actual. Actualiza password, update_password = 'NO', last_password_update. Respuesta JSON success/error.

---

## 6. Reglas de negocio extraídas

1. **Tipo fijo:** Este CRUD solo crea/edita usuarios con `type = 'user'` (panel admin). No se asigna empresa ni empleado.
2. **Email único:** A nivel aplicación, único entre tipos `user` y `high_user` (no se considera `high_employee` en este check).
3. **Contraseña:** Mínimo 5 caracteres; confirmación obligatoria en creación y cuando se cambia en edición/perfil; la nueva no puede ser igual a la actual en updatePassword.
4. **update_password:** En creación se setea 'SI' (forzar cambio). Al cambiar contraseña en update se vuelve a 'SI'; en updatePassword se setea 'NO'.
5. **last_password_update:** Se actualiza cuando se cambia la contraseña (update y updatePassword).
6. **Eliminación:** Física; prohibida si el usuario tiene comentarios atendidos, encuestas enviadas/creadas, mensajes enviados, temas de voz, comentarios leídos o filtros de empleado.
7. **Auditoría:** Toda creación, actualización y eliminación se registra en Log (usuario actual y, si tiene, empresa del usuario actual).
8. **Perfil:** Solo el propio usuario puede editar su perfil; admin puede además ver/editar roles y sección 2FA en esa misma vista.
9. **Imagen:** Opcional; jpg/jpeg/png/bmp, máx 20MB; almacenamiento en S3 en `users/profile_images/`; resize 150x150 manteniendo aspecto.
10. **Roles:** Se listan todos (Role::get()); no hay filtro por company_id en este CRUD. Asignación múltiple (attach/detach).

---

## 7. Permisos y acceso

- **Middleware:** Todas las rutas de usuarios pasan por `logged` y `2fa`. Además:
  - view_users: getIndex, getList, getUserView.
  - create_users: getCreate, create.
  - edit_users: getEdit, update.
  - trash_users: Trash.
- **Perfil:** getEditProfile y updateProfile no usan middleware de permiso; solo comprueban que el id sea el del usuario logueado.
- **updatePassword:** Solo `logged`; cualquier usuario autenticado puede cambiar su contraseña.
- El middleware `Permissions` usa `getCurrentRol()->hasPermissions(permissions_and)` y `hasOnePermission(permissions_or)` (ver `app/Http/Middleware/Permissions.php`).

---

## 8. Listados y filtros

- **Listado:** Una sola tabla DataTable; datos por AJAX a `get_admin_users`.
- **Consulta:** `User::where('type','user')->get()` — sin paginación en PHP (se devuelve todo el array); la paginación es del lado cliente (DataTable pageLength: 10).
- **Filtros:** No hay filtros por empresa, tipo ni fecha en el controlador; solo se listan todos los usuarios tipo `user`.
- **Columnas mostradas:** id, nombre (imagen + nombre + apellido paterno), correo, roles (spans), acciones (editar, ver, eliminar).
- **Exportación:** No hay exportación en este CRUD.
- **Búsqueda:** La búsqueda es la genérica del DataTable sobre las columnas cargadas.

---

## 9. Campos específicos del legacy (uso en este CRUD)

- **type:** En este CRUD siempre 'user'. Valores en el sistema: user (panel), high_user (panel con empresa), high_employee (app).
- **has_report_user:** Se setea 'SI' en creación; no se edita en el formulario. En el sistema se usa para lógica de reportes (p. ej. HighUsersController verifyReportUsers).
- **notification_voice_employees:** No se usa en UsersController; en el modelo y en otros módulos controla si recibe notificaciones de voz del colaborador.
- **update_password:** 'SI' = forzar cambio de contraseña; se pone 'SI' al crear y al cambiar contraseña en edición; 'NO' al cambiar contraseña en updatePassword.
- **last_password_update:** Se actualiza al cambiar contraseña en update y updatePassword.
- **user_tableau:** No se usa en este CRUD; integración Tableau.
- **company_id, high_employee_id:** No se asignan en UsersController; quedan null. Se usan en HighUsersController.

---

## 10. Integraciones con otros módulos

- **Logs:** Cada create/update/Trash crea un registro en `logs` asociado al usuario actual y a la empresa del usuario (si tiene company_id).
- **Roles (Entrust):** Asignación y desasignación con `attachRole`/`detachRoles`. Role tiene `company_id` en BD pero en este CRUD se listan todos con `Role::get()`.
- **S3:** Subida de foto de perfil (S3FileService, Storage::disk('s3')); lectura con getImageUrlAttribute (URL temporal o asset si está en public).
- **2FA:** La vista edit_profile muestra bloque Activar/Desactivar 2FA para admin; la acción se maneja en otro controlador (Google2FAController), no en UsersController.
- **Sesión:** getVerifySession comprueba si la sesión ha expirado según `company->has_session_limit` y 15 minutos.

---

## 11. Código relevante (fragmentos)

### Listado (solo type user)

```php
$users = User::where('type','user')->get();
// Se construye $users_list con nombre, email, roles, acciones y se devuelve ['data' => $users_list]
```

### Email único creación/edición

```php
$check_user = User::where("email", $data["email"])->whereIn('type', ['user', 'high_user'])->first();
if ($check_user && $check_user->id != $user_id) { // en update
    return redirect()->back()->withErrors(["Ya existe un usuario registrado con ese correo electrónico"])->withInput($request->input());
}
```

### Restricción antes de eliminar

```php
if ($user->comments_attended()->exists() || $user->sent_surveys()->exists() || $user->created_surveys()->exists()
    || $user->sent_messages()->exists() || $user->voice_employee_subjects()->exists()
    || $user->read_comments()->exists() || $user->high_employee_filters()->exists()) {
    return redirect()->back()->withErrors(["No puede borrar un usuario con registros asignados."]);
}
```

### Log de auditoría

```php
$log = new Log();
$log->date = new \DateTime('now');
$log->action = "El usuario ".$user_current->name." ".$user_current->paternal_last_name." (".$user_current->email.") ha creado el usuario: ...";
$log->save();
$user_current->logs()->save($log);
if (isset($user_current->company_id)) {
    Company::find($user_current->company_id)->logs()->save($log);
}
```

---

## 12. CRUD alternativo: High Users

El CRUD de **High Users** (`app/Http/Controllers/Admin/HighUsersController.php`) gestiona usuarios con `type = 'high_user'` y sí utiliza:

- **company_id:** Listado filtrado por empresa del usuario actual (o todos si no tiene empresa); selector de empresa en vistas.
- **high_employee_id:** Vinculación opcional con empleado (select/autocomplete).
- **position_id, department_id:** Asignación según empresa.
- **Paginación en servidor:** `paginate(10)` y filtros (nombre, empresa, etc.).
- **Rutas:** admin/high_users (getIndex, getList, getCreate, create, getEdit, update, Trash, getView, getEditProfile, updateProfile, getFilters).

Para tecben-core conviene decidir si un solo CRUD de “usuarios” unifica ambos flujos (user y high_user) con tipo y empresa/empleado opcionales, o se mantienen dos módulos separados como en el legacy.

---

## 13. Recomendaciones para tecben-core

### Mantener

- Permisos granulares (view_users, create_users, edit_users, trash_users) y middleware por permiso.
- Validación de email único en aplicación (y/o UNIQUE en BD por tenant).
- Regla de contraseña mínima (subir a 8 caracteres y política de complejidad si se desea).
- Regla “la nueva contraseña no puede ser igual a la actual” en cambio de contraseña.
- Auditoría (Log) en creación, edición y eliminación.
- Restricción de eliminación cuando existan relaciones críticas (voz, encuestas, mensajes, filtros).
- Perfil editable solo por el propio usuario; opción de que admin gestione roles (y 2FA) en la misma pantalla.
- Asignación múltiple de roles y uso de “rol actual” para permisos.

### Mejorar

- Usar **Form Requests** (StoreUsuarioRequest, UpdateUsuarioRequest, UpdateProfileRequest, UpdatePasswordRequest) en lugar de Validator inline.
- Aumentar longitud mínima de contraseña (ej. 8) y añadir reglas de complejidad.
- Filtrar roles por tenant/empresa si en tecben-core los roles son por empresa (`Role::where('company_id', ...)` o equivalente).
- Listado: paginación en servidor y filtros (empresa, tipo, búsqueda por nombre/email).
- Si se unifica con high_users: incluir company_id y high_employee_id en formularios con selectores adecuados.
- Eliminación: considerar **soft delete** en lugar de borrado físico y limpieza de imagen en S3 en Trash.
- Unificar borrado de imagen (local vs S3) en Trash; hoy solo se borra con `file_exists` en disco local.

### Descartar / Revisar

- Contraseña mínima de 5 caracteres (estándar actual suele ser 8+).
- Listar todos los usuarios tipo `user` sin filtro por empresa (en multi-tenant puede no ser deseable).
- Role::get() sin filtro por company_id si los roles son por empresa.
- Ruta GET para eliminar (admins/trash/{user_id}); en tecben-core usar DELETE con token CSRF y confirmación en front.

---

*Análisis basado en `UsersController`, vistas `admin/users`, modelo `User` y rutas en `routes/web.php`. High Users documentado de forma resumida para referencia.*
