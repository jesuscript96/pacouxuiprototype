import { ArchiveBoxIcon, UserGroupIcon } from '@heroicons/react/24/outline'
import { cn } from '@/lib/utils'
import type { ChatConversacion } from './chatsTypes'
import {
  avatarTono,
  chatTitulo,
  formatHoraCorta,
  iniciales,
  ultimoMensaje,
  ultimoMensajeResumen,
} from './chatsUtils'

type Props = {
  chat: ChatConversacion
  selected: boolean
  onSelect: () => void
}

/** Fila de conversación estilo herramienta de chat: avatar, título, último mensaje, hora y no leídos. */
export function ChatListItem({ chat, selected, onSelect }: Props) {
  const titulo = chatTitulo(chat)
  const last = ultimoMensaje(chat)

  return (
    <button
      type="button"
      onClick={onSelect}
      className={cn(
        'flex w-full items-center gap-2.5 rounded-lg border px-2.5 py-2 text-left transition-colors',
        selected
          ? 'border-[#3148c8]/40 bg-[#3148c8]/[0.08] ring-1 ring-[#3148c8]/18'
          : 'border-transparent bg-transparent hover:border-slate-200 hover:bg-white',
      )}
    >
      <span
        className={cn(
          'relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-bold',
          avatarTono(titulo),
        )}
        aria-hidden
      >
        {chat.tipo === 'grupo' ? (
          <UserGroupIcon className="h-5 w-5" />
        ) : (
          iniciales(chat.participantes[0]?.nombre ?? titulo)
        )}
        {chat.enLinea ? (
          <span className="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-500" />
        ) : null}
      </span>

      <span className="min-w-0 flex-1">
        <span className="flex items-baseline justify-between gap-2">
          <span
            className={cn(
              'truncate text-sm text-slate-900',
              chat.noLeidos > 0 ? 'font-semibold' : 'font-medium',
            )}
          >
            {titulo}
          </span>
          {last ? (
            <span
              className={cn(
                'shrink-0 text-[11px] tabular-nums',
                chat.noLeidos > 0 ? 'font-semibold text-[#3148c8]' : 'text-slate-400',
              )}
            >
              {formatHoraCorta(last.at)}
            </span>
          ) : null}
        </span>
        <span className="mt-0.5 flex items-center justify-between gap-2">
          <span
            className={cn(
              'truncate text-xs',
              chat.noLeidos > 0 ? 'font-medium text-slate-700' : 'text-slate-500',
            )}
          >
            {ultimoMensajeResumen(chat)}
          </span>
          <span className="flex shrink-0 items-center gap-1">
            {chat.archivado ? (
              <ArchiveBoxIcon className="h-3.5 w-3.5 text-slate-400" aria-hidden />
            ) : null}
            {chat.noLeidos > 0 ? (
              <span className="inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-[#3148c8] px-1 text-[10px] font-bold tabular-nums text-white">
                {chat.noLeidos}
              </span>
            ) : null}
          </span>
        </span>
      </span>
    </button>
  )
}
