# Módulo Empresas — Descripción y reglas de negocio

Documento generado a partir del análisis del resource **Empresas**, el **EmpresaService** y el formulario **EmpresaForm**. Incluye todas las variantes del módulo, validaciones dependientes, tablas afectadas y carpetas de archivos.

---

## 1. Descripción del módulo

El módulo **Empresas** permite crear y editar empresas (clientes/tenants) en el panel **Admin**. Cada empresa tiene datos de contacto, configuración de comisiones, productos, centros de costo, razones sociales, notificaciones, retenciones, encuesta de salida, alias de transacciones y archivos (foto, logo, documentos/contratos).

- **Panel:** Admin (`/admin`)
- **Resource:** `App\Filament\Resources\Empresas\EmpresaResource`
- **Modelo:** `App\Models\Empresa`
- **Servicio:** `App\Services\EmpresaService`
- **Formulario:** `App\Filament\Resources\Empresas\Schemas\EmpresaForm`
- **Páginas:** ListEmpresas, CreateEmpresa, EditEmpresa

---

## 2. Reglas de negocio y variantes (Toggles / campos condicionales)

A continuación se listan **todas** las variantes: qué se muestra o exige cuando un toggle o un valor concreto está activo.

### 2.1 Tipo de comisión (`tipo_comision`)

| Valor              | Comportamiento |
|--------------------|----------------|
| `PERCENTAGE`       | Se muestran y son **obligatorios**: `comision_semanal`, `comision_bisemanal`, `comision_quincenal`, `comision_mensual`, `comision_gateway`. No se usa repeater de rangos. |
| `FIXED_AMOUNT`     | Igual que PERCENTAGE: mismos 5 campos de comisión obligatorios. |
| `MIXED`            | Se ocultan los 5 campos anteriores. Se muestra el **Repeater** `rango_comision` con: `rango_comision_precio_desde`, `rango_comision_precio_hasta`, `rango_comision_monto_fijo`, `rango_comision_porcentaje`. Validación: "precio hasta" debe ser mayor que "precio desde"; todos los campos del rango son obligatorios cuando `tipo_comision` = MIXED. En BD se borran `comision_*` (se guardan en 0) y se crean/actualizan registros en `comision_rangos`. |

**Validaciones dependientes:**

- `comision_semanal`, `comision_bisemanal`, `comision_quincenal`, `comision_mensual`, `comision_gateway`: `required_unless:tipo_comision,MIXED`, `nullable`, `numeric`, `min:0`.
- Rango: `rango_comision_precio_desde` y `rango_comision_precio_hasta`: `required_if:tipo_comision,MIXED`, `numeric`, `min:0`; además "hasta" > "desde".
- `rango_comision_monto_fijo` y `rango_comision_porcentaje`: `required_if:tipo_comision,MIXED`, `numeric`, `min:0`; porcentaje `max:100`.

---

### 2.2 Retenciones (`permitir_retenciones`)

| Toggle activo | Campos que se muestran y/o se usan |
|---------------|------------------------------------|
| **Sí**        | Grupo visible con: `emails_retenciones` (Repeater con `email_retencion`), `dias_vencidos_retencion`, `dia_retencion_mensual`, `dia_retencion_semanal`, `dia_retencion_catorcenal`, `dia_retencion_quincenal`. No hay validación obligatoria de que al menos uno de los "día retención" esté lleno; el servicio crea registros en `configuracion_retencion_nominas` solo por cada periodicidad que venga con valor. |
| **No**        | Se oculta el grupo. En update se eliminan todos los registros de `configuracion_retencion_nominas` de la empresa. |

**Tabla afectada:** `configuracion_retencion_nominas` (empresa_id, emails, periodicidad_pago, fecha / dias / dia_semana según tipo).

---

### 2.3 Pagos catorcenales (`tiene_pagos_catorcenales`)

| Toggle activo | Comportamiento |
|---------------|----------------|
| **Sí**        | Se muestra `fecha_proximo_pago_catorcenal`. Validación: `minDate` = mañana. Se guarda en `empresas.fecha_proximo_pago_catorcenal`. Si además hay retenciones catorcenales, el servicio usa esta fecha como referencia para calcular la fecha de retención. |
| **No**        | Se oculta el campo; en BD la fecha se puede dejar null. |

---

### 2.4 Quincena personalizada (`tiene_quincena_personalizada`)

| Toggle activo | Comportamiento |
|---------------|----------------|
| **Sí**        | Se muestran `dia_inicio` y `dia_fin` (Select 1–30). Ambos **required**. Validación: `dia_fin` > `dia_inicio`. Se crea/actualiza un registro en `quincenas_personalizadas` (empresa_id, dia_inicio, dia_fin). |
| **No**        | Se oculta el grupo. En update se elimina el registro de `quincenas_personalizadas` de la empresa si existía. |

