# Ficha técnica: Módulo Centro de Pago (Legacy Paco) — CentroPagoResource

Documento de análisis para extraer lógica de negocio, modelos, rutas, validaciones y casos borde del CRUD de **centros de pago**. Solo describe lo que existe en el código.

---

## MÓDULO: Centro de Pago (PaymentCentersController / equivalente CentroPagoResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Gestiona **centros de pago** por empresa: listado, alta, edición, vista detalle y baja (trash). Cada centro tiene **nombre**, **empresa** (company_id obligatorio en formulario), **registro patronal** (patronal_register) y **dirección IMSS** (address_imss); los dos últimos son opcionales en validación (comentados en el controlador). El nombre es único por empresa. No hay soft delete ni filtros por high_employee_filters: high_user solo ve los centros de su empresa; admin ve todos. Controlador: `App\Http\Controllers\Admin\PaymentCentersController`. Rutas bajo `admin/payment_centers/*`. **Permisos en rutas:** `view_banks`, `create_banks`, `edit_banks`, `trash_banks` (no view_payment_centers, etc.). **Sidebar** muestra "Centros de pagos" si el rol tiene alguno de: `create_payment_centers`, `edit_payment_centers`, `view_payment_centers`, `trash_payment_centers` (inconsistencia con las rutas).

---

## ENTIDADES

### Tabla: `payment_centers`

- **PK:** id (bigint). Sin SoftDeletes (eliminación física).
- **Campos:** name (string), patronal_register (string nullable; migración inicial not null, luego update a nullable), address_imss (string nullable; igual), company_id (unsignedBigInteger nullable, FK companies cascade), timestamps.
- **Relaciones (modelo PaymentCenter):** company() belongsTo Company; high_employees() hasMany HighEmployee. La FK en high_employees (payment_center_id) tiene onDelete('cascade'): al eliminar un centro de pago, la base de datos puede cascadear según la migración; en el código del controlador Trash no se comprueba si hay high_employees asignados.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso (middleware) |
|------|--------|-------------------|----------------------|
| admin/payment_centers | GET | PaymentCentersController@getIndex | view_banks |
| admin/payment_centers/get | GET | getList | view_banks |
| admin/payment_centers/create | GET | getCreate | create_banks |
| admin/payment_centers/create | POST | create | create_banks |
| admin/payment_centers/edit/{payment_center_id} | GET | getEdit | edit_banks |
| admin/payment_centers/edit | POST | update | edit_banks |
| admin/payment_centers/view/{payment_center_id} | GET | getView | view_banks |
| admin/payment_centers/trash/{payment_center_id} | GET | Trash | trash_banks |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

**Sidebar:** Enlace "Centros de pagos" (admin_payment_centers) si el rol tiene al menos uno de: **create_payment_centers**, **edit_payment_centers**, **view_payment_centers**, **trash_payment_centers**. En seeds solo aparecen permisos **view_banks**, **create_banks**, **edit_banks**, **trash_banks**; si no se crean permisos payment_centers, el ítem del menú podría no mostrarse aunque las rutas usen banks.

---

## REGLAS DE NEGOCIO

