# Ficha técnica: Módulo Usuario (Legacy Paco) — UsuarioResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **usuarios de tipo "user"** (administradores del panel). Solo describe lo que existe en el código.

---

## MÓDULO: Usuario (UsersController / equivalente UsuarioResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Implementado con diferencias (módulo Usuarios en Filament; tabla `usuarios`, modelo `Usuario`; ver sección Diferencias).

Gestiona **usuarios administradores del panel** (`type = 'user'`): listado, alta, edición, vista detalle, baja (trash) y **perfil propio** (editar perfil / cambiar contraseña). El email es único entre `user` y `high_user`. Roles asignados vía Entrust (tabla `role_user`). Imagen de perfil en S3 (`users/profile_images/`). Controlador: `App\Http\Controllers\Admin\UsersController`. Rutas bajo `admin/users/*` y `admins/trash/*`; permisos: `view_users`, `create_users`, `edit_users`, `trash_users`. Perfil y cambio de contraseña no exigen permisos de recurso (solo que el usuario edite su propio registro).

---

## ENTIDADES

### Tabla: `users`

- **PK:** id (bigint). Sin soft deletes en este módulo (eliminación física).
- **Campos relevantes para tipo "user":** name, email, password, mother_last_name, paternal_last_name, phone, mobile, type ('user' | 'high_user' | etc.), image (path S3 o legacy), has_report_user (string, p. ej. 'SI'), update_password (enum SI/NO), last_password_update (datetime nullable), notification_voice_employees, user_tableau, receive_newsletter, position_id, department_id, company_id, high_employee_id, google2fa_secret, verified_2fa_at, enable_2fa, token_batch, remember_token, timestamps.
- **En este CRUD:** Para tipo "user" se fijan phone = "", mobile = "", type = "user", has_report_user = 'SI' (en create), update_password = 'SI' (en create). company_id, position_id, department_id no se editan en el formulario (quedan null para usuarios creados por este módulo).
- **Relaciones (modelo User):** roles() belongsToMany Role (role_user); company(), department(), position(), area(), high_employee(); logs(), folders(), created_surveys(), sent_messages(), sent_surveys(), read_comments(), comments_attended(), voice_employee_subjects(), high_employee_filters(), etc.

### Tabla pivot: `role_user`

- **FK:** user_id → users, role_id → roles. Asignación de roles; al crear/actualizar usuario se hace attachRole/detachRoles desde el array request `roles['roles']` (claves = name del rol).

### Imagen de perfil

- **Almacenamiento:** S3, path `users/profile_images/{uniqid()}.png`. Resize 150x150, encode PNG. En update/updateProfile se reutiliza el path si ya empieza por `users/profile_images`; si no, se genera uno nuevo. Eliminación: en Trash se llama `Storage::disk('uploads')->delete($user->image)` si `file_exists($user->image)`; si la imagen está en S3, file_exists es false y no se borra el objeto en S3 (🔧 deuda/bug).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/users | GET | UsersController@getIndex | view_users |
| admin/users/get | GET | getList | view_users |
| admin/users_view | GET | getView | (ninguno; logged, 2fa) |
| admin/users/create | GET | getCreate | create_users |
| admin/users/create | POST | create | create_users |
| admin/users/edit/{user_id} | GET | getEdit | edit_users |
| admin/users/edit | POST | update | edit_users |
| admins/trash/{user_id} | GET | Trash | trash_users |
| admin/users/view/{user_id} | GET | getUserView | view_users |
| admin/users/edit_profile/{user_id} | GET | getEditProfile | (ninguno; solo propio usuario) |
| admin/users/edit_profile | POST | updateProfile | (ninguno; solo propio usuario) |
| admin/users/get_session | GET | getVerifySession | (ninguno; logged) |
| admin/users/update_password | POST | updatePassword | (ninguno; logged) |

Middleware común: `logged`, `2fa`. Rutas con permiso usan `Permissions:{"permissions_and":["..."]}`.

**Nota:** La ruta de trash es `admins/trash/{user_id}` (sin `users` en el path).  
**Sidebar:** "Usuarios" (admin_users) si el rol tiene al menos uno de: edit_users, view_users, trash_users, create_users.

---

## REGLAS DE NEGOCIO

