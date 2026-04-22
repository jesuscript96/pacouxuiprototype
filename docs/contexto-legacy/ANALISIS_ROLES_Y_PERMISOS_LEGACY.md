# Análisis de roles y permisos en Legacy (Paco)

**Objetivo:** Extraer el sistema de roles y permisos del legacy para replicar la matriz en tecben-core (Shield) y asegurar paridad funcional.  
**Fuente:** Rutas (`routes/web.php`), middleware `Permissions`, controladores (hasRoles, hasOnePermission, hasPermissions), modelos Role/Permission y vistas de roles.  
**Limitación:** Los roles (nombres “Gerente de paco”, “Director Empresas”, etc.) se crean en la UI y se guardan en BD; sin acceso a la BD no se puede listar qué roles existen ni la matriz rol→permiso. Este documento aporta la lista completa de **permisos** usados en código y la **estructura** para construir la matriz.

---

## 1. Estructura de tablas en Legacy

### roles

| Campo        | Tipo              | Descripción                          |
|-------------|-------------------|--------------------------------------|
| id          | bigint unsigned   | PK, auto_increment                   |
| name        | varchar(255)      | **Unique** (ej. `admin`, `gerente_paco`) |
| display_name| varchar(255)      | Nombre para mostrar (ej. "Gerente de paco") |
| description | varchar(255)      | Descripción del rol                  |
| company_id  | bigint unsigned   | **Nullable**; si no null, rol por empresa |
| created_at  | timestamp         |                                      |
| updated_at  | timestamp         |                                      |
| deleted_at  | timestamp         | SoftDeletes en modelo Role           |

- **Global vs empresa:** Si `company_id` es null, el rol es global. Si tiene valor, el rol pertenece a esa empresa y en listados se filtra por `$user->company->roles()` o `Role::get()` según contexto.
- En **RolesController** al crear/editar: si el usuario es admin puede marcar “Asignar a empresa” y elegir empresa; si no es admin se usa `$user_current->company_id`.

### permissions

| Campo         | Tipo    | Descripción |
|--------------|---------|-------------|
| id           | bigint  | PK          |
| name         | varchar(255) | **Unique** (ej. `view_users`, `create_companies`) |
| display_name | varchar(255) | Nombre para UI (ej. "Ver usuarios") |
| description  | varchar(255) | Descripción |
| only_for_admin | unsignedBigInteger (0/1) | Si es 1, solo usuarios con rol admin ven el permiso en el formulario de roles |
| created_at   | timestamp | |
| updated_at   | timestamp | |

- Los permisos se cargan con `Permission::get()` en create/edit de roles; en la vista se filtran por `only_for_admin` (solo admin ve los marcados como solo admin).

### permission_role (pivot)

- `permission_id` → permissions.id (CASCADE)
- `role_id` → roles.id (CASCADE)
- **Primary key:** (permission_id, role_id)

### role_user (pivot)

- `user_id` → users.id (CASCADE)
- `role_id` → roles.id (CASCADE)
- **Primary key:** (user_id, role_id)

Un usuario puede tener **varios roles**. El “rol actual” se guarda en sesión (`getCurrentRol()`); los permisos se evalúan contra ese rol (middleware `Permissions` usa `getCurrentRol()->hasPermissions(permissions_and)` y `hasOnePermission(permissions_or)`).
s
---

## 2. Listado completo de permisos en Legacy (por módulo)

Todos los nombres que aparecen en rutas (middleware) o en controladores (`hasOnePermission`, `hasPermissions`). Agrupados por recurso/módulo.

### 2.1 Usuarios (panel)

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_users       | Listar/ver usuarios tipo user |
| create_users     | Formulario y POST crear usuario |
| edit_users       | Formulario y POST editar usuario |
| trash_users      | Eliminar usuario (Trash) |

### 2.2 Usuarios altas (high_users)

| Permiso Legacy     | Uso en código |
|--------------------|----------------|
| view_high_users    | Listado high users (con company) |
| create_high_users  | Crear high user |
| edit_high_users    | Editar high user |
| trash_high_users   | Eliminar high user |

### 2.3 Roles

| Permiso Legacy | Uso en código |
|----------------|----------------|
| view_roles    | Listar/ver roles |
| create_roles  | Crear rol |
| edit_roles     | Editar rol |
| trash_roles    | Eliminar rol |

### 2.4 Empresas (companies)

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_companies   | Listar/ver empresas |
| create_companies | Crear empresa |
| edit_companies   | Editar empresa, removefile, updatefile |
| trash_companies  | Eliminar empresa |

### 2.5 Empleados (high_employees)