**Tabla afectada:** `quincenas_personalizadas`.

---

### 2.5 Finiquito (`activar_finiquito`)

| Toggle activo | Comportamiento |
|---------------|----------------|
| **Sí**        | Se muestra `url_finiquito` (URL). Campo **required** cuando el toggle está activo. Se guarda en `empresas.url_finiquito`. |
| **No**        | Se oculta `url_finiquito`; en el servicio se guarda `url_finiquito` = null. |

---

### 2.6 Encuesta de salida (`permitir_encuesta_salida`)

| Toggle activo | Comportamiento |
|---------------|----------------|
| **Sí**        | Se muestra `razones` (CheckboxList) con opciones: ABANDONO, RENUNCIA, DESPIDO, FALLECIMIENTO, TÉRMINO DE CONTRATO. **Obligatorio** y **minItems(1)**. Se sincronizan registros en `razon_encuesta_salidas` (empresa_id, razon). |
| **No**        | Se oculta el CheckboxList; al desactivar se limpia el state de `razones`. En update se borran todos los `razon_encuesta_salidas` de la empresa. |

**Tabla afectada:** `razon_encuesta_salidas`.

---

### 2.7 Notificaciones de felicitaciones (`permitir_notificaciones_felicitaciones`)

| Toggle activo | Comportamiento |
|---------------|----------------|
| **Sí**        | Se muestra `segmento_notificaciones_felicitaciones` (Select: COMPANY, LOCATION). Se guarda en `empresas.segmento_notificaciones_felicitaciones`. |
| **No**        | Se oculta el Select; en el servicio se guarda `segmento_notificaciones_felicitaciones` = null. |

---

### 2.8 Aplicación compilada (`aplicacion_compilada`)

| Toggle activo | Comportamiento |
|---------------|----------------|
| **Sí**        | Se muestran `nombre_app` y `link_descarga_app` (URL). No hay required en formulario; el servicio guarda en `empresas.nombre_app` y `empresas.link_descarga_app`. En edición, el valor del toggle se deduce: true si ambos campos tienen valor. |
| **No**        | Se oculta el grupo. |

---

### 2.9 Industria / Subindustria

- Al cambiar `industria_id`, se resetea `sub_industria_id`.
- `sub_industria_id` solo ofrece opciones de la industria seleccionada (filtro por `industria_id`).

---

### 2.10 Fecha fin de contrato

- `fecha_fin_contrato` tiene `minDate` = `fecha_inicio_contrato`.

---

## 3. Validaciones por campo (resumen)

| Campo(s) | Regla |
|----------|--------|
| nombre, nombre_contacto, email_contacto, telefono_contacto, movil_contacto, email_facturacion | required |
| telefono_contacto, movil_contacto | maxLength(10), tel |
| email_contacto, email_facturacion | email |
| fecha_inicio_contrato, fecha_fin_contrato | required, date |
| fecha_fin_contrato | minDate(fecha_inicio_contrato) |
| tipo_comision | required, in PERCENTAGE|FIXED_AMOUNT|MIXED |
| comision_* (5 campos) | required_unless:tipo_comision,MIXED, nullable, numeric, min:0 |
| rango_comision_* | required_if:tipo_comision,MIXED, numeric, min:0 (porcentaje max:100); precio_hasta > precio_desde |
| app_android_id, app_ios_id | required |
| num_usuarios_reportes | required, numeric |
| url_finiquito | required si activar_finiquito = true, url |
| dia_inicio, dia_fin | required si tiene_quincena_personalizada = true; dia_fin > dia_inicio |
| razones | required y minItems(1) si permitir_encuesta_salida = true |
| Razones sociales (repeater) | nombre, rfc, cp, calle, numero_exterior, alcaldía, estado required; rfc 12 caracteres; cp 5 numérico |
| Productos (repeater) | producto_id required, desde required |
| foto | file, mimes:jpg,jpeg,png,bmp, max:20000 |
| documentos_contratos | PDF, multiple |
| logo_url | image, maxSize(5000) |

---

## 4. Tablas afectadas

### 4.1 Tabla principal

| Tabla | Uso |
|-------|-----|
| `empresas` | Registro principal: todos los campos fillable del modelo (nombre, contactos, fechas, toggles, comisiones, colores, logo_url, etc.). Soft deletes. |

### 4.2 Tablas de relación (pivot o N:M)

