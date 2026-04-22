# Ficha técnica: Módulo Segmentación Voz del Colaborador (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Segmentación Voz del Colaborador (SegmentacionVozColaboradorResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite **segmentar** los **temas de voz del colaborador** (VoiceEmployeeSubject) de dos formas: (1) **Por empresa:** qué temas ve cada empresa (catálogo por empresa y tema exclusivo para una empresa). (2) **Por usuario:** qué usuarios (high_user) tienen asignado cada tema para filtrar los comentarios de voz que ven en el módulo “Voz del colaborador”. La pantalla “Segmentación Voz del Colaborador” lista todos los temas y permite editar la asignación a **usuarios** (destinatarios). La asignación a **empresas** se gestiona en “Temas (Voz del Colaborador)” por empresa (create/edit con exclusive_for_company y pivot company_voice_employee_subject). Controlador: `VoiceEmployeeSubjectsController`; las rutas de segmentación exigen permiso `segment_voice_employee`.

---

## ENTIDADES

### Tabla: `voice_employee_subjects`

- **PK:** id (bigint unsigned).
- **Campos:** name (string), description (text nullable), exclusive_for_company (string nullable en migración 3; en código se usa como id de empresa). SoftDeletes (deleted_at).
- **Relaciones (modelo):** voice_employees (hasMany VoiceEmployee), users (belongsToMany User, pivot user_voice_employee_subject), companies (belongsToMany Company, pivot company_voice_employee_subject).

### Tabla: `user_voice_employee_subject` (pivot)

- **Campos:** id, user_id (FK users cascade), voice_employee_subject_id (FK voice_employee_subjects cascade), timestamps.
- **Uso:** Asignación de temas a usuarios (high_user). Quién tiene permiso segment_voice_employee puede asignar qué usuarios ven cada tema.

### Tabla: `company_voice_employee_subject` (pivot)

- **Campos:** id, company_id (FK companies cascade), voice_employee_subject_id (FK voice_employee_subjects cascade), timestamps.
- **Uso:** Asignación de temas a empresas. Un tema con exclusive_for_company = null se asigna a todas las empresas que “tienen temas”; un tema con exclusive_for_company = X solo a la empresa X (y se reasignan los temas de esa empresa).

### Tabla: `voice_employees` (contexto)

- **FK:** voice_employee_subject_id → voice_employee_subjects. Cada comentario de voz pertenece a un tema. En el listado de “Voz del colaborador” se filtran los comentarios por los temas que tiene asignados la empresa del usuario y, si además el usuario tiene temas asignados por segmentación, por la intersección (solo esos temas).

---

