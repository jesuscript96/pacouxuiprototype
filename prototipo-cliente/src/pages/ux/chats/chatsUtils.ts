import type { ChatChipFiltro, ChatConversacion, ChatMensaje } from './chatsTypes'

export type ChatsListFilters = {
  empresaId: string
  chip: ChatChipFiltro
}

export function emptyChatsFilters(): ChatsListFilters {
  return { empresaId: '', chip: 'todos' }
}

/** Título visible de la conversación (nombre de grupo o participantes unidos). */
export function chatTitulo(chat: ChatConversacion): string {
  if (chat.tipo === 'grupo' && chat.nombreGrupo) {
    return chat.nombreGrupo
  }
  return chat.participantes.map((p) => p.nombre).join(', ')
}

export function ultimoMensaje(chat: ChatConversacion): ChatMensaje | null {
  return chat.mensajes[chat.mensajes.length - 1] ?? null
}

/** Resumen del último mensaje para la lista (texto o tipo de adjunto). */
export function ultimoMensajeResumen(chat: ChatConversacion): string {
  const m = ultimoMensaje(chat)
  if (!m) {
    return 'Sin mensajes'
  }
  if (m.texto.trim()) {
    return m.texto
  }
  const kind = m.attachments[0]?.kind
  if (kind === 'image') {
    return '📷 Foto'
  }
  if (kind === 'video') {
    return '🎬 Video'
  }
  if (kind === 'document') {
    return '📄 Documento'
  }
  return 'Adjunto'
}

export function iniciales(nombre: string): string {
  const partes = nombre.trim().split(/\s+/)
  const primera = partes[0]?.[0] ?? ''
  const segunda = partes[1]?.[0] ?? ''
  return `${primera}${segunda}`.toUpperCase() || '?'
}

const AVATAR_TONOS = [
  'bg-[#3148c8]/10 text-[#3148c8]',
  'bg-sky-100 text-sky-700',
  'bg-amber-100 text-amber-700',
  'bg-emerald-100 text-emerald-700',
  'bg-rose-100 text-rose-700',
  'bg-violet-100 text-violet-700',
]

/** Tono determinista por texto para avatares mock. */
export function avatarTono(texto: string): string {
  let h = 0
  for (let i = 0; i < texto.length; i++) {
    h = (h * 31 + texto.charCodeAt(i)) >>> 0
  }
  return AVATAR_TONOS[h % AVATAR_TONOS.length]!
}

export function formatHoraCorta(iso: string): string {
  try {
    const d = new Date(iso)
    const hoy = new Date()
    const esHoy = d.toDateString() === hoy.toDateString()
    if (esHoy) {
      return d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
    }
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: '2-digit' })
  } catch {
    return iso
  }
}

export function formatHoraMensaje(iso: string): string {
  try {
    return new Date(iso).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
  } catch {
    return iso
  }
}

/** Separador de día estilo “02 DE JULIO DE 2026”. */
export function formatSeparadorDia(iso: string): string {
  try {
    return new Date(iso)
      .toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
      .toUpperCase()
  } catch {
    return iso
  }
}

/** Clave de día local para agrupar mensajes bajo separadores. */
export function claveDia(iso: string): string {
  try {
    const d = new Date(iso)
    return `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`
  } catch {
    return iso
  }
}

/**
 * Filtra conversaciones por empresa, chip y búsqueda.
 * Como WhatsApp: los archivados solo aparecen en su propio chip.
 */
export function filterChats(
  chats: ChatConversacion[],
  filters: ChatsListFilters,
  search: string,
): ChatConversacion[] {
  const q = search.trim().toLowerCase()

  return chats
    .filter((c) => {
      if (filters.empresaId && c.empresaId !== filters.empresaId) {
        return false
      }
      if (filters.chip === 'archivados') {
        if (!c.archivado) {
          return false
        }
      } else {
        if (c.archivado) {
          return false
        }
        if (filters.chip === 'individuales' && c.tipo !== 'individual') {
          return false
        }
        if (filters.chip === 'grupos' && c.tipo !== 'grupo') {
          return false
        }
        if (filters.chip === 'no_leidos' && c.noLeidos === 0) {
          return false
        }
      }
      if (!q) {
        return true
      }
      const haystack = [
        chatTitulo(c),
        ...c.participantes.map((p) => p.nombre),
        ...c.mensajes.map((m) => m.texto),
      ]
        .join(' ')
        .toLowerCase()
      return haystack.includes(q)
    })
    .sort((a, b) => {
      const ta = Date.parse(ultimoMensaje(a)?.at ?? '') || 0
      const tb = Date.parse(ultimoMensaje(b)?.at ?? '') || 0
      return tb - ta
    })
}
