# Guía de pruebas manuales para PO — Catálogos y módulos vinculados

Documento orientado a **Product Owner** para validar funcionalidad básica y **UX** tras QA, alineado con las fichas en `docs/fichas/`. Las pruebas se ejecutan en el **panel Admin** (`/admin`, login WorkOS), salvo que se indique lo contrario.

**Cómo usar esta guía**

- Marca cada ítem: **OK** / **Fallo** / **N/A** y anota observaciones.
- Prioriza primero los **flujos felices** (crear → listar → ver → editar) y luego validaciones y casos límite.
- **Nota sobre legacy vs Core:** las fichas describen el legacy Paco; en Tecben-Core puede haber mejoras (por ejemplo, restricción al borrar centros de costo con empresas asignadas). Si el comportamiento difiere de la ficha pero es **más seguro**, documéntalo como decisión de producto, no como bug automático.

---

## Prerrequisitos generales

| Ítem | Verificación |
|------|----------------|
| Acceso | Usuario con permisos de super admin o permisos Filament equivalentes a listar/crear/editar/borrar cada recurso. |
| Datos | Al menos una empresa de prueba, catálogos mínimos (industrias, productos si aplica, departamentos para felicitaciones, etc.). |
| Navegación | Cada módulo aparece en el menú lateral con etiqueta coherente; sin errores 403 inesperados. |
| UX global | Mensajes de éxito/error legibles; formularios con etiquetas en español; acciones destructivas con confirmación cuando aplique. |

---

## 1. Centros de costo

**Ficha:** `docs/fichas/ficha-modulo-centro-costos.md`  
**Contexto:** Catálogo global. La asignación a empresas se gestiona desde **Empresas** (no desde este CRUD). Servicios: **BELVO**, **EMIDA**, **STP**.

### 1.1 Funcional — flujo feliz

1. **Listado:** Abre el recurso; comprueba columnas útiles (servicio, nombre, acciones). Ordenación y búsqueda si existen.
2. **Crear BELVO:** Nombre, `key_id`, `secret_key` (o equivalentes en UI). Guardar; el registro aparece en listado.
3. **Crear EMIDA:** Nombre + terminales y clerks de recargas y de pago de servicios (cuatro pares de campos según ficha). Guardar.
4. **Crear STP:** Nombre + cuenta bancaria **numérica**. Guardar.
5. **Ver:** Abre detalle; datos coherentes con lo capturado.
6. **Editar:** Modifica campos permitidos; el tipo de servicio no debería cambiarse arbitrariamente sin control (según implementación).

### 1.2 Validaciones y negativas

| Caso | Resultado esperado (según ficha) |
|------|-----------------------------------|
| Servicio inválido (si se pudiera forzar) | Rechazo; mensaje claro (legacy: "Tipo de servicio NO Disponible"). |
| BELVO sin claves o nombre | No guardar; errores por campo. |
| EMIDA sin algún terminal/clerk obligatorio | No guardar. |
| STP con cuenta no numérica o vacía | No guardar. |
| Campos no usados por el servicio | Pueden quedar vacíos/null en otros tipos (comportamiento legacy). |

### 1.3 Eliminación y vínculo con empresas

- Intenta **eliminar** un centro **sin** asignar a ninguna empresa: debe permitirse (hard delete en legacy).
- En **Tecben-Core**, si existe protección: al estar asignado a una empresa, **no** debe eliminarse y debe mostrarse mensaje explícito.
- **UX:** Si `secret_key` se muestra en vista detalle, valora riesgo (la ficha legacy señala que exponer el secreto en claro es problema de seguridad/UX).

### 1.4 UX / criterios PO

- El formulario **muestra solo los campos del servicio seleccionado** (o equivalente claro), sin saturar al usuario.
- Tras guardar, feedback visible (notificación o redirect con mensaje).
- Coherencia de nombres de campo con negocio (BELVO/EMIDA/STP).

---

## 2. Bancos

**Ficha:** `docs/fichas/ficha-modulo-banco.md`  
**Contexto:** Catálogo **global**. Comisión asociada a intentos de cobro; eliminación por **soft delete** en legacy.

### 2.1 Funcional — flujo feliz

1. **Listado:** Bancos visibles; acciones Ver / Editar / Eliminar (según permisos).
2. **Crear:** Nombre, código, comisión — todos obligatorios; comisión numérica.
3. **Ver:** Confirma que se muestran los datos relevantes (la ficha legacy indica que la vista no mostraba comisión ni IVA; en Core verifica si es intencional mostrar comisión en detalle).
4. **Editar:** Actualiza y persiste.
5. **Eliminar:** Banco **sin** cuentas asociadas → debe eliminarse (soft delete en legacy).