## RUTAS Y PERMISOS (Segmentación Voz del Colaborador)

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/voice_employee_subjects/users/segmentation | GET | getIndexSegmentation | segment_voice_employee |
| admin/voice_employee_subjects/users/segmentation/get | GET | getListSegmentation | segment_voice_employee |
| admin/voice_employee_subjects/users/segmentation/edit/{id} | GET | getEditSegmentation | segment_voice_employee |
| admin/voice_employee_subjects/edit | POST | update (view=edit_segmentation / receivers) | edit_voice_employee_subjects Y segment_voice_employee |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["segment_voice_employee"]}` (en listado y edición de segmentación). La ruta de update exige **ambos** edit_voice_employee_subjects y segment_voice_employee (permissions_and).

El permiso **segment_voice_employee** también se usa en: queryReceivers (mensajes/encuestas/notificaciones) para el selector de destinatarios (permissions_or con create_messages, send_surveys, create_notifications_push).

---

## REGLAS DE NEGOCIO

- **RN-01:** Un tema puede ser **general** (exclusive_for_company = null) o **exclusivo** de una empresa (exclusive_for_company = company_id). Los temas generales se asignan a todas las empresas que ya tienen temas; los exclusivos solo a esa empresa.
- **RN-02:** **Segmentación por usuarios:** Solo usuarios con type = 'high_user' pueden ser asignados a un tema en la pivot user_voice_employee_subject. La pantalla “Segmentación Voz del Colaborador” lista temas y permite elegir destinatarios (usuarios) por tema.
- **RN-03:** Si el usuario logueado tiene **company_id:** al actualizar la segmentación de un tema (receivers), se conservan las asignaciones de usuarios de **otras empresas** y solo se hace sync con los usuarios seleccionados (que incluye implícitamente a los de otras empresas para no quitarlos). Así, un admin de empresa solo puede cambiar qué usuarios de su empresa tienen el tema; no puede desasignar usuarios de otras empresas.
- **RN-04:** Si en la edición de segmentación (view = edit_segmentation) se envía **receivers vacío** y el tema tenía usuarios asignados, se hace detach: si el usuario tiene company, solo se desasignan los usuarios de su empresa; si no tiene company, se desasignan todos.
- **RN-05:** En el listado de “Voz del colaborador” (VoiceEmployeesController): primero se filtran los comentarios por los temas de la empresa del usuario (company->voice_employee_subjects); si además el usuario tiene voice_employee_subjects asignados (segmentación por usuario), se filtra por la intersección (solo esos temas). Si no tiene temas por empresa no se aplica filtro de empresa; si no tiene temas por usuario no se aplica filtro por usuario.
- **RN-06:** Un usuario **sin company** (super admin) puede crear/editar temas con exclusive_for_company opcional; puede asignar cualquier high_user a cualquier tema en la segmentación. Un usuario **con company** solo puede editar/eliminar/ocultar temas cuyo exclusive_for_company sea su empresa o null (y en el caso de eliminar, solo si exclusive_for_company no es null o si es de su empresa).
- **RN-07:** No se puede **eliminar** (trash) un tema que tenga voice_employees (comentarios) asociados.
- **RN-08:** **Ocultar** (hide) desvincula el tema de una empresa concreta (detach en company_voice_employee_subject para esa company_id); no borra el tema ni la segmentación por usuarios.

---

## FLUJO PRINCIPAL (Segmentación por usuarios)

### Listado de segmentación (getIndexSegmentation / getListSegmentation)

1. getIndexSegmentation: devuelve vista index_segmentation (DataTable que consume getListSegmentation por AJAX).
2. getListSegmentation: temas a listar = si el usuario tiene company → company->voice_employee_subjects; si no → VoiceEmployeeSubject::all(). Para cada tema se muestra id, nombre, exclusive_for_company (solo admin) y botón “Editar” que lleva a getEditSegmentation(voice_employee_subject_id).

### Editar segmentación por usuarios (getEditSegmentation)

1. Obtener VoiceEmployeeSubject por id. Si no existe o (usuario tiene company y el tema es exclusivo de otra empresa) → redirigir con error.
2. Usuarios disponibles para elegir: si el usuario tiene company → company->users(); si no → User::where('type','high_user')->get().
3. user_voice_employee_subject = número de usuarios ya asignados al tema (si tiene company, solo de su empresa; si no, total).
4. Vista edit_segmentation con tema, usuarios y count; formulario POST a admin_voice_employee_subjects_update con view=edit_segmentation y receivers (array de user ids).

### Actualizar segmentación (update con view = edit_segmentation)

1. Validar name requerido; actualizar name y description del tema (y exclusive_for_company si viene en request).
2. receivers = array_keys(request->receivers) o [].
3. Si receivers no vacío: si usuario tiene company, añadir a receivers los users del tema que son de otras empresas (para no quitarlos); hacer sync del tema con User::where('type','high_user')->whereIn('id', receivers). Si usuario no tiene company, sync directo con esos users.
4. Si receivers vacío y view == edit_segmentation: detach de usuarios del tema; si usuario tiene company, detach solo users donde company_id = user->company_id; si no, detach todos.
5. La lógica de “segmentación por empresas” (company_voice_employee_subject) se ejecuta después en el mismo update (exclusive_for_company null → attach a todas las empresas que tienen temas; exclusive_for_company = X → detach de todas y reasignar solo a la empresa X los temas que correspondan).
6. Log “ha segmentado el tema” y redirect a admin_voice_employee_subjects_users_segmentation_edit con mensaje de éxito.

### Crear tema (create) y asignación inicial

- Si request->receivers existe, se asignan usuarios (high_user) al tema vía attach (segmentación por usuarios).
- Si exclusive_for_company == null, se hace attach del tema a todas las Company que tienen voice_employee_subjects. Si exclusive_for_company != null, se asigna solo a esa empresa y se reajusta la lista de temas de esa empresa (detach todos, attach temas generales + exclusivos de esa empresa).

### Uso en “Voz del colaborador” (VoiceEmployeesController)

- company_voice_employee_subjects = temas de la empresa del usuario (si tiene company y la empresa tiene temas).
- user_voice_employee_subjects = temas asignados al usuario (si tiene).
- Si no empty(company_voice_employee_subjects) se filtra voice_employees por voice_employee_subject_id in company_voice_employee_subjects.
- Si no empty(user_voice_employee_subjects) se filtra voice_employees por voice_employee_subject_id in user_voice_employee_subjects.
- Resultado: el usuario solo ve comentarios de voz de los temas que cumplan ambos criterios (empresa + usuario) cuando aplican.

---

## VALIDACIONES

- **name:** required en create y update (incluida la llamada desde edit_segmentation; en la vista edit_segmentation el nombre va readonly pero se envía y se valida).
- **description, exclusive_for_company:** opcionales. exclusive_for_company se usa como id de empresa en comparaciones y en Company::where('id', ...).

---

## VISTAS (Segmentación)

- **admin.voice_employee_subjects.index_segmentation:** Listado de temas con DataTable (AJAX a admin_voice_employee_subjects_users_segmentation_get). Columnas: id, nombre, exclusive_for_company (solo admin), botón Editar → getEditSegmentation.
- **admin.voice_employee_subjects.edit_segmentation:** Formulario con tema (name, description readonly), selector de usuarios (receivers), hidden view=edit_segmentation y voice_employee_subject_id. POST a admin_voice_employee_subjects_update.

---

## PERMISOS (Legacy, resumen)

- **segment_voice_employee:** getIndexSegmentation, getListSegmentation, getEditSegmentation; y update cuando se envía view=edit_segmentation o request->receivers. También usado en queryReceivers (mensajes/encuestas/notificaciones).
- **view_voice_employee_subjects:** listado empresas con temas, listado temas por empresa, ver tema, ocultar.
- **create_voice_employee_subjects:** getCreate, create.
- **edit_voice_employee_subjects:** getEdit, update (y requerido junto con segment_voice_employee en la ruta update).
- **trash_voice_employee_subjects:** trash, hide.

---

## CASOS BORDE

- **Tema exclusivo de otra empresa:** Si el usuario tiene company y el tema tiene exclusive_for_company distinto de su empresa, no puede editarlo ni eliminarlo; en getView y getEdit se redirige con “no se encuentra registrado”. En getEditSegmentation igual: no puede segmentar ese tema.
- **exclusive_for_company null:** Un usuario con company no puede eliminar (trash) un tema general; se devuelve “No cuenta con los permisos para eliminar este tema”.
- **Sync con receivers:** Al hacer sync(new_receivers), si el usuario tiene company se han añadido antes al array receivers los users de otras empresas; así sync mantiene esos usuarios y añade/quita los de la empresa actual. Si no se hiciera ese merge, al segmentar solo usuarios de una empresa se quitarían los de otras.
- **getListSegmentation:** Para usuario con company se usa $user->company->voice_employee_subjects (relación many-to-many). Si la empresa no tiene ningún tema asignado en company_voice_employee_subject, la colección está vacía y el listado de segmentación queda vacío aunque existan temas globales.

---

## BUGS E INCONSISTENCIAS

1. **exclusive_for_company como string en migración:** La migración update_voice_employee_subjects_3_table añade exclusive_for_company como string nullable. En el código se usa como id de empresa (Company::where('id', $voice_employee_subject->exclusive_for_company), comparaciones con $user->company->id). Si en BD se guardan ids numéricos, funciona por coerción; si en algún momento se migró a unsignedBigInteger no está en los archivos revisados.
2. **getListSegmentation para admin sin company:** Usa VoiceEmployeeSubject::all() (todos los temas). Para usuario con company usa solo company->voice_employee_subjects; si una empresa no tiene temas asignados en company_voice_employee_subject, no verá temas en la pantalla de segmentación aunque sí existan temas generales en la BD. Posible comportamiento confuso: temas recién creados como “general” se asignan a todas las empresas que ya tienen temas; empresas que nunca han tenido temas no aparecen en getListCompanies y no tendrían temas en su “company->voice_employee_subjects” hasta que se les asigne en create/update.
3. **Vista edit_segmentation:** Envía name y description (readonly) en el formulario; el update siempre actualiza name y description del tema. Si otro proceso cambiara el tema entre medias, se sobrescribiría; no es un bug crítico pero el flujo de segmentación podría solo enviar voice_employee_subject_id y view=edit_segmentation sin modificar name/description.
4. **Variable $user_voice_employee_subject en getEditSegmentation:** Se asigna el **count** de usuarios (integer). La vista muestra “Seleccionados {{ $user_voice_employee_subject }}”. Correcto; el nombre de variable suena a “relación” pero es un número.
5. **Uso de `Collection::except()` en asignación a empresas:** En update (y create) se usa `VoiceEmployeeSubject::...->get()->except($voice_employee_subject->id)`. En Laravel, `except()` elimina por **clave** de la colección (0, 1, 2, …), no por `id` del modelo. Debería usarse `->where('id', '!=', $voice_employee_subject->id)` o `->filter(fn($s) => $s->id != $voice_employee_subject->id)` / `->whereNotIn('id', $excluded_subjects)`.

---

## MODELOS INVOLUCRADOS

- **VoiceEmployeeSubject:** SoftDeletes, fillable name, description, exclusive_for_company. Relaciones: voice_employees (hasMany), users (belongsToMany con timestamps), companies (belongsToMany). Tabla voice_employee_subjects.
- **User:** voice_employee_subjects() belongsToMany VoiceEmployeeSubject con timestamps. Tabla pivot user_voice_employee_subject.
- **Company:** voice_employee_subjects() belongsToMany VoiceEmployeeSubject. Tabla pivot company_voice_employee_subject.
- **VoiceEmployee:** voice_employee_subject_id FK a voice_employee_subjects; usado en listados de “Voz del colaborador” filtrado por temas.

---

## MIGRACIONES

- **2019_12_11_140140_create_voice_employee_subjects_table:** Crea voice_employee_subjects (id, name, timestamps).
- **2021_10_05_113441_update_voice_employee_subjects_table:** Añade description (text nullable).
- **2021_10_29_092958_update_voice_employee_subjects_2_table:** Añade softDeletes.
- **2023_11_30_134139_update_voice_employee_subjects_3_table:** Añade exclusive_for_company (string nullable).
- **2021_08_18_122300_create_user_voice_employee_subject:** Crea pivot user_voice_employee_subject (id, user_id, voice_employee_subject_id, timestamps, FKs cascade).
- **2023_11_30_140924_create_company_voice_employee_subject:** Crea pivot company_voice_employee_subject (id, company_id, voice_employee_subject_id, timestamps, FKs cascade).

---

## AMBIGÜEDADES

- **Orden de aplicación de filtros en Voz del colaborador:** Primero se filtra por temas de la empresa; luego, si el usuario tiene temas asignados, se filtra por esos. Si ambos están definidos, es intersección. Si la empresa no tiene temas asignados, company_voice_employee_subjects = [] y no se hace whereIn (no se filtra por empresa); entonces podrían listarse todos los comentarios hasta que se aplique el filtro de usuario. Si el usuario tiene temas asignados, se filtra por esos. Si no tiene ni empresa con temas ni usuario con temas, no se aplica ningún whereIn y se ven todos los voice_employees. ⚠️ AMBIGUO: en algunos entornos podría esperarse que “sin temas en la empresa” signifique “no ver ningún comentario”.
- **except($voice_employee_subject->id):** En create y update se usa VoiceEmployeeSubject::...->get()->except($voice_employee_subject->id). except() sobre una colección usa las keys de la colección (índices numéricos), no el id del modelo. Por tanto except($voice_employee_subject->id) podría no quitar el tema actual de la lista como se pretende; puede ser un bug.

---

## DEUDA TÉCNICA

- **Pantalla “Temas (Voz del Colaborador)” vs “Segmentación”:** La primera se accede desde Catálogos Admin y permite CRUD de temas y asignación por empresa (exclusive_for_company + company_voice_employee_subject). La segunda solo lista temas y permite editar destinatarios (usuarios). El menú muestra “Segmentación Voz del Colaborador” solo con permiso segment_voice_employee; “Temas (Voz del Colaborador)” con view_voice_employee_subjects. Son dos entradas distintas para dos aspectos del mismo recurso.
- **Lógica de “segmentación por empresas” duplicada:** En create() y en update() se repite el bloque que, según exclusive_for_company sea null o no, hace attach a todas las empresas o detach/attach solo a la empresa correspondiente. La condición con except() y diff/pluck es densa y podría extraerse a un servicio.
- **Ruta única de update:** La misma acción update() sirve para editar tema (nombre, descripción, exclusividad, segmentación por empresas) y para editar solo la segmentación por usuarios (view=edit_segmentation). La rama de “segmentación por empresas” siempre se ejecuta; la de “segmentación por usuarios” depende de request->receivers y view.
- **Permiso segment_voice_employee en queryReceivers:** El mismo permiso se usa para la ruta de “query receivers” de mensajes/encuestas/notificaciones; quien puede segmentar voz puede también usar ese endpoint para elegir destinatarios. No hay documentación explícita de por qué comparten permiso.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
