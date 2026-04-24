import type { GuidanceContent } from './types'
import { REF_UXUI_MDC } from './snippets'

function g(partial: GuidanceContent): GuidanceContent {
  return partial
}

export const UX_TABLEAU_PLACEHOLDER: GuidanceContent = g({
  title: 'Placeholder de informe Tableau',
  summary:
    'Cáscara de altura fija y mensaje técnico: en producción iría `tableau-viz` con JWT; aquí no se confunde al validador de UX con errores 401.',
  bulletsCuandoUsar: [
    'Replicar layout del Blade legacy antes de cablear tokens y permisos Tableau.',
  ],
  bulletsEvitar: [
    'No mostrar banners de error simulados en el prototipo de validación con cliente.',
  ],
  equivalenteFilament: ['Vista Blade / Livewire que monta el web component y maneja `VizLoadError`.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_SOLICITUDES_HERO: GuidanceContent = g({
  title: 'Centro de solicitudes (configuración)',
  summary:
    'Dos pestañas independientes: tipos de permiso y categorías. Cada una tiene su propia tabla y slide-over; la guía detallada cambía según la pestaña activa debajo.',
  bulletsCuandoUsar: [
    'Pestañas cuando los dos catálogos los administra el mismo perfil pero no son filas del mismo tipo.',
    'Mantén la búsqueda y el “Nuevo” coherentes con el catálogo activo (ya se resetea al cambiar de pestaña).',
  ],
  bulletsEvitar: [
    'Unificar en una sola tabla permisos y categorías sin columna de tipo (confunde y rompe CRUD).',
  ],
  equivalenteFilament: ['Tabs con una `Table` por pestaña o resources hermanos enlazados.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_SOLICITUDES_PERMISOS: GuidanceContent = g({
  title: 'Catálogo de tipos de permiso',
  summary:
    'Filas que definen qué puede solicitar un colaborador (vacaciones, incapacidad, etc.); estado activo/inactivo visible en tabla.',
  bulletsCuandoUsar: [
    'CRUD en slide-over para no perder el contexto del listado al activar/desactivar un tipo.',
    'Iconos o badges claros para estado binario (activo/inactivo).',
  ],
  bulletsEvitar: [
    'No eliminar tipos en uso sin regla de negocio y aviso (catálogos referenciados).',
  ],
  equivalenteFilament: ['Tabla + formulario en `SlideOver` o Resource dedicado.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_SOLICITUDES_CATEGORIAS: GuidanceContent = g({
  title: 'Categorías de permisos',
  summary: 'Agrupación lógica (p. ej. Salud, Ausencias) para filtrar y presentar permisos al colaborador en la app.',
  bulletsCuandoUsar: [
    'Mantener nombres cortos y únicos por empresa para evitar duplicados en selects dependientes.',
  ],
  bulletsEvitar: [
    'No usar categorías como sustituto de permisos: son niveles distintos de catálogo.',
  ],
  equivalenteFilament: ['Relación categoría → permisos en schema de formulario.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_CATALOGOS: GuidanceContent = g({
  title: 'Catálogos con tabs y CRUD en panel lateral',
  summary:
    'Un tab por recurso (regiones, departamentos, …), misma tabla y slide-over para crear/editar/ver sin perder el listado.',
  bulletsCuandoUsar: [
    'Catálogos relacionados donde el usuario alterna entre listas sin cambiar de URL.',
    'Crear/editar en slide-over cuando el contexto de la tabla debe permanecer visible (Filament: `SlideOver`).',
  ],
  bulletsEvitar: [
    'No abrir un modal grande por cada edición si el formulario tiene muchas secciones (valorar página o wizard).',
    'No usar tabs si los datos de un tab dependen obligatoriamente de completar otro (ahí va wizard o pasos).',
  ],
  equivalenteFilament: [
    'Tabs con `Tables\\Table` por recurso; formularios en `SlideOver` o `CatalogSlideOver` en panel Admin.',
  ],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_CATALOGOS_EMPRESAS: GuidanceContent = g({
  title: 'Catálogo de empresas (asistente)',
  summary:
    'Listado en una ruta y alta/edición en página dedicada con pasos, barra de progreso clicable, tarjetas y revelado progresivo. El borrador se guarda en localStorage en la demo.',
  bulletsCuandoUsar: [
    'Formularios muy extensos (decenas de toggles y repeaters) donde un slide-over agota la altura útil.',
    'Cuando el usuario debe revisar bloques temáticos sin perder el sentido del avance (contrato, comisiones, integraciones…).',
  ],
  bulletsEvitar: [
    'No duplicar la misma lógica de validación condicional en React y en Filament sin una fuente de verdad en backend.',
  ],
  equivalenteFilament: [
    'Resource con `Wizard` + `Section` por paso; `CatalogSlideOver` no sustituye a un wizard para este volumen.',
  ],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_DOCUMENTOS_HERO: GuidanceContent = g({
  title: 'Biblioteca corporativa',
  summary:
    'Dos frentes: publicar archivos y vigilar quién los leyó o firmó. Las notas específicas de cada pestaña están justo encima de su tabla.',
  bulletsCuandoUsar: [
    'Separar carga de metadatos de la matriz destinatarios × documento (responsabilidades distintas).',
  ],
  bulletsEvitar: [
    'Mezclar en una sola grilla columnas de archivo y de lectura sin relación clara.',
  ],
  equivalenteFilament: ['Resource principal + relation manager o segunda tabla filtrada.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_DOCUMENTOS_CARGAR: GuidanceContent = g({
  title: 'Cargar y publicar documentos',
  summary: 'Biblioteca de archivos corporativos: nombre, vigencia y metadatos mínimos para publicación.',
  bulletsCuandoUsar: [
    'Cuando el valor está en el archivo y su vigencia, no en cientos de campos en línea.',
    'Subidas siempre vía servicio de archivos del backend (`ArchivoService`), no rutas hardcodeadas.',
  ],
  bulletsEvitar: [
    'No omitir tipo de archivo ni fecha de actualización en listados de compliance.',
  ],
  equivalenteFilament: ['`FileUpload`, columnas de tabla con enlace a URL firmada.'],
  referenciaReglasCursor: `${REF_UXUI_MDC} Ver \`.cursor/rules/archivo-service.mdc\`.`,
})

export const UX_DOCUMENTOS_DESTINATARIOS: GuidanceContent = g({
  title: 'Destinatarios y lecturas',
  summary: 'Quién recibió el documento, primera y última visualización, y estado de firma cuando aplique.',
  bulletsCuandoUsar: [
    'Tabla densa con badges semánticos para “leído / firmado” y acciones de seguimiento.',
  ],
  bulletsEvitar: [
    'No mezclar en esta vista la carga masiva de PDFs: mantener pestaña o recurso separado.',
  ],
  equivalenteFilament: ['Relation manager o segunda tabla filtrada por documento.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_CARTAS_HERO: GuidanceContent = g({
  title: 'Cartas SUA (nómina)',
  summary:
    'Consulta de cartas emitidas frente a carga por lotes. Las instrucciones por pestaña están encima de cada bloque.',
  bulletsCuandoUsar: [
    'Tabs cuando “ver historial” y “importar lote” no comparten las mismas columnas ni riesgos.',
  ],
  bulletsEvitar: [
    'Ejecutar importación masiva sin resumen en la misma vista que la consulta detallada.',
  ],
  equivalenteFilament: ['Pestañas o subnavegación con acciones distintas por contexto.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_CARTAS_VER: GuidanceContent = g({
  title: 'Consultar cartas emitidas',
  summary: 'Listado por colaborador, bimestre y razón social con estados (firmada, vista, pendiente).',
  bulletsCuandoUsar: [
    'Badges de estado con colores semánticos alineados al resto del panel.',
    'Acciones ver/editar en slide-over cuando el detalle es acotado.',
  ],
  bulletsEvitar: [
    'No usar el mismo copy para “vista” y “firmada” si legalmente importa el matiz.',
  ],
  equivalenteFilament: ['Tabla de resource con `TextColumn::badge()`.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_CARTAS_CARGAR: GuidanceContent = g({
  title: 'Cargar registros (batch)',
  summary: 'Flujo de carga masiva o generación en lote; priorizar confirmación y feedback de progreso en producción.',
  bulletsCuandoUsar: [
    'Separar “consulta” de “carga” en tabs cuando los actores o riesgos difieren.',
  ],
  bulletsEvitar: [
    'No ejecutar batch sin confirmación ni sin resumen de filas afectadas.',
  ],
  equivalenteFilament: ['`Action` masiva con formulario y cola (`ShouldQueue`) si aplica.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_COLABORADORES: GuidanceContent = g({
  title: 'Colaboradores (ficha RH)',
  summary: 'Listado denso con filtros y slide-over para ver/editar datos de la ficha; refleja resource principal de RH.',
  bulletsCuandoUsar: [
    'Mantener listado visible al revisar o editar una ficha (slide-over o infolist lateral).',
    'Badges de estado alineados a ciclo de vida (activo, baja programada, etc.).',
  ],
  bulletsEvitar: [
    'No modelar datos RH solo en `users`: la fuente canónica en tecben-core es `colaboradores` (ver reglas del monorepo).',
  ],
  equivalenteFilament: ['`ColaboradorResource`, secciones de formulario y políticas por permiso.'],
  referenciaReglasCursor: `${REF_UXUI_MDC} Ver también \`.cursor/rules/arquitecture-users-first.mdc\` para users vs colaboradores.`,
})

export const UX_VACANTES: GuidanceContent = g({
  title: 'Vacantes y reclutamiento',
  summary: 'Pipeline o etapas del proceso de selección con acciones por vacante.',
  bulletsCuandoUsar: [
    'Cuando el usuario piensa en “embudo” o estados de vacante más que en tabla plana.',
  ],
  bulletsEvitar: [
    'No usar el mismo patrón que catálogos RH si el flujo es temporal y por candidato.',
  ],
  equivalenteFilament: ['Resource con tabs o relation managers según ficha del módulo.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_ROLES: GuidanceContent = g({
  title: 'Roles y permisos',
  summary: 'Matrices o listas de permisos agrupados; cambios sensibles que requieren confirmación clara.',
  bulletsCuandoUsar: [
    'Agrupar permisos por recurso o por dominio para reducir fatiga.',
    'Textos de confirmación en español descriptivos (no solo “¿Estás seguro?”).',
  ],
  bulletsEvitar: [
    'No guardar cambios masivos de permisos sin resumen de lo que quedará activo.',
  ],
  equivalenteFilament: ['Shield / Spatie; formato `Acción:Modelo` en permisos del panel Cliente.'],
  referenciaReglasCursor: `${REF_UXUI_MDC} Ver \`.cursor/rules/filament-resource-permisos.mdc\`.`,
})

export const UX_BAJAS_PENDIENTES: GuidanceContent = g({
  title: 'Solicitudes de baja pendientes',
  summary: 'Cola de revisión: fechas futuras, motivo y departamento; acciones que pueden cambiar el estado del proceso.',
  bulletsCuandoUsar: [
    'Mantener copy que deje claro que es solicitud, no borrado inmediato del colaborador.',
    'Confirmación en acciones que ejecuten o cancelen la baja.',
  ],
  bulletsEvitar: [
    'No mezclar en la misma tabla bajas ya ejecutadas sin pestaña o filtro de estado.',
  ],
  equivalenteFilament: ['Tabla filtrada por estado + acciones con `requiresConfirmation()`.'],
  referenciaReglasCursor: `${REF_UXUI_MDC} Ver \`.cursor/rules/proteccion-registros-en-uso.mdc\`.`,
})

export const UX_BAJAS_HISTORIAL: GuidanceContent = g({
  title: 'Historial de bajas',
  summary: 'Auditoría: bajas ejecutadas o canceladas con trazabilidad; lectura predominante.',
  bulletsCuandoUsar: [
    'Para consultas RH/legal con menos acciones destructivas que en pendientes.',
  ],
  bulletsEvitar: [
    'No permitir “eliminar del historial” sin política explícita de retención.',
  ],
  equivalenteFilament: ['Vista de solo lectura o edición muy restringida por permiso.'],
  referenciaReglasCursor: `${REF_UXUI_MDC} Arquitectura users/colaboradores.`,
})