### 2.2 Validaciones y negativas

| Caso | Esperado |
|------|----------|
| Nombre, código o comisión vacíos | Validación; no guardar. |
| Comisión no numérica | Validación. |
| Código con caracteres no numéricos | En legacy podía comportarse mal; en Core debe validarse o rechazarse de forma clara. |
| Duplicados nombre/código | Legacy permitía duplicados; decide si en producto es aceptable o se requiere unicidad. |

### 2.3 Eliminación con uso

- Con una **cuenta de nómina** o **cuenta bancaria** que use ese banco (según modelo Core), **no** debe poder borrarse; mensaje comprensible (legacy: "No puede borrar un banco con registros asignados." / Core puede variar el texto).
- Tras soft delete, el banco no debe aparecer en listados normales.

### 2.4 UX / criterios PO

- Etiqueta de comisión alineada con negocio ("Comisión por intentos de cobro" o equivalente).
- Si existe cálculo de IVA sobre comisión (`bank_fee_vat`), define si el PO debe verlo en UI o solo en informes.

---

## 3. Estado de ánimo — Afecciones

**Ficha:** `docs/fichas/ficha-modulo-estado-animo-afeccion.md`  
**Contexto:** Catálogo global; un solo campo principal **nombre**. Relación N:M con registros de estado de ánimo en app.

### 3.1 Funcional — flujo feliz

1. Listado con paginación/búsqueda si aplica.
2. Crear afección con nombre válido.
3. Ver y editar nombre.
4. Eliminar afección **sin** moods asociados → eliminación física (legacy).

### 3.2 Validaciones y negativas

| Caso | Esperado |
|------|----------|
| Nombre vacío | No guardar; mensaje claro. |
| Eliminar con registros de estado de ánimo vinculados | **No** permitir; mensaje tipo "No puede borrar una afección con registros asignados." |

### 3.3 UX / permisos

- Comparte permisos con **Características** en legacy (`view_moods`, etc.); en Core verifica que el menú y las acciones sean coherentes para ambos submódulos.
- Duplicados de nombre: legacy permitía; decide criterio de producto.

---

## 4. Estado de ánimo — Características

**Ficha:** `docs/fichas/ficha-modulo-estado-animo-caracteristica.md`  
**Contexto:** Nombre obligatorio; **lista inicial** opcional: Normal, Mal, Muy mal, Bien, Muy bien (o vacío → "SIN ASIGNAR" en listados legacy).

### 4.1 Funcional — flujo feliz

1. Crear característica solo con nombre (sin lista inicial) y comprobar que en listado/vista se entiende el estado "sin asignar".
2. Crear/editar con **una** lista inicial seleccionada (máximo una opción en legacy).
3. Editar quitando la lista inicial (si la UI lo permite) → debe guardarse como null.
4. Eliminar sin moods asociados.

### 4.2 Validaciones y negativas

| Caso | Esperado |
|------|----------|
| Nombre vacío | No guardar. |
| Eliminar con moods asociados | Bloqueo con mensaje claro. |

### 4.3 UX / criterios PO

- Listado muestra columna "Lista inicial" con texto humano (no solo códigos `bad`, `well`, etc.).
- Búsqueda/filtro por lista inicial funciona sin confundir al usuario.
- Revisa títulos de pestañas/secciones (la ficha menciona texto erróneo "Área General" en legacy).

---

## 5. Productos

**Ficha:** `docs/fichas/ficha-modulo-producto.md`  
**Contexto:** Catálogo global: nombre y descripción obligatorios; soft delete; precios/asignación por empresa en otros flujos.

### 5.1 Funcional — flujo feliz

1. Crear producto con nombre y descripción.
2. Listar, ver, editar.
3. Eliminar producto **sin** empresas, **sin** empleados/colaboradores en pivot, **sin** filtros de segmentación asociados.

### 5.2 Validaciones y negativas

| Caso | Esperado |
|------|----------|
| Nombre o descripción vacíos | No guardar. |
| Eliminar con empresa, empleado o filtro vinculado | **No** permitir; mensaje "No puede borrar un producto con registros asignados." (o equivalente). |

### 5.3 Integración con Empresas

- En **alta de empresa** suele exigirse al menos un producto en catálogo (legacy); verifica flujo real en Core al crear empresa nueva.

### 5.4 UX / criterios PO

- Descripción con área de texto adecuada (longText).
- Mensajes de éxito sin errores tipográficos (legacy tenía "actualizada" para producto masculino).

---

## 6. Reconocimientos

