# Instrucciones: prototipo de Roles y Permisos (solo frontend, datos mock)

Este documento describe **cómo implementar** en otro proyecto las pantallas de **roles** y **permisos** como **prototipo**: interfaz completa, **sin persistencia en servidor**, usando **datos mock** en el cliente. Asume un stack equivalente al del kit Laravel + Inertia + React (o React con el mismo enfoque de layout y componentes).

---

## 1. Objetivo y alcance

| Qué incluye | Qué excluye (en prototipo) |
|-------------|----------------------------|
| Listado de roles, listado de permisos, edición de un rol con asignación de permisos (p. ej. checkboxes por grupo) | API real, base de datos, policies, seeders de producción |
| Navegación hacia esas pantallas | Reglas de negocio definitivas o sincronización con backend |
| Feedback visual al “guardar” (p. ej. toast) que deja claro que es simulado | Autorización real en rutas o acciones |

---

## 2. Estructura de datos mock (recomendada)

Definir en un módulo estático (por ejemplo `data/mock-rbac.ts` o `mocks/rbac.ts`) tipos y datos iniciales coherentes entre pantallas.

**Permiso**

- `id`: string estable (p. ej. `users.view`).
- `name` o clave técnica: mismo valor o redundante según prefieras.
- `label`: texto para UI.
- `group`: string para agrupar en la interfaz (p. ej. `Usuarios`, `Reportes`, `Catálogos`).

**Rol**

- `id`: string (UUID o entero como string).
- `name`: nombre del rol.
- `description`: breve descripción para la tabla y el formulario.
- `userCount`: número mock para mostrar en listado (opcional).
- `permissionIds`: array de `id` de permisos asignados a ese rol.

Los permisos mock deben ser la **fuente única de verdad** para el catálogo de permisos; los roles solo referencian ids.

---

## 3. Pantallas a crear

### 3.1 Roles — índice

- Tabla o lista con columnas mínimas: nombre, descripción, usuarios (si usas `userCount`), acción “Editar”.
- Enlace a la pantalla de edición del rol (ver rutas abajo).
- Opcional: botón “Nuevo rol” que, en prototipo, puede abrir el mismo formulario con estado vacío y un `id` temporal generado en cliente, o mostrar un toast “No implementado en prototipo”; lo mínimo viable es **solo edición** de roles ya presentes en mock.

### 3.2 Roles — edición (asignación de permisos)

- Campos de texto para nombre y descripción del rol (estado local inicializado desde mock según el rol seleccionado).
- Permisos agrupados por `group`: subtítulo por grupo y lista de checkboxes (uno por permiso).
- Estado: `Set` o array de ids seleccionados; al montar la página, copiar los `permissionIds` del rol mock correspondiente.
- Botón “Guardar”: en prototipo, actualiza solo el estado de React (y opcionalmente una copia en memoria del mock si quieres que el índice refleje cambios hasta recargar). Mostrar un toast del estilo: **“Cambios guardados (solo prototipo)”** para no confundir con persistencia real.
- Si el `id` del rol no existe en mock, mostrar mensaje claro (“Rol no encontrado”) y enlace de vuelta al índice.

### 3.3 Permisos — índice

- Vista de **catálogo de permisos**: lista o tabla agrupada por `group`.
- Para cada permiso: etiqueta amigable (`label`) y, opcionalmente, la clave técnica en tipografía secundaria.
- Solo lectura; sirve para revisar el vocabulario del sistema en el prototipo.

---

## 4. Rutas y enlace con el backend

**Opción A — Inertia con rutas declarativas (sin lógica servidor)**

- Registrar rutas protegidas por el mismo middleware que el resto del panel (`auth`, etc.) que solo rendericen el nombre del componente de página, **sin** pasar props desde PHP (todo mock en el cliente).
- Ejemplos de paths coherentes:
  - `GET /prototype/roles` → componente `prototype/roles/index`
  - `GET /prototype/permissions` → componente `prototype/permissions/index`
  - `GET /prototype/roles/edit?role={id}` → componente `prototype/roles/edit` (el `id` se lee en el cliente con `URLSearchParams` a partir de `usePage().url` o `window.location`).

**Opción B — SPA sin Inertia**

- Definir las mismas rutas en el router del frontend y usar los mismos componentes; los datos mock se importan igual.

La opción con **query `?role=`** evita depender de parámetros de ruta que el servidor deba inyectar como props en Inertia.

---

## 5. Navegación lateral

- Añadir entradas en el menú (sidebar) bajo una sección explícita **“Prototipo”** o **“RBAC (demo)”** para no mezclarlas con funciones productivas.
- Enlaces: índice de roles e índice de permisos.
- Resaltar activo según la misma lógica que el resto del proyecto (`pathname` actual vs `href`).

---

## 6. Componentes de UI

Reutilizar el sistema de diseño del proyecto (cards, tablas con utilidades Tailwind, checkboxes, labels, separadores entre grupos de permisos). No es obligatorio un componente `Table` si el proyecto solo tiene primitivos: una `<table>` con clases de borde y tipografía del tema es suficiente para prototipo.

**Layout y breadcrumbs**

- Misma plantilla de aplicación que el dashboard (cabecera con breadcrumbs: p. ej. `Dashboard` → `Roles` → `Editar rol`).

---

## 7. Accesibilidad mínima

- Cada checkbox asociado a su etiqueta con `<label>` (o `aria-labelledby`).
- Títulos de página (`<title>` vía el helper de metadatos que use el proyecto, p. ej. `Head` de Inertia).
- Enlaces con texto descriptivo (“Editar”, “Volver a roles”).

---

## 8. Qué dejar documentado en el repo destino

- Que las pantallas son **prototipo** y los datos **no** se persisten.
- Ruta base (`/prototype/...`) para poder eliminar o aislar fácilmente antes de producción.
- Si más adelante se conecta a API: sustituir el módulo mock por llamadas `fetch`/Inertia y mapear la misma forma de `Role` y `Permission` en el cliente.

---

## 9. Orden de implementación sugerido

1. Módulo mock con tipos, permisos y roles.
2. Página de permisos (solo lectura): valida agrupación y copy.
3. Índice de roles con enlaces a edición.
4. Edición de rol con checkboxes y toast de guardado simulado.
5. Entradas de menú y breadcrumbs.

Con esto puedes replicar el prototipo en el otro proyecto **solo con documentación y criterios claros**, sin acoplar nombres de archivos a un repositorio concreto.
