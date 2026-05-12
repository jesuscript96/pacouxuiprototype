import { ChevronDownIcon } from '@heroicons/react/24/outline'
import { useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
import { cn } from '@/lib/utils'
import { CATALOG_ASIGNADOS_VOZ, VOICE_ASSIGNEE_NONE } from './vozMockData'
import { flattenThreadToBubbles } from './vozFlattenMessages'
import { VoiceThreadStatusBadge } from './voiceStatusBadge'
import type { VoiceAttachment, VoiceLocalAdminReply, VoiceThread } from './vozTypes'
import { VoiceChatBubble } from './VoiceChatBubble'
import { VoiceComposer } from './VoiceComposer'
import { VoiceLightbox } from './VoiceLightbox'
import { formatVoiceHeaderDate, VoiceThreadHeader } from './VoiceThreadHeader'
import { VoiceThreadDetails } from './VoiceThreadDetails'

type Props = {
  thread: VoiceThread | null
  adminCompanyLabel: string
  localReplies: VoiceLocalAdminReply[]
  onPatchThread: (id: number, patch: Partial<VoiceThread>) => void
  onSendReply: (threadId: number, text: string, attachments: VoiceAttachment[]) => void
  onMarkAttended: (threadId: number) => void
  onReopen: (threadId: number) => void
}

export function VoiceThreadPanel({
  thread,
  adminCompanyLabel,
  localReplies,
  onPatchThread,
  onSendReply,
  onMarkAttended,
  onReopen,
}: Props) {
  const [lightbox, setLightbox] = useState<{ url: string; kind: 'image' | 'video' } | null>(null)
  const [detailOpen, setDetailOpen] = useState(true)
  const messagesScrollRef = useRef<HTMLDivElement>(null)

  const bubbles = useMemo(() => {
    if (!thread) {
      return []
    }
    return flattenThreadToBubbles(thread, localReplies)
  }, [thread, localReplies])

  useEffect(() => {
    setDetailOpen(true)
  }, [thread?.id])

  /** Chat anclado al último mensaje. */
  useLayoutEffect(() => {
    const el = messagesScrollRef.current
    if (!el) {
      return
    }
    const scrollEnd = (): void => {
      el.scrollTop = el.scrollHeight
    }
    scrollEnd()
    requestAnimationFrame(() => {
      scrollEnd()
      requestAnimationFrame(scrollEnd)
    })
  }, [thread?.id, bubbles.length])

  if (!thread) {
    return (
      <div className="flex h-full min-h-[280px] flex-1 flex-col items-center justify-center bg-slate-50/50 px-6 text-center">
        <p className="text-sm font-medium text-slate-700">Selecciona una solicitud</p>
        <p className="mt-1 max-w-sm text-sm text-slate-500">
          Elige un hilo en la lista para ver la conversación.
        </p>
      </div>
    )
  }

  const detailsDisabled = thread.status === 'Atendido'
  const displayName = thread.sender.isAnonymous ? 'Anónimo' : thread.sender.name

  return (
    <div className="flex h-full min-h-0 flex-1 flex-col overflow-hidden bg-white">
      <div className="flex shrink-0 items-center gap-2 border-b border-slate-200 px-3 py-2">
        <VoiceThreadStatusBadge status={thread.status} />
        <p className="min-w-0 flex-1 truncate text-sm font-semibold text-slate-900">
          {thread.category} · {displayName}
        </p>
        <span className="shrink-0 text-[11px] font-medium tabular-nums text-slate-500">
          #{thread.id}
        </span>
        <UiTooltip
          content={
            detailOpen
              ? 'Ocultar detalle del caso'
              : 'Ver detalle del caso (empresa, ubicación, prioridad…)'
          }
        >
          <Button
            type="button"
            variant="ghost"
            size="sm"
            className="shrink-0 gap-0.5 px-2 font-semibold text-[#3148c8] hover:bg-[#3148c8]/10"
            onClick={() => setDetailOpen((v) => !v)}
            aria-expanded={detailOpen}
          >
            Detalle
            <ChevronDownIcon
              className={cn('h-4 w-4 transition-transform', detailOpen && 'rotate-180')}
              aria-hidden
            />
          </Button>
        </UiTooltip>
      </div>

      {detailOpen ? (
        <div className="shrink-0 border-b border-slate-200 bg-slate-50/80 px-4 py-3">
          <p className="text-xs text-slate-600">
            <span className="font-semibold text-slate-700">Enviado:</span>{' '}
            {formatVoiceHeaderDate(thread.date)}
            {thread.urgency != null ? (
              <span className="ml-3">
                <span className="font-semibold text-slate-700">Urgencia:</span> {thread.urgency}
              </span>
            ) : null}
          </p>
          <div className="mt-3">
            <VoiceThreadHeader thread={thread} hideSummary />
          </div>
          <VoiceThreadDetails
            thread={thread}
            disabled={detailsDisabled}
            onPriorityChange={(priority) => onPatchThread(thread.id, { priority })}
            onCategoryKeyChange={(category) => onPatchThread(thread.id, { category })}
            onAssigneeChange={(assigneeKey) => {
              const row = CATALOG_ASIGNADOS_VOZ.find(
                (a) => a.value === (assigneeKey || VOICE_ASSIGNEE_NONE),
              )
              onPatchThread(thread.id, {
                assigneeKey,
                assignedToLabel: assigneeKey ? row?.label ?? null : null,
              })
            }}
          />
        </div>
      ) : null}

      <div
        ref={messagesScrollRef}
        className="min-h-0 flex-1 overflow-y-auto overscroll-contain bg-[#f0f4f8]/40 px-3 py-3 sm:px-4"
      >
        <div className="flex flex-col gap-2.5 pb-2">
          {bubbles.map((b) => (
            <VoiceChatBubble
              key={b.id}
              bubble={b}
              companyLabel={b.role === 'admin' ? adminCompanyLabel : undefined}
              onOpenMedia={(url, kind) => setLightbox({ url, kind })}
            />
          ))}
        </div>
      </div>

      <VoiceComposer
        status={thread.status}
        onSend={(text, attachments) => onSendReply(thread.id, text, attachments)}
        onMarkAttended={() => onMarkAttended(thread.id)}
        onReopen={() => onReopen(thread.id)}
      />

      {lightbox ? (
        <VoiceLightbox
          url={lightbox.url}
          kind={lightbox.kind}
          onClose={() => setLightbox(null)}
        />
      ) : null}
    </div>
  )
}
