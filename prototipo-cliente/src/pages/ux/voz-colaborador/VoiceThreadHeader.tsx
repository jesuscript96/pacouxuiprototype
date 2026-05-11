import { CheckCircleIcon, ClockIcon, PauseCircleIcon } from '@heroicons/react/24/outline'
import type { VoiceThread } from './vozTypes'

export function formatVoiceHeaderDate(iso: string): string {
  try {
    return new Date(iso).toLocaleString('es-MX', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return iso
  }
}

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

type Props = {
  thread: VoiceThread
  /** Solo metadatos (empresa, ubicación, atención); oculta estado, título y fecha de envío (p. ej. barra superior ya los muestra). */
  hideSummary?: boolean
}

export function VoiceThreadHeader({ thread, hideSummary }: Props) {
  const st = statusStyles[thread.status]
  const displayName = thread.sender.isAnonymous ? 'Anónimo' : thread.sender.name

  return (
    <div className={hideSummary ? '' : 'border-b border-slate-200 pb-4'}>
      {!hideSummary ? (
        <>
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div className="flex flex-wrap items-center gap-2">
              <span
                className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ${st.className}`}
              >
                <st.Icon className="h-4 w-4" aria-hidden />
                {st.label}
              </span>
              <span className="text-sm font-semibold text-slate-500">#{thread.id}</span>
              {thread.urgency != null ? (
                <span className="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-900 ring-1 ring-amber-200">
                  Urgencia: {thread.urgency}
                </span>
              ) : null}
            </div>
          </div>
          <p className="mt-3 text-lg font-semibold text-slate-900">
            {thread.category} · {displayName}
          </p>
          <p className="mt-1 text-sm text-slate-600">
            Enviado: {formatVoiceHeaderDate(thread.date)}
          </p>
        </>
      ) : null}
      <div className={`flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600 ${hideSummary ? '' : 'mt-3'}`}>
        <span>🏢 {thread.sender.company}</span>
        <span>📍 {thread.sender.location}</span>
        <span>💼 {thread.sender.department}</span>
        <span>👥 {thread.sender.area}</span>
        <span>👤 {thread.sender.position}</span>
      </div>
      {thread.attendedBy ? (
        <p className={`text-sm text-slate-700 ${hideSummary ? 'mt-2' : 'mt-3'}`}>
          <span className="font-medium">Atendido por:</span> {thread.attendedBy}
          {thread.attentionDate ? (
            <span className="text-slate-500">
              {' '}
              · Fecha atención: {formatVoiceHeaderDate(thread.attentionDate)}
            </span>
          ) : null}
        </p>
      ) : null}
    </div>
  )
}
