# Ficha técnica: Módulo Gestión de Carruseles (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Gestión de Carruseles (GestionCarruselesResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite **gestionar el carrusel de imágenes por empresa**: listar empresas (solo para admin; el no-admin tiene enlace directo a editar el carrusel de su empresa en el sidebar) y, por cada empresa, **editar las imágenes del carrusel** (agregar, quitar, conservar). Las imágenes se almacenan en **disco** en la ruta `assets/companies/carousel/{company_id}/` (disco `uploads`). No existe tabla ni modelo de carrusel; todo es basado en archivos. Máximo **5 imágenes** por empresa. Controlador: `CarouselManagementController`. Rutas bajo `admin/carousel_management/*`; permisos: `view_carousel_management`, `edit_carousel_management`.

---

## ENTIDADES

### Sin tabla de carrusel

- No hay migración ni modelo para carrusel. Los datos son **archivos** en el filesystem.
- **Ruta lógica:** `assets/companies/carousel/{company_id}/` dentro del disco configurado como `uploads` (Storage::disk('uploads')).
- **Contenido:** Archivos de imagen (en la práctica el controlador no valida tipo en servidor; la vista acepta jpg, jpeg, png, bmp por JavaScript). Cada empresa tiene su propia carpeta.

### Tabla: `companies` (contexto)

- getIndex/getList listan **todas** las empresas (Company::with('products:name')->...). No se filtra por empresa del usuario. La restricción de “solo mi empresa” para no-admin se hace vía **sidebar**: enlace directo a `admin_carousel_management_edit` con `company_id` del usuario.
- getEdit recibe `company_id` y carga la empresa; no hay comprobación de que el usuario tenga derecho a editar esa empresa (⚠️ ver CASOS BORDE).

### Otras tablas de contexto

- **Industry, SubIndustry:** para columnas industria/subindustria en el listado de empresas.
- **Log:** se crea un registro al modificar el carrusel (usuario, empresa, acción).

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/carousel_management | GET | CarouselManagementController@getIndex | view_carousel_management |
| admin/carousel_management/get | POST | getList | view_carousel_management |
| admin/carousel_management/edit/{company_id} | GET | getEdit | edit_carousel_management |
| admin/carousel_management/update | POST | update | edit_carousel_management |

Middleware: `logged`, `2fa` (en get y getEdit; update solo `logged`), `Permissions:{"permissions_and":["..."]}` según la ruta.

**Sidebar:** Si el usuario tiene `view_carousel_management` o `edit_carousel_management`: si es **admin** → enlace a `admin_carousel_management` (listado de empresas); si **no** es admin → enlace directo a `admin_carousel_management_edit` con `company_id` de su empresa. No hay redirección en getIndex ni en getEdit para impedir que un no-admin acceda al listado o a otra empresa si conoce la URL.

---

## REGLAS DE NEGOCIO

- **RN-01:** Máximo **5 imágenes** por carrusel (por empresa). Si la suma de “imágenes ya subidas que se conservan” + “nuevas a subir” supera 5, se devuelve error: "Solo estan permitidas 5 imagenes por carrusel".
- **RN-02:** Las imágenes existentes se identifican por **ruta completa** en el disco `uploads`: `assets/companies/carousel/{company_id}/{nombre_archivo}`. Los archivos que no se envían en `uploaded_files` se **eliminan** del disco (array_diff entre archivos existentes y los que se mantienen).
- **RN-03:** Al subir un archivo nuevo, se usa el **nombre original** del cliente (`getClientOriginalName()`). Si ya existe un archivo con ese nombre en la carpeta de la empresa, **no se sobrescribe** (se comprueba con `file_exists`); el archivo nuevo no se guarda en ese caso pero tampoco hay mensaje de error explícito (⚠️ posible pérdida silenciosa de la nueva imagen).
- **RN-04:** Tras actualizar correctamente: si el usuario **no es admin** y tiene `company_id`, redirect a `admin_carousel_management_edit` con su `company_id` y mensaje de éxito; si es admin (o no tiene company), redirect a `admin_carousel_management` (listado) con mensaje de éxito.
- **RN-05:** No hay validación en servidor del tipo MIME ni del tamaño de los archivos subidos; la restricción de formatos (jpg, jpeg, png, bmp) y la UX se hacen en JavaScript en la vista.

---

