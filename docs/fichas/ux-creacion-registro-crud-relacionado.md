# Creación en contexto: registro de otro CRUD desde un formulario de catálogo

## 1. Resumen

Patrón de interfaz en el que, **dentro del formulario de alta (o edición) de un recurso A**, el usuario puede **crear al vuelo un registro del recurso B** (otro CRUD) cuando el campo representa una **relación o dependencia** hacia B (por ejemplo: categoría, proveedor, unidad de medida, tipo, etc.).

Se evita salir del flujo actual, abandonar el formulario o abrir otra pestaña: la creación de B ocurre en un **popup (modal)** y, al terminar con éxito, el dato nuevo queda **disponible** para el campo que lo referencia (p. ej. el desplegable se actualiza y selecciona el registro recién creado).

> **Nota:** Este documento describe el patrón a nivel **funcional y visual** para poder replicarlo en otro proyecto con las mismas tecnologías. El detalle de rutas, nombres de recursos y validaciones depende de cada dominio.

---

## 2. Objetivo de experiencia de usuario

| Objetivo | Descripción |
|----------|-------------|
| **Continuidad** | No forzar al usuario a guardar borrador, cambiar de pantalla o perder el contexto del formulario de A. |
| **Descubribilidad** | Dejar claro que “falta” un valor en B y que puede **crearlo aquí**, sin buscar el menú del CRUD B. |
| **Eficiencia** | Reducir clics e idas y venidas entre listados y formularios. |
| **Coherencia** | El modal reutiliza las mismas reglas de negocio y validación que el formulario “completo” de creación de B (o un subconjunto acotado de campos). |

---

## 3. Comportamiento funcional

### 3.1 Dónde aparece

- En formularios de **creación** (y a veces **edición**) de catálogos o entidades que tienen campos de **selección** o **referencia** a otra entidad.
- Típicamente asociado a:
  - un `<select>` / combo,
  - un autocompletado,
  - o un bloque etiqueta + control donde el valor proviene de la tabla B.

No es obligatorio en todos los campos: solo donde el producto decide que la creación rápida aporta valor (relaciones “maestras” o catálogos que cambian con frecuencia).

### 3.2 Acción secundaria bajo el control

- Debajo del **input o del control principal** (no sustituye la etiqueta superior), se muestra una **línea de ayuda accionable**.
- Al **pulsar** esa línea:
  - se abre un **popup centrado** (modal),
  - el formulario padre permanece en segundo plano (en muchos casos con **overlay** que bloquea la interacción hasta cerrar el modal).

### 3.3 Contenido del popup

- Título claro del recurso B que se va a crear (p. ej. “Nueva categoría”, “Alta de proveedor”).
- Formulario de creación de B (campos mínimos necesarios o el mismo formulario reducido que en la ruta dedicada del CRUD B).
- Acciones típicas: **Cancelar** (cierra sin guardar), **Guardar** / **Crear** (envía y cierra si va bien).

### 3.4 Tras guardar con éxito

Comportamiento esperado (alineado con buenas prácticas):

1. Cerrar el modal.
2. **Refrescar** las opciones del campo en A (recarga de datos vía API/Inertia o invalidación de caché local).
3. **Seleccionar automáticamente** el nuevo registro de B en el campo que originó la acción.
4. Opcional: mensaje breve de confirmación (toast) del estilo “Registro creado”.

### 3.5 Cancelación y errores

- **Cancelar** o cerrar el modal: no se modifica A ni B; el campo en A conserva su valor previo.
- **Error de validación en B**: el modal permanece abierto; mensajes de error en el propio formulario del modal.
- **Error de red / servidor**: mensaje de error visible; el usuario puede reintentar sin perder el formulario padre.

### 3.6 Accesibilidad

- El modal debe ser **enfocable** con teclado, tener **título** asociado al diálogo y permitir **Escape** para cerrar (si el diseño lo permite).
- El texto debajo del campo debe ser un **botón** o **enlace** semántico (no solo texto pintado de azul sin rol interactivo).