| Permiso Legacy            | Uso en código |
|---------------------------|----------------|
| view_high_employees       | Listar, ver, aniversarios, cumpleaños, recibos (vista empleado), insurances download |
| create_high_employees    | Crear, carga masiva, plantillas, edición masiva |
| edit_high_employees       | Editar empleado, descargas, ver archivos |
| trash_high_employees      | Dar de baja, getTrash, Trash, plantilla bajas, carga masiva bajas |
| view_low_employees        | Bajas: listar, ver, reenviar encuesta salida, reporte, restaurar, editar |
| upload_high_employees_files | Subir archivos (ej. DNI) del empleado |
| load_authorizers          | Ver/cargar autorizadores (solicitudes) |
| view_employment_history  | Ver historial laboral (controlador, no ruta) |
| view_insurance_policy_document | Ver documento de póliza de seguro (controlador) |

### 2.6 Productos

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_products    | Listar/ver productos |
| create_products  | Crear producto |
| edit_products    | Editar producto |
| trash_products   | Eliminar producto |

### 2.7 Gestión de productos por empresa
aaa
| Permiso Legacy        | Uso en código |
|-----------------------|----------------|
| view_product_management  | Listado gestión productos por empresa |
| edit_product_management  | Editar, actualizar productos, query receivers |

### 2.8 Industrias / Subindustrias

| Permiso Legacy        | Uso en código |
|-----------------------|----------------|
| view_industries       | Industrias |
| create_industries      | Crear industria |
| edit_industries        | Editar industria |
| trash_industries       | Eliminar industria |
| view_sub_industries    | Subindustrias |
| create_sub_industries  | Crear subindustria |
| edit_sub_industries    | Editar subindustria |
| trash_sub_industries   | Eliminar subindustria |

### 2.9 Ubicaciones (locations)

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_locations   | Listar/ver ubicaciones |
| create_locations | Crear ubicación |
| edit_locations   | Editar, planilla, addPlanilla, findPlanilla |
| trash_locations  | Eliminar ubicación |

### 2.10 Departamentos / Áreas / Puestos

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_departments | Departamentos |
| create_departments | Crear departamento |
| edit_departments   | Editar departamento |
| trash_departments  | Eliminar departamento |
| view_areas        | Áreas |
| create_areas      | Crear área |
| edit_areas        | Editar área |
| trash_areas       | Eliminar área |
| view_positions    | Puestos |
| create_positions  | Crear puesto |
| edit_positions    | Editar puesto |
| trash_positions   | Eliminar puesto |

### 2.11 Regiones / Bancos / Centros de costo / Centros de pago

| Permiso Legacy     | Uso en código |
|--------------------|----------------|
| view_regions       | Regiones |
| create_regions     | Crear región |
| edit_regions       | Editar región |
| trash_regions      | Eliminar región |
| view_banks         | Bancos y **centros de pago** (mismo permiso) |
| create_banks       | Crear banco/centro pago |
| edit_banks         | Editar banco/centro pago |
| trash_banks        | Eliminar banco/centro pago |
| view_cost_centers  | Centros de costo |
| create_cost_centers| Crear centro de costo |
| edit_cost_centers  | Editar centro de costo |
| trash_cost_centers | Eliminar centro de costo |

### 2.12 Áreas / Departamentos / Puestos generales

| Permiso Legacy          | Uso en código |
|-------------------------|----------------|
| view_general_areas      | Áreas generales |
| create_general_areas    | Crear |
| edit_general_areas      | Editar |
| trash_general_areas     | Eliminar |
| view_general_departments| Departamentos generales |
| create_general_departments | Crear |
| edit_general_departments  | Editar |
| trash_general_departments | Eliminar |
| view_general_positions  | Puestos generales |
| create_general_positions| Crear |
| edit_general_positions  | Editar |
| trash_general_positions | Eliminar |

### 2.13 Filtros de empleados

| Permiso Legacy             | Uso en código |
|----------------------------|----------------|
| view_high_employee_filters | Ver filtros guardados |
| create_high_employee_filters | Crear filtro, query receivers |

### 2.14 Portfolio

| Permiso Legacy  | Uso en código |
|-----------------|----------------|
| view_portfolio  | Ver opciones de portafolio |
| edit_portfolio  | Actualizar portafolio |

### 2.15 Mensajes

| Permiso Legacy       | Uso en código |
|----------------------|----------------|
| view_messages        | Listar/ver mensajes, mensajes personalizados (get) |
| create_messages      | Crear mensaje, enviar |
| edit_messages        | Editar mensaje |
| trash_messages       | Eliminar mensaje |
| personalized_messages_view | Ver mensajes personalizados (listado) |
| personalized_messages     | Crear mensaje personalizado (create) |
| view_companies_messages   | Ver mensajes de todas las empresas (controlador; si no admin) |