## FLUJO PRINCIPAL

### Listado de empresas (getIndex / getList)

1. **getIndex:** `Company::with('products:name')->orderBy('id','desc')->paginate(10)`. No se filtra por empresa del usuario. Vista `admin.carousel_management.list` con include de la tabla. Parámetros de request: take (default 10), orderBy, sortDir, page.
2. **getList (POST):** Misma fuente de empresas con búsqueda opcional (id, general_name, contact_email, industry name, sub_industry name), ordenación por columna (id, general_name, industry, sub_industry, contact_email, actions) y paginación. Devuelve vista `admin.carousel_management.table`: tabla con columnas N°, Nombre (con foto), Industria, SubIndustria, Correo, Acciones (botón Editar → admin_carousel_management_edit con company_id).

### Editar carrusel (getEdit)

1. Buscar Company por `company_id`; si no existe → redirect back con mensaje "La empresa ... no se encuentra registrada".
2. Listar archivos del carrusel: `Storage::disk('uploads')->files('assets/companies/carousel/'.$company->id)`.
3. Para cada archivo se calcula tamaño legible y URL pública: `asset('assets/companies/carousel/'.$company->id.'/'.basename($file))`.
4. Vista `admin.carousel_management.edit` con company, user, files, documents_data. Formulario con hidden company_id, lista de imágenes actuales (cada una con hidden `uploaded_files[]` = basename) y posibilidad de añadir nuevos archivos (input file `documents[]`). Al eliminar una imagen desde la UI se quita el file-container (y por tanto su `uploaded_files[]`). Submit a `admin_carousel_management_update`.

### Actualizar carrusel (update)

1. `existing_files` = lista de rutas completas en `assets/companies/carousel/{company_id}`.
2. `uploaded_files` = request->uploaded_files (solo basenames); se les antepone la ruta `assets/companies/carousel/{company_id}/`.
3. Nuevos archivos: request->documents (array de archivos). Por cada uno: nombre original; si no existe en la carpeta, se guarda con `storeAs(..., $file_name, 'uploads')` y se añade la ruta a `uploaded_files`.
4. `total_files` = count(uploaded_files) + count(documents). Si total_files > 5 → redirect back con error "Solo estan permitidas 5 imagenes por carrusel".
5. `result` = archivos que estaban en disco pero no están en uploaded_files → se borran con `Storage::disk('uploads')->delete($result)`.
6. Log: "El usuario ... ha modificado masivamente el carrusel de imagenes para la empresa: ...". Asociación del log al usuario y a la empresa del usuario si tiene company_id.
7. Redirect según RN-04 (no-admin a su edit, admin a listado).

---

## VALIDACIONES

- **Límite 5 imágenes:** comprobado en update; mensaje "Solo estan permitidas 5 imagenes por carrusel".
- **Empresa existente:** en getEdit se comprueba que la empresa exista; si no, redirect con mensaje.
- No hay validación en servidor de tipo de archivo (imagen), tamaño máximo ni nombre seguro (posible sobrescritura o caracteres problemáticos si el cliente envía otro nombre). La vista usa `accept="image/png,image/jpeg,image/jpg"` y en JS se permiten jpg, jpeg, png, bmp.

---

## VISTAS

- **admin.carousel_management.list:** Título "Gestion de carrusel de imagenes", subtítulo "Listado de empresas para gestion de carrusel de imagenes." Incluye table; DataTable sin paginación en cliente, recarga vía POST get_admin_carousel_management con paginación, búsqueda y orden en servidor.
- **admin.carousel_management.table:** Tabla empresas: N°, Nombre (foto, general_name), Industria, SubIndustria, Correo, Acciones (Editar). Select "Mostrar 10/25/50/100 empresas", buscador, paginación Laravel.
- **admin.carousel_management.edit:** Título "Editar Carrusel", subtítulo "Agrega, edita o elimina imagenes del carrusel." Breadcrumb: PACO → Gestion de carrusel de imagenes → Editar Carrusel. Formulario multipart: company_id, lista de archivos actuales (cada uno con hidden uploaded_files[] = basename, enlace a URL de la imagen, tamaño, botón eliminar que quita el div del DOM). Botón de adjuntar (label que dispara input file documents[]); en JS se aceptan jpg, jpeg, png, bmp y se muestra vista previa. Modal para previsualizar imagen. Botones Guardar y Cancelar (vuelve a listado). Máximo 5 imágenes aplicado en servidor; en cliente no se deshabilita el envío si se pasan de 5 hasta que el servidor responde con error.

