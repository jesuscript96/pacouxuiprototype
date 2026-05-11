import { InboxIcon } from '@heroicons/react/24/outline'
import { protoInputClass } from '@/components/ux/protoFormStyles'
import type { VoiceThread } from './vozTypes'
import type { VoiceListFilters } from './vozFilterUtils'
import { VoiceThreadFilters } from './VoiceThreadFilters'
import { VoiceThreadListItem } from './VoiceThreadListItem'

type Props = {
  threads: VoiceThread[]
  selectedId: number | null
  search: string
  onSearchChange: (v: string) => void
  onSelect: (id: number) => void
  filters: VoiceListFilters
  onFiltersChange: (f: VoiceListFilters) => void
  onClearFilters: () => void
}

/**
 * Columna izquierda estilo bandeja: búsqueda, filtros y lista en un solo bloque visual.
 */
export function VoiceInboxSidebar({
  threads,
  selectedId,
  search,
  onSearchChange,
  onSelect,
  filters,
  onFiltersChange,
  onClearFilters,
}: Props) {
  return (
    <div className="flex h-full min-h-0 flex-col overflow-hidden bg-white">
      <div className="shrink-0 border-b border-slate-200/80 bg-slate-50/90 px-3 py-2.5">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#3148c8]/10 text-[#3148c8]">
            <InboxIcon className="h-4 w-4" aria-hidden />
          </div>
          <div className="min-w-0 flex-1">
            <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Bandeja</p>
            <p className="truncate text-sm font-semibold text-slate-900">
              Solicitudes
              <span className="ml-1.5 tabular-nums text-xs font-normal text-slate-500">({threads.length})</span>
            </p>
          </div>
        </div>
        <label htmlFor="voice-inbox-search" className="sr-only">
          Buscar en solicitudes
        </label>
        <input
          id="voice-inbox-search"
          type="search"
          className={`${protoInputClass} mt-2 h-9 py-1.5 text-sm`}
          placeholder="Buscar…"
          value={search}
          onChange={(e) => onSearchChange(e.target.value)}
        />
      </div>

      <VoiceThreadFilters
        filters={filters}
        onChange={onFiltersChange}
        onClear={onClearFilters}
        embedded
      />

      <div className="min-h-0 flex-1 overflow-y-auto overscroll-contain bg-slate-50/40 px-2 py-2">
        {threads.length === 0 ? (
          <p className="rounded-lg border border-dashed border-slate-200 bg-white px-3 py-10 text-center text-sm text-slate-500">
            No hay solicitudes con estos filtros.
          </p>
        ) : (
          <ul className="space-y-1.5">
            {threads.map((t) => (
              <li key={t.id}>
                <VoiceThreadListItem
                  thread={t}
                  selected={selectedId === t.id}
                  onSelect={() => onSelect(t.id)}
                  compact
                />
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}