### 2.16 Encuestas (surveys)

| Permiso Legacy        | Uso en código |
|-----------------------|----------------|
| view_surveys          | Categorías, dimensiones, dominios, categorías de preguntas, encuestas, envíos, NOM35, Tableau satisfacción/encuestas/NOM035 |
| create_surveys        | Crear categoría, dimensión, dominio, categoría pregunta, encuesta, NOM35 |
| edit_surveys          | Editar categoría, dimensión, dominio, categoría, encuesta, NOM35 |
| trash_surveys         | Eliminar categoría, dimensión, dominio, categoría, encuesta |
| duplicate_surveys     | Duplicar encuesta |
| send_surveys          | Enviar encuesta, custom, getReceivers |
| close_surveys         | Cerrar envío |
| edit_survey_shipping  | Editar envío (y en SurveyCategoriesController) |
| trash_survey_shipping | Eliminar envío |
| view_companies_surveys| Ver encuestas de todas las empresas (controlador; si no admin) |

### 2.17 Reconocimientos (acknowledgments)

| Permiso Legacy           | Uso en código |
|--------------------------|----------------|
| view_acknowledgments     | Listar/ver reconocimientos, filtros, list companies |
| create_acknowledgments   | Crear reconocimiento |
| edit_acknowledgments     | Editar reconocimiento y empresa |
| trash_acknowledgments    | Eliminar reconocimiento |
| view_sent_acknowledgments| Ver reconocimientos enviados, Tableau |

### 2.18 Voz del colaborador (voice_employees)

| Permiso Legacy             | Uso en código |
|-----------------------------|----------------|
| view_voice_employee_subjects| Temas por empresa: listar, ver |
| create_voice_employee_subjects | Crear tema |
| edit_voice_employee_subjects   | Editar tema |
| trash_voice_employee_subjects | Eliminar/ocultar tema |
| segment_voice_employee        | Segmentación usuarios (temas por usuario), update tema |
| view_voice_employees         | Listar comentarios, ver específico, filtros, ver empleado, Tableau |
| edit_voice_employees         | Actualizar estado (atender) |
| trash_voice_employees       | Eliminar comentario o extra |

### 2.19 Cuentas por cobrar / Cobranzas

| Permiso Legacy            | Uso en código |
|---------------------------|----------------|
| view_receivable_accounts  | Cuentas por cobrar, movimientos, recargas, reportes, penalizaciones, Tableau (como view_collections) |
| process_receivable_accounts | Procesar TXT cuentas por cobrar |
| generate_internal_reports | Reporte interno (vista, filtros, generar) |
| delete_penalties          | Borrar penalizaciones (controlador; hasPermissions) |
| view_collections          | Tableau receivables (o hasOnePermission en controlador) |

### 2.20 Recibos de nómina

| Permiso Legacy        | Uso en código |
|-----------------------|----------------|
| view_payroll_receipts | Listar, descargar, filtros, massive export |
| load_payroll_receipts | Cargar recibos |
| trash_payroll_receipts| Eliminar recibo |

### 2.21 Verificación de cuentas

| Permiso Legacy  | Uso en código |
|-----------------|----------------|
| verify_accounts | Verificación de cuentas (get, accountVerification, reportes excel/txt) |

### 2.22 Reclutamiento

| Permiso Legacy                 | Uso en código |
|--------------------------------|----------------|
| view_recruitments              | Vacantes, candidatos, Tableau |
| create_recruitments            | Crear vacante |
| edit_recruitments              | Editar vacante |
| trash_recruitments             | Eliminar vacante |
| view_recruitments_candidates   | Ver candidatos |
| edit_recruitments_candidates   | Editar candidato, comentarios, archivos |
| trash_recruitments_candidates  | Eliminar candidato |
| trash_recruitments_candidates_comment | Eliminar comentario de candidato |

### 2.23 Directorios (folders) y archivos empresa

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_folders     | Directorios (folders) |
| create_folders   | Crear directorio |
| edit_folders     | Editar directorio, subcarpetas, archivos |
| trash_folders    | Eliminar directorio |
| view_ad_folders  | Archivos empresa (solicitudes documentos digitales) |
| create_ad_folders| Crear carpeta archivos |
| edit_ad_folders  | Editar, subir, autorizar/no autorizar solicitudes |
| trash_ad_folders | Eliminar carpeta |

### 2.24 Contratos laborales (employment contract)

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_ec_folders  | Carpetas y archivos de contratos |
| create_ec_folders| Crear carpeta |
| edit_ec_folders  | Editar, subir, preview |
| trash_ec_folders | Eliminar carpeta/archivo/contrato |
| sign_ec_files    | Firmar contrato (vista firmante, filtros, batch) |

