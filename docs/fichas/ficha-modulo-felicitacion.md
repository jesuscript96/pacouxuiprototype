# Ficha técnica: Módulo Felicitaciones / Mensajes personalizados (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Felicitaciones (FelicitacionResource / Mensajes personalizados)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite **crear, editar, listar y eliminar plantillas de mensajes de felicitación** por tipo **CUMPLEAÑOS** y **ANIVERSARIO** (tabla `festivities`). Cada plantilla tiene título, tipo, cuerpo de mensaje con placeholders [Nombre], [Apellido paterno], [Apellido materno], [Empresa], empresa asignada, departamento opcional (para filtrar destinatarios), remitente (user_id), importancia (URGENTE/NO URGENTE) y logo opcional. El comando `send:birthday` (Birthdaycongratulations) recorre las festividades y el día actual envía un **mensaje** y **notificación push** a cada empleado que cumple años o aniversario ese día (según tipo y filtro por departamento). La configuración de “quién recibe notificación de que hoy es cumpleaños de X” (toda la empresa o solo la ubicación) está en Company (allow_congratulation_notifications, congratulation_notifications_type) y se usa en el comando `birthdays:notification`. En el panel admin el módulo se llama **Mensajes personalizados**; el menú exige permisos personalized_messages, personalized_messages_list, personalized_messages_edit o personalized_messages_view. Controlador: `PersonalizedMessagesController`. Rutas bajo `admin/personalized/*`; permisos: `personalized_messages_view`, `personalized_messages` (crear), `view_messages`, `create_messages`, `edit_messages`, `trash_messages`.

---

## ENTIDADES

### Tabla: `festivities`

- **PK:** id (bigint unsigned).
- **Campos:** title (string), type (string: CUMPLEAÑOS | ANIVERSARIO), message (longText; con placeholders [Nombre], [Apellido paterno], [Apellido materno], [Empresa]), response_required (string), response_type (string), is_important (string: URGENTE | NO URGENTE), company_id (unsignedBigInteger nullable, FK companies cascade), user_id (unsignedBigInteger nullable, FK users cascade; añadido update_festivities_3), department_id (unsignedBigInteger nullable, FK departments cascade; añadido update_festivities, companies_filter eliminado en esa migración), logo (string nullable; ruta en disco; añadido update_festivities_4). timestamps.
- **Relaciones (modelo Festivity):** company() belongsTo Company, department() belongsTo Department, users() hasMany User (nombre confuso; en uso solo user_id como remitente).

### Tabla: `companies` (contexto)

- **Campos usados en felicitaciones:** allow_congratulation_notifications (boolean), congratulation_notifications_type (enum COMPANY | LOCATION nullable). Define si se envían notificaciones push de “hoy es cumpleaños de X” y a qué alcance (toda la empresa o solo misma ubicación). No forma parte del CRUD de mensajes personalizados; se edita en CompaniesController (create/edit empresa).
- **Relación:** festivities() hasMany Festivity.

### Tabla: `messages` (contexto)

- El comando Birthdaycongratulations **crea** registros Message por cada empleado que cumple años/aniversario, reemplazando en el cuerpo los placeholders y asociando el mensaje al empleado (received_messages), al remitente (sent_messages), StatusHistory, MessageTableu y Notification. Los mensajes enviados no se gestionan en este CRUD; este CRUD solo gestiona las **plantillas** (Festivity).

### Otras tablas de contexto

- **Department:** filtro opcional por departamento en la plantilla; el comando solo envía a empleados de ese departamento cuando department_id está definido.
- **User:** remitente de la plantilla (user_id); en el comando se usa como sender del mensaje.
- **Log:** auditoría al crear, actualizar y eliminar una festividad.
- **HighEmployee:** destinatarios efectivos según fecha de nacimiento (CUMPLEAÑOS) o admission_date (ANIVERSARIO) y opcionalmente department_id.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/personalized/messages | GET | PersonalizedMessagesController@getIndex | personalized_messages_view |
| admin/personalized/get | GET | getList | view_messages |
| admin/personalized/create | GET | getCreate | personalized_messages |
| admin/personalized/create | POST | create | create_messages |
| admin/personalized/edit/{festivity} | GET | getEdit | edit_messages |
| admin/personalized/edit/{festivity} | POST | update | edit_messages |
| admin/personalized/trash/{festivity} | GET | Trash | trash_messages |
| admin/personalized/departments | POST | queryDepartments | create_messages o edit_messages (permissions_or) |

Middleware: `logged`, `2fa` (salvo create POST que solo `logged`), `Permissions:{"permissions_and":["..."]}` o `permissions_or` según ruta.

