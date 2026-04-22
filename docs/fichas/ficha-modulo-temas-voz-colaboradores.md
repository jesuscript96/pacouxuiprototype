# Ficha técnica: Módulo Temas Voz del Colaborador (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Temas Voz del Colaborador (TemasVozColaboradoresResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite gestionar los **temas de voz del colaborador** (VoiceEmployeeSubject) por empresa: listar empresas que tienen temas asignados, listar temas de una empresa, crear, editar, ver, eliminar (trash) y **ocultar** (hide) un tema para una empresa. Un tema puede ser **general** (visible para todas las empresas que tienen temas) o **exclusivo** de una empresa (exclusive_for_company). La asignación a empresas se hace mediante la pivot company_voice_employee_subject. La asignación a **usuarios** (qué usuarios ven cada tema) se gestiona en el módulo “Segmentación Voz del Colaborador”, no aquí. Controlador: `VoiceEmployeeSubjectsController`. Menú: “Temas (Voz del Colaborador)”.

---

## ENTIDADES

### Tabla: `voice_employee_subjects`

- **PK:** id (bigint unsigned).
- **Campos:** name (string), description (text nullable), exclusive_for_company (string nullable en migración; en código se usa como id de empresa). SoftDeletes (deleted_at).
- **Relaciones (modelo):** voice_employees (hasMany VoiceEmployee), users (belongsToMany User, pivot user_voice_employee_subject), companies (belongsToMany Company, pivot company_voice_employee_subject).

### Tabla: `company_voice_employee_subject` (pivot)

- **Campos:** id, company_id (FK companies cascade), voice_employee_subject_id (FK voice_employee_subjects cascade), timestamps.
- **Uso:** Qué temas ve cada empresa. Un tema general (exclusive_for_company = null) se asigna a todas las empresas que ya tienen temas; un tema exclusivo solo a la empresa indicada.

### Tabla: `voice_employees` (contexto)

- **FK:** voice_employee_subject_id → voice_employee_subjects. No se puede eliminar un tema que tenga comentarios de voz asociados.

---

