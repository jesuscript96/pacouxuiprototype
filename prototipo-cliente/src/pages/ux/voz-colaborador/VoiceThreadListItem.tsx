import { ClockIcon } from '@heroicons/react/24/outline'
import type { VoiceThread } from './vozTypes'

function formatListDate(iso: string): string {
  try {
    return new Date(iso).toLocaleString('es-MX', {
      day: '2-digit',
      month: 'short',
      year: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return iso
  }
}

function snippet(text: string, max = 72): string {
  const t = text.replace(/\s+/g, ' ').trim()
  if (t.length <= max) {
    return t
  }
  return `${t.slice(0, max)}…`
}

type Props = {
  thread: VoiceThread
  selected: boolean
  onSelect: () => void
  /** Lista compacta tipo inbox (menos aire, más densidad). */
  compact?: boolean
}

export function VoiceThreadListItem({ thread, selected, onSelect, compact }: Props) {
  const name = thread.sender.isAnonymous ? 'Anónimo' : thread.sender.name

  const pad = compact ? 'p-2.5' : 'p-3'
  const round = compact ? 'rounded-lg' : 'rounded-xl'

  return (
    <button
      type="button"
      onClick={onSelect}
      className={`w-full border text-left transition-colors ${round} ${pad} ${
        selected
          ? 'border-[#3148c8]/40 bg-[#3148c8]/[0.08] ring-1 ring-[#3148c8]/18'
          : 'border-slate-200/90 bg-white hover:border-slate-300 hover:bg-slate-50/90'
      }`}
    >
      <div className="flex items-start justify-between gap-2">
        <p className="line-clamp-1 min-w-0 flex-1 text-sm font-semibold text-slate-900">{thread.category}</p>
        <span className="shrink-0 text-[10px] font-medium text-slate-400">#{thread.id}</span>
      </div>
      <p className="mt-0.5 text-[11px] text-slate-500">{formatListDate(thread.date)}</p>
      <p className="mt-0.5 truncate text-xs text-slate-700">
        {name} · {thread.sender.company}
      </p>
      <p className={`mt-1.5 text-slate-600 ${compact ? 'line-clamp-2 text-xs' : 'line-clamp-2 text-sm'}`}>
        {snippet(thread.comment, compact ? 100 : 72)}
      </p>
      <div className={`mt-1.5 flex flex-wrap gap-1 ${compact ? '' : 'gap-1.5'}`}>
        <span className="inline-flex max-w-full truncate rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-800 ring-1 ring-sky-200/80">
          {thread.priority}
        </span>
        <span className="inline-flex max-w-full truncate rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-600 ring-1 ring-slate-200/80">
          {thread.assignedToLabel ?? 'Sin asignar'}
        </span>
        <span className="inline-flex items-center gap-0.5 rounded bg-[#3148c8]/10 px-1.5 py-0.5 text-[10px] font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/20">
          <ClockIcon className="h-3 w-3" aria-hidden />
          {thread.status}
        </span>
      </div>
    </button>
  )
}
