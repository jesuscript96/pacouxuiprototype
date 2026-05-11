import { CheckCircleIcon, ClockIcon, PauseCircleIcon } from '@heroicons/react/24/outline'
import type { VoiceThread } from './vozTypes'

const statusStyles: Record<
  VoiceThread['status'],
  { className: string; Icon: typeof ClockIcon; label: string }
> = {
  Pendiente: {
    className: 'bg-slate-100 text-slate-700 ring-slate-200',
    Icon: PauseCircleIcon,
    label: 'Pendiente',
  },
  'En Proceso': {
    className: 'bg-[#3148c8]/10 text-[#3148c8] ring-[#3148c8]/25',
    Icon: ClockIcon,
    label: 'En Proceso',
  },
  Atendido: {
    className: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    Icon: CheckCircleIcon,
    label: 'Atendido',
  },
}

export function VoiceThreadStatusBadge({ status }: { status: VoiceThread['status'] }) {
  const st = statusStyles[status]
  return (
    <span
      className={`inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ${st.className}`}
    >
      <st.Icon className="h-3.5 w-3.5" aria-hidden />
      {st.label}
    </span>
  )
}