### 2.25 Capacitación

| Permiso Legacy    | Uso en código |
|-------------------|----------------|
| view_capacitation | Listar, reportes, desactivar |
| create_capacitation | Crear, editar, duplicar, reportes, DC3, empleados |
| edit_capacitation   | (no usado en rutas; create_capacitation cubre edición en varias rutas) |
| trash_capacitation  | Eliminar capacitación |

### 2.26 Bienestar en línea (online wellness)

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_ow_folders  | Carpetas bienestar |
| create_ow_folders| Crear carpeta |
| edit_ow_folders  | Editar, subcarpetas, archivos |
| trash_ow_folders | Eliminar carpeta/subcarpeta/archivo |

### 2.27 Notificaciones push

| Permiso Legacy              | Uso en código |
|-----------------------------|----------------|
| view_notifications_push     | Listar, ver, receptores |
| create_notifications_push   | Crear y enviar |
| edit_notifications_push    | Editar notificación |
| trash_notifications_push    | Eliminar notificación |
| view_companies_notifications_push | Ver notificaciones de todas las empresas (controlador) |

### 2.28 Cartas SUA

| Permiso Legacy   | Uso en código |
|------------------|----------------|
| view_sua_letters | Listar, ver, filtros |
| load_sua_letters | Cargar datos |
| trash_sua_letters| Eliminar carta |

### 2.29 Categorías y tipos de solicitudes

| Permiso Legacy          | Uso en código |
|-------------------------|----------------|
| view_request_categories| Categorías de solicitudes |
| create_request_categories | Crear categoría |
| edit_request_categories   | Editar categoría |
| trash_request_categories  | Eliminar categoría |
| view_requests_type      | Tipos de solicitud |
| create_requests_type    | Crear tipo |
| edit_requests_type     | Editar tipo |
| trash_requests_type    | Eliminar tipo |

### 2.30 Estado de ánimo (moods)

| Permiso Legacy | Uso en código |
|----------------|----------------|
| view_moods     | Características y trastornos de ánimo |
| create_moods   | Crear característica/trastorno |
| edit_moods     | Editar |
| trash_moods    | Eliminar |

### 2.31 Seguros

| Permiso Legacy           | Uso en código |
|--------------------------|----------------|
| view_insurance_memberships | Reporte membresías seguros |
| view_insurance_policy_document | Ver documento póliza (controlador) |

### 2.32 Tableau / Reportes

| Permiso Legacy          | Uso en código |
|-------------------------|----------------|
| view_mental_health_board | Tableau salud mental, bienestar colaboradores |
| view_collections        | Tableau cobranzas (receivables) |

### 2.33 Otros

| Permiso Legacy     | Uso en código |
|--------------------|----------------|
| load_ios_code      | Carga códigos iOS (apps_download) |
| view_carousel_management | Carrusel imágenes empresa |
| edit_carousel_management | Editar carrusel |
| create_messages, send_surveys, create_notifications_push, segment_voice_employee | Usados en **permissions_or** en query receivers (mensajes/encuestas/notificaciones/segmentación voz) |

**Nota:** Centros de pago usan los mismos permisos que Bancos (`view_banks`, etc.). Las rutas de pasarela de pago y débito directo no usan middleware de permisos. Si en la UI legacy existen permisos como "Pasarela de pago (ver)", "Onboarding" o "Mi expediente", pueden estar solo en la tabla `permissions`; para listarlos ejecutar `SELECT * FROM permissions` en la BD.

---

## 3. Consultas SQL para obtener roles y matriz (ejecutar en BD Legacy)

Para rellenar la matriz real rol→permiso y el listado de roles con display_name/description hay que ejecutar en la base de datos:

```sql
-- Listado de roles (con empresa si existe)
SELECT id, name, display_name, description, company_id
FROM roles
WHERE deleted_at IS NULL
ORDER BY name;

-- Permisos por rol (matriz)
SELECT r.id AS role_id, r.name AS role_name, r.display_name AS role_display_name,
       p.id AS permission_id, p.name AS permission_name, p.display_name AS permission_display_name
FROM roles r
JOIN permission_role pr ON pr.role_id = r.id
JOIN permissions p ON p.id = pr.permission_id
WHERE r.deleted_at IS NULL
ORDER BY r.name, p.name;

-- Resumen: permisos por rol (una fila por rol, permisos concatenados)
SELECT r.name AS role_name, r.display_name, r.company_id,
       GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ', ') AS permissions
FROM roles r
LEFT JOIN permission_role pr ON pr.role_id = r.id
LEFT JOIN permissions p ON p.id = pr.permission_id
WHERE r.deleted_at IS NULL
GROUP BY r.id, r.name, r.display_name, r.company_id;

-- Listado de todos los permisos (para comparar con este documento)
SELECT id, name, display_name, description, only_for_admin
FROM permissions
ORDER BY name;
```