- **RN-01:** **Alcance:** Si el usuario tiene **company** (high_user), solo ve y puede elegir centros de su empresa (`$user->company->payment_centers`). Si no tiene company (admin), ve todos (`PaymentCenter::get()`) y en create/edit puede elegir cualquier empresa.
- **RN-02:** **Crear:** company y name obligatorios. patronal_register y address_imss opcionales (se guardan null si no se envían). **Unicidad:** no puede existir otro centro de pago con el mismo name en la misma empresa (mismo company_id). Tras crear el registro se asocia a la empresa con `$company->payment_centers()->save($payment_center)`.
- **RN-03:** **Editar:** company y name obligatorios. Misma unicidad nombre por empresa; en el código **no se excluye el propio registro** al comprobar exists(), por lo que al guardar sin cambiar nombre ni empresa se devuelve error "Ya se encuentra registrado un Centro de pago con esa información" (🔧 bug).
- **RN-04:** **Trash:** No se comprueba si el centro tiene high_employees (u otras relaciones). Se elimina con delete físico. La FK high_employees.payment_center_id tiene onDelete('cascade') en migración: según el motor, al borrar el centro podrían eliminarse o actualizarse los empleados; en Laravel la migración define cascade en la tabla high_employees hacia payment_centers, por lo que al borrar un payment_center los high_employees con ese payment_center_id pueden verse afectados (depende del SGBD: típicamente CASCADE elimina o restringe; si es CASCADE delete, se borrarían empleados).
- **RN-05:** **Logs:** En create, update y Trash se crea un log con acción descriptiva (usuario y nombre del centro) y se asocia al usuario y a su company si tiene company_id.
- **RN-06:** **Listado (getList):** Devuelve JSON para DataTable con columnas: id, name, patronal_register, address_imss, company.general_name, acciones. Si algún centro tuviera company_id null, el acceso a $payment_center->company->general_name en el listado produciría error; en create siempre se asocia empresa, por lo que en uso normal todos tienen company.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

- **getIndex:** Devuelve vista `admin.payment_centers.list` (DataTable que consume getList por AJAX).
- **getList:** Según usuario: company ? company->payment_centers : PaymentCenter::get(). Por cada centro: id, name, patronal_register, address_imss, company->general_name, botones Editar/Ver/Eliminar. Respuesta JSON `{ data: [...] }`. La lista solo muestra columna Empresa si el usuario tiene rol admin (en la vista list el thead tiene @if(Auth::user()->hasRoles('admin')) para la columna Empresa); getList siempre incluye company.general_name en cada fila, por lo que si hay admin y high_user el JSON es el mismo pero la tabla oculta la columna para no admin.

### Crear (getCreate / create)

- **getCreate:** Companies: si high_user solo su empresa; si admin todas. Vista create con select company (required), name (required), patronal_register, address_imss.
- **create:** Validar company y name required. Buscar Company por id. Comprobar unicidad name por company_id (exists()). Crear PaymentCenter con name, patronal_register, address_imss; luego $company->payment_centers()->save($payment_center) para asignar company_id. Log; redirect a admin_payment_centers con mensaje éxito.

### Ver (getView)

- Buscar PaymentCenter por id; si no existe, redirect a admin_payment_centers con error. Vista view con empresa, nombre, registro patronal, dirección IMSS. Si company_id fuera null, $payment_center->company->general_name fallaría.

### Editar (getEdit / update)

- **getEdit:** PaymentCenter por id; si no existe, redirect con error. Companies igual que getCreate. Vista edit con payment_center_id, company, name, patronal_register, address_imss.
- **update:** Comprobar que exista el centro. Validar company y name required. Unicidad name por company **sin excluir el registro actual** → bug al guardar sin cambios. Actualizar name, patronal_register, address_imss, company_id; save(); log; redirect a admin_payment_centers_edit.

### Trash (eliminar)

- Buscar PaymentCenter por id. Si no existe, redirect back con error. **Después** se asigna $message (orden correcto a diferencia de otros módulos). Log; delete físico; redirect a admin_payment_centers con message_info. No se comprueba high_employees ni otras relaciones; la FK en high_employees tiene onDelete cascade (ver RN-04).

---

## VALIDACIONES

- **create / update:** company required, name required. Mensajes: "La Empresa es requerida", "El Nombre del Centro de Pago es requerido". patronal_register y address_imss están comentados como required en el controlador y no se validan como obligatorios; se guardan como null si vienen vacíos.
- **Unicidad:** En create y update se comprueba PaymentCenter::where('company_id', $company)->where('name', $data['name'])->exists(). En update no se excluye el id del registro actual.

---

## VISTAS

- **admin.payment_centers.list:** Título "Centros de pagos"; DataTable id dataTables-payment-centers con ajax get_admin_payment_centers. Columnas: N°, Nombre, Registro Patronal, Dirección IMSS, (solo admin) Empresa, acciones. Modal confirmación eliminar.
- **admin.payment_centers.create:** Formulario company (select required), name (required), patronal_register, address_imss. action admin_payment_centers_create.
- **admin.payment_centers.edit:** Igual con payment_center_id y datos precargados. action admin_payment_centers_update.
- **admin.payment_centers.view:** Solo lectura: empresa, nombre, registro patronal, dirección IMSS. Enlace Regresar a admin_payment_centers.

