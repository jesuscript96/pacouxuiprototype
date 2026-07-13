import { QuestionMarkCircleIcon, UserGroupIcon } from '@heroicons/react/24/outline'
import { Fragment, useLayoutEffect, useRef, useState } from 'react'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
import { cn } from '@/lib/utils'
import { VoiceLightbox } from '../voz-colaborador/VoiceLightbox'
import { CHAT_AUTOR_LOCAL, type ChatAttachment, type ChatConversacion } from './chatsTypes'
import {
  avatarTono,
  chatTitulo,
  claveDia,
  formatSeparadorDia,
  iniciales,
} from './chatsUtils'
import { ChatBubble } from './ChatBubble'
import { ChatComposer } from './ChatComposer'
import { ChatInfoPanel } from './ChatInfoPanel'

type Props = {
  chat: ChatConversacion | null
  empresaLabel: string
  onSend: (chatId: number, text: string, attachments: ChatAttachment[]) => void
  onToggleArchivado: (chatId: number) => void
}

/**
 * Panel derecho: header con info del chat (ampliable), conversación y compositor mock.
 * Se monta con `key={chat.id}` desde la página para resetear info y scroll por chat.
 */
export function ChatPanel({ chat, empresaLabel, onSend, onToggleArchivado }: Props) {
  const [lightbox, setLightbox] = useState<{ url: string; kind: 'image' | 'video' } | null>(null)
  const [infoOpen, setInfoOpen] = useState(false)
  const messagesScrollRef = useRef<HTMLDivElement>(null)

  /** Conversación anclada al último mensaje. */
  useLayoutEffect(() => {
    const el = messagesScrollRef.current
    if (!el) {
      return
    }
    const scrollEnd = (): void => {
      el.scrollTop = el.scrollHeight
    }
    scrollEnd()
    requestAnimationFrame(() => {
      scrollEnd()
      requestAnimationFrame(scrollEnd)
    })
  }, [chat?.id, chat?.mensajes.length])

  if (!chat) {
    return (
      <div className="flex h-full min-h-[280px] flex-1 flex-col items-center justify-center bg-slate-50/50 px-6 text-center">
        <p className="text-sm font-medium text-slate-700">Selecciona una conversación</p>
        <p className="mt-1 max-w-sm text-sm text-slate-500">
          Elige un chat en la lista para ver los mensajes.
        </p>
      </div>
    )
  }

  const titulo = chatTitulo(chat)
  const subtitulo =
    chat.tipo === 'grupo'
      ? `${chat.participantes.length} participantes`
      : chat.enLinea
        ? 'en línea'
        : 'desconectado'
  const nombresPorId = new Map(chat.participantes.map((p) => [p.id, p.nombre]))

  return (
    <div className="flex h-full min-h-0 flex-1 flex-col overflow-hidden bg-white">
      <div className="flex shrink-0 items-center gap-2.5 border-b border-slate-200 px-3 py-2">
        <span
          className={cn(
            'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold',
            avatarTono(titulo),
          )}
          aria-hidden
        >
          {chat.tipo === 'grupo' ? (
            <UserGroupIcon className="h-5 w-5" />
          ) : (
            iniciales(chat.participantes[0]?.nombre ?? titulo)
          )}
        </span>
        <div className="min-w-0 flex-1">
          <p className="truncate text-sm font-semibold text-slate-900">{titulo}</p>
          <p
            className={cn(
              'truncate text-xs',
              chat.tipo === 'individual' && chat.enLinea
                ? 'font-medium text-emerald-600'
                : 'text-slate-500',
            )}
          >
            {subtitulo}
          </p>
        </div>
        <UiTooltip
          content={
            infoOpen
              ? 'Ocultar información del chat'
              : 'Ver información del chat (tipo, empresa, participantes…)'
          }
        >
          <Button
            type="button"
            variant="ghost"
            size="icon-lg"
            className={cn(
              'shrink-0 rounded-full text-slate-500 hover:bg-[#3148c8]/10 hover:text-[#3148c8]',
              infoOpen && 'bg-[#3148c8]/10 text-[#3148c8]',
            )}
            onClick={() => setInfoOpen((v) => !v)}
            aria-expanded={infoOpen}
          >
            <QuestionMarkCircleIcon className="size-6" aria-hidden />
            <span className="sr-only">Información del chat</span>
          </Button>
        </UiTooltip>
      </div>

      {infoOpen ? (
        <ChatInfoPanel
          chat={chat}
          empresaLabel={empresaLabel}
          onToggleArchivado={() => onToggleArchivado(chat.id)}
        />
      ) : null}

      <div
        ref={messagesScrollRef}
        className="min-h-0 flex-1 overflow-y-auto overscroll-contain bg-[#f0f4f8]/40 px-3 py-3 sm:px-4"
      >
        <div className="flex flex-col gap-2.5 pb-2">
          {chat.mensajes.map((m, i) => {
            const prev = chat.mensajes[i - 1]
            const nuevoDia = !prev || claveDia(prev.at) !== claveDia(m.at)
            const saliente = m.autorId === chat.perspectivaId || m.autorId === CHAT_AUTOR_LOCAL
            const autorNombre =
              chat.tipo === 'grupo' && !saliente ? nombresPorId.get(m.autorId) : undefined
            return (
              <Fragment key={m.id}>
                {nuevoDia ? (
                  <div className="my-1.5 flex justify-center">
                    <span className="rounded-full bg-white px-3 py-1 text-[10px] font-semibold tracking-wide text-slate-500 shadow-sm ring-1 ring-slate-200/80">
                      {formatSeparadorDia(m.at)}
                    </span>
                  </div>
                ) : null}
                <ChatBubble
                  mensaje={m}
                  saliente={saliente}
                  autorNombre={autorNombre}
                  onOpenMedia={(url, kind) => setLightbox({ url, kind })}
                />
              </Fragment>
            )
          })}
        </div>
      </div>

      <ChatComposer onSend={(text, attachments) => onSend(chat.id, text, attachments)} />

      {lightbox ? (
        <VoiceLightbox url={lightbox.url} kind={lightbox.kind} onClose={() => setLightbox(null)} />
      ) : null}
    </div>
  )
}
