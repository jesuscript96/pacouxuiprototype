import { ChatBubbleLeftRightIcon } from '@heroicons/react/24/outline'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { UxHero } from '@/components/ux/UxHero'
import { paths } from '@/navigation/config'
import { UX_VOZ_COLABORADOR } from '@/guidance/uxSections'
import { CATALOG_EMPRESAS_VOZ, INITIAL_VOICE_THREADS } from './vozMockData'
import {
  emptyVoiceFilters,
  filterVoiceThreads,
  type VoiceListFilters,
} from './vozFilterUtils'
import type { VoiceAttachment, VoiceLocalAdminReply, VoiceThread } from './vozTypes'
import { VoiceInboxSidebar } from './VoiceInboxSidebar'
import { VoiceThreadPanel } from './VoiceThreadPanel'

export function VozColaboradorUxPage() {
  const [threads, setThreads] = useState<VoiceThread[]>(() => [...INITIAL_VOICE_THREADS])
  const [filters, setFilters] = useState<VoiceListFilters>(() => emptyVoiceFilters())
  const [search, setSearch] = useState('')
  const [selectedId, setSelectedId] = useState<number | null>(() => INITIAL_VOICE_THREADS[0]?.id ?? null)
  const [localReplies, setLocalReplies] = useState<Record<number, VoiceLocalAdminReply[]>>({})

  const filtered = useMemo(
    () => filterVoiceThreads(threads, filters, search),
    [threads, filters, search],
  )

  useEffect(() => {
    if (selectedId != null && filtered.some((t) => t.id === selectedId)) {
      return
    }
    setSelectedId(filtered[0]?.id ?? null)
  }, [filtered, selectedId])

  const selected = useMemo(
    () => (selectedId != null ? threads.find((t) => t.id === selectedId) ?? null : null),
    [threads, selectedId],
  )

  const adminCompanyLabel = useMemo(() => {
    if (!selected) {
      return 'Empresa'
    }
    return CATALOG_EMPRESAS_VOZ.find((e) => e.value === selected.empresaId)?.label ?? selected.sender.company
  }, [selected])

  const repliesForSelected = selectedId != null ? localReplies[selectedId] ?? [] : []

  const patchThread = useCallback((id: number, patch: Partial<VoiceThread>) => {
    setThreads((prev) => prev.map((t) => (t.id === id ? { ...t, ...patch } : t)))
  }, [])

  const onSendReply = useCallback((threadId: number, text: string, attachments: VoiceAttachment[]) => {
    const reply: VoiceLocalAdminReply = {
      id: `local-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
      text,
      at: new Date().toISOString(),
      attachments,
    }
    setLocalReplies((prev) => ({
      ...prev,
      [threadId]: [...(prev[threadId] ?? []), reply],
    }))
  }, [])

  const onMarkAttended = useCallback((threadId: number) => {
    patchThread(threadId, {
      status: 'Atendido',
      attentionDate: new Date().toISOString(),
      attendedBy: 'Carlos Méndez Ríos',
    })
  }, [patchThread])

  const onReopen = useCallback((threadId: number) => {
    patchThread(threadId, { status: 'En Proceso' })
  }, [patchThread])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Comunicación"
        title="Comentarios"
        description="Visualiza y atiende las solicitudes que han enviado los colaboradores. Recuerda que pueden haber comentarios anónimos que de igual manera debes de atender."
        icon={ChatBubbleLeftRightIcon}
        stat={{ label: 'Bandeja (demo)', value: String(filtered.length), hint: 'Hilos visibles con filtros' }}
        guidance={UX_VOZ_COLABORADOR}
      />

      <nav className="text-sm text-slate-600" aria-label="Migas de pan">
        <Link to={paths.inicio} className="font-medium text-[#3148c8] hover:underline">
          PACO
        </Link>
        <span className="mx-2 text-slate-400">/</span>
        <span className="text-slate-800">Comentarios</span>
      </nav>

      <div className="flex min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200/95 bg-white shadow-sm ring-1 ring-slate-950/[0.04] lg:flex-row lg:[min-height:min(680px,calc(100dvh-220px))] lg:max-h-[calc(100dvh-220px)]">
        <aside className="flex min-h-[min(320px,42vh)] w-full shrink-0 flex-col border-slate-200 lg:min-h-0 lg:w-[min(100%,320px)] lg:max-w-[360px] lg:border-r lg:border-b-0">
          <VoiceInboxSidebar
            threads={filtered}
            selectedId={selectedId}
            search={search}
            onSearchChange={setSearch}
            onSelect={setSelectedId}
            filters={filters}
            onFiltersChange={setFilters}
            onClearFilters={() => setFilters(emptyVoiceFilters())}
          />
        </aside>
        <section className="flex min-h-[min(380px,58vh)] min-w-0 flex-1 flex-col border-t border-slate-200 lg:min-h-0 lg:border-t-0">
          <VoiceThreadPanel
            thread={selected}
            adminCompanyLabel={adminCompanyLabel}
            localReplies={repliesForSelected}
            onPatchThread={patchThread}
            onSendReply={onSendReply}
            onMarkAttended={onMarkAttended}
            onReopen={onReopen}
          />
        </section>
      </div>
    </div>
  )
}
