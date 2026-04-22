# Análisis técnico: Módulo de Ubicaciones (Panel Cliente)

## 1. Resumen

El módulo **Ubicaciones** del panel **Cliente** permite a cada empresa (tenant) gestionar sus sucursales/ubicaciones y asociar razones sociales a cada una. Está integrado con el multi-tenant de Filament (`Empresa` como tenant) y con el sistema de permisos (Shield).

---

## 2. Ubicación en la aplicación

| Aspecto | Detalle |
|--------|---------|
| **Panel** | `cliente` (`ClientePanelProvider`) |
| **Grupo de navegación** | Catálogos Colaboradores |
| **Recurso Filament** | `App\Filament\Cliente\Resources\Ubicaciones\UbicacionResource` |
| **Modelo principal** | `App\Models\Ubicacion` |
| **Scoped to tenant** | Sí (`$isScopedToTenant = true`) |

El panel Cliente usa `tenant(Empresa::class)`, por lo que todas las listas y formularios de Ubicaciones se filtran por la empresa del tenant actual.

---

## 3. Tablas afectadas

### 3.1 Tablas principales

| Tabla | Uso |
|-------|-----|
| **`ubicaciones`** | Registro principal de cada ubicación/sucursal. Campos: `id`, `nombre`, `empresa_id`, `cp`, `mostrar_modal_calendly`, `registro_patronal_sucursal`, `direccion_imss`, `deleted_at`, `timestamps`. |
| **`razones_sociales_ubicaciones`** | Tabla pivote entre ubicaciones y razones sociales. Campos: `id`, `razon_social_id`, `ubicacion_id`, `deleted_at`, `timestamps`. Relación N:M entre `Ubicacion` y `Razonsocial`. |

### 3.2 Tablas relacionadas (lectura/escritura desde el módulo)

| Tabla | Uso |
|-------|-----|
| **`empresas`** | La ubicación pertenece a una empresa (`empresa_id`). El valor se asigna por tenant, no por selección del usuario. |
| **`razones_sociales`** | Se crean o actualizan razones sociales desde el repeater del formulario de ubicación (nombre, RFC, dirección, colonia, alcaldía, estado, etc.). |
| **`empresas_razones_sociales`** | Al crear una razón social desde una ubicación, se asocia la razón social a la empresa de la ubicación (`syncWithoutDetaching`). |
| **`logs`** | Auditoría: se inserta un registro en cada creación, actualización y borrado de una ubicación (accion, fecha, user_id). |

---

## 4. Reglas de negocio

### 4.1 Empresa (tenant)

- **RN-1.** Toda ubicación pertenece a una sola empresa.
- **RN-2.** La empresa de la ubicación no es elegible por el usuario: se toma del tenant actual (`Filament::getTenant()?->id`) y se envía como campo oculto (`empresa_id`).
- **RN-3.** El listado y las rutas de recurso solo muestran/editan ubicaciones de la empresa del tenant (por `isScopedToTenant` y scope de tenant en el panel).

### 4.2 Ubicación

- **RN-4.** Campos obligatorios: `nombre`, `cp` (código postal, máx. 5 caracteres).
- **RN-5.** `mostrar_modal_calendly` es un booleano; por defecto `true` y no se muestra en el formulario (campo oculto).
- **RN-6.** `registro_patronal_sucursal` y `direccion_imss` son opcionales.
- **RN-7.** Las ubicaciones usan borrado lógico (`SoftDeletes`). En el recurso se desactiva el scope de soft deletes en el binding para poder editar/ver registros en papelera si se requiere.

### 4.3 Razones sociales

- **RN-8.** Una ubicación puede tener varias razones sociales y una razón social puede estar en varias ubicaciones (N:M vía `razones_sociales_ubicaciones`).
- **RN-9.** Desde el formulario de ubicación se pueden crear nuevas razones sociales (repeater) o vincular existentes; al guardar se hace `sync` de las razones del repeater con la ubicación.
- **RN-10.** Al crear una razón social desde una ubicación, además de asociarla a la ubicación se asocia a la empresa de esa ubicación en `empresas_razones_sociales` (`syncWithoutDetaching`).
- **RN-11.** En el repeater de razones sociales: nombre y RFC obligatorios; RFC entre 12 y 13 caracteres; dirección (calle, número exterior, colonia, alcaldía, estado, CP) con validaciones y carga de colonias vía API SEPOMEX por código postal (CP 5 dígitos válido).

### 4.4 Autorización

- **RN-12.** El acceso al recurso Ubicaciones está gobernado por `UbicacionPolicy`, que delega en permisos de Shield: `ViewAny:Ubicacion`, `View:Ubicacion`, `Create:Ubicacion`, `Update:Ubicacion`, `Delete:Ubicacion`, `Restore:Ubicacion`, `ForceDelete:Ubicacion`, etc.

### 4.5 Auditoría

- **RN-13.** En cada creación, actualización y borrado de una ubicación se registra un evento en la tabla `logs` (descripción de la acción, fecha, usuario).

---

## 5. Flujo de información

### 5.1 Listado (ListUbicaciones)

