# Ficha técnica: Módulo Segmentación de Productos (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Segmentación de Productos (SegmentacionProductosResource / Gestión de productos)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite **gestionar la segmentación de productos por empresa**: listar empresas que tienen productos asignados, listar los productos de cada empresa y, por cada producto de una empresa, **definir qué empleados (high_employees) deben tener el producto ACTIVO o INACTIVO** según filtros (ubicación, área, departamento, puesto, región, género, mes de nacimiento, edad, antigüedad). Se puede guardar esos criterios como **filtros de producto** (tabla `product_filters`) para reutilizarlos. Los empleados que cumplen los filtros se marcan en la pivot `high_employee_product` como ACTIVO (reason vacío, change_type AUTOMATIC); los que no cumplen se marcan INACTIVO con la razón indicada. Solo se consideran empleados que ya tienen el producto con change_type AUTOMATIC y reason distinto de "INCUMPLIMIENTO DE PAGO". Controlador: `ProductManagementController`. Rutas bajo `admin/product_management/*`; permisos: `view_product_management`, `edit_product_management`.

---

## ENTIDADES

### Tabla: `companies` (contexto)

- Se listan empresas con `Company::has('products')`. Relación: `products()` belongsToMany Product (pivot company_product), `product_filters()` hasMany ProductFilter.

### Tabla: `products` (contexto)

- Catálogo global. Relación: `product_filters()` hasMany ProductFilter; pivot `high_employee_product` con status, reason, change_type.

### Tabla: `product_filters`

- **PK:** id (bigint unsigned).
- **FK:** company_id → companies (cascade), product_id → products (cascade), area_id, department_id, location_id, position_id (nullable; migración create); **region_id** añadido en migración `2025_02_21_135905_update_product_filters_table` (nullable).
- **Campos:** genders (longText, cast array), months (longText, cast array), age_from, age_till, month_filter_from, month_filter_till (integer nullable), reason (string). timestamps.
- **Uso:** Criterios de segmentación por empresa+producto: combinaciones de ubicación/área/departamento/puesto/región y/o género/meses nacimiento/edad/antigüedad; reason es la razón de desactivación para quienes no cumplen. Un mismo company+product puede tener varias filas (combinaciones de criterios). Se gestionan desde la pantalla "Editar producto" de Gestión de productos.
- **Relaciones (modelo ProductFilter):** company(), department(), area(), position(), location(), product(), region(). **Nota:** El modelo no incluye `region_id` en `$fillable`; el controlador asigna `$product_filter->region_id`; si hay asignación masiva podría no persistirse sin añadirlo a fillable (⚠️ ver CASOS BORDE).

### Tabla pivot: `high_employee_product`

- **FK:** high_employee_id, product_id. **Campos:** status (ACTIVO/INACTIVO), reason, change_type (AUTOMATIC u otros).
- En este módulo se actualizan solo los empleados que tienen el producto con change_type = 'AUTOMATIC' y reason <> 'INCUMPLIMIENTO DE PAGO'. Quienes cumplen los filtros → updateExistingPivot(status ACTIVO, reason '', change_type AUTOMATIC); quienes no → INACTIVO con reason del formulario.

### Otras tablas de contexto