- **RN-01:** Solo se listan y gestionan usuarios con `type = 'user'` (getList, getView, create guarda type = 'user', update/updateProfile fuerzan type = 'user').
- **RN-02:** **Email único** entre tipos `user` y `high_user`: en create, update y updateProfile se comprueba que no exista otro usuario (user o high_user) con el mismo email; si existe y no es el mismo id, error "Ya existe un usuario registrado con ese correo electrónico".
- **RN-03:** **Campos obligatorios en create:** name, email, mother_last_name, paternal_last_name, password, confirm_password. En update/updateProfile: name, mother_last_name, paternal_last_name, email (password opcional; si se envía, confirm_password obligatorio).
- **RN-04:** **Contraseña:** mínimo 5 caracteres; debe coincidir con confirm_password. En create se hashea y se guarda update_password = 'SI'. En update, si se envía password se actualiza también update_password = 'SI' y last_password_update = now. En updateProfile no se actualiza update_password ni last_password_update al cambiar contraseña. En updatePassword (cambio desde perfil): la nueva contraseña no puede ser igual a la actual; tras éxito se pone update_password = 'NO' y last_password_update = now.
- **RN-05:** **Foto (opcional):** mimes jpg,jpeg,png,bmp, max 20000 KB. Subida a S3 en create, update y updateProfile.
- **RN-06:** **Roles:** Se envían como `roles[roles][{role_name}]`. En create/update se hace detach de todos y attach de los seleccionados (por name; se resuelven con Role::whereIn('name', array_keys($roles))).
- **RN-07:** **Perfil propio:** getEditProfile y updateProfile solo permiten editar si `edit_user->id == current_user->id`; si no, redirect back con mensaje "El usuario que intenta editar no coincide con el logueado actualmente".
- **RN-08:** **Trash:** No se puede eliminar si el usuario tiene registros en: comments_attended, sent_surveys, created_surveys, sent_messages, voice_employee_subjects, read_comments, high_employee_filters. Mensaje: "No puede borrar un usuario con registros asignados." Antes de eliminar se hace detachRoles; luego delete físico del usuario.
- **RN-09:** **Logs:** En create, update, updateProfile y Trash se crea un registro en tabla `logs` (acción descriptiva con nombre del usuario actual y del usuario afectado) y se asocia al usuario actual y, si tiene company_id, a la empresa.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

- getIndex: devuelve vista `admin.users.list` (DataTable que pide getList).
- getList: User::where('type','user')->get(); por cada usuario se formatea nombre (imagen + name + paternal_last_name), email, roles (spans con display_name), acciones (Editar, Ver, Eliminar). Respuesta JSON `{ data: [...] }`.

### getView (admin/users_view)

- Misma consulta type = 'user'; devuelve JSON con lista de usuarios (id, nombre con imagen, email, roles). No lleva permiso en la ruta; uso posible: selector o vista alternativa (legacy).

### Crear (getCreate / create)

- getCreate: Role::get(); vista create con name, paternal_last_name, mother_last_name, email, password, confirm_password, photo, roles (checkboxes). create: validar campos; comprobar email único (user/high_user); comprobar password === confirm_password y longitud >= 5; crear User con type = 'user', has_report_user = 'SI', update_password = 'SI', phone/mobile = '', image = ''; si hay photo, subir a S3 y guardar path; asignar roles; crear log; redirect a admin_users con mensaje éxito.

### Ver (getUserView)

- Busca User por id; si no existe, redirect a admin_users con mensaje. Vista `admin.users.view` solo lectura.

### Editar (getEdit / update)

- getEdit: User por id; si no existe, redirect a admin_users. Role::get(); vista edit con user_id, datos personales, photo, roles, password/confirm_password opcionales. update: validar name, apellidos, email; email único (excluyendo el propio id); si password no vacío, validar y actualizar password + update_password + last_password_update; actualizar datos; si hay photo, S3 (reutilizar path si ya users/profile_images); detachRoles y attach roles del request; log; redirect a admin_users_edit con mensaje.

### Perfil (getEditProfile / updateProfile)

- getEditProfile: solo si edit_user->id == current_user->id; si no, redirect back. Vista edit_profile. updateProfile: mismo chequeo de identidad; validaciones y unicidad de email igual que update; password opcional; foto opcional; log; redirect a admin_users_edit_profile.

