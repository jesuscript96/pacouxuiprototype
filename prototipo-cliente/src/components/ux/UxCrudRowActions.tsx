import { EyeIcon, PencilSquareIcon, TrashIcon } from '@heroicons/react/24/outline'

type Props = {
  onView?: () => void
  onEdit?: () => void
  onDelete?: () => void
}

const btn =
  'rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8]/30'

/**
 * Acciones de fila estilo listado Filament (ver / editar / eliminar) — solo UI de prototipo.
 */
export function UxCrudRowActions({ onView, onEdit, onDelete }: Props) {
  return (
    <div className="flex justify-end gap-0.5">
      <button
        type="button"
        className={`${btn} hover:text-[#3148c8]`}
        aria-label="Ver"
        onClick={onView}
      >
        <EyeIcon className="h-5 w-5" />
      </button>
      <button
        type="button"
        className={`${btn} hover:text-indigo-700`}
        aria-label="Editar"
        onClick={onEdit}
      >
        <PencilSquareIcon className="h-5 w-5" />
      </button>
      <button
        type="button"
        className={`${btn} hover:bg-red-50 hover:text-red-600`}
        aria-label="Eliminar"
        onClick={onDelete}
      >
        <TrashIcon className="h-5 w-5" />
      </button>
    </div>
  )
}