- **high_employees:** filtros por location_id, area_id, department_id, position_id, region_id, gender, birthdate (mes, edad), admission_date (meses de antigüedad).
- **areas, departments, locations, positions, regions:** pertenecen a la empresa; se cargan con `has('high_employees')` para el formulario de edición.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/product_management | GET | ProductManagementController@getIndex | view_product_management |
| admin/product_management/get | POST | getList | view_product_management |
| admin/product_management/edit/{company_id}/{product_id} | GET | getEdit | edit_product_management |
| admin/product_management/products/{company_id} | GET | getProducts | edit_product_management |
| admin/product_management/receivers/query | POST | queryReceivers | edit_product_management |
| admin/product_management/update_products | POST | updateProducts | edit_product_management |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}` según la ruta.

**Sidebar:** Si el usuario tiene `view_product_management` o `edit_product_management`: si es **admin** → enlace a `admin_product_management` (listado de empresas); si **no** es admin → enlace directo a `admin_product_management_products` con `company_id` de su empresa. Un no-admin que entre por URL a `admin/product_management` puede ver el listado de todas las empresas (no hay redirección en getIndex).

---

## REGLAS DE NEGOCIO

- **RN-01:** En **updateProducts** son obligatorios: `company_id`, `product_id`, `reason`. La razón se usa para marcar como INACTIVO a los empleados que no cumplen los filtros.
- **RN-02:** Solo se aplica la segmentación a empleados que **ya tienen** el producto con `change_type = 'AUTOMATIC'` y `reason <> 'INCUMPLIMIENTO DE PAGO'`. El resto no se toca.
- **RN-03:** Empleados que **cumplen** los filtros del formulario → pivot actualizada a status ACTIVO, reason vacío, change_type AUTOMATIC. Empleados que **no cumplen** → status INACTIVO, reason = valor del campo "reason" del request.
- **RN-04:** Si se marca "Guardar filtros" (`save_filters`), se borran todos los `product_filters` existentes para ese company+product y se crean nuevos según las combinaciones de criterios elegidos (ubicación, área, departamento, puesto, región, y opcionalmente género, meses nacimiento, edad, antigüedad). Si no se eligen criterios de ubicación/área/departamento/puesto/región pero sí género/meses/edad/antigüedad, se crea una sola fila ProductFilter sin FKs de estructura.
- **RN-05:** Los filtros se interpretan como **intersección**: el empleado debe cumplir todos los criterios seleccionados (región, ubicación, área, departamento, puesto, género, mes nacimiento, rango edad, rango antigüedad) para quedar ACTIVO.
- **RN-06:** Al guardar filtros, las combinaciones se generan con `crossJoin` de las listas seleccionadas (regiones, áreas, departamentos, puestos, ubicaciones), generando muchas filas ProductFilter; cada fila tiene una combinación concreta de region_id, area_id, department_id, position_id, location_id (según keys). Lógica repetitiva y extensa en el controlador (🔧 DEUDA TÉCNICA).

---

## FLUJO PRINCIPAL

### Listado de empresas (getIndex / getList)

1. **getIndex:** `Company::has('products')->with('products:name')->orderBy('id','desc')->paginate(10)`. Vista `admin.product_management.list` con include de `table`. Request: take (default 10), orderBy, sortDir, page.
2. **getList (POST):** Misma query con búsqueda opcional (id, general_name, contact_email, industry name, sub_industry name, products name), ordenación por columna (id, general_name, industry, sub_industry, contact_email, products, actions) y paginación. Devuelve vista `admin.product_management.table` (tabla HTML para rellenar DataTable en el listado). Columnas: N°, Nombre, Industria, SubIndustria, Correo, Productos (nombres), Acciones (enlace "Editar" → admin_product_management_products con company_id).

### Listado de productos de una empresa (getProducts)

1. Buscar Company por company_id; si no existe → redirect back con mensaje "La empresa ... no se encuentra registrada".
2. Vista `admin.product_management.product_list` con `$company`. Tabla con productos de la empresa (`$company->products`): N°, Nombre, botón Editar → `admin_product_management_edit(company_id, product_id)`.

### Editar segmentación de un producto (getEdit)

1. Buscar Company y Product por ids; si alguno no existe → redirect back con mensaje.
2. `$filter = $company->product_filters()->where('product_id', $product->id)->first()` (solo se usa el primero para pre-seleccionar en vista; hay varios posibles).
3. Cargar áreas, puestos, departamentos, ubicaciones, regiones de la empresa que tengan high_employees; ordenar por nombre.
4. Vista `admin.product_management.edit` con company, product, positions, departments, areas, locations, regions, user, filter. Formulario POST a `admin_product_management_update` con filtros (location, area, department, position, region, genders, months, age_receiver_from/till, month_receiver_filter_from/till), reason, save_filters (opcional).

### Actualizar segmentación (updateProducts)

1. Validar `company_id`, `product_id`, `reason` requeridos.
2. Obtener empleados "receivers" de la empresa que tienen el producto con change_type AUTOMATIC y reason <> INCUMPLIMIENTO DE PAGO; aplicar los mismos filtros del request (locations, areas, departments, positions, regions, genders, months, age_receiver_from/till, month_receiver_filter_from/till). Lista de ids = quienes cumplen.
3. `distinct_receivers` = empleados que tienen el producto (mismas condiciones AUTOMATIC/razón) pero no están en la lista de receivers.
4. Para cada receiver en la lista → `$product->high_employees()->updateExistingPivot($receiver, ['status'=>'ACTIVO','reason'=>'','change_type'=>'AUTOMATIC'])`.
5. Para cada distinct_receiver → `updateExistingPivot(..., ['status'=>'INACTIVO','reason'=>$reason])`.
6. Si `save_filters`: borrar product_filters existentes para ese company+product; construir combinaciones (crossJoin) y crear registros ProductFilter (company_id, product_id, reason, location_id, area_id, department_id, position_id, region_id, genders, months, age_from, age_till, month_filter_from, month_filter_till); asociar a company y product con `$company->product_filters()->save($product_filter)` y `$product->product_filters()->save($product_filter)`.
7. Crear Log (acción "ha modificado masivamente el producto: ..."). Redirect a `admin_product_management_products` con company_id y mensaje "... modificados exitosamente".

### Consulta de destinatarios (queryReceivers) – AJAX

1. Request: company_id, product_id, take, search, orderBy, sortDir, page, action, name (filtro activo), initial_load.
2. Receivers = high_employees de la empresa (o todos con el producto si no company_id) que tienen el producto (whereHas products product_id). Se aplican filtros opcionales (regions, locations, areas, departments, positions, genders, months, age_from/till, month_from/till). Búsqueda por id, nombre completo, región, área, ubicación, departamento, puesto. Ordenación por columna. Paginación.
3. En la primera carga (page==1, action==getTable, !initialLoad) se rellenan selectores (areas, positions, locations, departments, regions) distintos de los receivers para los dropdowns de la vista.
4. Respuesta JSON: `view` (HTML de la tabla high_employees), `filters`, `selectors`. Vista parcial: `admin.product_management.high_employees.table`.

---

## VALIDACIONES

- **updateProducts:** company_id required, product_id required, reason required. Mensajes: "El id de la empresa es requerido", "El id del producto es requerido", "La razón de desactivacion es requerida".
- No hay validación de que la empresa tenga asignado ese producto (company->products) antes de aplicar la segmentación; si no está asignado, la query de receivers podría estar vacía pero no se comprueba explícitamente.

---

## VISTAS

- **admin.product_management.list:** Título "Gestion de productos", subtítulo "Listado de empresas para gestion de productos." Incluye table; DataTable (dataTables-companies) que recarga vía POST get_admin_product_management con paginación, búsqueda, orden.
- **admin.product_management.table:** Tabla empresas: N°, Nombre (con foto), Industria, SubIndustria, Correo, Productos (lista de nombres), Acciones (Editar → productos de la empresa). Select "Mostrar 10/25/50/100 empresas", buscador, paginación Laravel.
- **admin.product_management.product_list:** Título "Gestion de productos", subtítulo "Listado de productos de {{ company->general_name }}." Tabla productos: N°, Nombre, Editar (enlace a getEdit). DataTable cliente.
- **admin.product_management.edit:** Formulario con pestaña "information" (nombre del producto). Filtros: Ubicación, Departamento, Área, Puesto, Región (multiselect); Género, Mes nacimiento; Edad desde/hasta; Antigüedad (meses) desde/hasta. Campo "Razón de desactivación" (reason). Opción "Guardar filtros". Tabla de destinatarios (high_employees) cargada por AJAX (queryReceivers). Breadcrumb: PACO → Gestion de productos (si admin) → Listado productos de la empresa → Editar producto. Botones Guardar y Cancelar (vuelve a product_list).
- **admin.product_management.high_employees.table:** Parcial para la tabla de empleados/destinatarios en la edición (usada por queryReceivers).

---

## USO EN OTROS MÓDULOS

- **Product (ProductoResource):** El catálogo de productos y la pivot company_product se gestionan en otros flujos (Companies, CRUD productos). Este módulo solo usa Product y Company para listar y para aplicar segmentación sobre high_employee_product y product_filters.
- **Alta/Baja colaboradores:** Asignan o desasignan productos a empleados (high_employee_product); la segmentación aquí recalcula ACTIVO/INACTIVO según filtros guardados o elegidos en el formulario.
- **FICHA_TECNICA_SEGMENTACION_VOZ_COLABORADOR_LEGACY:** El patrón de "queryReceivers" y destinatarios por filtros es similar al de segmentación de voz (temas por usuario).

---

## MODELOS INVOLUCRADOS

- **Company:** product_filters() hasMany ProductFilter; products() belongsToMany Product.
- **Product:** product_filters() hasMany ProductFilter; high_employees() belongsToMany con pivot status, reason, change_type.
- **ProductFilter:** company_id, product_id, location_id, area_id, department_id, position_id, region_id (en BD; no en fillable), genders (array), months (array), age_from, age_till, month_filter_from, month_filter_till, reason. Relaciones: company(), product(), location(), area(), department(), position(), region().

---

## MIGRACIONES

- **create_product_filters_table:** product_filters con area_id, department_id, location_id, position_id, company_id, product_id, genders, months, age_from, age_till, month_filter_from, month_filter_till, reason; FKs cascade. Sin region_id.
- **update_product_filters_table (2025_02_21_135905):** Añade region_id nullable a product_filters con FK a regions.

---

## PERMISOS LEGACY

- **view_product_management:** Listado de empresas con productos; listado de productos de una empresa (getList, getIndex, getProducts).
- **edit_product_management:** Entrar a editar segmentación (getEdit), actualizar (updateProducts), consultar destinatarios (queryReceivers).

---

## CASOS BORDE

- **Empresa sin productos:** getIndex/getList usan `Company::has('products')`, así que no aparecen empresas sin productos. Si se accede por URL a getProducts(company_id) de una empresa sin productos, la tabla de productos estará vacía; si se accede a getEdit(company_id, product_id) con un product_id que no está en la empresa, la lógica no comprueba que el producto esté en company->products (podría haber incoherencia).
- **ProductFilter fillable:** region_id no está en $fillable de ProductFilter; el controlador asigna directamente $product_filter->region_id. En Laravel, la asignación directa sí se persiste en save() aunque no esté en fillable; solo affectaría si se usara create($attributes) o fill($attributes) con un array que incluya region_id. Por tanto no es necesariamente un bug, pero es inconsistente con el resto de campos estructurales.
- **Varios ProductFilter por company+product:** Al cargar getEdit se usa solo el primero (`->first()`) para marcar opciones seleccionadas en la vista; si hay varias filas con distintas combinaciones, la pre-selección no refleja todas.
- **Razón "INCUMPLIMIENTO DE PAGO":** Los empleados con este reason en la pivot no se consideran en la segmentación (ni para ACTIVO ni para INACTIVO); quedan fuera del alcance del update masivo.

---

## AMBIGÜEDADES

- No está explícito en el código si un producto debe estar previamente asignado a la empresa (company_product) para que la segmentación tenga efecto; se asume que la pantalla solo se usa cuando la empresa ya tiene ese producto.
- El uso exacto de los ProductFilter guardados fuera de esta pantalla (p. ej. en altas de colaboradores o en jobs) no se ha verificado en este análisis; la ficha de Alta colaboradores podría definir si se usan estos filtros para asignar automáticamente productos a nuevos empleados.

---

## DEUDA TÉCNICA

- Lógica de construcción de combinaciones de filtros (crossJoin y asignación a keys) muy larga y repetitiva en updateProducts; podría extraerse a un servicio o helper.
- Log::info($keys) y Log::info($product_filter) dejados en el código de producción.
- queryReceivers devuelve HTML en JSON (view renderizada); patrón legacy típico para tablas dinámicas.

---

## DIFERENCIAS CON TECBEN-CORE

Por definir (no verificado en este análisis). Si en tecben-core existe un recurso equivalente a "Segmentación de productos" o "Gestión de productos por empresa", conviene comparar: permisos (view_product_management / edit_product_management), consideración de change_type y reason en high_employee_product, y persistencia de product_filters con region_id y combinaciones múltiples.