### Cambio de contraseña (updatePassword)

- Solo usuario actual; validar password === confirm_password, longitud >= 5, y que la nueva no sea igual a la actual (Hash::check). Actualizar password, update_password = 'NO', last_password_update = now. Respuesta JSON status/message.

### Sesión (getVerifySession)

- Comprueba si la sesión está “expirada” para usuarios con company: si la empresa tiene has_session_limit y han pasado >= 15 minutos desde lastActivityTime, devuelve successMessage "Expired", si no "No Expired". Respuesta JSON.

### Trash (eliminar)

- Buscar User por id. En el código se asigna `$message = "Se ha eliminado el usuario: ".$user->email` antes del `if (!$user)` → ⚠️ si el id no existe, $user es null y se produce error. Si no existe, redirect back con error "El usuario no existe." Si tiene relaciones (comments_attended, sent_surveys, created_surveys, sent_messages, voice_employee_subjects, read_comments, high_employee_filters), redirect back con error. Crear log; detachRoles; borrar imagen solo si file_exists($user->image) en disco local; User::where("id",$user_id)->delete(). Redirect a admin_users con message_info.

---

## VALIDACIONES

- **create:** name, email (required, email), mother_last_name, paternal_last_name, password, confirm_password required. Mensajes: "El nombre es requerido", "El correo es requerido", "El campo correo debe tener un formato de correo electrónico", "El apellido materno es requerido", "El apellido paterno es requerido", "La contraseña es requerida", "La confirmacion de contraseña es requerida". Adicional: password === confirm_password, strlen(password) >= 5; email único user/high_user.
- **update / updateProfile:** name, mother_last_name, paternal_last_name, email (required, email). Si se envía password: confirm_password requerido, coincidencia y longitud >= 5. Email único excluyendo el id actual.
- **photo (si se sube):** file, mimes:jpg,jpeg,png,bmp, max:20000. Mensajes: formato y "El maximo permitido para una imagen es 20MB".
- **updatePassword:** password === confirm_password, strlen >= 5, nueva contraseña distinta de la actual (Hash::check).

---

## VISTAS

- **admin.users.list:** Título "Usuarios"; subtítulo administración de usuarios del panel. Botón Crear (admin_users_create). DataTable id dataTables-users (datos vía getList). Columnas: N°, Nombre, Correo, Roles, Acciones (Editar, Ver, Eliminar). Modal de confirmación para eliminar.
- **admin.users.create:** Formulario name, paternal_last_name, mother_last_name, email, password, confirm_password, photo, roles (checkboxes). action admin_users_create.
- **admin.users.edit:** Igual que create con user_id y datos precargados; password/confirm_password opcionales. action admin_users_update.
- **admin.users.view:** Vista detalle del usuario (solo lectura).
- **admin.users.edit_profile:** Edición del propio perfil (datos personales, email, photo, roles, password opcional). action admin_users_update_profile.

---

## USO EN OTROS MÓDULOS

- **Auth / 2FA:** Google2FAController redirige a `admin_users_edit_profile` para usuarios tipo "user" tras verificación 2FA.
- **Dropdown / layout:** Enlace "Perfil de usuario" usa `route("admin_".Auth::user()->type."s_edit_profile", ["user_id"=>Auth::user()->id])` (para type "user" → admin_users_edit_profile).
- **Roles, logs, mensajes, encuestas, voz del colaborador, filtros de empleados:** El modelo User tiene relaciones que condicionan la posibilidad de Trash y se usan en otros controladores (company, roles, etc.).

---

## MODELOS INVOLUCRADOS

- **User (App\User):** tabla users; EntrustUserTrait (roles), Notifiable, HasApiTokens. roles() belongsToMany Role; company(), department(), position(), area(), high_employee(); logs(), high_employee_filters(), created_surveys(), sent_surveys(), sent_messages(), read_comments(), comments_attended(), voice_employee_subjects(), etc. getImageUrlAttribute (público o S3 temporal). getCurrent(), getCurrentRol(), hasRoles().
- **Role (App\Models\Role):** asignación vía role_user; display_name usado en vistas.
- **Company, Log:** Log se asocia al usuario actual y a su company si existe.

---

## MIGRACIONES (usuarios)