## RUTAS Y PERMISOS (Temas Voz del Colaborador)

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/voice_employee_subjects/companies | GET | getIndexCompanies | view_voice_employee_subjects |
| admin/voice_employee_subjects/companies/get | GET | getListCompanies | view_voice_employee_subjects |
| admin/voice_employee_subjects/companies/{company_id} | GET | getIndex | view_voice_employee_subjects |
| admin/voice_employee_subjects/companies/{company_id}/get | GET | getList | view_voice_employee_subjects |
| admin/voice_employee_subjects/companies/{company_id}/create | GET | getCreate | create_voice_employee_subjects |
| admin/voice_employee_subjects/create | POST | create | create_voice_employee_subjects |
| admin/voice_employee_subjects/companies/{company_id}/edit/{id} | GET | getEdit | edit_voice_employee_subjects |
| admin/voice_employee_subjects/edit | POST | update | edit_voice_employee_subjects **y** segment_voice_employee |
| admin/voice_employee_subjects/companies/{company_id}/view/{id} | GET | getView | view_voice_employee_subjects |
| admin/voice_employee_subjects/companies/{company_id}/trash/{id} | GET | trash | trash_voice_employee_subjects |
| admin/voice_employee_subjects/companies/{company_id}/hide/{id} | GET | hide | trash_voice_employee_subjects |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`. La ruta **update** exige **ambos** edit_voice_employee_subjects y segment_voice_employee (el mismo endpoint sirve para editar tema y para guardar segmentación por usuarios).

---

## REGLAS DE NEGOCIO

- **RN-01:** Nombre del tema obligatorio (validación name required).
- **RN-02:** Un tema puede ser **general** (exclusive_for_company = null) o **exclusivo** de una empresa (exclusive_for_company = company_id). Solo usuarios con rol **admin** pueden elegir; si no es admin, al crear/editar se fuerza exclusive_for_company = company_id (la empresa desde la que se accede).
- **RN-03:** **Crear tema general:** Si exclusive_for_company == null, se hace attach del tema a todas las Company que ya tienen voice_employee_subjects. **Crear tema exclusivo:** Se asigna solo a esa empresa y se reajusta la lista de temas de esa empresa (detach todos, attach temas generales + exclusivos de esa empresa).
- **RN-04:** **Actualizar tema:** Si exclusive_for_company == null, se attach el tema a todas las empresas que tienen temas y aún no lo tienen. Si exclusive_for_company != null, se detach el tema de todas las empresas y se reasigna solo a la empresa indicada (detach de la empresa, attach de temas generales + exclusivos de esa empresa).
- **RN-05:** No se puede **eliminar** (trash) un tema que tenga voice_employees asociados. Mensaje: "No puede borrar un tema con registros asignados."
- **RN-06:** Usuario **con company:** No puede eliminar un tema **general** (exclusive_for_company == null). Solo puede eliminar temas exclusivos de su empresa. No puede ver/editar/eliminar/ocultar temas exclusivos de **otra** empresa (redirect con "no se encuentra registrado").
- **RN-07:** **Ocultar (hide):** Desvincula el tema de la empresa actual (detach en company_voice_employee_subject para esa company_id). No borra el tema ni la segmentación por usuarios; la empresa deja de ver ese tema en su listado.
- **RN-08:** Para entrar al listado de temas de una empresa, la empresa debe tener al menos un tema asignado (company->voice_employee_subjects()->exists()); si no, redirect con error "La empresa que intenta consultar no tiene temas de voz del colaborador asignados".

---

## FLUJO PRINCIPAL

### Listado de empresas (getIndexCompanies / getListCompanies)

1. getIndexCompanies: vista list_company (DataTable que consume getListCompanies por AJAX).
2. getListCompanies: empresas a listar = si el usuario tiene company → solo su empresa (Company::where('id', $user->company->id)->has('voice_employee_subjects')->get()); si no → Company::has('voice_employee_subjects')->get(). Para cada empresa: id, general_name, botón "Ver temas asignados" → getIndex(company_id).

### Listado de temas por empresa (getIndex / getList)

1. getIndex: valida que la empresa exista y que tenga temas (voice_employee_subjects()->exists()); si no, redirect a companies con error. Vista list con company_id (DataTable vía getList).
2. getList: $company->voice_employee_subjects. Para cada tema: id, name, description, exclusive_for_company (GENERAL o nombre empresa; solo si admin), acciones. **Acciones según rol y exclusividad:** Admin: siempre Editar, Ver, Eliminar, Ocultar. No admin: si tema exclusive_for_company == company->id → Editar, Ver, Eliminar, Ocultar; si no → solo Ver y Ocultar.

### Crear (getCreate / create)

1. getCreate: companies = Company::get() (para el select de empresa si admin). Vista create con company_id, nombre, descripción, y si admin: toggle "Asignar a empresa" (is_asignable) y select exclusive_for_company (habilitado solo si is_asignable marcado). Si no admin: hidden company_id y exclusive_for_company = company_id.
2. create: name required. Se crea VoiceEmployeeSubject (name, description, exclusive_for_company si viene). Opcionalmente se asignan receivers (segmentación por usuarios) con attach. Luego segmentación por empresas: si exclusive_for_company == null, attach a todas las Company que tienen voice_employee_subjects; si no, reasignar temas de la empresa exclusiva (detach de la empresa, attach temas generales + exclusivos). Log y redirect al listado de temas de la empresa (company_id del request).

### Ver (getView)

1. Buscar tema por id; si no existe o (usuario tiene company y tema es exclusivo de otra empresa) → redirect con error. Vista view con tema y company_id (nombre, descripción, solo lectura).

### Editar (getEdit / update desde formulario de tema)

1. getEdit: tema por id; si no existe o usuario con company y tema exclusivo de otra empresa → redirect. companies = Company::get(). Vista edit con name, description, exclusive_for_company (solo admin: toggle is_asignable y select), hidden voice_employee_subject_id y company_id.
2. update (cuando no viene view=edit_segmentation ni receivers): actualiza name, description, exclusive_for_company (si no viene, null). Ejecuta la lógica de segmentación por empresas (mismo bloque que en create: attach a todas o reasignar solo a la empresa exclusiva). Log "ha actualizado el tema" y redirect a admin_voice_employee_subjects_edit.

### Eliminar (trash)

1. Validar tema existe; si usuario tiene company y tema es general → "No cuenta con los permisos para eliminar este tema"; si tema exclusivo de otra empresa → "no se encuentra registrado". Si voice_employees()->exists() → "No puede borrar un tema con registros asignados." Detach del tema en todas las companies (company_voice_employee_subjects). VoiceEmployeeSubject::where("id",...)->delete() (soft delete). Log y redirect al listado de temas de la empresa.

### Ocultar (hide)

1. Validar tema existe; si usuario tiene company y tema exclusivo de otra empresa → redirect con error. Detach del tema solo para la company_id de la ruta (company->voice_employee_subjects()->detach($voice_employee_subject->id)). Log "ha ocultado el tema" y redirect al listado de temas de la empresa.

---

## VALIDACIONES

- **name:** required (mensaje: "El nombre es requerido").
- **description:** opcional; se guarda string vacío si no viene.
- **exclusive_for_company:** opcional; si no se envía (ej. toggle "Asignar a empresa" desmarcado), se guarda null.

---

## VISTAS

- **admin.voice_employee_subjects.list_company:** Título "Empresas con temas de voz del colaborador". DataTable (get_admin_voice_employee_subjects_companies). Columnas: N°, Nombre, "Ver temas asignados".
- **admin.voice_employee_subjects.list:** Título "Temas", subtítulo "Administra los temas de voz del colaborador". Breadcrumb: Empresas con temas → Temas. Botón Crear (admin_voice_employee_subjects_create_index con company_id). DataTable por company_id. Columnas: N°, Nombre, Descripción, Exclusividad (solo admin), acciones. Modales Eliminar y Ocultar.
- **admin.voice_employee_subjects.create:** Nombre (required), Descripción. Si admin: toggle "Asignar a empresa", select Empresa (exclusive_for_company, disabled si toggle desmarcado). Hidden company_id. POST a admin_voice_employee_subjects_create.
- **admin.voice_employee_subjects.edit:** Igual que create; voice_employee_subject_id hidden; valores iniciales desde $voice_employee_subject. POST a admin_voice_employee_subjects_update. Si admin, is_asignable marcado si exclusive_for_company != null.
- **admin.voice_employee_subjects.view:** Muestra id, nombre, descripción. Solo lectura.

---

## USO EN OTROS MÓDULOS

- **Voz del colaborador (VoiceEmployeesController):** Filtra comentarios de voz por los temas de la empresa del usuario (company->voice_employee_subjects) y, si tiene, por los temas asignados al usuario (segmentación).
- **Segmentación Voz del Colaborador:** Lista los mismos temas (voice_employee_subjects) y permite editar destinatarios (user_voice_employee_subject). Comparte el endpoint update con este recurso.

---

## MODELOS INVOLUCRADOS

- **VoiceEmployeeSubject:** SoftDeletes, fillable name, description, exclusive_for_company. Relaciones: voice_employees (hasMany), users (belongsToMany), companies (belongsToMany).
- **Company:** voice_employee_subjects() belongsToMany VoiceEmployeeSubject (pivot company_voice_employee_subject).
- **VoiceEmployee:** voice_employee_subject_id FK; usado para impedir eliminar tema con comentarios.

---

## MIGRACIONES

- **2019_12_11_140140_create_voice_employee_subjects_table:** Crea voice_employee_subjects (id, name, timestamps).
- **2021_10_05_113441_update_voice_employee_subjects_table:** Añade description (text nullable).
- **2021_10_29_092958_update_voice_employee_subjects_2_table:** Añade softDeletes.
- **2023_11_30_134139_update_voice_employee_subjects_3_table:** Añade exclusive_for_company (string nullable).
- **2023_11_30_140924_create_company_voice_employee_subject:** Crea pivot company_voice_employee_subject (company_id, voice_employee_subject_id, FKs cascade, timestamps).

---

## PERMISOS (Legacy)

- **view_voice_employee_subjects:** listado empresas con temas, listado temas por empresa, ver tema, ocultar.
- **create_voice_employee_subjects:** getCreate, create.
- **edit_voice_employee_subjects:** getEdit, update (junto con segment_voice_employee en la ruta update).
- **trash_voice_employee_subjects:** trash, hide.

En el sidebar el ítem "Temas (Voz del Colaborador)" se muestra si el usuario tiene al menos uno de: edit_voice_employee_subjects, view_voice_employee_subjects, trash_voice_employee_subjects, create_voice_employee_subjects.

---

## CASOS BORDE

- **Empresa sin temas:** No se puede acceder al listado de temas de esa empresa (getIndex exige voice_employee_subjects()->exists()). Solo aparecen en el listado de empresas las que ya tienen al menos un tema.
- **Usuario no admin creando tema:** exclusive_for_company se fuerza a company_id; el tema queda exclusivo de su empresa.
- **Eliminar tema general siendo usuario con company:** No permitido; mensaje "No cuenta con los permisos para eliminar este tema".
- **Ocultar:** Solo desvincula de una empresa; el tema sigue existiendo y asignado a otras empresas y a usuarios (segmentación).

---

## BUGS E INCONSISTENCIAS

1. **exclusive_for_company como string:** La migración usa string nullable; en código se compara con company->id y se usa en Company::where('id', ...). Coerción numérica puede funcionar; tipo consistente sería unsignedBigInteger.
2. **Collection::except() en create/update:** Se usa VoiceEmployeeSubject::...->get()->except($voice_employee_subject->id). except() elimina por **clave** de la colección (0,1,2...), no por id del modelo; podría no excluir el tema como se pretende. Debería usarse where('id','!=',...) o filter/whereNotIn por id.
3. **Update exige segment_voice_employee:** Para guardar solo nombre/descripción/exclusividad desde el formulario de edición de tema, el usuario debe tener **ambos** edit_voice_employee_subjects y segment_voice_employee; si no tiene segment_voice_employee, el POST a update fallará por permisos.

---

## DEUDA TÉCNICA

- **Dos pantallas para el mismo recurso:** "Temas (Voz del Colaborador)" (este recurso) y "Segmentación Voz del Colaborador" comparten modelo y update; la primera enfocada en CRUD y asignación por empresa, la segunda en asignación por usuario.
- **Lógica de asignación a empresas duplicada:** El bloque que según exclusive_for_company hace attach a todas las empresas o reasigna solo a una está en create() y en update(); convendría extraerlo a un servicio.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
