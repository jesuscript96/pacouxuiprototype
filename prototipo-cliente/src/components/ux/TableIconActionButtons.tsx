import { Fragment, type ComponentType } from 'react'
import {
  ArrowDownTrayIcon,
  DocumentDuplicateIcon,
  EllipsisVerticalIcon,
  EyeIcon,
  PaperAirplaneIcon,
  PaperClipIcon,
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/react/24/outline'

import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { clsx } from '../../utils/cn'

export type TableIconActionTone =
  | 'view'
  | 'edit'
  | 'delete'
  | 'download'
  | 'duplicate'
  | 'send'
  | 'attach'

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
 * Menú ⋮ con acciones de fila (misma semántica que antes, presentación compacta).
 */
export function TableIconActionButtons({ actions, className }: Props) {
  if (actions.length === 0) {
    return null
  }

  return (
    <div className={clsx('flex shrink-0 justify-end', className)}>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button
            type="button"
            variant="ghost"
            size="icon"
            className="h-8 w-8 text-slate-500 hover:text-slate-800"
            aria-label="Acciones del registro"
          >
            <EllipsisVerticalIcon className="h-5 w-5" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[11rem]">
          {actions.map((a, index) => {
            const Icon = IconByTone[a.tone]
            const title = a.hint ? `${a.label} — ${a.hint}` : a.label
            const isDelete = a.tone === 'delete'
            const sepBefore =
              isDelete && actions.slice(0, index).some((prev) => prev.tone !== 'delete')

            return (
              <Fragment key={a.id}>
                {sepBefore ? <DropdownMenuSeparator /> : null}
                <DropdownMenuItem
                  variant={isDelete ? 'destructive' : 'default'}
                  className="gap-2"
                  disabled={a.disabled}
                  title={title}
                  onClick={() => {
                    a.onClick?.()
                  }}
                >
                  <Icon className={clsx('h-4 w-4', !isDelete && 'text-slate-500')} aria-hidden />
                  {a.label}
                </DropdownMenuItem>
              </Fragment>
            )
          })}
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}