**Ficha:** `docs/fichas/ficha-modulo-reconocimientos.md`  
**Contexto:** Catálogo con nombre, descripción, **menciones necesarias**, **enviable / no enviable**, **exclusivo / no exclusivo**; relación N:M con empresas (pivot: enviable y menciones por empresa); imágenes inicial/final opcionales; **reconocimientos enviados** y export Excel en legacy.

### 6.1 Funcional — CRUD catálogo

1. **Crear** con todos los campos obligatorios (nombre, descripción, cantidad de menciones).
2. **Exclusivo:** seleccionar empresas concretas; verificar que solo esas queden vinculadas.
3. **No exclusivo:** verificar que se asignen **todas** las empresas (comportamiento legacy al crear/actualizar).
4. **Imágenes:** subir formatos permitidos (jpg, jpeg, png, bmp) dentro del límite de tamaño (legacy 20 MB); ver sin roturas.
5. **Ver / Editar:** datos y relación con empresas coherentes.

### 6.2 Gestión por empresa (si existe en Core)

- Desde el reconocimiento, flujo **"Ver empresas"** / relation manager: listado por empresa con menciones y enviable.
- **Editar por empresa:** sobrescribir `necessary_mentions` y enviable solo para esa empresa; comprobar persistencia.

### 6.3 Eliminación

- No eliminar si hay empresas vinculadas o envíos (`acknowledgment_shippings`); mensaje claro.
- Soft delete en legacy.

### 6.4 Reconocimientos enviados (si está implementado)

- Listado con filtros (empresa, fechas, remitente, destinatario, categoría).
- Export a Excel si aplica.
- **PO:** Si el módulo no existe aún en Core, documéntalo como **hueco funcional** frente a legacy.

### 6.5 UX / criterios PO

- Etiquetas claras para ENVIABLE / NO ENVIABLE y EXCLUSIVO / NO EXCLUSIVO.
- Ayuda contextual sobre qué significa "menciones necesarias" para el usuario administrador.

---

## 7. Felicitaciones (mensajes personalizados)

**Ficha:** `docs/fichas/ficha-modulo-felicitacion.md`  
**Contexto:** Plantillas por empresa; tipos **CUMPLEAÑOS** y **ANIVERSARIO**; placeholders `[Nombre]`, `[Apellido paterno]`, `[Apellido materno]`, `[Empresa]`; remitente obligatorio; importancia; departamento opcional; logo opcional.

### 7.1 Funcional — flujo feliz

1. **Listado:** Si el usuario está acotado a empresa, solo plantillas de su empresa; super admin ve todas (validar scope en Core frente a bugs legacy del `company_id`).
2. **Crear:** Título, tipo, cuerpo con **al menos un** placeholder de destinatario (nombre o apellidos), remitente, empresa (si admin).
3. **Departamento opcional:** Crear con y sin departamento; documentar impacto esperado (solo empleados de ese depto al ejecutar job de envío).
4. **Logo:** Subir imagen válida (legacy 5 MB); ver miniatura o nombre de archivo si aplica.
5. **Editar / eliminar** plantilla propia (scope por empresa para usuarios no admin — **verificar que Core no permita editar por ID ajenos**).

### 7.2 Validaciones y negativas

| Caso | Esperado |
|------|----------|
| Sin placeholder de destinatario | Error claro ("Debe haber al menos un dato del destinatario" o similar). |
| Sin empresa válida | Error de asignación. |
| Título o mensaje vacíos | No guardar. |
| Logo formato/tamaño inválido | Validación explícita. |

### 7.3 Integración con Empresas

- En **Empresas**, toggle **notificaciones de felicitaciones** y segmento **COMPANY / LOCATION** (ficha empresas §2.7): al activar, debe mostrarse el selector; al desactivar, no quedar valores huérfanos confusos.

### 7.4 Jobs / entrega (fuera de UI pero criterio de negocio)

- Si tienes entorno con comandos programados (`send:birthday` equivalente), prueba un día con empleado de prueba que cumpla fecha; si no, deja como **prueba pendiente** documentada.

### 7.5 UX / criterios PO

- Asistente o botones para insertar placeholders en el cuerpo del mensaje.
- Vista previa del mensaje (legacy la tenía).
- Textos de tipo CUMPLEAÑOS / ANIVERSARIO visibles y sin ambigüedad.

---

## 8. Empresas

