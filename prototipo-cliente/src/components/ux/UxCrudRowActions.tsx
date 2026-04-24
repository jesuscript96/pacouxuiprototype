import {
  EllipsisVerticalIcon,
  EyeIcon,
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

type Props = {
  onView?: () => void
  onEdit?: () => void
  onDelete?: () => void
}

/**
 * Menú de accesos directos (⋮) estilo listado Filament — solo UI de prototipo.
 */
export function UxCrudRowActions({ onView, onEdit, onDelete }: Props) {
  const hasAny = Boolean(onView || onEdit || onDelete)
  if (!hasAny) {
    return null
  }

  const showSepBeforeDelete = Boolean(onDelete && (onView || onEdit))

  return (
    <div className="flex justify-end">
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
          {onView ? (
            <DropdownMenuItem
              className="gap-2"
              onClick={() => {
                onView()
              }}
            >
              <EyeIcon className="h-4 w-4 text-slate-500" />
              Ver
            </DropdownMenuItem>
          ) : null}
          {onEdit ? (
            <DropdownMenuItem
              className="gap-2"
              onClick={() => {
                onEdit()
              }}
            >
              <PencilSquareIcon className="h-4 w-4 text-slate-500" />
              Editar
            </DropdownMenuItem>
          ) : null}
          {showSepBeforeDelete ? <DropdownMenuSeparator /> : null}
          {onDelete ? (
            <DropdownMenuItem
              variant="destructive"
              className="gap-2"
              onClick={() => {
                onDelete()
              }}
            >
              <TrashIcon className="h-4 w-4" />
              Eliminar
            </DropdownMenuItem>
          ) : null}
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}
