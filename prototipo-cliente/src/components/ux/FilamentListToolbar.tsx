import { MagnifyingGlassIcon, PlusIcon } from '@heroicons/react/24/outline'

type Props = {
  /** Título del recurso en plural (ej. «Regiones») — encima de la tabla. */
  heading: string
  /** Etiqueta del botón primario (ej. «Nueva región»). */
  newLabel: string
  onNew: () => void
  searchValue: string
  onSearchChange: (value: string) => void
  searchPlaceholder?: string
  /** Texto auxiliar bajo la barra (equiv. a filtros / hint en Filament). */
  hint?: string
}

/**
 * Barra superior de un listado Resource Filament: búsqueda + acción crear.
 * Solo UI; la búsqueda filtra en memoria en el prototipo.
 */
export function FilamentListToolbar({
  heading,
  newLabel,
  onNew,
  searchValue,
  onSearchChange,
  searchPlaceholder = 'Buscar',
  hint,
}: Props) {
  return (
    <div className="space-y-3">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 className="text-base font-semibold leading-tight text-slate-900">{heading}</h2>
        <div className="flex flex-wrap items-center gap-2">
          <div className="relative min-w-[12rem] flex-1 sm:max-w-xs">
            <MagnifyingGlassIcon
              className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
              aria-hidden
            />
            <input
              type="search"
              value={searchValue}
              onChange={(e) => onSearchChange(e.target.value)}
              placeholder={searchPlaceholder}
              className="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20"
            />
          </div>
          <button
            type="button"
            onClick={onNew}
            className="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-[#3148c8] px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#2a3db0]"
          >
            <PlusIcon className="h-4 w-4" aria-hidden />
            {newLabel}
          </button>
        </div>
      </div>
      {hint ? <p className="text-xs text-slate-500">{hint}</p> : null}
    </div>
  )
}