1. Usuario entra al recurso Ubicaciones en el panel Cliente con un tenant (Empresa) seleccionado.
2. Filament aplica el scope de tenant: solo se consultan `Ubicacion` con `empresa_id` = tenant actual.
3. La tabla (`UbicacionesTable`) muestra: nombre, empresa.nombre, cp, mostrar_modal_calendly (icono), registro patronal sucursal, dirección IMSS, razones sociales (badges), fechas (ocultas por defecto).
4. Filtro de registros en papelera (`TrashedFilter`).
5. Acciones por registro: Editar, Eliminar. Acciones masivas: Eliminar, Restaurar, Force delete.

### 5.2 Crear ubicación (CreateUbicacion)

1. Formulario con sección "Información de la ubicación":
   - `empresa_id`: Hidden, valor por defecto = tenant actual.
   - `nombre`, `cp` (obligatorios).
   - `mostrar_modal_calendly`: Hidden, default `true`.
   - `registro_patronal_sucursal`, `direccion_imss` (opcionales).
2. Sección "Razones sociales": repeater con 0 ítems por defecto; cada ítem son los campos de una razón social (nombre, RFC, dirección, colonia desde SEPOMEX, etc.).
3. Antes de crear: `mutateFormDataBeforeCreate` extrae `razones_sociales` del payload y lo guarda en `$razonesSocialesPayload`; se quita `razones_sociales` de `$data` para no enviarlo al modelo.
4. Se crea el registro `Ubicacion` con los datos del formulario (incluido `empresa_id` y `mostrar_modal_calendly`).
5. Después de crear: `afterCreate` recorre `$razonesSocialesPayload`; por cada ítem con nombre y RFC:
   - Se crea o actualiza `Razonsocial` con `razonSocialAttributes($item)`.
   - Se asocia la razón social a la empresa: `$razonSocial->empresas()->syncWithoutDetaching([$empresaId])`.
   - Se asocia la razón social a la ubicación: `$this->record->razonesSociales()->attach($razonSocial->id)`.
6. El modelo `Ubicacion` en evento `created` escribe en `logs`.

### 5.3 Editar ubicación (EditUbicacion)

1. Al cargar el formulario: `mutateFormDataBeforeFill` rellena `razones_sociales` con los datos de `$this->record->razonesSociales()`; para cada una se obtienen colonias vía SEPOMEX por CP y se rellenan los campos del repeater (incl. `api_options_storage` para el Select de colonia).
2. El usuario edita datos de la ubicación y/o del repeater de razones sociales.
3. Antes de guardar: `mutateFormDataBeforeSave` extrae `razones_sociales` en `$razonesSocialesPayload` y lo quita de `$data`.
4. Se actualiza el registro `Ubicacion`.
5. Después de guardar: `afterSave` procesa `$razonesSocialesPayload`:
   - Si el ítem tiene `id`: se actualiza la `Razonsocial` existente.
   - Si no tiene `id`: se crea una nueva `Razonsocial` y se asocia a la empresa y a la ubicación.
   - Al final se hace `$this->record->razonesSociales()->sync($ids)` con los IDs de las razones que quedaron en el repeater (vincula solo esas razones a la ubicación).
6. El modelo `Ubicacion` en evento `updated` escribe en `logs`.

### 5.4 Eliminación

- Eliminar: soft delete de la ubicación; evento `deleted` del modelo escribe en `logs`.
- Restore / Force delete: según acciones de la tabla o de la página de edición, con permisos correspondientes.

---

## 6. Componentes del módulo

| Componente | Ruta / Clase |
|------------|------------------|
| Recurso | `App\Filament\Cliente\Resources\Ubicaciones\UbicacionResource` |
| Formulario | `App\Filament\Cliente\Resources\Ubicaciones\Schemas\UbicacionForm` |
| Tabla | `App\Filament\Cliente\Resources\Ubicaciones\Tables\UbicacionesTable` |
| Páginas | `ListUbicaciones`, `CreateUbicacion`, `EditUbicacion` |
| Política | `App\Policies\UbicacionPolicy` |
| Modelos | `Ubicacion`, `Empresa`, `Razonsocial` |

---

## 7. Dependencias externas

- **Configuración:** `config('app.sepomex')` — URL base del servicio SEPOMEX para obtener colonias por código postal (usado en el repeater de razones sociales).
- **Autenticación:** Panel Cliente (WorkOS u otro login configurado) y middleware `module:cliente`, `ScopeByCompany`, `EnsurePanelAccessByUserType`.
- **Permisos:** Spatie Laravel Permission / Filament Shield con permisos tipo `ViewAny:Ubicacion`, `Create:Ubicacion`, etc.

---

## 8. Diagrama de flujo (resumido)

```
[Listado] → Filament + Tenant(Empresa) → Query Ubicacion (empresa_id = tenant)
                ↓
[Crear]   → Form (empresa_id hidden, mostrar_modal_calendly hidden)
                → Create Ubicacion
                → afterCreate: por cada ítem repeater → Crear/actualizar Razonsocial
                            → empresas_razones_sociales (syncWithoutDetaching)
                            → razones_sociales_ubicaciones (attach)
                → Log (created)

[Editar]  → mutateFormDataBeforeFill: razones_sociales desde BD + SEPOMEX colonias
                → Form (edición)
                → mutateFormDataBeforeSave: extraer razones_sociales
                → Update Ubicacion
                → afterSave: crear/actualizar Razonsocial, sync razones_sociales_ubicaciones
                → Log (updated)

[Eliminar] → Soft delete Ubicacion → Log (deleted)
```

Este documento describe el estado del módulo según el código analizado y sirve como referencia técnica para mantenimiento y ampliaciones.
