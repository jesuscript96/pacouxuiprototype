# Ficha técnica: Módulo Centros de Costo (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Centros de Costo (cost_centers / CentroCostoResource)

Permite listar, crear, editar, ver y eliminar (hard delete) centros de costo. Los centros de costo se usan para dispersiones de transacciones y están asociados a servicios externos: **BELVO** (débito directo), **EMIDA** (recargas y pago de servicios) y **STP**. Cada centro define credenciales o datos según el servicio. Las empresas (companies) se asocian a centros de costo mediante tabla pivot `company_cost_center`; esa asociación se gestiona desde el módulo de Empresas, no desde este CRUD.

---

## ENTIDADES

### Tabla principal: `cost_centers`

- **PK:** `id` (bigint unsigned).
- **Campos (tras migración 2025_03_24):** `service` (string nullable), `name` (string nullable), `bank_account` (string nullable), `terminal_id_tae` (string nullable), `terminal_id_ps` (string nullable), `clerk_id_tae` (string nullable), `clerk_id_ps` (string nullable), `key_id` (string nullable), `key_secret` (string nullable). `timestamps`.
- **Relaciones (modelo CostCenter):** `companies()` → belongsToMany con tabla pivot `company_cost_center`.
- **Nota:** La migración inicial tenía `company_id` (FK a companies); la migración `update_cost_centers_table` eliminó esa FK y la columna; la relación con empresas pasó a N:M vía pivot.

### Tabla pivot: `company_cost_center`

- **PK:** `id`. **FK:** `company_id` → companies (cascade), `cost_center_id` → cost_centers (cascade). `timestamps`.
- Uso: asociar empresas a centros de costo. La asignación se hace en `CompaniesController` (request `cost_centers`); el `CostCentersController` no gestiona esta relación al crear/editar un centro de costo.

---

## REGLAS DE NEGOCIO

- **RN-01:** El tipo de servicio debe ser uno de: BELVO, EMIDA, STP. Cualquier otro valor se rechaza con "Tipo de servicio NO Disponible".
- **RN-02 (BELVO):** Obligatorios: nombre del centro de costo, key_id, key_secret. No se exigen terminal/clerk ni cuenta bancaria.
- **RN-03 (EMIDA):** Obligatorios: nombre, terminal de recargas (terminal_id_tae), clerk de recargas (clerk_id_tae), terminal de pago de servicios (terminal_id_ps), clerk de pago de servicios (clerk_id_ps).
- **RN-04 (STP):** Obligatorios: nombre, cuenta bancaria (bank_account); la cuenta debe ser numérica.
- **RN-05:** Eliminación es física (delete); no hay soft delete. No se valida si el centro está en uso por empresas o por cuentas por cobrar / Belvo / Emida antes de borrar.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/cost_centers | GET | CostCentersController@getIndex | view_cost_centers |
| admin/cost_centers/get | GET | CostCentersController@getList | view_cost_centers |
| admin/cost_centers/create | GET/POST | getCreate / create | create_cost_centers |
| admin/cost_centers/edit/{id} | GET | getEdit | edit_cost_centers |
| admin/cost_centers/edit | POST | update | edit_cost_centers |
| admin/cost_centers/view/{id} | GET | getView | view_cost_centers |
| admin/cost_centers/trash/{id} | GET | Trash | trash_cost_centers |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}`.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

1. getIndex: devuelve vista `admin.cost_centers.list` (DataTable que consume getList por AJAX).
2. getList: `CostCenter::all()` sin filtros ni paginación en backend; se serializa para DataTable (id, service, name, botones Editar/Ver/Eliminar).

### Crear (getCreate / create)

1. getCreate: servicios fijos `['BELVO','EMIDA','STP']`; vista `admin.cost_centers.create` con formulario dinámico según servicio (JavaScript muestra campos por tipo).
2. create: validación según `request->cost_center_service` (BELVO/EMIDA/STP); si no es ninguno, redirect con error "Tipo de servicio NO Disponible". Se crea `CostCenter` con los campos enviados (campos no usados por el servicio se guardan como null). Log de auditoría (usuario y opcionalmente company del usuario). Redirect a listado con mensaje de éxito. No se asocia el centro a ninguna empresa en este flujo.

### Ver (getView)

1. Buscar centro por id; si no existe, redirect a listado con mensaje. Vista `admin.cost_centers.view` con datos del centro (solo lectura; campos opcionales mostrados con isset).

### Editar (getEdit / update)

1. getEdit: buscar centro por id; si no existe, redirect a listado. Vista `admin.cost_centers.edit` con formulario según `$cost_center->service` (BELVO/EMIDA/STP).
2. update: validación según `cost_center_service_name` (mismo criterio que create). Actualizar modelo y guardar; log de auditoría; redirect a edit con mensaje de éxito.

### Eliminar (Trash)

1. Buscar centro por id; si no existe, redirect back con error. Crear log "ha eliminado el Centro de costos: {name}". `CostCenter::where("id", $cost_center_id)->delete()`. Redirect a listado con mensaje.

---

## VALIDACIONES

- **BELVO:** cost_center_name required; cost_center_key_id required; cost_center_key_secret required.
- **EMIDA:** cost_center_name, cost_center_terminal_tae, cost_center_clerk_tae, cost_center_terminal_ps, cost_center_clerk_ps required (mensajes: Terminal/Clerk de Recargas y de Pago de Servicios).
- **STP:** cost_center_name required; cost_center_account required|numeric.
- No hay validación de unicidad (nombre por servicio, etc.). No se valida longitud ni formato de keys/terminals más allá de required.

---

## VISTAS

- **admin.cost_centers.list:** DataTable (id tabla `dataTables-cost-centers`), AJAX a `get_admin_cost_centers`. Columnas: N°, Servicio, Nombre, acciones (Editar, Ver, Eliminar). Modal de confirmación para eliminar. Botón "Crear" a `admin_cost_centers_create`.
- **admin.cost_centers.create:** Formulario con select "Selecciona el tipo de servicio"; contenedor dinámico `#container_cost_center` rellenado por JS según BELVO/EMIDA/STP (campos nombre, keys, terminales, clerks, cuenta según servicio).
- **admin.cost_centers.edit:** Formulario por tipo de servicio (bloques @if por $cost_center->service); hidden cost_center_id; action admin_cost_centers_update.
- **admin.cost_centers.view:** Muestra service, name y, si existen, bank_account, terminal_id_tae/ps, clerk_id_tae/ps, key_id, key_secret.