| Tabla | Relación | Acción en create/update |
|-------|----------|---------------------------|
| `empresas_razones_sociales` | empresa_id, razon_social_id | Sync desde repeater razones_sociales (crear/actualizar razones_sociales y luego sync de IDs). |
| `empresas_productos` | empresa_id, producto_id, desde, precio_unitario, precio_base, margen_variacion | Create: attach por cada item con desde (meses); Update: detach todos y attach de nuevo. |
| `empresas_centros_costos` | empresa_id, centro_costo_id | Sync con centro_costo_belvo_id, centro_costo_emida_id, centro_costo_stp_id. |
| `empresas_notificaciones_incluidas` | empresa_id, notificacion_incluida_id | Sync según toggles notificaciones_incluidas.{id}. |
| `empresas_reconocimientos` | (reconocimientos no exclusivos se asocian en create) | Solo create: attach de reconocimientos no exclusivos. |
| `empresas_temas_voz_colaboradores` | empresa_id, tema_voz_colaborador_id | Create: attach temas no exclusivos o exclusivos de esta empresa; Update: si no tiene ninguno, se re-asignan. |

### 4.3 Tablas hijas o 1:N por empresa

| Tabla | Uso |
|-------|-----|
| `comision_rangos` | empresa_id, tipo_comision, precio_desde, precio_hasta, cantidad_fija, porcentaje. Create/update: si tipo_comision = MIXED se crean filas; si no MIXED se borran todas. |
| `configuracion_retencion_nominas` | empresa_id, emails (JSON), periodicidad_pago, fecha/dias/dia_semana. Si permitir_retenciones = true se crean hasta 4 filas (MENSUAL, SEMANAL, CATORCENAL, QUINCENAL) según los campos llenos; si false se borran todas. |
| `quincenas_personalizadas` | empresa_id, dia_inicio, dia_fin. Una fila por empresa; create/update/delete según tiene_quincena_personalizada y valores. |
| `razon_encuesta_salidas` | empresa_id, razon. Sync: se borran todas y se crean las que vengan en razones si permitir_encuesta_salida = true. |
| `frecuencia_notificaciones` | empresa_id, dias, tipo, siguiente_fecha. Una por empresa tipo "ESTADOS DE ÁNIMO"; create/update/delete según frecuencia_notificaciones_estado_animo. |
| `alias_tipo_transacciones` | empresa_id, tipo_transaccion, alias. Tres tipos: ADELANTO DE NOMINA, PAGO DE SERVICIO, RECARGA. Sync: crear/actualizar si hay valor; eliminar si está vacío. |

### 4.4 Tablas de catálogo usadas (solo lectura en el módulo)

- `industrias`, `sub_industrias`
- `razones_sociales` (creadas/actualizadas desde el repeater, no solo lectura)
- `productos`, `centro_de_costos`, `notificaciones_incluidas`
- `configuracion_app` (se vincula por app_android_id / app_ios_id y se guarda configuracion_app_id en empresas)
- `reconocimientos`, `temas_voz_colaboradores`

### 4.5 Logs

- En modelo `Empresa` (booted): al created, updated, deleted se crea un registro en `logs` (accion, fecha, user_id; empresa_id no se rellena en los ejemplos del modelo pero la tabla lo soporta).

---

## 5. Carpetas de archivos (disco `uploads`)

El disco `uploads` está definido en `config/filesystems.php` con `root` = `public_path()` (raíz pública). Todas las rutas son relativas a esa raíz.

| Ruta | Uso |
|------|-----|
| `assets/companies/photos/{empresa_id}.png` | Foto de la empresa. En create/update: se redimensiona a 150x150 y se guarda como PNG. El formulario sube a temporal y el servicio mueve aquí. |
| `assets/companies/logos/` | Logos. Create: `{empresa_id}_{timestamp}.png`. Update: mismo patrón; se borra el anterior si existe. FileUpload puede enviar ruta en `livewire-tmp/` o solo nombre; el servicio resuelve y guarda la ruta final en `empresas.logo_url`. |
| `assets/companies/files/{empresa_id}/` | Documentos/contratos (PDF). Create: se crea el directorio y cada archivo se guarda como `{Y_m_d_His}_{index}.pdf`. Update: se añaden nuevos con el mismo patrón de nombre (no se borran los existentes desde el servicio; el form puede enviar la lista completa de archivos). |

**Nota:** La carpeta de carrusel `assets/companies/carousel/{empresa_id}/` se usa en el módulo **Gestión de Carruseles**, no en el formulario de Empresas.

---

## 6. Flujo Create vs Update (EmpresaService)

### 6.1 Create

