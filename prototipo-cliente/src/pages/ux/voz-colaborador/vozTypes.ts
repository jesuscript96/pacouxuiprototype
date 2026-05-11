export type VoiceAttachmentKind = 'image' | 'video' | 'document'

export type VoiceAttachmentSide = 'comment' | 'result'

/** Adjunto en un mensaje del hilo (colaborador = comment, admin = result). */
export type VoiceAttachment = {
  id: number | null
  kind: VoiceAttachmentKind
  side: VoiceAttachmentSide
  url: string
  original_name: string
  mime_type: string
  size_bytes: number | null
}

export type VoiceThreadExtra = {
  id: number
  comment: string
  result: string
  attentionDate?: string
  attachments: VoiceAttachment[]
}

export type VoiceThreadStatus = 'Pendiente' | 'En Proceso' | 'Atendido'

export type VoiceThreadPriority = 'Sin Asignar' | 'Baja' | 'Media' | 'Alta'

export type VoiceThread = {
  id: number
  status: VoiceThreadStatus
  priority: VoiceThreadPriority
  sender: {
    name: string
    isAnonymous: boolean
    company: string
    location: string
    department: string
    area: string
    position: string
  }
  category: string
  empresaId: string
  ubicacionKey: string
  comment: string
  result: string
  date: string
  attentionDate?: string
  attendedBy?: string
  urgency?: number
  /** Etiqueta para badge “Responsable” en lista. */
  assignedToLabel?: string | null
  /** Valor interno del select Asignar a (`carlos` / `laura` / vacío). */
  assigneeKey?: string
  attachments: VoiceAttachment[]
  extras: VoiceThreadExtra[]
}

/** Burbuja lista para render (colaborador izquierda, admin derecha). */
export type VoiceDisplayBubble = {
  id: string
  role: 'collaborator' | 'admin'
  text: string
  at: string
  attachments: VoiceAttachment[]
}

/** Respuestas demo añadidas desde el composer (solo prototipo). */
export type VoiceLocalAdminReply = {
  id: string
  text: string
  at: string
  attachments: VoiceAttachment[]
}
