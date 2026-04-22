import type { GuidanceContent } from './types'
import { REF_UXUI_MDC } from './snippets'

/** Texto único incrustado en `CrudSlideOver` para quien implementa el mismo patrón en Filament. */
export const GUIDANCE_CRUD_SLIDEOVER_BODY: GuidanceContent = {
  title: 'Por qué este formulario está en un panel lateral',
  summary:
    'Así el listado sigue visible: el usuario no pierde contexto de filas ni filtros. Es la decisión por defecto para crear/editar/ver en catálogos y listados densos.',
  bulletsCuandoUsar: [
    'El registro se edita en pocos pasos y conviene comparar con otras filas.',
    'El flujo vuelve al listado al cerrar (Escape o clic fuera).',
  ],
  bulletsEvitar: [
    'Formularios con muchos pasos dependientes: mejor página dedicada o asistente (`Wizard`).',
    'Confirmación destructiva sola: usa modal centrado con `requiresConfirmation()`.',
  ],
  equivalenteFilament: ['`SlideOver`, acciones de tabla con `->form()` y ancho fijo.'],
  referenciaReglasCursor: REF_UXUI_MDC,
}
