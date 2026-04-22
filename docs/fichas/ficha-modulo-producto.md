# Ficha técnica: Módulo Productos (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Productos (ProductoResource / products)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite listar, crear, editar, ver y eliminar **productos** del catálogo global. Cada producto tiene nombre y descripción. Los productos se asocian a empresas (company_product: precios, enable_from, variación) y a empleados (high_employee_product: estado, razón, tipo de cambio); además pueden tener filtros por empresa/ubicación/área/departamento/puesto (product_filters). No se puede eliminar un producto que tenga empresas, empleados o filtros asignados. Eliminación por soft delete. La asignación a empresas y la configuración de precios/filtros se gestiona en otros módulos (Companies, Product Management). Controlador: `ProductsController`.

---

## ENTIDADES

### Tabla principal: `products`

- **PK:** `id` (bigint unsigned).
- **Campos:** `name` (string), `description` (longText; añadido en update_products_table). `timestamps`, `deleted_at` (soft deletes, update_products_2_table).
- **Relaciones (modelo Product):** companies() belongsToMany con pivot (base_price, unit_price, enable_from, variation_margin); high_employees() belongsToMany con pivot (status, reason, change_type); product_filters() hasMany ProductFilter.

### Tabla pivot: `company_product`

- **FK:** company_id → companies (cascade), product_id → products (cascade). **Campos:** unit_price, base_price, enable_from (y en modelo withPivot variation_margin si existe columna). Asignación producto–empresa y precios; se gestiona desde módulo Empresas / Product Management, no desde este CRUD.

### Tabla pivot: `high_employee_product`

- **FK:** high_employee_id, product_id. **Campos pivot:** status, reason, change_type. Empleados que tienen asignado el producto; se gestiona desde Alta de colaboradores / lógica de productos por empresa.

### Tabla: `product_filters`

- **FK:** product_id → products (cascade); company_id, area_id, department_id, location_id, position_id (nullable). **Campos:** genders, months, age_from, age_till, month_filter_from, month_filter_till, reason. Filtros por empresa/ubicación/área/departamento/puesto/edad/género/meses para asignar producto a empleados; se gestiona en otros flujos.

---

## REGLAS DE NEGOCIO

- **RN-01:** Nombre y descripción obligatorios (validación name required, description required).
- **RN-02:** No se puede eliminar un producto que tenga empresas asignadas (`$product->companies()->exists()`), empleados asignados (`$product->high_employees()->exists()`) o filtros (`$product->product_filters()->exists()`). Mensaje: "No puede borrar un producto con registros asignados."
- **RN-03:** Eliminación es soft delete (modelo usa SoftDeletes); el controlador usa `Product::where("id",$product_id)->delete()`.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/products | GET | ProductsController@getIndex | view_products |
| admin/products/get | GET | ProductsController@getList | view_products |
| admin/products/create | GET/POST | getCreate / create | create_products |
| admin/products/edit/{id} | GET | getEdit | edit_products |
| admin/products/edit | POST | update | edit_products |
| admin/products/view/{id} | GET | getView | view_products |
| admin/products/trash/{id} | GET | Trash | trash_products |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

1. getIndex: vista `admin.products.list` (DataTable que consume getList por AJAX).
2. getList: `Product::all()` (con SoftDeletes excluye eliminados). Serializa para DataTable: id, name, botones Editar/Ver/Eliminar. Sin paginación en servidor; paginación en el cliente (DataTable).

### Crear (getCreate / create)

1. getCreate: vista `admin.products.create` (formulario nombre y descripción).
2. create: Validator name y description required. Se crea Product con name y description, save(). Log de auditoría (usuario y opcionalmente company del usuario). Redirect a listado con mensaje "Producto creado exitosamente".

### Ver (getView)

1. Buscar producto por id; si no existe redirect a listado con mensaje. Vista view con producto (nombre, descripción, id; solo lectura).

### Editar (getEdit / update)

1. getEdit: buscar producto por id; si no existe redirect a listado. Vista edit con name, description y hidden product_id.
2. update: Validator name y description required. Se actualiza name y description, save(). Log y redirect a edit con mensaje "Producto actualizada exitosamente" (typo "actualizada" en mensaje).

### Eliminar (Trash)

1. Buscar producto por id; si no existe redirect back "El producto no existe." Si companies()->exists() || high_employees()->exists() || product_filters()->exists() → redirect back "No puede borrar un producto con registros asignados." Log y Product::where("id",$product_id)->delete() (soft delete). Redirect a listado.

---

## VALIDACIONES

- **name:** required (mensaje: "El nombre es requerido").
- **description:** required (mensaje: "La descripcion es requerida").
- No hay validación de unicidad de nombre.

