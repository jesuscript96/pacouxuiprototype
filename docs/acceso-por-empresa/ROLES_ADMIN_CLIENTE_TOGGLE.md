# Gestión de roles: solo Admin, toggle empresa y permisos por panel

## Paso 1: RoleResource fuera del panel Cliente

**Código actual (antes):**
```php
->resources([
    \App\Filament\Resources\Shield\RoleResource::class,
])
```

**Cambios aplicados:**

1. **`->resources([])`** – ya no se registra ningún recurso de roles en el panel.
2. **Quitar `FilamentShieldPlugin` del array de plugins del panel Cliente.**  
   El plugin de Shield en `register()` añade `RoleResource::class` a cualquier panel donde `Utils::isResourcePublished($panel)` sea false. Al vaciar `resources([])`, la lista de recursos del panel ya no contenía `\RoleResource`, así que el plugin volvía a registrar su RoleResource en Cliente. Con tenancy activo (`tenant(Empresa::class)`), Filament aplica tenancy a todos los recursos del panel; al estar RoleResource (modelo SpatieRole) sin relación `empresa`, se producía la LogicException. Al no cargar el plugin en el panel Cliente, no se registra RoleResource ahí y el error desaparece. El User sigue usando `HasPanelShield` y `canAccessPanel` (a nivel de modelo), no depende del plugin en el panel.

---

## Paso 2: Formulario actual del RoleResource (Admin)

El `RoleResource` en `app/Filament/Resources/Shield/RoleResource.php` ya tenía:

1. **Toggle existente:** `is_asignable` (label "Asignar a empresa"), solo visible para super_admin, con `->live()` y `afterStateUpdated` que pone `company_id` en null cuando se desactiva.
2. **Select empresa:** `company_id`, requerido cuando el toggle está ON, deshabilitado cuando está OFF.
3. **Permisos:** `getSelectAllFormComponent()` y `getShieldFormComponents()` del trait (pestañas Resources, Pages, Widgets, Custom del **panel actual** = Admin).

Shield lista los permisos con **CheckboxList** por recurso (o una vista simple si está configurado), agrupados por pestañas. Los nombres de los campos son el FQCN del recurso (ej. `App\Filament\Resources\Usuarios\UsuarioResource`).

**Cambios realizados:**

- Se mantiene el toggle `is_asignable` y el `company_id` (solo para super_admin).
- **Toggle OFF:** se muestra la sección de permisos estándar de Shield (recursos/páginas/widgets/custom del panel Admin), con `->visible(fn ($get) => ! (bool) $get('is_asignable'))`.
- **Toggle ON:** se muestra una sección nueva "Permisos (panel Cliente)" que solo incluye los **recursos del panel Cliente** (obtenidos por descubrimiento desde `Filament::getPanel('cliente')->getResources()`), excluyendo RoleResource. Sin pestañas de Role, pages ni widgets del Cliente.
- En Create/Edit se filtran los permisos a guardar: si `is_asignable` es true solo se toman los que vienen de campos cuyo nombre contiene `Filament\Cliente\Resources`; si es false, solo los que no lo contienen.

---

## Paso 3: Filtrar permisos por panel – opción elegida

**Opción recomendada: descubrimiento por panel (equivalente a Opción C).**

- **Admin (toggle OFF):** se usa `getShieldFormComponents()` tal cual; Shield sigue usando `Filament::getResources()` del panel actual (Admin), así que se muestran solo recursos del Admin.
- **Cliente (toggle ON):** se obtienen los recursos con `Filament::getPanel('cliente')->getResources()`, se excluye RoleResource y se construye la misma estructura de permisos (mismo formato de nombre y métodos de policy que en config) para cada recurso.

**Ventajas:**

- No hace falta mantener a mano listas de modelos ni namespaces en config.
- Si mañana se agrega un recurso nuevo al panel Cliente (ej. CartaSuaResource), sus permisos aparecen solos en la sección "Asignar a empresa" sin tocar el RoleResource.
- Reutiliza la misma convención de permisos (separator, case, policy methods) que Shield.

**Implementación:** `getClientePanelResourcesTransformed()` y `getClientePanelPermissionsTabs()` en `RoleResource`, usando `Filament::getPanel('cliente')->getResources()` y el config de Shield para construir las keys de permisos.

---

## Paso 4: Permisos Role fuera de roles del panel Cliente

- En **ShieldPanelClienteSeeder** se dejaron de crear y asignar permisos `ViewAny:Role`, etc. Los permisos del panel Cliente son solo los de Departamento y DepartamentoGeneral.
- Los roles `gestor_catalogos`, `consultor_catalogos`, `admin_empresa` y `rh_empresa` ya no tienen permisos de Role; la gestión de roles es exclusiva del super_admin en el panel Admin.
- Al editar/crear un rol con "Asignar a empresa" activado, la sección de permisos que se muestra es solo la de recursos del panel Cliente (sin Role).

---

## Paso 5: Verificación

- **Panel Cliente:** en el menú no debe aparecer ninguna sección de Roles.
- **Panel Admin, toggle OFF:** al crear/editar rol sin "Asignar a empresa" se muestran las pestañas de permisos del panel Admin (recursos, páginas, widgets, custom).
- **Panel Admin, toggle ON:** al activar "Asignar a empresa" y elegir empresa, la sección de permisos pasa a ser "Recursos (panel Cliente)" con solo los recursos del panel Cliente (p. ej. Departamento, DepartamentoGeneral), sin Role.
- Flujo: crear rol con empresa → asignar a usuario tipo admin → login en `/cliente` → el usuario solo ve los módulos según los permisos de ese rol (Dashboard, Departamentos, Departamentos generales, etc.).

## Comando shield:generate-cliente

Para generar **solo permisos** de los recursos del panel Cliente (tras agregar un nuevo recurso), sin tocar Admin ni crear roles:

```bash
php artisan shield:generate-cliente
```

Idempotente; formato de permisos `Acción:Modelo` (PascalCase), guard `web`. Ver `docs/acceso-por-empresa/AGREGAR_MODULO_PANEL_CLIENTE.md` para el procedimiento completo al agregar un nuevo módulo al panel Cliente.
