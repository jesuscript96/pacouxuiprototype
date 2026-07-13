import { cn } from '@/lib/utils'
import { VoiceAttachmentList } from '../voz-colaborador/VoiceAttachmentList'
import type { ChatMensaje } from './chatsTypes'
import { formatHoraMensaje } from './chatsUtils'

type Props = {
  mensaje: ChatMensaje
  /** Alineado a la derecha (perspectiva del chat o mensajes demo del revisor). */
  saliente: boolean
  /** Nombre del autor visible en grupos para mensajes entrantes. */
  autorNombre?: string
  onOpenMedia?: (url: string, kind: 'image' | 'video') => void
}

export function ChatBubble({ mensaje, saliente, autorNombre, onOpenMedia }: Props) {
  return (
    <div className={cn('flex w-full', saliente ? 'justify-end' : 'justify-start')}>
      <div
        className={cn(
          'max-w-[80%] rounded-2xl px-3.5 py-2.5 shadow-sm',
          saliente ? 'rounded-br-md bg-[#e9f0ff] text-[#40444B]' : 'rounded-bl-md bg-white text-[#40444B] ring-1 ring-slate-200/70',
        )}
      >
        {autorNombre ? (
          <p className="mb-0.5 text-xs font-semibold text-[#3148c8]">{autorNombre}</p>
        ) : null}
        {mensaje.texto.trim() ? (
          <p className="whitespace-pre-wrap text-sm leading-relaxed">{mensaje.texto}</p>
        ) : null}
        <VoiceAttachmentList attachments={mensaje.attachments} onOpenMedia={onOpenMedia} />
        {mensaje.reacciones.length > 0 ? (
          <div className="mt-1.5 flex flex-wrap gap-1">
            {mensaje.reacciones.map((r) => (
              <span
                key={r.etiqueta}
                className="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-600 ring-1 ring-slate-200/80"
              >
                {r.etiqueta}
                <span className="tabular-nums font-semibold">{r.conteo}</span>
              </span>
            ))}
          </div>
        ) : null}
        <p className={cn('mt-1 text-[11px] text-[#9b9b9b]', saliente && 'text-right')}>
          {formatHoraMensaje(mensaje.at)}
        </p>
      </div>
    </div>
  )
}