1. Rellenar modelo `Empresa` con datos del form (incl. toggles y comisiones según tipo_comision).
2. Si `permitir_retenciones`: crear filas en `configuracion_retencion_nominas` por cada periodicidad con dato (mensual, semanal, catorcenal, quincenal).
3. Si hay `frecuencia_notificaciones_estado_animo`: crear `frecuencia_notificaciones` (tipo ESTADOS DE ÁNIMO).
4. Si `tipo_comision` = MIXED y hay `rango_comision`: crear `comision_rangos`.
5. Si `tiene_quincena_personalizada` y hay dia_inicio/dia_fin: crear `quincenas_personalizadas`.
6. Sync notificaciones incluidas (attach).
7. Sync razones sociales (crear/actualizar `razones_sociales` y sync pivot `empresas_razones_sociales`).
8. Sync centros de costo (belvo, emida, stp).
9. Attach productos con pivot (desde, precio_unitario 0, precio_base 0, margen_variacion 0).
10. Attach reconocimientos no exclusivos.
11. Attach temas voz colaboradores (no exclusivos o exclusivos de esta empresa).
12. Si `permitir_encuesta_salida` y hay `razones`: crear `razon_encuesta_salidas`.
13. Crear `alias_tipo_transacciones` por cada alias con valor (nómina, servicio, recarga).
14. Si hay `documentos_contratos`: crear directorio y mover PDFs.
15. Si hay `logo_url`: mover a `assets/companies/logos/` y actualizar empresa.
16. Si hay `foto`: redimensionar y guardar en `assets/companies/photos/{id}.png`.
17. Vincular `configuracion_app_id` si existe ConfiguracionApp por app_android_id o app_ios_id.

### 6.2 Update

1. Actualizar `Empresa` con `onlyFillable($data)` (excluyendo logo_url para tratarlo aparte).
2. Ajustar comisiones según tipo_comision (si no MIXED rellenar; si MIXED poner a 0).
3. Ajustar vigencia_mensajes_urgentes (solo si != 0).
4. Vincular configuracion_app_id si aplica.
5. `syncRelationsAfterUpdate`: comision_rangos, frecuencia_notificaciones estado ánimo, razones encuesta salida, quincena personalizada, configuracion_retencion_nominas, notificaciones_incluidas, razones_sociales, centros_costos, productos (detach + attach), logo, documentos_contratos, foto, alias_tipo_transacciones; y si no tiene temas voz, re-asignar temas.

---

## 7. Archivos clave del módulo

| Ruta | Descripción |
|------|-------------|
| `app/Models/Empresa.php` | Modelo, fillable, relaciones, logs en booted. |
| `app/Services/EmpresaService.php` | create(), update(), syncRelationsAfterUpdate(), syncRazonesSociales(), syncConfiguracionRetencionNominas(), syncFrecuenciaNotificacionesEstadoAnimo(), syncRazonesEncuestaSalida(), syncQuincenaPersonalizada(), syncAliasTipoTransacciones(), syncLogoUpdate(), syncDocumentosContratosUpdate(), syncFotoUpdate(). |
| `app/Filament/Resources/Empresas/EmpresaResource.php` | Resource, form, table, pages. |
| `app/Filament/Resources/Empresas/Schemas/EmpresaForm.php` | Todo el schema del formulario (secciones, toggles, repeaters, validaciones). |
| `app/Filament/Resources/Empresas/Pages/ListEmpresas.php` | Listado; getTableQuery puede filtrar por empresa del usuario si no es super_admin. |
| `app/Filament/Resources/Empresas/Pages/CreateEmpresa.php` | Create; usa EmpresaService::create. |
| `app/Filament/Resources/Empresas/Pages/EditEmpresa.php` | Edit; mutateFormDataBeforeFill para razones_sociales, productos, rango_comision, retenciones, centros costo, alias, notificaciones, documentos, etc.; handleRecordUpdate llama EmpresaService::update. |
| `app/Filament/Resources/Empresas/Tables/EmpresasTable.php` | Columnas y acciones de la tabla. |

---

## 8. Deuda técnica / TODOs referenciados en código

- En Create: "Falta implementar roles" para activo/fecha_activacion (por ahora siempre activo = false).
- EmpresaForm: varios "TODO: Revisar este campo" en retenciones y aplicacion_compilada.
- syncRazonesSociales: "ACTUALIZAR a NULL a los empleados que pertenecen a la razón social eliminada" no implementado.
- EmpresaService (comentarios finales): notificaciones post-activación, sincronización de productos por high_employee, borrado de razones sociales huérfanas y comprobación de rol admin para activar empresa no migrados.

---

*Documento generado a partir del análisis del módulo Empresas (resource, service, form y modelo).*
