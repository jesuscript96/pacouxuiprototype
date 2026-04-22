import { ClipboardDocumentCheckIcon, DocumentCheckIcon, MegaphoneIcon } from '@heroicons/react/24/outline'
import type { ComponentType } from 'react'
import { SectionTitle } from './SectionTitle'

const acciones: {
  titulo: string
  descripcion: string
  cta: string
  urgencia: 'alta' | 'media' | 'baja'
  tiempo: string
  Icon: ComponentType<{ className?: string }>
}[] = [
  {
    titulo: '5 solicitudes por aprobar',
    descripcion: 'La más antigua lleva 3 días esperando respuesta.',
    cta: 'Revisar solicitudes',
    urgencia: 'alta',
    tiempo: 'Hoy',
    Icon: ClipboardDocumentCheckIcon,
  },
  {
    titulo: '2 cartas SUA sin firmar',
    descripcion: 'Cierre del periodo mensual en 4 días.',
    cta: 'Completar firmas',
    urgencia: 'media',
    tiempo: '4 días',
    Icon: DocumentCheckIcon,
  },
  {
    titulo: 'Encuesta de clima',
    descripcion: 'Lanza la siguiente oleada antes del 30 de abril.',
    cta: 'Programar envío',
    urgencia: 'baja',
    tiempo: '15 días',
    Icon: MegaphoneIcon,
  },
]

function badge(urgencia: (typeof acciones)[0]['urgencia']) {
  switch (urgencia) {
    case 'alta':
      return {
        label: 'Prioridad alta',
        cls: 'bg-rose-50 text-rose-700 ring-rose-200',
        bar: 'bg-rose-500',
        icon: 'bg-rose-100 text-rose-600',
        hover: 'hover:border-rose-300 hover:shadow-rose-500/10',
      }
    case 'media':
      return {
        label: 'Prioridad media',
        cls: 'bg-amber-50 text-amber-800 ring-amber-200',
        bar: 'bg-amber-500',
        icon: 'bg-amber-100 text-amber-600',
        hover: 'hover:border-amber-300 hover:shadow-amber-500/10',
      }
    default:
      return {
        label: 'Planificada',
        cls: 'bg-sky-50 text-sky-700 ring-sky-200',
        bar: 'bg-sky-500',
        icon: 'bg-sky-100 text-sky-600',
        hover: 'hover:border-sky-300 hover:shadow-sky-500/10',
      }
  }
}

export function ProximasAcciones() {
  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="Próximas acciones" />
      <div className="space-y-3">
        {acciones.map((accion) => {
          const b = badge(accion.urgencia)
          const Icon = accion.Icon
          return (
            <div
              key={accion.titulo}
              className={`dash-action-card group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-lg sm:p-5 ${b.hover}`}
            >
              <div className={`absolute left-0 top-0 h-full w-1 ${b.bar}`} />
              <div className="flex items-start gap-3 pl-2 sm:gap-4">
                <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${b.icon}`}>
                  <Icon className="h-5 w-5" />
                </div>
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span
                      className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ${b.cls}`}
                    >
                      <span className={`inline-flex h-1.5 w-1.5 rounded-full ${b.bar}`} />
                      {b.label}
                    </span>
                    <span className="text-[11px] font-medium text-slate-400">· {accion.tiempo}</span>
                  </div>
                  <p className="mt-1 text-sm font-semibold text-slate-800">{accion.titulo}</p>
                  <p className="mt-0.5 text-xs leading-relaxed text-slate-500">{accion.descripcion}</p>
                  <button
                    type="button"
                    className="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-[#3148c8] hover:text-[#2436a3]"
                  >
                    {accion.cta}
                    <svg
                      className="h-3.5 w-3.5 transition-transform group-hover:translate-x-1"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}
