/** Envío mostrado en tabla (legacy: ENVIADO / NO ENVIADO). */
export type MensajeEstadoEnvio = 'enviado' | 'no_enviado'

/** Contenido del paso 1 del wizard. */
export type MensajeContenidoDraft = {
  recurrente: boolean
  asunto: string
  solicitarRespuesta: boolean
  urgente: boolean
  exclusivoAlgunos: boolean
  recordatorioDias: string
  cuerpo: string
  /** Nombres de archivos elegidos (solo prototipo, sin subida). */
  adjuntosNombres: string[]
}

/** Criterios de audiencia (paso 2). Vacío = sin restringir ese eje. */
export type AudienciaCriterios = {
  empresaId: string
  adeudos: '' | 'si' | 'no'
  ubicacionId: string
  departamentoId: string
  areaId: string
  regionId: string
  puestoId: string
  razonSocialId: string
  genero: '' | 'f' | 'm' | 'nb'
  mesNacimiento: string
  edadDesde: string
  edadHasta: string
  antiguedadMesesDesde: string
  antiguedadMesesHasta: string
}

export type MensajeWizardDraft = {
  mensaje: MensajeContenidoDraft
  audiencia: AudienciaCriterios
}

/** Fila del listado principal. */
export type MensajeRow = {
  key: string
  id: number
  titulo: string
  enviados: number
  leidos: number
  noLeidos: number
  urgente: boolean
  estadoEnvio: MensajeEstadoEnvio
  programado: boolean
  /** Texto ya formateado para la tabla */
  fechaEnvio: string
  empresaEmisorId: string
  empresaEmisorNombre: string
  tipoMensajeId: string
  tipoMensajeLabel: string
  wizardSnapshot?: {
    mensaje: MensajeContenidoDraft
    audiencia: AudienciaCriterios
    destinatarioIds: string[]
  }
}

/** Filtros del listado (pantalla principal). */
export type MensajesListFilters = {
  fechaDesde: string
  fechaHasta: string
  empresaId: string
  estadoEnvio: '' | MensajeEstadoEnvio
  tituloKey: string
  programado: '' | 'si' | 'no'
  urgente: '' | 'si' | 'no'
  tipoMensajeId: string
}

/** Destinatario mock para paso 3 y motor de filtro. */
export type DestinatarioMock = {
  id: string
  nombre: string
  ubicacionEtiqueta: string
  puestoEtiqueta: string
  empresaId: string
  tieneAdeudos: boolean
  ubicacionId: string
  departamentoId: string
  areaId: string
  regionId: string
  puestoId: string
  razonSocialId: string
  genero: 'f' | 'm' | 'nb'
  mesNacimiento: number
  edad: number
  antiguedadMeses: number
}