---

## USO EN OTROS MÓDULOS

- **HighEmployeesController:** Selector de centros de pago en alta y edición de colaboradores; asignación de payment_center_id.
- **Api (PacoApi):** ApiPaymentCentersController (get, create, view, update, delete) por empresa; ApiHighEmployeesController valida centro de pago por nombre y company_id.
- **EmploymentContractsController (API), DigitalDocumentsController (API), FileCompanyController:** Obtienen PaymentCenter del high_employee para lógica de documentos/contratos.

---

## MODELOS INVOLUCRADOS

- **PaymentCenter (App\Models\PaymentCenter):** tabla payment_centers, fillable name, patronal_register, address_imss, company_id. company() belongsTo Company; high_employees() hasMany HighEmployee.
- **Company:** payment_centers() hasMany PaymentCenter.
- **HighEmployee:** payment_center_id (nullable), belongsTo PaymentCenter; migración con onDelete cascade hacia payment_centers.

---

## MIGRACIONES

- **create_payment_centers_table:** id, name, patronal_register (string), address_imss (string), company_id (nullable FK companies cascade), timestamps.
- **update_payment_centers_table:** patronal_register y address_imss pasan a nullable.
- **update_high_employees_32_table:** payment_center_id (nullable FK payment_centers) en high_employees con onUpdate cascade, onDelete cascade.

---

## PERMISOS LEGACY

- Rutas usan: **view_banks**, **create_banks**, **edit_banks**, **trash_banks**.
- Sidebar usa: **create_payment_centers**, **edit_payment_centers**, **view_payment_centers**, **trash_payment_centers**. En RolesTableSeeder solo se referencian los permisos *banks*; los *payment_centers* pueden no existir, generando desajuste menú / acceso.

---

## CASOS BORDE

- **Update sin cambiar nombre ni empresa:** La comprobación de unicidad no excluye el id actual, por lo que devuelve error y no permite guardar.
- **Trash con centros con empleados:** No hay validación; si la FK en high_employees tiene onDelete cascade, el borrado del centro podría eliminar o dejar en null los high_employees según el SGBD. Conviene confirmar comportamiento real (MySQL CASCADE elimina o rechaza según configuración).
- **getList con company_id null:** Si existiera un centro con company_id null (p. ej. creado por otro medio), $payment_center->company sería null y $payment_center->company->general_name daría error.

---

## AMBIGÜEDADES

- **Permisos banks vs payment_centers:** Las rutas protegen con view_banks, create_banks, etc.; el menú lateral depende de create_payment_centers, etc. No queda claro si es intencional (dos nombres para el mismo recurso) o si el sidebar debería usar los permisos banks.
- **Cascade en high_employees:** La migración pone onDelete('cascade') en la FK de high_employees hacia payment_centers; si el SGBD interpreta CASCADE como borrar hijos, eliminar un centro borraría empleados. Si es SET NULL no suele usarse cascade en Laravel así; hay que revisar el efecto real en BD.

---

## DEUDA TÉCNICA

- Unicidad en update: añadir `->where('id', '!=', $payment_center_id)` (o equivalente) para no bloquear guardar sin cambios.
- Trash: valorar comprobar si existen high_employees (u otras relaciones) antes de borrar y devolver error o desasignar, en lugar de depender solo del cascade.
- create(): `Company::find($data['company'])` puede devolver null si el id no existe; no se valida antes de usar $company en where y en save.

---

## DIFERENCIAS CON TECBEN-CORE

- Si en tecben-core existe un recurso de Centros de Pago (Filament o similar), conviene contrastar: permisos (banks vs centro_pago), unicidad nombre por empresa con exclusión del registro en edición, validación de empresa existente, política de borrado frente a empleados asignados y uso de cascade. No se ha verificado implementación actual en tecben-core en este análisis.
