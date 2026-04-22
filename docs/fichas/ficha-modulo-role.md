# Ficha técnica: Módulo Roles (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Roles (RoleResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite **listar, crear, editar, ver y eliminar (papelera) roles** del sistema. Cada rol tiene nombre (único), nombre para mostrar, descripción y **company_id** opcional (rol por empresa o rol global). Se asignan **permisos** al rol vía tabla pivot `permission_role`. Los roles con empresa se listan por empresa para usuarios con company; los usuarios sin company (admin global) ven todos los roles. Al crear/editar, un admin puede marcar "Asignar a empresa" y elegir empresa; si no marca o no es admin, el rol queda con la empresa del usuario actual o sin empresa. No se puede eliminar un rol que tenga usuarios asignados. Eliminación por soft delete. Controlador: `RolesController`. Rutas bajo `admin/roles/*`; permisos: `view_roles`, `create_roles`, `edit_roles`, `trash_roles`.

---

## ENTIDADES

### Tabla: `roles`

- **PK:** id (bigint unsigned).
- **Campos:** name (string, unique en migración Entrust), display_name (string nullable), description (string nullable), company_id (unsignedBigInteger nullable; añadido en update_roles_table), timestamps, deleted_at (soft deletes; update_roles_2_table).
- **Relaciones (modelo Role):** users() belongsToMany User (pivot role_user), permissions() belongsToMany Permission (pivot permission_role), company() definido como belongsToMany(Company) en el modelo pero la tabla tiene company_id (FK) y Company tiene hasMany(Role) — en la práctica es relación N:1 con Company (🔧 modelo incorrecto: debería ser belongsTo(Company)).

### Tabla pivot: `role_user`

- **FK:** user_id → users (cascade), role_id → roles (cascade). Primary (user_id, role_id).
- **Uso:** Asignación de roles a usuarios. No se gestiona en este CRUD; se gestiona en el módulo de usuarios. Al eliminar un rol se exige que no tenga usuarios (role->users()->exists()).

### Tabla pivot: `permission_role`

- **FK:** permission_id → permissions (cascade), role_id → roles (cascade). Primary (permission_id, role_id).
- **Uso:** Permisos asignados al rol. En create se hace attach de los permisos elegidos; en update se hace detach de todos y luego attach de los seleccionados. En Trash se hace detach antes de borrar el rol.

### Tabla: `permissions` (contexto)

- **Campos:** name, display_name, description, only_for_admin (usado en vistas: permisos solo visibles para usuarios con rol admin). En create/edit se listan todos (Permission::get()); en la vista se ocultan los only_for_admin si el usuario no es admin.
- **Relación:** roles() belongsToMany Role.

### Tabla: `companies` (contexto)