---

## USO EN OTROS MÓDULOS

- **CompaniesController:** En create/edit de empresas se envían `cost_centers` (array de ids); se asocian con `$company->cost_centers()` (sync o attach según lógica del controlador). En vista create se listan `belvo_cost_centers`, `emida_cost_centers`, `stp_cost_centers` (CostCenter::where('service', ...)->get()).
- **AuthController (API):** Si el usuario tiene high_employee y la empresa tiene cost_centers con service EMIDA, se asigna `cost_center_business_name` al usuario (para lógica de negocio/EMIDA).
- **Belvo (débito directo):** Jobs y controladores usan `CostCenter` por servicio BELVO (key_id, key_secret, name) para clientes, métodos de pago y solicitudes de pago. ReceivableAccount y payroll_advances/services guardan campo `cost_center` (string) para trazabilidad.
- **Api ServicesController (EMIDA):** Recargas y pago de servicios usan el centro de costo EMIDA de la empresa (terminal_id_ps, terminal_id_tae, clerk_id_ps, clerk_id_tae, name).

---

## CASOS BORDE

- **Eliminar centro en uso:** No se comprueba si hay empresas asociadas (company_cost_center), cuentas por cobrar, clientes Belvo o servicios que referencien el centro. El delete puede dejar referencias huérfanas o fallos en jobs que filtran por cost_center.
- **Cambio de servicio en edición:** El formulario de edición muestra campos según el servicio actual; no se puede cambiar el tipo de servicio en la vista (cost_center_service_name va como hidden/readonly). Si se manipula el request y se cambia el servicio, se actualizaría el modelo; las validaciones se aplican según el valor enviado.
- **Listado sin paginación en servidor:** getList devuelve todos los registros; con muchos centros de costo la respuesta puede ser pesada; la paginación es solo en el front (DataTable).

---

## BUGS E INCONSISTENCIAS

1. **Eliminación sin comprobar uso:** Trash no verifica empresas vinculadas ni uso en receivable_accounts, belvo_direct_debit_customers, belvo_payment_methods, etc. Riesgo de integridad referencial o errores en procesos que asumen que el centro existe.
2. **Relación N:M no gestionada en CRUD:** Al crear/editar un centro de costo no se ofrece asignar empresas; la asignación es solo desde Empresas. Coherente con el diseño pero puede generar centros "sueltos" hasta que se asignen desde empresas.
3. **Vista edit: pestañas duplicadas:** En edit.blade.php hay tres `<li class="active">` en los nav-tabs (servicio, "/", nombre); solo debería haber una pestaña activa o ser un encabezado, no pestañas reales.
4. **key_secret en vista:** La vista "Ver" muestra key_secret en texto plano; es dato sensible; debería enmascararse o no mostrarse en UI.

---

## MODELOS INVOLUCRADOS

- **CostCenter** (App\Models\CostCenter): tabla `cost_centers`, fillable company_id, service, name, bank_account, terminal_id_tae, terminal_id_ps, clerk_id_tae, clerk_id_ps, key_id, key_secret. Relación `companies()` belongsToMany (tabla pivot company_cost_center). En modelo sigue fillable `company_id` aunque la columna ya no existe en BD (migración la eliminó); podría causar confusión o error si se asigna.
- **Company:** relación `cost_centers()` belongsToMany con CostCenter.

---

## MIGRACIONES

- **2025_01_20_152449_create_cost_centers_table:** Crea cost_centers con company_id (FK companies cascade), service, name, bank_account, terminal_id_tae, terminal_id_ps, clerk_id_tae, clerk_id_ps, key_id, key_secret (todos no nullable).
- **2025_03_24_193615_update_cost_centers_table:** Hace nullable service, name, bank_account, terminal_id_tae, terminal_id_ps, clerk_id_tae, clerk_id_ps, key_id, key_secret; elimina FK y columna company_id.
- **2025_03_26_153400_create_company_cost_center_table:** Crea pivot company_cost_center (company_id, cost_center_id, timestamps, FKs cascade).

---

## PERMISOS (Legacy)

- **view_cost_centers:** listar, ver detalle, getList.
- **create_cost_centers:** getCreate, create.
- **edit_cost_centers:** getEdit, update.
- **trash_cost_centers:** Trash.

Según documentación de roles: permisos de catálogo; disponibles para roles que gestionan catálogos (no solo admin).

---

## DEUDA TÉCNICA

- **CostCenter::$fillable:** Incluye `company_id` pero la tabla ya no tiene esa columna; conviene quitarlo del fillable para evitar asignación involuntaria.
- **Logs:** Se registra acción y usuario (y company del usuario si tiene company_id); no hay campo estructurado de recurso afectado (ej. cost_center_id).
- **Validación de servicio:** El valor "Tipo de servicio NO Disponible" se devuelve como error genérico; podría ser mensaje de validación por campo.

---

*Análisis realizado sobre el código del proyecto Paco (solo lectura). Para reglas de negocio oficiales o comportamiento deseado en tecben-core, validar con negocio.*
