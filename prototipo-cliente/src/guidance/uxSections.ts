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

export const UX_CURSOS: GuidanceContent = g({
  title: 'Cursos y capacitación',
  summary:
    'CRUD de cursos con KPIs, reportes y wizard amplio para configurar contenido, evaluaciones, segmentación, notificaciones, certificados y datos STPS. Todo opera con mock data en memoria dentro del prototipo React.',
  bulletsCuandoUsar: [
    'Wizard cuando el alta combina información básica, contenido por módulos/lecciones, evaluación y reglas administrativas.',
    'Shortcuts visibles para “Reporte histórico” y “Nuevo curso” porque son los dos caminos frecuentes del usuario RH.',
    'Tabla principal compacta con menú de tres puntos para acciones secundarias: reportes, duplicar, desactivar y eliminar.',
  ],
  bulletsEvitar: [
    'No mezclar el reporte histórico dentro del formulario de edición: es una consulta operativa independiente.',
    'No simular carga real de archivos en el prototipo; las portadas y recursos se representan como nombres/tipos mock.',
    'No esconder acciones sensibles sin confirmación: desactivar y eliminar deben pedir contexto antes de cambiar la lista.',
  ],
  equivalenteFilament: [
    'Resource con `Table`, acciones de fila, `Wizard` en formulario y páginas/acciones separadas para reportes exportables.',
  ],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_VACANTES: GuidanceContent = g({
  title: 'Vacantes y reclutamiento',
  summary:
    'Listado de vacantes con acciones Ver / Editar / Eliminar. Crear y editar abren un asistente en modal centrado con dos pasos: (1) información estructurada de la vacante—empresa, jornada, modalidad, salario opcional, visibilidad, textos—y (2) constructor del formulario que verá el candidato, con vista previa en rejilla.',
  bulletsCuandoUsar: [
    'Cuando el usuario piensa en “embudo” o estados de vacante más que en tabla plana.',
    'Wizard en modal cuando el flujo es secuencial (ficha + formulario) sin abandonar el contexto del listado.',
  ],
  bulletsEvitar: [
    'No usar el mismo patrón que catálogos RH si el flujo es temporal y por candidato.',
  ],
  equivalenteFilament: ['Resource con tabs o relation managers según ficha del módulo.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_MENSAJES: GuidanceContent = g({
  title: 'Mensajes internos',
  summary:
    'Listado con filtros avanzados y etiquetas (chips) por cada criterio activo. Crear abre un wizard en modal en tres pasos: (1) contenido y opciones de envío con interruptores y cuerpo en texto amplio—en producción irá editor WYSIWYG—, (2) criterios de audiencia combinados con Y con chips de refuerzo, (3) lista de destinatarios donde la selección se alinea con los filtros al avanzar desde el paso 2 y se puede afinar por fila o con “Todos” / “Alinear con filtros”.',
  bulletsCuandoUsar: [
    'Cuando el alcance depende de catálogos RH y el usuario necesita ver el impacto antes de enviar.',
    'Chips de filtros visibles junto al listado para lectura rápida del contexto sin abrir el panel.',
  ],
  bulletsEvitar: [
    'No confundir este prototipo con envío real: no hay API, colas ni permisos de empresa.',
  ],
  equivalenteFilament: ['Resource de mensajes o campaña con wizard Filament y relación a destinatarios.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_TEMAS_VOZ: GuidanceContent = g({
  title: 'Catálogo de Temas de Voz',
  summary:
    'Listado simple para mantener el catálogo de temas que el colaborador elige al enviar un comentario de voz. Filtro por empresa antes de la tabla (Todas / Solo globales / empresa específica) y popup en dos pasos para crear/editar: paso 1 (Tema y descripción) y paso 2 (Segmentación: asignación a empresa + lista de destinatarios con buscador y master toggle). El shortcut «Segmentar» del menú de fila abre el wizard directo en el paso 2.',
  bulletsCuandoUsar: [
    'Cuando el catálogo es pequeño y el usuario alterna entre temas globales y exclusivos por empresa sin perder el contexto del listado.',
    'Filtro por empresa visible y persistente: al elegir empresa, ver los temas exclusivos de esa empresa junto con los globales que también aplican.',
    'Wizard de dos pasos cuando el contenido (Tema/Descripción) y la segmentación (Empresa + Destinatarios) son responsabilidades distintas y conviene mostrarlas por separado.',
    'Acceso directo al paso 2 desde la acción «Segmentar» del menú de fila para ajustar destinatarios sin tocar el contenido del tema.',
  ],
  bulletsEvitar: [
    'No confundir «exclusivo de empresa» con un permiso: la exclusividad solo limita en qué empresa aparece el tema en la app.',
    'No permitir guardar sin nombre, con el interruptor activo y empresa vacía, o sin al menos un destinatario seleccionado.',
    'No mezclar este catálogo con el de categorías o estados de la bandeja de Voz del colaborador.',
  ],
  equivalenteFilament: [
    'Resource Filament con `Tables\\Table` + `Wizard` de dos pasos en modal (`Modal`) y validación con `->required()` en Form Request o el Resource. La acción de «Segmentar» equivale a un `Action::make()` que abre el modal directamente sobre el paso 2.',
  ],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_VOZ_COLABORADOR: GuidanceContent = g({
  title: 'Voz del colaborador (comentarios)',
  summary:
    'Bandeja tipo inbox: columna izquierda con filtros compactos y lista de solicitudes; panel derecho con cabecera del hilo, prioridad/categoría/asignación, conversación colaborador↔admin con adjuntos (imagen, video, documento) y compositor con validación cliente (máx. 3 archivos, 20 MB, tipos permitidos). Sin backend: los envíos se reflejan solo en estado local del prototipo.',
  bulletsCuandoUsar: [
    'Cuando el admin de empresa debe atender quejas, sugerencias o felicitaciones con varias rondas de mensajes.',
    'Mantener visible el contexto del colaborador (empresa, ubicación, categoría) mientras se responde.',
  ],
  bulletsEvitar: [
    'No asumir que los adjuntos mock son URLs persistentes: en producción vendrán de almacenamiento firmado.',
    'No confundir este módulo con el chat interno corporativo (otro diseño y entidades).',
  ],
  equivalenteFilament: [
    'Resource de comentarios / “voz del colaborador” con vista detalle y timeline de mensajes en panel Cliente.',
  ],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_CHATS: GuidanceContent = g({
  title: 'Chats (revisión de conversaciones)',
  summary:
    'Vista tipo herramienta de chat convencional: columna izquierda con filtro de empresa, chips Todos/Individuales/Grupos/No leídos/Archivados y lista de conversaciones (avatar, último mensaje, hora, no leídos); panel derecho más ancho con header del chat (participantes, botón de info ampliable), conversación con adjuntos (imagen, video, documento), reacciones y compositor mock. Sin backend: envíos y archivado viven solo en estado local.',
  bulletsCuandoUsar: [
    'Cuando el admin necesita revisar conversaciones de colaboradores de su(s) empresa(s) sin ser participante.',
    'Individuales y grupos en la misma lista, distinguidos por avatar/etiqueta y filtrables con chips (patrón WhatsApp).',
  ],
  bulletsEvitar: [
    'No confundir con Voz del colaborador (bandeja de solicitudes con estados/atención): aquí no hay estados de atención.',
    'No asumir que los adjuntos mock son URLs persistentes: en producción vendrán de almacenamiento firmado.',
  ],
  equivalenteFilament: [
    'Página Livewire dedicada (no Resource CRUD) con lista de conversaciones + panel de mensajes en tiempo real.',
  ],
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

export const UX_PERMISOS_CATALOGO: GuidanceContent = g({
  title: 'Catálogo de permisos (referencia)',
  summary:
    'Listado de solo lectura del vocabulario Acción:Modelo para revisar etiquetas y grupos antes de cablear API.',
  bulletsCuandoUsar: [
    'Mostrar clave técnica en tipografía secundaria para alinear con Shield y soporte.',
    'Agrupar por dominio (Usuarios, Catálogos, etc.) para escanear sin fatiga.',
  ],
  bulletsEvitar: [
    'No confundir esta vista con la asignación al rol: la edición vive en "Editar rol".',
  ],
  equivalenteFilament: ['Listado de `Permission` o documentación autogenerada en admin.'],
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

export const UX_ENCUESTAS: GuidanceContent = g({
  title: 'Encuestas (centro unificado)',
  summary:
    'Consolida en pestañas lo que el legacy tenía disperso: listado de encuestas, categorías y monitoreo de envíos. Reduce navegación y duplicación de filtros.',
  bulletsCuandoUsar: [
    'Una sola página con `UxTabs` cuando los sub-listados comparten dominio (encuestas) y perfil de usuario.',
    'KPIs arriba para dar contexto de participación antes de entrar al detalle.',
  ],
  bulletsEvitar: [
    'No abrir una pantalla completa para cada micro-acción (categorías y editar envío van en slide-over/modal).',
  ],
  equivalenteFilament: ['Resource de Encuesta con Tabs/Relation managers y acciones en modal.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_ENCUESTAS_CATEGORIAS: GuidanceContent = g({
  title: 'Categorías de encuesta',
  summary: 'Catálogo simple (nombre + empresa) para organizar encuestas; CRUD en slide-over sin perder el listado.',
  bulletsCuandoUsar: [
    'Slide-over para alta/edición rápida manteniendo el contexto de la tabla.',
    'Mostrar "Sin asignar" cuando la categoría no está ligada a una empresa.',
  ],
  bulletsEvitar: [
    'No permitir borrar categorías con encuestas ligadas sin aviso (catálogo en uso).',
  ],
  equivalenteFilament: ['Resource catálogo + `CatalogSlideOver` con permiso por acción.'],
  referenciaReglasCursor: `${REF_UXUI_MDC} Ver \`.cursor/rules/proteccion-registros-en-uso.mdc\`.`,
})

export const UX_ENCUESTAS_ENVIADAS: GuidanceContent = g({
  title: 'Encuestas enviadas (monitoreo)',
  summary:
    'Estadística e histórico de campañas: enviados / contestados / no contestados, vigencia y estado. Editar un envío reprograma sin reconstruir la encuesta.',
  bulletsCuandoUsar: [
    'Badges y barras de participación para lectura rápida del avance.',
    'Editar envío en modal acotado (fechas y vigencia), no el constructor completo.',
  ],
  bulletsEvitar: [
    'No mezclar el diseño del cuestionario con la operación del envío.',
  ],
  equivalenteFilament: ['Tabla con columnas calculadas + acción de reprogramación en modal.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_ENCUESTA_BUILDER: GuidanceContent = g({
  title: 'Constructor de encuestas (estilo Typeform)',
  summary:
    'Lienzo de 3 paneles: lista de bloques, editor con vista previa en vivo (una pregunta a la vez) y ajustes contextuales del bloque. Bloques de bienvenida, sección ponderada, NPS y agradecimiento.',
  bulletsCuandoUsar: [
    'Vista previa fiel a lo que verá quien responde, para validar tono y longitud.',
    'Ajustes del bloque a la derecha (obligatoria, ponderación, dimensión) que cambian según el tipo.',
    'Pantallas de bienvenida y agradecimiento como bloques del propio formulario.',
  ],
  bulletsEvitar: [
    'No encerrar la construcción en un wizard rígido; el orden de bloques debe ser libre.',
    'No mostrar branching/lógica visual todavía (evolución futura, fuera de alcance).',
  ],
  equivalenteFilament: ['Builder Livewire con repeater de bloques + preview, o paquete de form builder.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})

export const UX_NOM035: GuidanceContent = g({
  title: 'Cuestionarios NOM-035',
  summary:
    'Guías oficiales precargadas (solo lectura), con envío segmentado, reporte de riesgo descargable y control de participación de destinatarios en una misma página.',
  bulletsCuandoUsar: [
    'Las guías son de referencia: ver escala y valores, no editarlas.',
    'Unificar envíos, destinatarios y reporte para evitar saltar entre módulos.',
  ],
  bulletsEvitar: [
    'No permitir editar las preguntas oficiales de la norma.',
    'No exponer resultados individuales si la aplicación debe ser confidencial.',
  ],
  equivalenteFilament: ['Guías como datos semilla de solo lectura + acciones de envío y reporte.'],
  referenciaReglasCursor: REF_UXUI_MDC,
})