**Sidebar:** Enlace "Mensajes Personalizados" si el usuario tiene al menos uno de: personalized_messages, personalized_messages_list, personalized_messages_edit, personalized_messages_view (ruta admin_personalized_messages).

---

## REGLAS DE NEGOCIO

- **RN-01:** title, message y senders (remitente) son **obligatorios** en create y update.
- **RN-02:** El cuerpo del mensaje debe contener **al menos un placeholder** de destinatario: [Nombre], [Apellido paterno] o [Apellido materno]. Si no hay ninguno, error "Debe haber al menos un dato del destinatario".
- **RN-03:** Cada plantilla pertenece a una **empresa** (company_id). Si el usuario es admin elige empresa en el formulario; si no es admin se usa la empresa del usuario. Si no hay empresa válida, error "Debe haber una empresa a la cual asignar el mensaje".
- **RN-04:** **Departamento opcional:** si se elige departamento, el comando send:birthday solo envía la felicitación a empleados de ese departamento (para ese tipo y empresa). Si no se elige, se envían a todos los que cumplan la fecha en la empresa.
- **RN-05:** **Tipo:** CUMPLEAÑOS (filtro por birthdate día/mes) o ANIVERSARIO (filtro por admission_date día/mes, año distinto al actual). El comando recorre Festivity::get() y para cada una filtra high_employees por empresa, por tipo de fecha y por department_id si existe.
- **RN-06:** **Logo:** opcional; archivo jpg, jpeg, png o bmp, máximo 5 MB. Se guarda en `assets/companies/festivities/logos/{company_id}_{time}.png` en disco `uploads`. En update se puede eliminar (logo_url no enviado) o reemplazar subiendo otro archivo.
- **RN-07:** Listado (getList): si el usuario tiene **company** y permiso personalized_messages_view, solo se listan festividades de su empresa (`Festivity::where('company_id', $company_id)`); si **no tiene company** (admin), se listan todas. getIndex usa la misma lógica pero con un posible bug: usa `$user->company->company_id` (Company no tiene atributo company_id; debería ser `$user->company_id`) para filtrar, lo que puede dejar el filtro en null (⚠️ ver CASOS BORDE).
- **RN-08:** getEdit y Trash no comprueban que la festividad pertenezca a la empresa del usuario cuando no es admin; un no-admin podría editar/eliminar una plantilla de otra empresa si conoce el id (⚠️ ver CASOS BORDE).
- **RN-09:** Eliminación (Trash) es **física** (delete); no hay soft delete en Festivity. Se hace detach de relaciones si las hubiera y festivity->delete().

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

1. **getIndex:** Si usuario tiene company y permiso personalized_messages_view, mensajes = Festivity::where('company_id', $user->company->company_id) — aquí company_id es incorrecto (Company tiene id, no company_id); si no tiene company, Festivity::get(). Se construyen listas de companies y types para filtros de la vista. Vista `admin.personalized_messages.list` con DataTable que consume getList.
2. **getList (GET):** Misma lógica de scope (company_id cuando tiene company + personalized_messages_view). Para cada festividad: id, title, type, is_important (con estilo si URGENTE), botones Editar y Eliminar; si admin, columna con nombre de empresa (o "SIN ASIGNAR"). Respuesta JSON `{ data: messages_list }`.

### Crear (getCreate / create)

1. **getCreate:** Remitentes: si admin, User type user o high_user; si no, users high_user de su empresa. Departamentos: de la empresa del usuario o todos con high_employees. Companies para selector (admin). Vista create con title, type (CUMPLEAÑOS/ANIVERSARIO), message (con placeholders), is_important, senders, company (admin), department opcional, logo opcional, vista previa. recipient_data = ['Nombre', 'Apellido paterno', 'Apellido materno'].
2. **create:** Validar title, message, senders required. Comprobar que message contenga al menos un placeholder de destinatario. company = admin ? Company::find(request->company) : user->company. department = request->department si existe. Crear Festivity (title, type, message, is_important, response_type '', response_required 'NO', user_id). Si hay logo: validar mimes jpg,jpeg,png,bmp max 5000 KB, guardar en assets/companies/festivities/logos/{company_id}_{time}.png. company->festivities()->save(message); si department, department->festivities()->save(message). Log y redirect a admin_personalized_messages con "Creado exitosamente".

### Editar (getEdit / update)

