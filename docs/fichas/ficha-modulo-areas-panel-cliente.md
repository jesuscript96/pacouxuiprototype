# Módulo de Áreas (Panel Cliente)

## 1. Resumen

El módulo **Áreas** del panel **Cliente** permite a cada empresa (tenant) gestionar sus áreas y asociarlas a un área general. Cada área pertenece a una empresa y a una sola área general; las áreas generales disponibles en el formulario se limitan a las de la empresa del tenant actual. Está integrado con el multi-tenant de Filament (`Empresa` como tenant).

---

## 2. Ubicación en la aplicación

| Aspecto | Detalle |
|--------|---------|
| **Panel** | `cliente` (`ClientePanelProvider`) |
| **Grupo de navegación** | Catálogos Colaboradores |
| **Recurso Filament** | `App\Filament\Cliente\Resources\Areas\AreaResource` |
| **Modelo principal** | `App\Models\Area` |
| **Scoped to tenant** | Sí (`$isScopedToTenant = true`) |

El panel Cliente usa `tenant(Empresa::class)`, por lo que el listado y los formularios de Áreas solo muestran y filtran por la empresa del tenant actual.

---

## 3. Tablas incluidas y relacionadas

### 3.1 Tabla principal del módulo

| Tabla | Descripción | Campos |
|-------|-------------|--------|
| **`areas`** | Registro principal de cada área. | `id`, `nombre`, `area_general_id`, `empresa_id`, `deleted_at`, `created_at`, `updated_at` |

- **`nombre`**: string, obligatorio. Nombre del área.
- **`area_general_id`**: FK a `areas_generales`. Obligatorio. Agrupa el área dentro de una categoría/área general.
- **`empresa_id`**: FK a `empresas`. Obligatorio. Se asigna por tenant, no por selección del usuario.
- **`deleted_at`**: borrado lógico (SoftDeletes).

### 3.2 Tablas relacionadas

| Tabla | Uso en el módulo |
|-------|-------------------|
| **`areas_generales`** | Cada área pertenece a una área general. El Select de área general en el formulario solo muestra áreas generales de la empresa del tenant (`empresa_id` = tenant actual). |
| **`empresas`** | La área pertenece a una empresa. El valor `empresa_id` se toma del tenant activo y se envía como campo oculto. |

### 3.3 Relaciones del modelo Area

- **`areaGeneral()`**: `BelongsTo` → `AreaGeneral`. Una área pertenece a una área general.
- **`empresa()`**: `BelongsTo` → `Empresa`. Una área pertenece a una empresa.

---

## 4. Reglas de negocio

### 4.1 Empresa (tenant)

- **RN-1.** Toda área pertenece a una sola empresa.
- **RN-2.** La empresa no es elegible por el usuario: se toma del tenant actual (`Filament::getTenant()?->id`) y se envía como campo oculto (`empresa_id`).
- **RN-3.** El listado y las rutas del recurso solo muestran/editan áreas de la empresa del tenant (por `isScopedToTenant` y scope de tenant en el panel).

### 4.2 Área general

- **RN-4.** Toda área debe estar asociada a una área general (`area_general_id` obligatorio).
- **RN-5.** En el formulario solo se ofrecen áreas generales de la empresa del tenant actual (consulta filtrada por `empresa_id` = tenant). No se pueden elegir áreas generales de otras empresas.

### 4.3 Área

- **RN-6.** Campo obligatorio: `nombre` (máx. 255 caracteres).
- **RN-7.** Las áreas usan borrado lógico (`SoftDeletes`). En el recurso se desactiva el scope de soft deletes en el binding para poder editar/ver registros en papelera.
- **RN-8.** Tras crear un registro, el usuario es redirigido al listado del recurso (`getRedirectUrl()` en `CreateArea`).

### 4.4 Acciones en listado y edición

- **RN-9.** Por registro: Editar, Eliminar (soft delete), Forzar eliminar, Restaurar. Acciones masivas: Eliminar, Forzar eliminar, Restaurar. Filtro de registros en papelera (`TrashedFilter`).

---

## 5. Flujo de información

### 5.1 Listado (ListAreas)

1. El usuario accede al recurso Áreas en el panel Cliente con un tenant (Empresa) seleccionado.
2. Filament aplica el scope de tenant: solo se consultan `Area` con `empresa_id` = tenant actual.
3. La tabla muestra: nombre, área general (nombre), fechas de creación y actualización (ocultas por defecto).
4. Filtro de registros en papelera. Acciones por fila y masivas según RN-9.

### 5.2 Crear área (CreateArea)

1. Formulario con sección "Información del área":
   - `empresa_id`: Hidden, valor por defecto = tenant actual.
   - `nombre`: obligatorio, máx. 255 caracteres.
   - `area_general_id`: Select con relación `areaGeneral`, filtrado por `empresa_id` = tenant; búsqueda y precarga; obligatorio.
2. Al guardar se crea el registro `Area` con los datos del formulario.
3. Redirección al listado del recurso (index).

### 5.3 Editar área (EditArea)

1. Se carga el registro (incluidos los que están en papelera, por `getRecordRouteBindingEloquentQuery`).
2. El usuario modifica nombre y/o área general (solo áreas generales del tenant).
3. Al guardar se actualiza el registro. Acciones en cabecera: Eliminar, Forzar eliminar, Restaurar.

### 5.4 Eliminación

- Eliminar: soft delete del registro.
- Restaurar / Forzar eliminar: según acciones de la tabla o de la página de edición.

---

## 6. Componentes del módulo

| Componente | Ruta / Clase |
|------------|------------------|
| Recurso | `App\Filament\Cliente\Resources\Areas\AreaResource` |
| Formulario | `App\Filament\Cliente\Resources\Areas\Schemas\AreaForm` |
| Tabla | `App\Filament\Cliente\Resources\Areas\Tables\AreasTable` |
| Páginas | `ListAreas`, `CreateArea`, `EditArea` |
| Modelos | `Area`, `AreaGeneral`, `Empresa` |

---

## 7. Dependencias

- **Panel:** Cliente con `tenant(Empresa::class)` y middleware del panel (p. ej. `module:cliente`, `ScopeByCompany`).
- **Módulo Áreas generales:** Las áreas dependen de que existan áreas generales de la empresa para poder asignar `area_general_id`. Se recomienda configurar al menos un área general por empresa antes de crear áreas.

Este documento describe el estado del módulo según el código y sirve como referencia para mantenimiento y ampliaciones.
