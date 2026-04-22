# Ficha técnica: Módulo Bancos (Legacy Paco)

Documento de análisis para extraer lógica de negocio, modelos, migraciones, bugs e inconsistencias. Solo describe lo que existe en el código.

---

## MÓDULO: Bancos (BancoResource)

**FECHA ANÁLISIS:** 2025-03-18  
**ANALIZADO POR:** Agente paco-legacy  
**ESTADO EN TECBEN-CORE:** Por definir (no verificado en este análisis)

Permite **listar, crear, editar, ver y eliminar** el catálogo de **bancos**. Cada banco tiene nombre, código (identificador numérico/clave) y comisión (decimal; en la UI "Comision por intentos de cobro"). Es un catálogo **global** (sin filtro por empresa). No se puede eliminar un banco que tenga cuentas (accounts) asignadas. Eliminación por soft delete. El modelo expone un accessor `vat` que calcula el IVA sobre la comisión usando `config('app.bank_fee_vat')`. Controlador: `BanksController`. Rutas bajo `admin/banks/*`; permisos: `view_banks`, `create_banks`, `edit_banks`, `trash_banks`. Los mismos permisos se usan para el módulo de **centros de pago** (PaymentCentersController).

---

## ENTIDADES

### Tabla: `banks`

- **PK:** id (bigint unsigned).
- **Campos:** name (string), code (integer; añadido en update_banks_table), timestamps, deleted_at (soft deletes; update_banks_2_table), commission (decimal 20,2 default 0; update_banks_3_table).
- **Relaciones (modelo Bank):** accounts() hasMany Account. Accessor: getVatAttribute() = commission * (config('app.bank_fee_vat')/100).

### Tabla: `accounts` (contexto)

- **FK:** bank_id → banks. Las cuentas (por empresa/empleado o negocio) referencian un banco. Trash comprueba `$bank->accounts()->exists()`; si hay cuentas no se permite borrar el banco.

---

## RUTAS Y PERMISOS

| Ruta | Método | Controlador@método | Permiso |
|------|--------|-------------------|---------|
| admin/banks | GET | BanksController@getIndex | view_banks |
| admin/banks/get | GET | getList | view_banks |
| admin/banks/create | GET | getCreate | create_banks |
| admin/banks/create | POST | create | create_banks |
| admin/banks/edit/{bank_id} | GET | getEdit | edit_banks |
| admin/banks/edit | POST | update | edit_banks |
| admin/banks/view/{bank_id} | GET | getView | view_banks |
| admin/banks/trash/{bank_id} | GET | Trash | trash_banks |

Middleware: `logged`, `2fa`, `Permissions:{"permissions_and":["..."]}` según la ruta.

**Sidebar:** Enlace "Bancos" si el usuario tiene al menos uno de: edit_banks, view_banks, trash_banks, create_banks.

---

## REGLAS DE NEGOCIO

- **RN-01:** name, code y commission son **obligatorios** en create y update.
- **RN-02:** commission debe ser **numérico** (validación en controlador).
- **RN-03:** **No se puede eliminar** un banco que tenga cuentas asignadas (`$bank->accounts()->exists()`). Mensaje: "No puede borrar un banco con registros asignados."
- **RN-04:** Eliminación es **soft delete** (modelo Bank usa SoftDeletes). Trash hace `Bank::where("id",$bank_id)->delete()`. getList usa Bank::all(), que excluye automáticamente los soft-deleted.
- **RN-05:** No hay validación de **unicidad** de name ni de code; se pueden crear bancos duplicados por nombre o código.
- **RN-06:** Catálogo sin scope por empresa; cualquier usuario con los permisos ve y gestiona todos los bancos.

---

## FLUJO PRINCIPAL

### Listado (getIndex / getList)

1. getIndex: vista `admin.banks.list` (DataTable que consume getList por AJAX).
2. getList: `Bank::all()` (excluye soft-deleted). Para cada banco: id, name, code, botones Editar / Ver / Eliminar. Respuesta JSON `{ data: banks_list }`. Sin paginación en servidor; DataTable en cliente con orden por columna 1 (nombre) asc.

### Crear (getCreate / create)

1. getCreate: vista `admin.banks.create` (formulario name, code, commission). Sin datos adicionales.
2. create: Validar name, code, commission required; commission numeric. Bank::create($data). Log "ha creado el banco: ...". Asociar log al usuario y a la empresa del usuario si tiene company_id (bloque duplicado en el código). Redirect a admin_banks con "Banco creado exitosamente".

### Ver (getView)

1. Buscar banco por id; si no existe redirect a admin_banks con mensaje "El banco ... no se encuentra registrado". Vista view con bank (muestra id, name, code; la vista no muestra commission ni vat).

### Editar (getEdit / update)

1. getEdit: Buscar banco por id; si no existe redirect a admin_banks. Vista edit con bank (name, code, commission). Hidden bank_id.
2. update: Validar name, code, commission required; commission numeric. Bank::find($bank_id)->update($data). Log "ha actualizado el banco: ...". Mismo patrón de log duplicado (usuario + company si existe, dos veces). Redirect a admin_banks_edit con bank_id y "Banco actualizado exitosamente".

### Eliminar (Trash)