---

## 4. Reglas de negocio detrás de los permisos

- **Rol “actual”:** Los permisos se evalúan contra el rol en sesión (`getCurrentRol()`), no contra la unión de todos los roles del usuario. El usuario debe tener el rol correcto seleccionado para acceder a una ruta.
- **permissions_and vs permissions_or:** En rutas se usa `Permissions:{"permissions_and":["perm1","perm2"]}` (debe tener todos) o `"permissions_or":["perm1","perm2"]` (basta uno). En controladores se usa `hasOnePermission([...])` (uno de la lista) o `hasPermissions('perm')` / `hasPermissions([...])` (todos).
- **Admin:** El rol `admin` (por nombre) se usa con `hasRoles('admin')` para saltarse filtros por empresa (ver todas las empresas, todas las encuestas, etc.). No es un permiso sino un rol especial.
- **Scoping por empresa:** Si el usuario tiene `company_id`, muchos listados se filtran por su empresa. Los permisos `view_companies_surveys`, `view_companies_messages`, `view_companies_notifications_push` permiten ver recursos de **todas** las empresas cuando el usuario no es admin (lógica en controladores).
- **only_for_admin:** Los permisos con `only_for_admin = 1` solo se muestran en el formulario de roles a usuarios con rol admin; el resto de usuarios no puede asignarlos.
- **Dependencias implícitas:** Para “editar” normalmente se necesita poder “ver” (las rutas de edición suelen ser distintas; no hay comprobación explícita de “tiene view para poder edit”). Para subir archivos de empleados se exige `edit_high_employees` y `upload_high_employees_files` (ambos en permissions_and).

---

## 5. Mapeo Legacy → Shield (tecben-core)

Shield usa convención `acción_recurso` (ej. `view_empresa`, `create_usuario`). En legacy los nombres son en inglés y a veces plural. Propuesta de equivalencia (recurso en singular para Shield):