1. **getEdit:** Festivity por route binding. Si no existe redirect a listado. Departamentos según old('company') o festivity->company. Remitentes y companies como en getCreate. default_logo = festivity->logo o logo por defecto de empresa. Vista edit con festivity y mismos campos que create.
2. **update:** Validaciones iguales que create. company y department según usuario (admin elige empresa). Actualizar title, type, message, is_important, user_id. Logo: si hay archivo nuevo, validar y guardar, borrar logo anterior del disco; si no hay archivo y no viene logo_url, borrar logo y poner null. festivity->company()->associate($company)->save(); department associate o dissociate. Log y redirect con "Mensaje actualizado exitosamente".

### Eliminar (Trash)

1. Festivity por route binding. Si no existe redirect back. Log "ha eliminado el mensaje personalizado: ...". festivity->delete(). Redirect a admin_personalized_messages con mensaje "Se ha eliminado el mensaje: ...".

### Comando send:birthday (Birthdaycongratulations)

1. Fecha actual (mes, día, año). Festivity::get() (todas las plantillas).
2. Por cada plantilla: company = plantilla->company. Si type == CUMPLEAÑOS: high_employees de la empresa con birthdate mes/día iguales y que tengan user; si plantilla tiene department_id, filtrar por ese departamento. Por cada empleado: reemplazar placeholders en message ([Nombre], [Apellido paterno], [Apellido materno], [Empresa] con department->name o company->general_name). Crear Message (title, body, is_scheduled NO PROGRAMADO, is_sent ENVIADO, type_message, etc.), asociar a sender (user_id de plantilla o 1), high_employee->received_messages()->attach, StatusHistory, MessageTableu, Notification ("te desea un feliz cumpleaños..."), NotificationPush a tokens OneSignal del empleado.
3. Si type == ANIVERSARIO: igual pero filtro por admission_date mes/día y año distinto al actual; mismo flujo Message + Notification ("felicidades por cumplir un año más...").
4. Cola: high_priority_notifications para el push.

### Comando birthdays:notification (BirthdaysNotification)

1. Empleados con birthdate hoy y cuya empresa tenga allow_congratulation_notifications = 1.
2. Por cada empleado: obtener tokens OneSignal de otros empleados según congratulation_notifications_type: COMPANY → misma empresa; LOCATION o null → misma ubicación (location_id). Enviar push "Hoy es cumpleaños de {nombre} {apellido}" con type CUMPLEAÑOS. No crea Message ni Festivity; solo notificación push a compañeros.

---

## VALIDACIONES

- **create/update:** title required, message required, senders required. Mensajes: "El titulo es requerido", "El cuerpo del mensaje es requerido", "El remitente es requerido". Al menos un placeholder [Nombre], [Apellido paterno] o [Apellido materno] en message.
- **logo (si se sube):** file, mimes:jpg,jpeg,png,bmp, max:5000 (5 MB). Mensajes: "Solo los formatos jpg,png y bmp estan permitidos", "Disculpe! El maximo permitido para una imagen es 5MB".
- No se valida unicidad de título por empresa ni que el usuario no-admin solo edite/elimine plantillas de su empresa.

---

## VISTAS

- **admin.personalized_messages.list:** Listado con DataTable (AJAX getList). Filtros por empresa y tipo. Columnas: id, título, tipo, importancia, acciones (Editar, Eliminar); si admin, columna empresa. Botón Crear. Modal confirmación eliminar.
- **admin.personalized_messages.create:** Formulario: título, tipo (CUMPLEAÑOS/ANIVERSARIO), importancia (URGENTE/NO URGENTE), remitente, cuerpo del mensaje, empresa (admin), departamento opcional, logo, checkboxes para incluir placeholders en mensaje, vista previa con logo y colores de empresa. action admin_personalized_messages_create.
- **admin.personalized_messages.edit:** Igual que create con datos de la festividad; action admin_personalized_messages_update con festivity id. Secciones de vista previa para cumpleaños y aniversario según tipo.

---

## USO EN OTROS MÓDULOS

- **Birthdaycongratulations (send:birthday):** Lee Festivity, genera Message y Notification por cada empleado que cumple años/aniversario hoy.
- **BirthdaysNotification (birthdays:notification):** Usa allow_congratulation_notifications y congratulation_notifications_type de Company para enviar push a compañeros (empresa o ubicación).
- **CompaniesController:** create/edit empresa guardan allow_congratulation_notifications y congratulation_notifications_type (no son parte del CRUD de mensajes personalizados).
- **AuthController (API):** Usa congratulation_notifications_type para segmentar (LOCATION o null vs COMPANY).
- **MessagesController (API):** Obtiene logo de la festividad para mostrar en mensaje (festivity_logo_url, festivity->logo).
- **Department:** hasMany Festivity; filtro opcional por departamento en plantilla.