---

## USO EN OTROS MÓDULOS

- **Panel cliente / front:** Las imágenes del carrusel se sirven desde `asset('assets/companies/carousel/'.$company->id.'/'.basename($file))`; se asume que en el front se consume esta ruta o una API que liste los archivos de la carpeta para la empresa del usuario. No se ha verificado en este análisis dónde se muestra el carrusel al usuario final (app, web cliente, etc.).
- **Company:** Solo como contexto (id, general_name, photo, industry, sub_industry, contact_email) para el listado y para construir la ruta de archivos.

---

## MODELOS INVOLUCRADOS

- **Company:** usado para listado y para obtener company_id en la ruta de almacenamiento. No hay relación explícita “carousel” en el modelo; la relación es implícita por convención de ruta.
- **Log:** creación de registro de auditoría al modificar el carrusel.
- **User:** getCurrent(), hasRoles('admin'), company_id para redirect y log.

---

## MIGRACIONES

- No existe migración para carrusel. El almacenamiento es solo en filesystem (disco `uploads`, ruta `assets/companies/carousel/{company_id}/`). La existencia de la carpeta depende de que se haya subido al menos un archivo para esa empresa.

---

## PERMISOS LEGACY

- **view_carousel_management:** Acceso al listado de empresas (getIndex, getList) para elegir empresa y editar su carrusel.
- **edit_carousel_management:** Acceso a getEdit(company_id) y a update (subir/eliminar imágenes).

---

## CASOS BORDE

- **getEdit sin restricción por empresa:** getEdit($company_id) no comprueba que el usuario no-admin solo pueda editar su propia empresa. Un usuario con edit_carousel_management podría abrir `admin/carousel_management/edit/{otra_empresa_id}` y modificar el carrusel de otra empresa. La restricción es solo de menú (sidebar lleva a su company_id).
- **update: mismo riesgo:** update usa request->company_id sin validar que coincida con la empresa del usuario cuando no es admin. Un no-admin podría enviar company_id de otra empresa y modificar ese carrusel.
- **Nombre de archivo duplicado:** Si se sube un archivo nuevo con el mismo nombre que uno existente, no se sobrescribe y no se añade a uploaded_files; el total_files podría contar el nuevo como “nuevo” pero no se guarda. Comportamiento confuso para el usuario.
- **Carpeta inexistente:** Si la empresa nunca ha tenido imágenes, `Storage::disk('uploads')->files('assets/companies/carousel/'.$company_id)` podría devolver array vacío o depender del comportamiento del disco (directorio inexistente). Al subir la primera imagen, storeAs crea la ruta. No se ha comprobado si files() lanza o devuelve vacío si la carpeta no existe.
- **catch (Exception $e):** El controlador usa `catch (Exception $e)` pero no hay `use Exception` en la cabecera; en PHP podría resolverse con \Exception. Si falta, error de clase no encontrada en tiempo de ejecución.

---

## AMBIGÜEDADES

- Dónde y cómo se muestran las imágenes del carrusel al usuario final (portal cliente, app móvil, etc.) no está documentado en este controlador; solo la gestión en admin.
- Si el disco `uploads` está en public_path o en storage y cómo se exponen las URLs `asset('assets/companies/carousel/...')` depende de la configuración del proyecto (symlink, rutas públicas, etc.).

---

## DEUDA TÉCNICA

- Sin modelo ni tabla: el carrusel no es trazable vía BD (orden de imágenes, metadatos, historial). Cualquier cambio implica solo agregar/quitar archivos en disco.
- Validación de tipo/tamaño de imagen solo en cliente; un request manipulado podría subir otros tipos o archivos grandes.
- Falta de autorización explícita en getEdit y update para restringir por company_id cuando el usuario no es admin.
- Eliminación de archivos sin soft delete ni respaldo; borrado definitivo en disco.

---

## DIFERENCIAS CON TECBEN-CORE

Por definir (no verificado en este análisis). Si en tecben-core existe gestión de carrusel por empresa, conviene comparar: almacenamiento (disco vs BD/blob), límite de imágenes, validaciones en servidor y comprobación de que el usuario solo pueda editar el carrusel de su empresa.