| Permiso Legacy            | Permiso Shield sugerido   | Módulo / Recurso |
|---------------------------|---------------------------|-------------------|
| view_users                | view_usuario              | Usuarios          |
| create_users              | create_usuario            | Usuarios          |
| edit_users                | update_usuario            | Usuarios          |
| trash_users               | delete_usuario            | Usuarios          |
| view_high_users           | view_usuario_alta         | Usuarios (altas)  |
| create_high_users         | create_usuario_alta       | Usuarios (altas)  |
| edit_high_users           | update_usuario_alta       | Usuarios (altas)  |
| trash_high_users          | delete_usuario_alta       | Usuarios (altas)  |
| view_roles                | view_rol                  | Roles             |
| create_roles              | create_rol                | Roles             |
| edit_roles                | update_rol                | Roles             |
| trash_roles               | delete_rol                | Roles             |
| view_companies            | view_empresa              | Empresas          |
| create_companies          | create_empresa            | Empresas          |
| edit_companies            | update_empresa            | Empresas          |
| trash_companies           | delete_empresa            | Empresas          |
| view_high_employees       | view_empleado             | Empleados         |
| create_high_employees     | create_empleado           | Empleados         |
| edit_high_employees       | update_empleado           | Empleados         |
| trash_high_employees      | delete_empleado           | Empleados         |
| view_low_employees        | view_baja_empleado        | Bajas             |
| upload_high_employees_files | upload_archivo_empleado | Empleados         |
| load_authorizers          | load_autorizadores        | Solicitudes       |
| view_employment_history  | view_historial_laboral    | Empleados         |
| view_insurance_policy_document | view_documento_poliza | Seguros        |
| view_products            | view_producto             | Productos         |
| create_products          | create_producto           | Productos         |
| edit_products            | update_producto           | Productos         |
| trash_products           | delete_producto           | Productos         |
| view_product_management  | view_gestion_producto_empresa | Productos     |
| edit_product_management  | update_gestion_producto_empresa | Productos   |
| view_industries          | view_industria             | Catálogos         |
| create_industries        | create_industria           | Catálogos         |
| edit_industries         | update_industria            | Catálogos         |
| trash_industries        | delete_industria            | Catálogos         |
| view_sub_industries     | view_subindustria          | Catálogos         |
| create_sub_industries   | create_subindustria        | Catálogos         |
| edit_sub_industries     | update_subindustria        | Catálogos         |
| trash_sub_industries    | delete_subindustria        | Catálogos         |
| view_locations         | view_ubicacion             | Catálogos         |
| create_locations       | create_ubicacion           | Catálogos         |
| edit_locations         | update_ubicacion           | Catálogos         |
| trash_locations        | delete_ubicacion           | Catálogos         |
| view_departments       | view_departamento          | Catálogos         |
| create_departments     | create_departamento        | Catálogos         |
| edit_departments       | update_departamento        | Catálogos         |
| trash_departments      | delete_departamento        | Catálogos         |
| view_areas             | view_area                  | Catálogos         |
| create_areas           | create_area                | Catálogos         |
| edit_areas             | update_area                | Catálogos         |
| trash_areas            | delete_area                | Catálogos         |
| view_positions         | view_puesto                 | Catálogos         |
| create_positions       | create_puesto              | Catálogos         |
| edit_positions         | update_puesto              | Catálogos         |
| trash_positions        | delete_puesto               | Catálogos         |
| view_regions           | view_region                 | Catálogos         |
| create_regions         | create_region               | Catálogos         |
| edit_regions           | update_region               | Catálogos         |
| trash_regions          | delete_region               | Catálogos         |
| view_banks             | view_banco                  | Catálogos         |
| create_banks           | create_banco                | Catálogos         |
| edit_banks             | update_banco                | Catálogos         |
| trash_banks            | delete_banco                | Catálogos         |
| view_cost_centers      | view_centro_costo          | Catálogos         |
| create_cost_centers    | create_centro_costo        | Catálogos         |
| edit_cost_centers      | update_centro_costo        | Catálogos         |
| trash_cost_centers     | delete_centro_costo        | Catálogos         |
| view_general_areas     | view_area_general          | Catálogos         |
| create_general_areas   | create_area_general        | Catálogos         |
| edit_general_areas     | update_area_general        | Catálogos         |
| trash_general_areas    | delete_area_general        | Catálogos         |
| view_general_departments | view_departamento_general | Catálogos      |
| create_general_departments | create_departamento_general | Catálogos   |
| edit_general_departments  | update_departamento_general | Catálogos   |
| trash_general_departments | delete_departamento_general | Catálogos   |
| view_general_positions   | view_puesto_general       | Catálogos         |
| create_general_positions | create_puesto_general     | Catálogos         |
| edit_general_positions   | update_puesto_general     | Catálogos         |
| trash_general_positions  | delete_puesto_general     | Catálogos         |
| view_high_employee_filters | view_filtro_empleado    | Filtros           |
| create_high_employee_filters | create_filtro_empleado | Filtros        |
| view_portfolio         | view_portafolio            | Portfolio         |
| edit_portfolio         | update_portafolio          | Portfolio         |
| view_messages         | view_mensaje               | Mensajes          |
| create_messages       | create_mensaje             | Mensajes          |
| edit_messages         | update_mensaje             | Mensajes          |
| trash_messages        | delete_mensaje             | Mensajes          |
| personalized_messages_view | view_mensaje_personalizado | Mensajes      |
| personalized_messages    | create_mensaje_personalizado | Mensajes     |
| view_companies_messages  | view_mensajes_empresas   | Mensajes          |
| view_surveys          | view_encuesta               | Encuestas         |
| create_surveys        | create_encuesta             | Encuestas         |
| edit_surveys          | update_encuesta             | Encuestas         |
| trash_surveys         | delete_encuesta             | Encuestas         |
| duplicate_surveys     | duplicate_encuesta          | Encuestas         |
| send_surveys          | send_encuesta               | Encuestas         |
| close_surveys          | close_encuesta              | Encuestas         |
| edit_survey_shipping  | update_envio_encuesta       | Encuestas         |
| trash_survey_shipping | delete_envio_encuesta       | Encuestas         |
| view_companies_surveys | view_encuestas_empresas     | Encuestas         |
| view_acknowledgments  | view_reconocimiento         | Reconocimientos   |
| create_acknowledgments| create_reconocimiento       | Reconocimientos   |
| edit_acknowledgments  | update_reconocimiento      | Reconocimientos   |
| trash_acknowledgments | delete_reconocimiento      | Reconocimientos   |
| view_sent_acknowledgments | view_reconocimiento_enviado | Reconocimientos |
| view_voice_employee_subjects | view_tema_voz          | Voz               |
| create_voice_employee_subjects | create_tema_voz      | Voz               |
| edit_voice_employee_subjects  | update_tema_voz      | Voz               |
| trash_voice_employee_subjects | delete_tema_voz      | Voz               |
| segment_voice_employee | segmentar_voz               | Voz               |
| view_voice_employees  | view_voz                    | Voz               |
| edit_voice_employees  | update_voz                  | Voz               |
| trash_voice_employees | delete_voz                  | Voz               |
| view_receivable_accounts | view_cuenta_por_cobrar   | Cobranza          |
| process_receivable_accounts | process_cuenta_por_cobrar | Cobranza    |
| generate_internal_reports | generate_reporte_interno  | Reportes          |
| delete_penalties      | delete_penalizacion         | Cobranza          |
| view_collections      | view_cobranzas              | Cobranza          |
| view_payroll_receipts | view_recibo_nomina          | Nómina            |
| load_payroll_receipts | load_recibo_nomina          | Nómina            |
| trash_payroll_receipts| delete_recibo_nomina        | Nómina            |
| verify_accounts       | verify_cuenta               | Cuentas           |
| view_recruitments     | view_reclutamiento          | Reclutamiento     |
| create_recruitments   | create_reclutamiento        | Reclutamiento     |
| edit_recruitments     | update_reclutamiento        | Reclutamiento     |
| trash_recruitments    | delete_reclutamiento        | Reclutamiento     |
| view_recruitments_candidates | view_candidato        | Reclutamiento     |
| edit_recruitments_candidates | update_candidato      | Reclutamiento     |
| trash_recruitments_candidates | delete_candidato     | Reclutamiento     |
| trash_recruitments_candidates_comment | delete_comentario_candidato | Reclutamiento |
| view_folders          | view_directorio             | Documentos        |
| create_folders        | create_directorio           | Documentos        |
| edit_folders          | update_directorio           | Documentos        |
| trash_folders         | delete_directorio           | Documentos        |
| view_ad_folders       | view_archivo_empresa        | Documentos        |
| create_ad_folders     | create_archivo_empresa      | Documentos        |
| edit_ad_folders       | update_archivo_empresa      | Documentos        |
| trash_ad_folders      | delete_archivo_empresa      | Documentos        |
| view_ec_folders       | view_contrato_laboral       | Contratos         |
| create_ec_folders     | create_contrato_laboral     | Contratos         |
| edit_ec_folders       | update_contrato_laboral     | Contratos         |
| trash_ec_folders      | delete_contrato_laboral     | Contratos         |
| sign_ec_files         | sign_contrato_laboral       | Contratos         |
| view_capacitation     | view_capacitacion            | Capacitación      |
| create_capacitation   | create_capacitacion         | Capacitación      |
| trash_capacitation    | delete_capacitacion         | Capacitación      |
| view_ow_folders       | view_bienestar_directorio   | Bienestar         |
| create_ow_folders     | create_bienestar_directorio | Bienestar         |
| edit_ow_folders       | update_bienestar_directorio | Bienestar         |
| trash_ow_folders      | delete_bienestar_directorio | Bienestar         |
| view_notifications_push | view_notificacion_push    | Notificaciones    |
| create_notifications_push | create_notificacion_push  | Notificaciones    |
| edit_notifications_push   | update_notificacion_push  | Notificaciones    |
| trash_notifications_push  | delete_notificacion_push  | Notificaciones    |
| view_companies_notifications_push | view_notificaciones_empresas | Notificaciones |
| view_sua_letters      | view_carta_sua              | SUA               |
| load_sua_letters      | load_carta_sua              | SUA               |
| trash_sua_letters     | delete_carta_sua            | SUA               |
| view_request_categories | view_categoria_solicitud   | Solicitudes        |
| create_request_categories | create_categoria_solicitud | Solicitudes    |
| edit_request_categories  | update_categoria_solicitud  | Solicitudes    |
| trash_request_categories | delete_categoria_solicitud  | Solicitudes    |
| view_requests_type    | view_tipo_solicitud         | Solicitudes        |
| create_requests_type  | create_tipo_solicitud       | Solicitudes        |
| edit_requests_type    | update_tipo_solicitud       | Solicitudes        |
| trash_requests_type   | delete_tipo_solicitud        | Solicitudes        |
| view_moods            | view_estado_animo           | Estado ánimo       |
| create_moods          | create_estado_animo         | Estado ánimo       |
| edit_moods            | update_estado_animo        | Estado ánimo       |
| trash_moods           | delete_estado_animo         | Estado ánimo       |
| view_insurance_memberships | view_membresia_seguro   | Seguros            |
| view_mental_health_board | view_tablero_salud_mental | Reportes         |
| load_ios_code         | load_codigo_ios             | Sistema            |
| view_carousel_management | view_carrusel_empresa    | Empresa            |
| edit_carousel_management | update_carrusel_empresa  | Empresa            |

