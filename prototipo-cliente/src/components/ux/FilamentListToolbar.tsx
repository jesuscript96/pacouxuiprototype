import { MagnifyingGlassIcon, PlusIcon } from '@heroicons/react/24/outline'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

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
              className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
              aria-hidden
            />
            <Input
              type="search"
              value={searchValue}
              onChange={(e) => onSearchChange(e.target.value)}
              placeholder={searchPlaceholder}
              className="h-9 w-full pl-9 shadow-sm"
              aria-label={searchPlaceholder}
            />
          </div>
          <Button type="button" size="default" className="shrink-0 gap-1.5 font-semibold" onClick={onNew}>
            <PlusIcon className="h-4 w-4" aria-hidden />
            {newLabel}
          </Button>
        </div>
      </div>
      {hint ? <p className="text-xs text-muted-foreground">{hint}</p> : null}
    </div>
  )
}