- **create_users_table:** id, name, email (unique), email_verified_at, password, remember_token, timestamps.
- **update_users_table:** mother_last_name, paternal_last_name, phone, mobile, type, position_id, department_id, company_id (FKs a positions, departments, companies).
- **update_users_2_table:** has_report_user.
- **update_users_3_table:** high_employee_id (FK high_employees).
- **update_users_4_table:** drop unique en email.
- **update_users_5_table:** image.
- **update_users_6_table:** notification_voice_employees (enum SI/NO).
- **update_users_7_table:** user_tableau nullable.
- **update_users_8_table:** receive_newsletter (enum SI/NO).
- **update_users_9_table:** update_password (enum SI/NO), last_password_update (datetime nullable).
- **google_authentication:** google2fa_secret (longText), verified_2fa_at (datetime nullable).
- **add_field_2fa_enable:** enable_2fa (boolean default false).
- **create_field_signer_batch:** token_batch (string nullable).

---

## PERMISOS LEGACY

- **view_users:** getIndex, getList, getUserView.
- **create_users:** getCreate, create.
- **edit_users:** getEdit, update.
- **trash_users:** Trash.
- **Sin permiso de recurso:** getView (admin/users_view), getEditProfile, updateProfile, getVerifySession, updatePassword (solo logged / propio usuario).

---

## CASOS BORDE

- **Trash con user_id inexistente:** Se asigna `$message = "Se ha eliminado el usuario: ".$user->email` antes de `if (!$user)`; si el usuario no existe, $user es null y se produce error al acceder a $user->email.
- **Trash e imagen en S3:** La imagen se guarda en S3 (path users/profile_images/...). En Trash solo se llama Storage::disk('uploads')->delete($user->image) si file_exists($user->image); en S3 no hay file_exists local, por lo que la imagen no se borra de S3.
- **getEditProfile / updateProfile:** Cualquier usuario autenticado puede acceder a edit_profile con su propio user_id; si intenta con otro user_id, redirect back. No se exige permiso create/edit_users para editar el propio perfil.
- **Ruta trash:** Es `admins/trash/{user_id}` (admins en lugar de admin/users); enlaces en vistas usan route('admin_users_trash', ['user_id' => ...]).

---

## AMBIGÜEDADES

- **getView (admin/users_view):** No tiene middleware de permiso view_users; no está claro si es intencional (selector público para cualquier usuario logueado) o omisión.
- **Roles en getCreate/getEdit:** Se usa Role::get() sin filtrar por empresa ni tipo; los usuarios "user" pueden tener asignados roles que en otros contextos se asocian a company (no documentado en este controlador).

---

## DEUDA TÉCNICA

- Eliminación de imagen en Trash no borra el objeto en S3 cuando la imagen está en S3.
- Orden de comprobación en Trash: construir $message después de comprobar que $user existe.
- Posible inconsistencia: update actualiza update_password y last_password_update al cambiar contraseña; updateProfile no los actualiza al cambiar contraseña (solo actualiza el hash).

---

## DIFERENCIAS CON TECBEN-CORE

- **Tabla y modelo:** En tecben-core el módulo de usuarios usa tabla `usuarios` y modelo `Usuario` (Filament UsuarioResource); en legacy es tabla `users` y modelo `User` con type = 'user'.
- **Tipos de usuario:** Legacy distingue en la misma tabla por `type` ('user', 'high_user', etc.); tecben-core documenta tipos user / admin / employee con tabla usuarios y posiblemente empresa_id, empleado_id.
- **Roles:** Legacy usa Entrust y tabla role_user; tecben-core usa Spatie/Shield u otro sistema de permisos.
- **Email único:** Legacy exige unicidad entre user y high_user; en tecben-core la unicidad puede ser global en usuarios.
- **Perfil y contraseña:** En legacy existe edit_profile y updatePassword en el mismo controlador sin permisos de recurso; en tecben-core suele existir una página de Perfil separada (Filament).
- **Imagen:** Legacy guarda en S3 (users/profile_images); tecben-core puede usar avatar (URL o almacenamiento distinto).
- **Trash:** Legacy elimina físicamente y comprueba relaciones (comments_attended, sent_surveys, etc.); en tecben-core puede haber soft delete o políticas distintas.