---

## MODELOS INVOLUCRADOS

- **Festivity:** tabla festivities. fillable: title, type, message, response_required, response_type, is_important, company_id, user_id, logo. company() belongsTo Company, department() belongsTo Department, users() hasMany User (nombre confuso; en práctica solo user_id como remitente).
- **Company:** festivities() hasMany Festivity; allow_congratulation_notifications, congratulation_notifications_type; getFestivityLogoUrlAttribute() para logo por defecto en vistas.
- **Department:** festivities() hasMany Festivity.
- **Message, Notification, StatusHistory, MessageTableu, HighEmployee, User:** usados en el comando para crear mensajes y notificaciones al ejecutar send:birthday.

---

## MIGRACIONES

- **create_festivities_table:** title, type, message (string), response_required, response_type, is_important, companies_filter (longText nullable), company_id nullable FK companies, timestamps.
- **update_festivities_table:** department_id nullable FK departments; dropColumn companies_filter.
- **update_festivities_2_table:** message pasa a longText.
- **update_festivities_3_table:** user_id nullable FK users.
- **update_festivities_4_table:** logo string nullable.

---

## PERMISOS LEGACY

- **personalized_messages_view:** Ver listado (getIndex) y filtrar por empresa/tipo (getList con scope por empresa cuando usuario tiene company).
- **personalized_messages:** Acceso a getCreate (formulario de creación).
- **view_messages:** getList (datos de la tabla).
- **create_messages:** POST create y queryDepartments.
- **edit_messages:** getEdit y POST update.
- **trash_messages:** Trash.

---

## CASOS BORDE

- **getIndex con company:** Se usa `$user->company->company_id` para filtrar Festivity. El modelo Company no tiene atributo `company_id` (tiene `id`). Eso deja el filtro como null, por lo que `Festivity::where('company_id', null)` mostraría solo plantillas sin empresa asignada en lugar de las de la empresa del usuario. ⚠️ Bug probable; debería ser `$user->company_id`.
- **getEdit/Trash sin scope por empresa:** Un usuario con empresa que tenga edit_messages o trash_messages puede editar o eliminar una festividad de otra empresa si conoce el id (p. ej. admin_personalized_messages_edit/999). No hay comprobación company_id == user->company_id.
- **Remitente user_id:** Si la plantilla tiene user_id null o el usuario fue eliminado, el comando usa User::find($company_message->user_id ?? 1); puede fallar si el usuario 1 no existe.
- **Empleados sin user:** El comando filtra high_employees con ->has('user'); los empleados sin usuario asociado no reciben la felicitación por mensaje ni push (OneSignal requiere user).
- **companies_filter:** La columna fue eliminada en update_festivities_table. El comando Birthdaycongratulations asigna $message->companies_filter para mensajes tipo ANIVERSARIO; ese atributo corresponde al modelo Message (tabla messages), no a Festivity, por lo que no hay conflicto con la migración de festivities.

---

## AMBIGÜEDADES

- **personalized_messages_list / personalized_messages_edit:** El sidebar comprueba hasOnePermission(['personalized_messages','personalized_messages_list','personalized_messages_edit','personalized_messages_view']). Las rutas usan personalized_messages_view, view_messages, create_messages, edit_messages, trash_messages. No está claro si personalized_messages_list y personalized_messages_edit se usan en backend o solo en menú.
- **Orden de envío:** Festivity::get() no aplica orden; si hay varias plantillas para la misma empresa/tipo/departamento, el orden de procesamiento y cuál mensaje recibe el empleado no está definido (podría haber duplicados o la última sobrescribiendo).

---

## DEUDA TÉCNICA

- getIndex usa $user->company->company_id (atributo inexistente en Company); debería ser $user->company_id.
- Relación Festivity->users() hasMany User es confusa; solo se usa user_id como remitente (belongsTo sería correcto).
- Trash por GET; recomendable POST/DELETE para eliminar.
- Eliminación física sin soft delete; no hay historial de plantillas borradas.
- Comando send:birthday hace muchas operaciones síncronas (crear Message, Notification, StatusHistory, MessageTableu, push) por cada empleado; podría optimizarse con colas.

---

## DIFERENCIAS CON TECBEN-CORE

Por definir (no verificado en este análisis). Si en tecben-core existe el módulo de felicitaciones o mensajes personalizados (plantillas cumpleaños/aniversario), comparar: scope por empresa en listado y en edición/eliminación, placeholders soportados, integración con comandos programados y con allow_congratulation_notifications / congratulation_notifications_type de Company.