Si en Shield ya tienes nombres como `view_empresa`, `create_usuario`, etc., basta con alinear recurso por recurso (empresa↔companies, usuario↔users, etc.) y añadir los que falten según esta lista.

---

## 6. Matriz de permisos por rol (plantilla)

Sin acceso a la BD, la matriz debe rellenarse ejecutando las consultas del apartado 3. Plantilla (ejemplo con nombres de permisos legacy):

| Permiso (legacy)     | Rol 1 (ej. admin) | Rol 2 (ej. gerente) | Rol 3 (ej. RH) | … |
|----------------------|--------------------|----------------------|----------------|---|
| view_users           | ✅                 | ✅                   | ✅             |   |
| create_users         | ✅                 | ✅                   | ✅             |   |
| edit_users           | ✅                 | ✅                   | ✅             |   |
| trash_users          | ✅                 | ✅                   | ❌             |   |
| view_high_employees  | ✅                 | ✅                   | ✅             |   |
| create_high_employees| ✅                 | ✅                   | ✅             |   |
| view_voice_employees | ✅                 | ✅                   | ✅             |   |
| edit_voice_employees | ✅                 | ✅                   | ✅             |   |
| view_payroll_receipts| ✅                 | ✅                   | ❌             |   |
| …                    | …                  | …                    | …              |   |

