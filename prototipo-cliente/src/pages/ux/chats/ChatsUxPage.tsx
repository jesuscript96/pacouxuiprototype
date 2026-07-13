import { ChatBubbleOvalLeftEllipsisIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { UxHero } from '@/components/ux/UxHero'
import { paths } from '@/navigation/config'
import { UX_CHATS } from '@/guidance/uxSections'
import { CATALOG_EMPRESAS_CHAT, INITIAL_CHATS } from './chatsMockData'
import {
  CHAT_AUTOR_LOCAL,
  type ChatAttachment,
  type ChatConversacion,
  type ChatMensaje,
} from './chatsTypes'
import { emptyChatsFilters, filterChats, type ChatsListFilters } from './chatsUtils'
import { ChatSidebar } from './ChatSidebar'
import { ChatPanel } from './ChatPanel'
import { NuevoChatDialog, type NuevoChatDraft } from './NuevoChatDialog'

export function ChatsUxPage() {
  const [chats, setChats] = useState<ChatConversacion[]>(() => [...INITIAL_CHATS])
  const [filters, setFilters] = useState<ChatsListFilters>(() => emptyChatsFilters())
  const [search, setSearch] = useState('')
  const [selectedId, setSelectedId] = useState<number | null>(null)
  const [nuevoChatOpen, setNuevoChatOpen] = useState(false)

  const filtered = useMemo(
    () => filterChats(chats, filters, search),
    [chats, filters, search],
  )

  /** Si la selección deja de ser visible con los filtros, cae al primer chat (ajuste en render). */
  const visibleSelectedId =
    selectedId != null && filtered.some((c) => c.id === selectedId)
      ? selectedId
      : filtered[0]?.id ?? null
  if (visibleSelectedId !== selectedId) {
    setSelectedId(visibleSelectedId)
  }

  const selected = useMemo(
    () =>
      visibleSelectedId != null
        ? chats.find((c) => c.id === visibleSelectedId) ?? null
        : null,
    [chats, visibleSelectedId],
  )

  const empresaLabel = useMemo(() => {
    if (!selected) {
      return 'Empresa'
    }
    return (
      CATALOG_EMPRESAS_CHAT.find((e) => e.value === selected.empresaId)?.label ?? 'Empresa'
    )
  }, [selected])

  const patchChat = useCallback((id: number, patch: Partial<ChatConversacion>) => {
    setChats((prev) => prev.map((c) => (c.id === id ? { ...c, ...patch } : c)))
  }, [])

  /** Al abrir un chat se limpian sus no leídos (demo). */
  const onSelect = useCallback(
    (id: number) => {
      setSelectedId(id)
      patchChat(id, { noLeidos: 0 })
    },
    [patchChat],
  )

  const onSend = useCallback(
    (chatId: number, text: string, attachments: ChatAttachment[]) => {
      const mensaje: ChatMensaje = {
        id: `local-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        autorId: CHAT_AUTOR_LOCAL,
        texto: text,
        at: new Date().toISOString(),
        attachments,
        reacciones: [],
      }
      setChats((prev) =>
        prev.map((c) => (c.id === chatId ? { ...c, mensajes: [...c.mensajes, mensaje] } : c)),
      )
    },
    [],
  )

  const onToggleArchivado = useCallback(
    (chatId: number) => {
      const chat = chats.find((c) => c.id === chatId)
      if (!chat) {
        return
      }
      patchChat(chatId, { archivado: !chat.archivado })
    },
    [chats, patchChat],
  )

  const onCreateChat = useCallback((draft: NuevoChatDraft) => {
    const nuevo: ChatConversacion = {
      id: Date.now(),
      tipo: draft.tipo,
      nombreGrupo: draft.tipo === 'grupo' ? draft.nombreGrupo : undefined,
      participantes: draft.participantes,
      perspectivaId: draft.participantes[0]?.id ?? CHAT_AUTOR_LOCAL,
      empresaId: draft.empresaId,
      archivado: false,
      noLeidos: 0,
      mensajes: [],
    }
    setChats((prev) => [nuevo, ...prev])
    setFilters((prev) => ({ ...prev, chip: 'todos' }))
    setSearch('')
    setSelectedId(nuevo.id)
  }, [])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Comunicación"
        title="Chats"
        description="Visualiza las conversaciones de los colaboradores de tu empresa. Los chats individuales y los grupos comparten la misma lista y se filtran con los chips."
        icon={ChatBubbleOvalLeftEllipsisIcon}
        stat={{
          label: 'Conversaciones (demo)',
          value: String(filtered.length),
          hint: 'Visibles con los filtros actuales',
        }}
        guidance={UX_CHATS}
      />

      <nav className="text-sm text-slate-600" aria-label="Migas de pan">
        <Link to={paths.inicio} className="font-medium text-[#3148c8] hover:underline">
          PACO
        </Link>
        <span className="mx-2 text-slate-400">/</span>
        <span className="text-slate-800">Chats</span>
      </nav>

      <div className="flex min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200/95 bg-white shadow-sm ring-1 ring-slate-950/[0.04] lg:flex-row lg:[min-height:min(680px,calc(100dvh-220px))] lg:max-h-[calc(100dvh-220px)]">
        <aside className="flex min-h-[min(320px,42vh)] w-full shrink-0 flex-col border-slate-200 lg:min-h-0 lg:w-[min(100%,320px)] lg:max-w-[360px] lg:border-r lg:border-b-0">
          <ChatSidebar
            chats={filtered}
            selectedId={visibleSelectedId}
            search={search}
            onSearchChange={setSearch}
            onSelect={onSelect}
            filters={filters}
            onFiltersChange={setFilters}
            onNuevoChat={() => setNuevoChatOpen(true)}
          />
        </aside>
        <section className="flex min-h-[min(380px,58vh)] min-w-0 flex-1 flex-col border-t border-slate-200 lg:min-h-0 lg:border-t-0">
          <ChatPanel
            key={selected?.id ?? 'sin-chat'}
            chat={selected}
            empresaLabel={empresaLabel}
            onSend={onSend}
            onToggleArchivado={onToggleArchivado}
          />
        </section>
      </div>

      <NuevoChatDialog
        open={nuevoChatOpen}
        onClose={() => setNuevoChatOpen(false)}
        onCreate={onCreateChat}
      />
    </div>
  )
}
