/**
 * Contenido de guía orientado a desarrolladores que portan el UX/UI a otro repo
 * (p. ej. Filament). Convive con el prototipo visual sin sustituir reglas de negocio.
 */
export type GuidanceContent = {
  /** Título corto de la sección (ej. “Badges de estado”). */
  title: string
  /** Una o dos frases: qué demuestra este bloque. */
  summary: string
  bulletsCuandoUsar: string[]
  bulletsEvitar: string[]
  /** Patrones Filament / Livewire aproximados (orientación, no norma). */
  equivalenteFilament?: string[]
  /** Reglas de producto en el monorepo (solo referencia). */
  referenciaReglasCursor?: string
}

/** Guía corta ligada a una variante visual (ej. vidrio primario vs neutro). */
export type VariantHintContent = {
  titulo: string
  eligeEstoSi: string[]
  /** Si aplica, matiza para reducir dudas (“mejor no si…”). */
  mejorNoSi?: string[]
  filament?: string
}
