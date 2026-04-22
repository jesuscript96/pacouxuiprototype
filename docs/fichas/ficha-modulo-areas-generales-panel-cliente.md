# Módulo de Áreas generales (Panel Cliente)

## 1. Resumen

El módulo **Áreas generales** del panel **Cliente** permite a cada empresa (tenant) definir categorías o agrupaciones de alto nivel (áreas generales) que luego se usan para clasificar las **Áreas**. Una área general pertenece a una sola empresa y puede tener muchas áreas asociadas. Está integrado con el multi-tenant de Filament (`Empresa` como tenant).

---

## 2. Ubicación en la aplicación

| Aspecto | Detalle |
|--------|---------|
| **Panel** | `cliente` (`ClientePanelProvider`) |
| **Grupo de navegación** | Catálogos Colaboradores |
| **Recurso Filament** | `App\Filament\Cliente\Resources\AreasGenerales\AreaGeneralResource` |
| **Modelo principal** | `App\Models\AreaGeneral` |
| **Scoped to tenant** | Sí (`$isScopedToTenant = true`) |

El panel Cliente usa `tenant(Empresa::class)`, por lo que el listado y los formularios de Áreas generales solo muestran y filtran por la empresa del tenant actual.

---

## 3. Tablas incluidas y relacionadas

### 3.1 Tabla principal del módulo

| Tabla | Descripción | Campos |
|-------|-------------|--------|
| **`areas_generales`** | Registro principal de cada área general. | `id`, `nombre`, `empresa_id`, `deleted_at`, `created_at`, `updated_at` |

- **`nombre`**: string, obligatorio. Nombre de la área general (ej. "Operaciones", "Ventas").
- **`empresa_id`**: FK a `empresas`. Obligatorio. Se asigna por tenant, no por selección del usuario.
- **`deleted_at`**: borrado lógico (SoftDeletes).

### 3.2 Tablas relacionadas

| Tabla | Uso en el módulo |
|-------|-------------------|
| **`areas`** | Cada área general puede tener muchas áreas (`Area.area_general_id` → `areas_generales.id`). En el listado se muestra la cantidad de áreas asociadas (`areas_count`). |
| **`empresas`** | La área general pertenece a una empresa. El valor `empresa_id` se toma del tenant activo y se envía como campo oculto. |

### 3.3 Relaciones del modelo AreaGeneral

- **`empresa()`**: `BelongsTo` → `Empresa`. Una área general pertenece a una empresa.
- **`areas()`**: `HasMany` → `Area`. Una área general tiene muchas áreas (campo `area_general_id` en `areas`).

---

## 4. Reglas de negocio

### 4.1 Empresa (tenant)

- **RN-1.** Toda área general pertenece a una sola empresa.
- **RN-2.** La empresa no es elegible por el usuario: se toma del tenant actual (`Filament::getTenant()?->id`) y se envía como campo oculto (`empresa_id`).
- **RN-3.** El listado y las rutas del recurso solo muestran/editan áreas generales de la empresa del tenant (por `isScopedToTenant` y scope de tenant en el panel).

### 4.2 Área general

- **RN-4.** Campo obligatorio: `nombre` (máx. 255 caracteres).
- **RN-5.** Las áreas generales usan borrado lógico (`SoftDeletes`). En el recurso se desactiva el scope de soft deletes en el binding para poder editar/ver registros en papelera.
- **RN-6.** Tras crear un registro, el usuario es redirigido al listado del recurso (`getRedirectUrl()` en `CreateAreaGeneral`).

### 4.3 Relación con Áreas

- **RN-7.** Una área general puede tener cero o muchas áreas. La tabla del listado muestra el conteo de áreas asociadas (`areas_count`) mediante `withCount('areas')`.
- **RN-8.** Al eliminar (soft delete o force delete) un área general, las áreas que la referencian pueden quedar con FK huérfana según la política de la base de datos; el modelo no impide el borrado. Para integridad referencial se recomienda no eliminar áreas generales que tengan áreas asociadas o manejar esa lógica en reglas o restricciones de BD.

### 4.4 Acciones en listado y edición

- **RN-9.** Por registro: Editar, Eliminar (soft delete), Forzar eliminar, Restaurar. Acciones masivas: Eliminar, Forzar eliminar, Restaurar. Filtro de registros en papelera (`TrashedFilter`).

---

## 5. Flujo de información

### 5.1 Listado (ListAreasGenerales)

1. El usuario accede al recurso Áreas generales en el panel Cliente con un tenant (Empresa) seleccionado.
2. Filament aplica el scope de tenant: solo se consultan `AreaGeneral` con `empresa_id` = tenant actual.
3. La consulta incluye `withCount('areas')` para mostrar cuántas áreas tiene cada área general.
4. La tabla muestra: id, nombre, cantidad de áreas (oculta por defecto), fechas de creación y actualización (ocultas por defecto).
5. Filtro de registros en papelera. Acciones por fila y masivas según RN-9.

### 5.2 Crear área general (CreateAreaGeneral)

1. Formulario con sección "Información del área general":
   - `empresa_id`: Hidden, valor por defecto = tenant actual.
   - `nombre`: obligatorio, máx. 255 caracteres.
2. Al guardar se crea el registro `AreaGeneral` con los datos del formulario.
3. Redirección al listado del recurso (index).

### 5.3 Editar área general (EditAreaGeneral)

1. Se carga el registro (incluidos los que están en papelera, por `getRecordRouteBindingEloquentQuery`).
2. El usuario modifica el nombre.
3. Al guardar se actualiza el registro. Acciones en cabecera: Eliminar, Forzar eliminar, Restaurar.

### 5.4 Eliminación

- Eliminar: soft delete del registro.
- Restaurar / Forzar eliminar: según acciones de la tabla o de la página de edición.

---

## 6. Componentes del módulo

| Componente | Ruta / Clase |
|------------|------------------|
| Recurso | `App\Filament\Cliente\Resources\AreasGenerales\AreaGeneralResource` |
| Formulario | `App\Filament\Cliente\Resources\AreasGenerales\Schemas\AreaGeneralForm` |
| Tabla | `App\Filament\Cliente\Resources\AreasGenerales\Tables\AreasGeneralesTable` |
| Páginas | `ListAreasGenerales`, `CreateAreaGeneral`, `EditAreaGeneral` |
| Modelos | `AreaGeneral`, `Area`, `Empresa` |

---

## 7. Relación con el módulo Áreas

- Las **Áreas generales** son el catálogo de categorías; las **Áreas** son el detalle que se clasifica bajo una área general.
- Orden recomendado de uso: crear primero las áreas generales de la empresa y después las áreas, asignando a cada área un área general de la misma empresa.
- El recurso de Áreas usa este módulo para rellenar el Select `area_general_id`, filtrando solo áreas generales del tenant actual.

---

## 8. Dependencias

- **Panel:** Cliente con `tenant(Empresa::class)` y middleware del panel (p. ej. `module:cliente`, `ScopeByCompany`).

Este documento describe el estado del módulo según el código y sirve como referencia para mantenimiento y ampliaciones.
