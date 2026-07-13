import type { VoiceAttachment } from '../voz-colaborador/vozTypes'

/**
 * Adjunto de chat: mismo shape que Voz del colaborador para reutilizar
 * tiles, lightbox y validación de archivos pendientes.
 */
export type ChatAttachment = VoiceAttachment

/** Reacción agregada estilo app móvil (“Me gusta”, “Me encanta”…). */
export type ChatReaccion = {
  etiqueta: string
  conteo: number
}

export type ChatParticipante = {
  id: string
  nombre: string
}

export type ChatMensaje = {
  id: string
  autorId: string
  texto: string
  at: string
  attachments: ChatAttachment[]
  reacciones: ChatReaccion[]
}

export type ChatTipo = 'individual' | 'grupo'

export type ChatConversacion = {
  id: number
  tipo: ChatTipo
  /** Solo grupos. */
  nombreGrupo?: string
  participantes: ChatParticipante[]
  /** Participante cuyos mensajes se alinean a la derecha en la revisión. */
  perspectivaId: string
  empresaId: string
  archivado: boolean
  noLeidos: number
  enLinea?: boolean
  mensajes: ChatMensaje[]
}

/** Chips de la lista (patrón WhatsApp: individuales y grupos juntos, filtrables). */
export type ChatChipFiltro = 'todos' | 'individuales' | 'grupos' | 'no_leidos' | 'archivados'

/** Autor local del compositor demo (el revisor). */
export const CHAT_AUTOR_LOCAL = 'admin-local'
