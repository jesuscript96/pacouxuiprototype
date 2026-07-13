import { ArchiveBoxIcon, ArrowUturnLeftIcon } from '@heroicons/react/24/outline'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import type { ChatConversacion } from './chatsTypes'
import { avatarTono, iniciales } from './chatsUtils'

type Props = {
  chat: ChatConversacion
  empresaLabel: string
  onToggleArchivado: () => void
}

/** Detalle ampliable del chat: tipo, empresa, participantes y acciones. */
export function ChatInfoPanel({ chat, empresaLabel, onToggleArchivado }: Props) {
  const totalAdjuntos = chat.mensajes.reduce((acc, m) => acc + m.attachments.length, 0)

  return (
    <div className="shrink-0 border-b border-slate-200 bg-slate-50/80 px-4 py-3">
      <div className="flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-600">
        <span>
          <span className="font-semibold text-slate-700">Tipo:</span>{' '}
          {chat.tipo === 'grupo' ? 'Grupo' : 'Chat individual'}
        </span>
        <span>
          <span className="font-semibold text-slate-700">Empresa:</span> {empresaLabel}
        </span>
        <span>
          <span className="font-semibold text-slate-700">Mensajes:</span>{' '}
          <span className="tabular-nums">{chat.mensajes.length}</span>
        </span>
        <span>
          <span className="font-semibold text-slate-700">Adjuntos:</span>{' '}
          <span className="tabular-nums">{totalAdjuntos}</span>
        </span>
      </div>

      <p className="mt-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
        Participantes ({chat.participantes.length})
      </p>
      <ul className="mt-1.5 flex flex-wrap gap-1.5">
        {chat.participantes.map((p) => (
          <li
            key={p.id}
            className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white py-0.5 pl-0.5 pr-2.5 text-xs text-slate-700"
          >
            <span
              className={cn(
                'flex h-5 w-5 items-center justify-center rounded-full text-[9px] font-bold',
                avatarTono(p.nombre),
              )}
              aria-hidden
            >
              {iniciales(p.nombre)}
            </span>
            {p.nombre}
          </li>
        ))}
      </ul>

      <div className="mt-3 flex justify-end">
        <Button
          type="button"
          variant="outline"
          size="sm"
          className="gap-1.5 rounded-full border-slate-300 bg-white font-medium text-slate-700 hover:bg-slate-50"
          onClick={onToggleArchivado}
        >
          {chat.archivado ? (
            <>
              <ArrowUturnLeftIcon className="h-4 w-4" aria-hidden />
              Desarchivar chat
            </>
          ) : (
            <>
              <ArchiveBoxIcon className="h-4 w-4" aria-hidden />
              Archivar chat
            </>
          )}
        </Button>
      </div>
    </div>
  )
}