1. Buscar banco por id. **Orden en código:** se asigna `$message = "Se ha eliminado el banco: ".$bank->name` antes de comprobar `if (!$bank)`; si el banco no existe se produciría error al acceder a $bank->name (⚠️ ver CASOS BORDE). Si no existe → redirect back "El banco no existe." Si bank->accounts()->exists() → redirect back "No puede borrar un banco con registros asignados." Log "ha eliminado el banco: ..." (bloque duplicado para company). Bank::where("id",$bank_id)->delete() (soft delete). Redirect a admin_banks con mensaje "Se ha eliminado el banco: ...".

---

## VALIDACIONES

- **name:** required ("El nombre es requerido").
- **code:** required ("El codigo es requerido"). No se valida tipo integer en controlador; la migración define code como integer (el formulario usa input text).
- **commission:** required ("La comisión es requerida"), numeric ("La comisión debe ser numerica"). No hay min/max ni validación de valor positivo en reglas del validador (el decimal en BD permite negativos si no hay check).
- No hay validación de unicidad de name o code.

---

## VISTAS

- **admin.banks.list:** Título "Bancos", subtítulo "Administra el listado de bancos." DataTable (id dataTables-banks), AJAX a get_admin_banks. Columnas: N°, Nombre, Código, acciones (Editar, Ver, Eliminar). Modal confirmación eliminar. Botón Crear.
- **admin.banks.create:** Formulario: Nombre (text required), Código (text required), Comision por intentos de cobro (text required). action admin_banks_create. Breadcrumb: PACO → Bancos → Crear Banco.
- **admin.banks.edit:** Mismo formulario con valores del banco; hidden bank_id. action admin_banks_update.
- **admin.banks.view:** Solo lectura: id (#), Nombre, Codigo. No muestra commission ni vat. Botón Regresar a admin_banks.

---

## USO EN OTROS MÓDULOS

- **Accounts (cuentas):** La tabla accounts tiene bank_id; al dar de alta o editar cuentas se elige un banco del catálogo. Por eso Trash comprueba accounts()->exists().
- **PaymentCentersController (centros de pago):** Usa los mismos permisos view_banks y edit_banks para listar/editar/ver centros de pago por empresa; no comparte modelo Bank.
- **ReceivableAccountsController:** Rutas bank_transactions para archivos/transacciones bancarias; no dependen del CRUD de bancos directamente.

---

## MODELOS INVOLUCRADOS

- **Bank** (App\Models\Bank): tabla banks, SoftDeletes, fillable name, code, commission. accounts() hasMany Account. getVatAttribute() usa config('app.bank_fee_vat').
- **Account:** bank_id FK a banks; relación bank() belongsTo Bank.

---

## MIGRACIONES

- **create_banks_table:** banks con id, name, timestamps.
- **update_banks_table:** añade code (integer).
- **update_banks_2_table:** añade softDeletes().
- **update_banks_3_table:** añade commission (decimal 20,2 default 0).

---

## PERMISOS LEGACY

- **view_banks:** getIndex, getList, getView. También usado por PaymentCentersController (listado y vista de centros de pago).
- **create_banks:** getCreate, create.
- **edit_banks:** getEdit, update. También usado por PaymentCentersController (editar centro de pago).
- **trash_banks:** Trash.

---

## CASOS BORDE

- **Trash con banco inexistente:** En Trash se asigna `$message = "Se ha eliminado el banco: ".$bank->name` y después se comprueba `if (!$bank)`. Si el banco no existe, $bank es null y el acceso a $bank->name provoca error antes del redirect. Debería comprobarse !$bank antes de usar $bank.
- **Código como texto en formulario:** El campo code en create/edit es input type="text"; en BD es integer. Si el usuario introduce caracteres no numéricos, el guardado podría fallar o comportarse de forma inesperada según el driver (p. ej. MySQL puede convertir a 0). No hay validación numeric/digits para code en el controlador.
- **Comisión negativa:** La validación solo exige numeric; no hay min:0. Se podría guardar una comisión negativa.
- **Log duplicado a company:** En create, update y Trash el bloque que asocia el log a la empresa del usuario está duplicado (dos veces "if(isset($user->company_id)){ ... company_user->logs()->save($log); }"); sin efecto funcional extra pero es redundante.

---

## AMBIGÜEDADES

- **Uso de commission y vat:** El accessor vat depende de config('app.bank_fee_vat'); no se ha verificado en este análisis dónde se usa el atributo vat (informes, cálculos de cobro, etc.). La vista view no muestra commission ni vat.
- **Code:** No está documentado si el código debe ser el CLABE interbancario o un identificador interno; la migración lo define como integer (típicamente clave numérica de banco en México, 3 dígitos).

---

## DEUDA TÉCNICA

- Orden incorrecto en Trash: comprobar !$bank antes de usar $bank->name para $message.
- Bloque duplicado de "guardar log en company" en create, update y Trash.
- Trash por GET; recomendable POST/DELETE para eliminar.
- Validación de code como integer o dígitos si se espera clave numérica; validación min:0 para commission si la regla de negocio es que no sea negativa.
- Vista view no muestra commission ni vat; podría ser intencional (solo datos básicos) o omisión.

---

## DIFERENCIAS CON TECBEN-CORE

Por definir (no verificado en este análisis). Si en tecben-core existe catálogo de bancos, comparar: campos (name, code, commission), unicidad de code, restricción de eliminación cuando hay cuentas, y uso de vat/config bank_fee_vat.