- Company tiene roles() hasMany(Role). getList filtra por company->roles() cuando el usuario tiene company.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/roles | GET | RolesController@getIndex | view_roles |
| admin/roles/get | GET | getList | view_roles |
| admin/roles/create | GET | getCreate | create_roles |
| admin/roles/create | POST | create | create_roles |
| admin/roles/edit/{role_id} | GET | getEdit | edit_roles |
| admin/roles/edit | POST | update | edit_roles |
| admin/roles/view/{role_id} | GET | getView | view_roles |
| admin/roles/trash/{role_id} | GET | Trash | trash_roles |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}` según la ruta.

**Sidebar:** Enlace "Roles" si el usuario tiene al menos uno de: edit_roles, view_roles, trash_roles, create_roles.

---

## REGLAS DE NEGOCIO

- **RN-01:** name, display_name y description son **obligatorios** (validación en create y update).
- **RN-02:** El **name** del rol debe ser **único** en la tabla roles. Si ya existe un rol con ese name al crear, se devuelve error "Ya existe un rol registrado con ese nombre". En edición, se permite si el name no cambia o si el nuevo name no está usado por otro rol.
- **RN-03:** **No se puede eliminar** un rol que tenga usuarios asignados (`$role->users()->exists()`). Mensaje: "Desasigne los usuarios que tienen este rol primero para poder eliminarlo".
- **RN-04:** Eliminación es **soft delete** (modelo Role usa SoftDeletes). Trash hace detach de permisos y `Role::where("id",$role_id)->delete()`. getList usa Role::get() / company->roles()->get(), que excluyen automáticamente los soft-deleted.
- **RN-05:** **Asignación a empresa:** Si el usuario es **admin** (hasRoles('admin')): en create/edit puede activar "Asignar a empresa" y elegir una empresa; entonces se guarda request->company en role->company_id. Si no activa el switch: en create se usa company_id del usuario actual si tiene company, si no company_id queda null; en update se pone company_id = null (rol global). Si el usuario **no es admin**: no ve el switch ni el selector de empresa; en create se asigna role->company_id = user_current->company_id si tiene company; en update se asigna igual (no se pone null). Así, un admin puede crear roles globales (company_id null) desmarcando "Asignar a empresa" y sin company; un no-admin siempre asocia el rol a su empresa.
- **RN-06:** **Permisos:** Se envían como request->permissions (array asociativo nombre => valor). Se obtienen con Permission::whereIn('name', array_keys($permissions)); se hace attach de cada permission->id al rol. En update se hace detach de todos y luego attach de los nuevos seleccionados.
- **RN-07:** En listado (getList): si el usuario tiene **company** se muestran solo los roles de su empresa (`$user->company->roles()->get()`); si **no tiene company** (admin global) se muestran todos los roles (`Role::get()`). getEdit, getView y Trash **no filtran por company**: buscan el rol por id sin comprobar que pertenezca a la empresa del usuario (⚠️ ver CASOS BORDE).

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

1. getIndex: vista `admin.roles.list` (DataTable que consume getList por AJAX).
2. getList: si usuario tiene company → roles = company->roles()->get(); si no → roles = Role::get() (excluye soft-deleted). Para cada rol se serializa: id, display_name, description (HTML), botones Editar / Ver / Eliminar. Respuesta JSON `{ data: roles_list }`.

### Crear (getCreate / create)

1. getCreate: Company::all(), Permission::get(). Vista create con permisos (checkboxes; los only_for_admin solo se muestran si Auth::user()->hasRoles('admin')) y, si admin, switch "Asignar a empresa" y select de empresa (disabled si el switch está desactivado).
2. create: Validar name, display_name, description required. Comprobar que no exista otro rol con el mismo name. Role::create($data). Asignar company_id según request (company + is_asignable) o user->company_id o null. Attach de permisos seleccionados (por name). Log "ha creado el Rol: ...". Redirect a admin_roles con mensaje "Rol creado exitosamente".

### Ver (getView)

1. Buscar rol por id; si no existe redirect a admin_roles con mensaje "El rol ... no se encuentra registrado". Vista view con role (nombre, display_name, descripción, lista de permisos del rol en tabla).

### Editar (getEdit / update)

1. getEdit: Buscar rol por id; si no existe redirect a admin_roles. Company::all(), Permission::get(). Vista edit con role, permissions, companies; permisos pre-marcados según $role->hasPermissions($permission->name). Si admin, switch y select de empresa según role->company_id.
2. update: Validar name, display_name, description required. Comprobar unicidad de name (excluyendo el rol actual). Actualizar name, display_name, description. Asignar company_id igual que en create (company+is_asignable, o user company, o null). role->permissions()->detach(); luego attach de los permisos seleccionados. Log "ha actualizado el Rol: ...". Redirect a admin_roles_edit con role_id y mensaje "Rol actualizado exitosamente".

### Eliminar (Trash)

1. Buscar rol por id; si no existe redirect back "El rol no existe." Si role->users()->exists() → redirect back "Desasigne los usuarios que tienen este rol primero para poder eliminarlo". Log "ha eliminado el Rol: ...". role->permissions()->detach(); Role::where("id",$role_id)->delete() (soft delete). Redirect a admin_roles con mensaje "Se ha eliminado el rol: ...".

---

## VALIDACIONES

- **name:** required ("El nombre es requerido"). Unicidad a nivel tabla (comprobación manual en create/update).
- **display_name:** required ("El nombre para mostrar es requerido").
- **description:** required ("La descripcion es requerida").
- En la vista create/edit, el select "Empresa" tiene atributo required; cuando "Asignar a empresa" está activo el select está habilitado y es obligatorio; cuando está desactivado el select está disabled (no se envía) y el backend usa company del usuario o null.
- No hay validación de que el role_id en edit/view/trash pertenezca a la empresa del usuario cuando el usuario tiene company (el listado sí filtra, pero las rutas de edición/ver/eliminar no comprueban company).

---

## VISTAS

- **admin.roles.list:** Título "Roles", subtítulo sobre permisos en la plataforma. DataTable (id dataTables-roles) con AJAX a get_admin_roles. Columnas: N°, Nombre, Descripción, acciones (Editar, Ver, Eliminar). Modal de confirmación para eliminar. Botón Crear.
- **admin.roles.create:** Formulario: Datos básicos (name, display_name, description). Si admin: switch "Asignar a empresa" y select Empresa (deshabilitado si switch off). Permisos: lista de checkboxes por permiso (display_name, description); permisos only_for_admin solo visibles para admin. action admin_roles_create.
- **admin.roles.edit:** Igual que create con valores precargados; hidden role_id. action admin_roles_update. Permisos marcados con $role->hasPermissions($permission->name) (el método acepta string y lo convierte a array internamente).
- **admin.roles.view:** Solo lectura: id, name, display_name, description y tabla de permisos del rol (id, name, display_name, description). Botón Regresar a admin_roles.

---

## USO EN OTROS MÓDULOS

- **Usuarios (UserResource):** Asignación de roles a usuarios vía pivot role_user. Al dar de alta o editar usuario se eligen roles (normalmente de la empresa del usuario o globales). Los permisos del usuario se resuelven por el rol actual (getCurrentRol(), hasOnePermission, hasPermissions).
- **Middleware Permissions:** Comprueba permisos del rol actual del usuario para acceder a rutas (view_roles, edit_roles, etc.).
- **Sidebar y menús:** Visibilidad de opciones según permisos del rol (ej. hasOnePermission(['view_roles', ...])).

---

## MODELOS INVOLUCRADOS

- **Role** (App\Models\Role): Extiende EntrustRole, SoftDeletes. fillable: name, display_name, description, company_id. users() belongsToMany User; permissions() belongsToMany Permission; company() belongsToMany Company (incorrecto: debería ser belongsTo Company). hasOnePermission($permissions), hasPermissions($permissions) para comprobar permisos del rol.
- **Company:** roles() hasMany Role.
- **Permission:** name, display_name, description, only_for_admin. roles() belongsToMany Role.
- **User:** relación con roles vía role_user (Entrust/gestión en módulo usuarios).

---

## MIGRACIONES

- **entrust_setup_tables:** Crea roles (id, name unique, display_name, description nullable, timestamps), role_user (user_id, role_id, PK y FKs cascade), permissions, permission_role (permission_id, role_id, PK y FKs cascade).
- **update_roles_table:** Añade company_id nullable en roles con FK a companies (cascade).
- **update_roles_2_table:** Añade softDeletes() en roles.

---

## PERMISOS LEGACY

- **view_roles:** Listar roles (getIndex, getList) y ver detalle (getView).
- **create_roles:** getCreate y create.
- **edit_roles:** getEdit y update.
- **trash_roles:** Trash (eliminar rol).

---

## CASOS BORDE

- **Edición/ver/eliminar de rol de otra empresa:** getList filtra por company->roles() para usuarios con company, por lo que en la lista solo ven sus roles. getEdit, getView y Trash no comprueban que el role_id pertenezca a la empresa del usuario. Un usuario con company que conozca el ID de un rol de otra empresa podría acceder a admin/roles/edit/{id}, view o trash. ⚠️ Posible fallo de autorización.
- **Admin sin company:** Si el admin no tiene company_id y en create no marca "Asignar a empresa", el rol queda con company_id = null (rol global). En update, si desmarca "Asignar a empresa", role->company_id = null. Correcto para roles globales.
- **Unicidad de name:** La comprobación es global (Role::where("name", $data["name"])->first()). Los roles con company_id distinto pueden tener el mismo name en la BD según la migración (unique solo en name); si se quisiera name único por empresa habría que cambiar lógica y posiblemente migración.
- **Role::get() sin orden:** getList no aplica orderBy; el orden en el DataTable es por columna 0 desc (id), pero los datos vienen en orden indefinido del query.

---

## AMBIGÜEDADES

- **"Asignar a empresa" en admin:** Cuando el admin no marca el switch, en create el código hace else if (isset($user_current->company)) y asigna esa company. No hay rama explícita "dejar null" en create para admin; solo queda null si el admin no tiene company. Es decir, un admin con empresa que crea rol sin marcar "Asignar a empresa" termina con el rol asignado a su empresa. En update sí hay else { company_id = null }, por lo que al desmarcar se pone null. Comportamiento distinto entre create y update para admin con company.
- **Modelo Role company():** Definido como belongsToMany(Company); en la BD la relación es company_id en roles. Company usa hasMany(Role). El uso en controlador es $role->company_id = ...; $role->save(). No se usa $role->company en este controlador; en getList se usa $user->company->roles(). La definición correcta sería belongsTo(Company).

---

## DEUDA TÉCNICA

- Relación company() en Role definida como belongsToMany en lugar de belongsTo (inconsistente con BD y con Company::roles() hasMany).
- Eliminación en Trash por GET; debería ser POST/DELETE para evitar borrados por enlace o referrer.
- Log comentado con formato distinto al usado; se dejó la línea nueva con name + paternal_last_name + email.

---

## DIFERENCIAS CON TECBEN-CORE

Por definir (no verificado en este análisis). Si en tecben-core existe CRUD de roles, comparar: scope por empresa en listado y en edición/ver/eliminar, unicidad de name (global vs por empresa), y comportamiento de "Asignar a empresa" en create vs update.
