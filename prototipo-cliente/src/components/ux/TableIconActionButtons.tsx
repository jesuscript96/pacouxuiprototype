import type { ComponentType } from 'react'
import {
  ArrowDownTrayIcon,
  DocumentDuplicateIcon,
  EyeIcon,
  PaperAirplaneIcon,
  PaperClipIcon,
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/react/24/outline'
import { clsx } from '../../utils/cn'

export type TableIconActionTone =
  | 'view'
  | 'edit'
  | 'delete'
  | 'download'
  | 'duplicate'
  | 'send'
  | 'attach'

const toneClass: Record<TableIconActionTone, string> = {
  edit: 'bg-slate-200/90 text-slate-700 ring-slate-300/50 hover:bg-slate-300/90 hover:text-slate-900',
  view: 'bg-indigo-100 text-indigo-800 ring-indigo-200/60 hover:bg-indigo-200/90 hover:text-indigo-950',
  delete: 'bg-rose-100 text-rose-800 ring-rose-200/60 hover:bg-rose-200/90 hover:text-rose-950',
  download: 'bg-sky-100 text-sky-800 ring-sky-200/60 hover:bg-sky-200/90 hover:text-sky-950',
  duplicate: 'bg-violet-100 text-violet-800 ring-violet-200/60 hover:bg-violet-200/90 hover:text-violet-950',
  send: 'bg-emerald-100 text-emerald-800 ring-emerald-200/60 hover:bg-emerald-200/90 hover:text-emerald-950',
  attach: 'bg-amber-100 text-amber-900 ring-amber-200/60 hover:bg-amber-200/90 hover:text-amber-950',
}

const IconByTone: Record<TableIconActionTone, ComponentType<{ className?: string }>> = {
  view: EyeIcon,
  edit: PencilSquareIcon,
  delete: TrashIcon,
  download: ArrowDownTrayIcon,
  duplicate: DocumentDuplicateIcon,
  send: PaperAirplaneIcon,
  attach: PaperClipIcon,
}

export type TableIconAction = {
  id: string
  tone: TableIconActionTone
  /** Accesibilidad y tooltip nativo */
  label: string
  /** Texto extra en hover (p. ej. «Demo») */
  hint?: string
  onClick?: () => void
  disabled?: boolean
}

type Props = {
  actions: TableIconAction[]
  className?: string
}

/**
 * Botones solo icono estilo squircle pastel (acciones de tabla / toolbar).
 */
export function TableIconActionButtons({ actions, className }: Props) {
  return (
    <div className={clsx('flex flex-shrink-0 flex-nowrap justify-end gap-1.5', className)}>
      {actions.map((a) => {
        const Icon = IconByTone[a.tone]
        const title = a.hint ? `${a.label} — ${a.hint}` : a.label
        return (
          <button
            key={a.id}
            type="button"
            aria-label={a.label}
            title={title}
            disabled={a.disabled}
            onClick={a.onClick}
            className={clsx(
              'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8]/35 focus-visible:ring-offset-1',
              toneClass[a.tone],
              a.disabled && 'cursor-not-allowed opacity-50 hover:bg-current/10',
            )}
          >
            <Icon className="h-[1.125rem] w-[1.125rem]" aria-hidden />
          </button>
        )
      })}
    </div>
  )
}
