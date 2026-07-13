import {
  BuildingOffice2Icon,
  ChatBubbleLeftRightIcon,
  PlusIcon,
} from '@heroicons/react/24/outline'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass } from '@/components/ux/protoFormStyles'
import { cn } from '@/lib/utils'
import { CATALOG_EMPRESAS_CHAT } from './chatsMockData'
import type { ChatChipFiltro, ChatConversacion } from './chatsTypes'
import type { ChatsListFilters } from './chatsUtils'
import { ChatListItem } from './ChatListItem'

const CHIPS: { id: ChatChipFiltro; label: string }[] = [
  { id: 'todos', label: 'Todos' },
  { id: 'individuales', label: 'Individuales' },
  { id: 'grupos', label: 'Grupos' },
  { id: 'no_leidos', label: 'No leídos' },
  { id: 'archivados', label: 'Archivados' },
]

type Props = {
  chats: ChatConversacion[]
  selectedId: number | null
  search: string
  onSearchChange: (v: string) => void
  onSelect: (id: number) => void
  filters: ChatsListFilters
  onFiltersChange: (f: ChatsListFilters) => void
  onNuevoChat: () => void
}

/**
 * Columna izquierda estilo herramienta de chat: filtro de empresa,
 * búsqueda, chips (patrón WhatsApp) y lista de conversaciones.
 */
export function ChatSidebar({
  chats,
  selectedId,
  search,
  onSearchChange,
  onSelect,
  filters,
  onFiltersChange,
  onNuevoChat,
}: Props) {
  return (
    <div className="flex h-full min-h-0 flex-col overflow-hidden bg-white">
      <div className="shrink-0 border-b border-slate-200/80 bg-slate-50/90 px-3 py-2.5">
        <div className="flex items-center gap-1.5">
          <BuildingOffice2Icon className="h-4 w-4 shrink-0 text-[#3148c8]" aria-hidden />
          <span className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
            Empresa
          </span>
        </div>
        <div className="mt-1.5">
          <ProtoSelect
            value={filters.empresaId}
            onValueChange={(v) => onFiltersChange({ ...filters, empresaId: v })}
            options={CATALOG_EMPRESAS_CHAT}
            placeholder="Todas las empresas"
            aria-label="Filtrar chats por empresa"
          />
        </div>
      </div>

      <div className="shrink-0 border-b border-slate-200/80 px-3 py-2.5">
        <div className="flex items-center justify-between gap-2">
          <div className="flex min-w-0 items-center gap-2">
            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#3148c8]/10 text-[#3148c8]">
              <ChatBubbleLeftRightIcon className="h-4 w-4" aria-hidden />
            </div>
            <p className="truncate text-sm font-semibold text-slate-900">
              Conversaciones
              <span className="ml-1.5 tabular-nums text-xs font-normal text-slate-500">
                ({chats.length})
              </span>
            </p>
          </div>
          <UiTooltip content="Iniciar un chat individual o crear un grupo">
            <Button
              type="button"
              variant="default"
              size="icon"
              className="shrink-0 rounded-full border-0 bg-[#3148c8] text-white shadow-sm hover:bg-[#3148c8]/90"
              onClick={onNuevoChat}
            >
              <PlusIcon className="h-4 w-4" aria-hidden />
              <span className="sr-only">Nuevo chat</span>
            </Button>
          </UiTooltip>
        </div>

        <label htmlFor="chats-search" className="sr-only">
          Buscar chat
        </label>
        <input
          id="chats-search"
          type="search"
          className={`${protoInputClass} mt-2 h-9 py-1.5 text-sm`}
          placeholder="Buscar chat…"
          value={search}
          onChange={(e) => onSearchChange(e.target.value)}
        />

        <div className="mt-2 flex flex-wrap gap-1.5" role="tablist" aria-label="Filtrar conversaciones">
          {CHIPS.map((chip) => {
            const active = filters.chip === chip.id
            return (
              <button
                key={chip.id}
                type="button"
                role="tab"
                aria-selected={active}
                onClick={() => onFiltersChange({ ...filters, chip: chip.id })}
                className={cn(
                  'rounded-full border px-2.5 py-1 text-xs font-semibold transition-colors',
                  active
                    ? 'border-[#3148c8]/30 bg-[#3148c8] text-white shadow-sm'
                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50',
                )}
              >
                {chip.label}
              </button>
            )
          })}
        </div>
      </div>

      <div className="min-h-0 flex-1 overflow-y-auto overscroll-contain bg-slate-50/40 px-2 py-2">
        {chats.length === 0 ? (
          <p className="rounded-lg border border-dashed border-slate-200 bg-white px-3 py-10 text-center text-sm text-slate-500">
            No hay chats con estos filtros.
          </p>
        ) : (
          <ul className="space-y-0.5">
            {chats.map((c) => (
              <li key={c.id}>
                <ChatListItem
                  chat={c}
                  selected={selectedId === c.id}
                  onSelect={() => onSelect(c.id)}
                />
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}