---

## 4. Presentación visual

### 4.1 Jerarquía en pantalla

```
┌─────────────────────────────────────────┐
│  Etiqueta del campo (ej. Categoría)     │
│  ┌───────────────────────────────────┐   │
│  │  [ Select / input principal    ▼ ]│   │
│  └───────────────────────────────────┘   │
│  Crear nueva categoría…                   │  ← línea secundaria, estilo “enlace”
└─────────────────────────────────────────┘
```

- La **frase accionable** va **inmediatamente debajo** del control principal, con **menor peso visual** que la etiqueta del campo.
- No debe competir con el botón principal de envío del formulario de A (sigue siendo una **acción secundaria** contextual al campo).

### 4.2 Estilo de la “frasecita” en azul

Intención visual habitual:

- Color **acento / primario** del tema (en interfaces tipo shadcn/Tailwind suele ser `text-primary`, que en tema claro se percibe como azul si el primary está definido así).
- Tamaño de texto **ligeramente menor** que el del input (p. ej. `text-sm`).
- Comportamiento de **enlace**: subrayado al hover (`underline` / `underline-offset`) para indicar que es clicable.
- No debe parecer un mensaje de error (evitar rojo); no debe parecer texto deshabilitado (evitar gris muy apagado sin contraste).

Ejemplo de clases coherentes con un stack React + Tailwind + botón variante “link”:

- `text-primary text-sm underline-offset-4 hover:underline` (equivalente conceptual a `variant="link"` en componentes tipo shadcn).

### 4.3 Popup (modal)

- **Overlay** semitransparente sobre el formulario de A.
- **Panel** centrado, ancho acorde al formulario de B (a menudo `max-w-md` o `max-w-lg`).
- **Cabecera** con título y a veces descripción breve.
- **Cuerpo** con scroll si hay muchos campos.
- **Pie** con botones alineados a la derecha (Cancelar + Crear) o según el diseño del sistema.

---

## 5. Integración técnica (referencia para mismo stack)

Sin acoplar a un solo archivo del repo, el patrón encaja con:

| Capa | Rol |
|------|-----|
| **Frontend** | Estado `open` del modal; componente `Dialog` (p. ej. Radix UI); formulario hijo que POSTea la creación de B. |
| **Backend** | Endpoint de store de B reutilizable desde la pantalla completa y desde el modal (misma validación). |
| **Inertia** | Tras éxito, `router.reload({ only: [...] })` o respuesta con datos del nuevo B para actualizar opciones y `setData` del campo en A. |
| **API JSON** | Alternativa: `POST` a recurso B + respuesta con el objeto creado; el cliente actualiza opciones y selección. |

Nombre informal del patrón en literatura y equipos: **quick create**, **inline related create** o **nested create from field**.

---

## 6. Cuándo usarlo y cuándo no

**Tiene sentido** cuando B es un catálogo pequeño o medio, se crea con pocos campos y el usuario a menudo descubre que “aún no existe” la opción mientras completa A.

**Conviene evitarlo** cuando la creación de B es muy compleja (muchas pestañas, adjuntos, flujos legales), o cuando la política del producto exige que todo alta de B pase por una pantalla dedicada con checklist y trazabilidad.

---

## 7. Checklist de implementación en el otro proyecto

- [ ] Reutilizar la misma validación y políticas de autorización que el alta estándar de B.
- [ ] Tras éxito, actualizar opciones del campo y preseleccionar el nuevo registro.
- [ ] Mantener el formulario de A intacto (no resetear salvo que sea intencional).
- [ ] Texto accionable con aspecto de enlace primario y foco visible por teclado.
- [ ] Modal accesible (título, foco atrapado o gestión de foco al abrir/cerrar).

Con esto se puede replicar el mismo comportamiento y la misma lectura visual en el otro proyecto con tecnologías equivalentes (Laravel, Inertia, React, librería de diálogos y sistema de diseño con color primario para el enlace).