**Ficha:** `docs/fichas/ficha-modulo-empresas.md`  
**Contexto:** Formulario amplio en Filament Admin: datos de contacto, comisiones (incl. MIXED con rangos), retenciones, quincena personalizada, finiquito, encuesta de salida, notificaciones felicitaciones, app, industria/subindustria, razones sociales, **productos**, **centros de costo** (Belvo, Emida, STP), notificaciones incluidas, reconocimientos en alta, temas de voz, alias de transacciones, archivos.

### 8.1 Smoke test de creación (mínimo viable)

Completa solo lo **obligatorio** del formulario según ficha §3 y guarda:

- Datos de contacto y facturación requeridos.
- Fechas de contrato (fin ≥ inicio).
- `tipo_comision` y campos de comisión o repeater MIXED según caso.
- IDs de app Android/iOS y `num_usuarios_reportes`.
- Al menos una razón social en repeater con datos válidos (RFC 12 caracteres, CP 5 dígitos, etc.).
- Al menos un **producto** con campo **desde** (meses).
- **Centros de costo:** uno por servicio si el negocio lo exige en alta.

Comprueba: registro creado, sin error 500, redirección o notificación coherente.

### 8.2 Variantes condicionales (checklist)

| Toggle / condición | Qué probar |
|--------------------|------------|
| `tipo_comision` = MIXED | Aparece repeater de rangos; "hasta" > "desde"; comisiones fijas en 0 en BD según servicio. |
| `permitir_retenciones` | Grupo visible; al desactivar en edición, se limpian configuraciones asociadas. |
| `tiene_pagos_catorcenales` | Fecha próximo pago; validación de fecha mínima (mañana). |
| `tiene_quincena_personalizada` | Día inicio/fin required; fin > inicio. |
| `activar_finiquito` | URL finiquito required y formato URL. |
| `permitir_encuesta_salida` | Al menos una razón seleccionada. |
| `permitir_notificaciones_felicitaciones` | Selector COMPANY / LOCATION. |
| `aplicacion_compilada` | Nombre app y link (opcionales en form según ficha). |
| Industria | Al cambiar industria, subindustria se resetea y opciones filtradas. |

### 8.3 Archivos

- **Foto:** jpg/png/bmp, límite según validación (hasta 20 MB en ficha).
- **Logo:** imagen, límite 5 MB.
- **Documentos:** PDF múltiples; rutas bajo `assets/companies/files/{id}/`.

Verifica previsualización, mensajes de error y que no se pierdan archivos previos al editar sin tocarlos.

### 8.4 Relaciones críticas

- **Productos:** repeater con `producto_id` y `desde`; tras guardar, aparecen en segmentación/gestión si aplica.
- **Centros de costo:** tres selects (Belvo, Emida, STP) alineados con catálogo de centros de costo.
- **Reconocimientos no exclusivos:** en **create**, se adjuntan automáticamente (ficha §6.1 paso 10); verifica en relation manager o BD de prueba.

### 8.5 Edición

- Cambiar un toggle de activado a desactivado y confirmar que datos derivados se limpian (retenciones, quincena, razones encuesta, etc., según ficha §6.2).
- No eliminar razón social usada sin proceso acordado (el código puede tener TODO sobre empleados afectados).

### 8.6 UX / criterios PO

- Formulario largo: uso de **secciones colapsables**, progreso claro, validación inline sin perder datos.
- Tiempos de guardado aceptables con muchos repeaters.
- Mensajes de validación en español y alineados con etiquetas del formulario.

---

## Matriz rápida de dependencias entre módulos

| Módulo | Depende de / alimenta a |
|--------|-------------------------|
| Centros de costo | Empresas (asignación); caches/selects por servicio. |
| Bancos | Cuentas de nómina / cuentas bancarias. |
| Productos | Empresas (pivot productos); segmentación. |
| Empresas | Productos, centros de costo, reconocimientos, felicitaciones (config notificaciones), industrias, razones sociales. |
| Reconocimientos | Empresas; app móvil para envíos. |
| Felicitaciones | Empresa, departamentos, usuarios remitentes; config empresa. |
| Estado de ánimo (afecciones/características) | Registros de ánimo en app; frecuencia notificaciones en empresa (tabla `frecuencia_notificaciones` según ficha empresas). |

---

## Plantilla de registro de sesión de prueba

```
Fecha:
Entorno (URL):
Usuario / rol:

Módulo:
Casos ejecutados: ___ / ___
Fallos:
  - ID / descripción / pasos / severidad
Observaciones UX:
Decisiones producto pendientes:
```

---

*Basado en las fichas: centro-costos, banco, estado-ánimo-afección, estado-ánimo-característica, producto, reconocimientos, felicitación, empresas. Ajustar ítems si el roadmap excluye subflujos (p. ej. export Excel de reconocimientos enviados).*