Pasos recomendados:

1. Ejecutar en legacy las consultas SQL del apartado 3.
2. Rellenar esta tabla (o un CSV/Excel) por cada rol existente.
3. En tecben-core, crear los roles en Shield y asignar los permisos equivalentes según el mapeo del apartado 5.

---

## 7. Recomendaciones para tecben-core (Shield)

1. **Nomenclatura:** Unificar en `acción_recurso` en español (view_empresa, create_usuario, update_empleado, delete_rol). Si Shield ya generó en inglés (view_company), mantener coherencia con el resto del proyecto.
2. **Permisos que hay que crear en Shield:** Revisar los 99 permisos actuales; añadir los que falten de la lista anterior (p. ej. `view_companies_surveys`, `edit_survey_shipping`, `trash_survey_shipping`, `view_employment_history`, `view_insurance_policy_document`, `delete_penalties`, `upload_high_employees_files`, `view_carousel_management`, `edit_carousel_management`, `segment_voice_employee`, etc.).
3. **Agrupación por módulo:** Agrupar permisos por recurso/módulo (usuarios, empresas, empleados, encuestas, voz, nómina, documentos, contratos, capacitación, notificaciones, reclutamiento, etc.) para la UI de asignación de roles y para políticas de Shield.
4. **Rol “admin”:** Definir un rol superusuario (ej. `admin`) que tenga todos los permisos o que bypasee la comprobación por permiso (como en legacy con `hasRoles('admin')`).
5. **Rol actual (current role):** Si en tecben-core el usuario puede tener varios roles, decidir si se mantiene el concepto de “rol actual” en sesión para evaluar permisos o se evalúa la unión de todos los roles.
6. **Roles por empresa:** Mantener `company_id` en roles (o equivalente en Shield) para que los roles sean asignables por tenant/empresa y los listados filtren por empresa cuando corresponda.
7. **Permisos “solo admin”:** Si se replica la lógica de `only_for_admin`, definir qué permisos solo pueden ser asignados por un superadmin.
8. **Migración de datos:** Exportar de legacy la matriz rol→permiso (consultas del §3) y, con un script o seeder, crear en tecben-core los mismos roles y asignaciones usando los nombres de permiso de Shield.

---

## 8. Archivos clave en Legacy

- **Middleware:** `app/Http/Middleware/Permissions.php` (evalúa permissions_and y permissions_or con getCurrentRol()).
- **Modelos:** `app/Models/Role.php` (SoftDeletes, company_id, hasPermissions, hasOnePermission), `app/Models/Permission.php` (only_for_admin).
- **Controlador:** `app/Http/Controllers/Admin/RolesController.php` (getList filtra roles por company del usuario; create/update asignan company_id y permisos por name).
- **Vistas:** `resources/views/admin/roles/create.blade.php`, `edit.blade.php` (listado de permisos con switches; solo admin ve empresa y permisos only_for_admin).
- **Rutas:** `routes/web.php` (todas las rutas con middleware `Permissions:{"permissions_and":[...]}` o `"permissions_or":[...]`).
- **Migraciones:** `database/migrations/2014_10_12_143801_entrust_setup_tables.php`, `2021_03_18_100642_update_permissions_table.php` (only_for_admin); migración que añade `company_id` a `roles` (update_roles_table).

---

*Análisis generado a partir del código del proyecto Paco (solo lectura). Para la matriz real de roles y permisos es necesario ejecutar las consultas SQL del §3 en la base de datos legacy.*
