import type { VariantHintContent } from './types'

/** Misma clase `dash-glass-hero`; solo cambia el acento del borde izquierdo. */
export const HINT_GLASS_PRIMARY: VariantHintContent = {
  titulo: 'Vidrio con acento primario (#3148c8)',
  eligeEstoSi: [
    'Es el bloque más importante de la fila (KPI principal, CTA principal o héroe de módulo).',
    'Necesitas que el usuario reconozca la marca sin saturar el fondo.',
  ],
  mejorNoSi: [
    'Hay tres o más tarjetas iguales en importancia: no pongas todas con acento primario (ruido visual).',
    'El fondo detrás ya es muy colorido: usa neutro para recuperar contraste.',
  ],
  filament: 'Misma idea que un `Section` o card con borde/accent `primary` controlado.',
}

export const HINT_GLASS_NEUTRAL: VariantHintContent = {
  titulo: 'Vidrio con acento neutro (slate)',
  eligeEstoSi: [
    'Métricas secundarias, tarjetas de apoyo o listas donde el contenido debe mandar más que el color.',
    'Repites el patrón en grid: alterna o usa neutro en la mayoría y primario en una sola tarjeta.',
  ],
  mejorNoSi: [
    'Es la única tarjela de un dashboard vacío: un toque primario ayuda a anclar la marca.',
  ],
  filament: 'Equivalente a peso visual `gray` / sin `color(\'primary\')` en el contenedor.',
}

export const HINT_GLASS_SECONDARY: VariantHintContent = {
  titulo: 'Vidrio con acento secundario (indigo)',
  eligeEstoSi: [
    'Jerarquía intermedia: importante pero no debe competir con el KPI “estrella”.',
    'Variedad en dashboard largo para que el usuario escanee bloques distintos sin cansarse.',
  ],
  mejorNoSi: [
    'En formularios largos de captura: prioriza fondos planos y bordes claros.',
  ],
  filament: 'Úsalo como “info” o capa secundaria, no como acción destructiva.',
}