---

## VISTAS

- **admin.products.list:** Título "Productos", subtítulo "Administra los diferentes productos/módulos que Paco ofrece a los clientes." DataTable (id dataTables-products), AJAX a get_admin_products. Columnas: N°, Nombre, acciones (Editar, Ver, Eliminar). Modal confirmación eliminar. Botón Crear.
- **admin.products.create:** Formulario nombre (text required) y descripción (textarea required). action admin_products_create.
- **admin.products.edit:** Formulario name, description, hidden product_id. action admin_products_update.
- **admin.products.view:** Muestra id (#), nombre y descripción. Solo lectura.

---

## USO EN OTROS MÓDULOS

- **CompaniesController (getCreate):** Exige Product::all() (o count >= 1) para mostrar el formulario de alta de empresa; en create/update de empresa se asignan productos con precios (company_product). CompaniesController create guarda cost_centers, products (attach con base_price, unit_price, enable_from, etc.).
- **ProductManagementController:** Gestión de productos por empresa (precios, enable_from, filtros, destinatarios); usa el mismo modelo Product y la pivot company_product.
- **HighEmployeesController:** Asignación de productos a empleados según product_filters y enable_from; alta/baja de empleado actualiza high_employee_product.
- **InsurancesController, otros:** Referencias a products por empresa o por empleado.

---

## MODELOS INVOLUCRADOS

- **Product** (App\Models\Product): tabla products, SoftDeletes, fillable name, description. Relaciones: companies() belongsToMany con withPivot('base_price','unit_price','enable_from','variation_margin'); high_employees() belongsToMany con withPivot('status','reason','change_type'); product_filters() hasMany ProductFilter.
- **Company:** products() belongsToMany Product (pivot company_product).
- **HighEmployee:** products() belongsToMany Product (pivot high_employee_product).
- **ProductFilter:** product_id FK; filtros por empresa, ubicación, área, departamento, puesto, género, meses, edad, etc.

---

## MIGRACIONES

- **2019_09_13_183843_create_products_table:** Crea products (id, name, timestamps).
- **2020_03_24_104140_update_products_table:** Añade description (longText).
- **2021_10_28_153757_update_products_2_table:** Añade softDeletes() a products.
- **2019_09_26_230601_create_company_product_table:** Crea pivot company_product (company_id, product_id, unit_price, base_price, enable_from, FKs cascade). (variation_margin puede venir de otra migración.)
- **2019_09_27_212631_create_high_employee_product_table:** Crea pivot high_employee_product (high_employee_id, product_id, status, reason, change_type, etc.).
- **2021_04_22_144745_create_product_filters_table:** Crea product_filters (product_id, company_id, area_id, department_id, location_id, position_id, genders, months, age_from, age_till, month_filter_from, month_filter_till, reason, FKs).

---

## PERMISOS (Legacy)

- **view_products:** listar, ver detalle, getList.
- **create_products:** getCreate, create.
- **edit_products:** getEdit, update.
- **trash_products:** Trash.

Catálogo global; en sidebar "Productos". Según documentación: view_producto, create_producto, update_producto, delete_producto (Productos).

---

## CASOS BORDE

- **Eliminar con empresas, empleados o filtros:** Se impide con mensaje claro. Eliminación es soft delete; las FKs en company_product, high_employee_product y product_filters apuntan al id del producto; si en otros listados se usa Product::all() o sin withTrashed, el producto no aparece pero las filas pivot siguen existiendo (product_id sigue referenciando el id).
- **Listado:** getList devuelve todos los registros no eliminados; paginación solo en el cliente.
- **Unicidad de nombre:** No se valida; pueden existir dos productos con el mismo nombre.

---

## AMBIGÜEDADES

- **variation_margin en pivot:** El modelo Product usa withPivot('variation_margin'); la migración create_company_product_table que se leyó no incluye variation_margin. Puede existir otra migración que la añada a company_product.

---

## DEUDA TÉCNICA

- **Mensaje de update:** Redirect con "Producto actualizada exitosamente" (debería ser "actualizado").
- **Paginación en servidor:** getList devuelve todos los productos; con muchos registros la respuesta puede ser pesada.
- **Logs:** Se registra acción, usuario y opcionalmente company del usuario; no hay campo estructurado de recurso afectado (ej. product_id).

---

## DIFERENCIAS CON TECBEN-CORE (si aplica)

- No verificado en este análisis. Al implementar: mantener RN-01 a RN-03, soft delete, bloqueo de eliminación si hay companies, high_employees o product_filters; valorar unicidad de nombre si el negocio lo exige.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
