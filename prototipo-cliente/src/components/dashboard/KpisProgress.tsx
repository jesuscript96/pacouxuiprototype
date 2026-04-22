import {
  DevicePhoneMobileIcon,
  DocumentCheckIcon,
  HeartIcon,
  IdentificationIcon,
} from '@heroicons/react/24/outline'
import type { ComponentType } from 'react'
import { SectionTitle } from './SectionTitle'

const kpis: {
  titulo: string
  valor: number
  meta: number
  descripcion: string
  tono: 'indigo' | 'emerald' | 'amber' | 'violet'
  Icon: ComponentType<{ className?: string }>
}[] = [
  {
    titulo: 'Tasa de registro',
    valor: 78,
    meta: 90,
    descripcion: '7,844 de 10,000 colaboradores registrados',
    tono: 'indigo',
    Icon: IdentificationIcon,
  },
  {
    titulo: 'Clima laboral',
    valor: 92,
    meta: 85,
    descripcion: 'Encuesta semestral · 1,423 respuestas',
    tono: 'emerald',
    Icon: HeartIcon,
  },
  {
    titulo: 'Cumplimiento SUA',
    valor: 64,
    meta: 100,
    descripcion: '2,890 cartas firmadas de 4,500',
    tono: 'amber',
    Icon: DocumentCheckIcon,
  },
  {
    titulo: 'Engagement app',
    valor: 81,
    meta: 75,
    descripcion: 'Usuarios activos últimos 30 días',
    tono: 'violet',
    Icon: DevicePhoneMobileIcon,
  },
]

function iconBg(tono: (typeof kpis)[0]['tono']) {
  switch (tono) {
    case 'emerald':
      return 'bg-emerald-100 text-emerald-600'
    case 'amber':
      return 'bg-amber-100 text-amber-600'
    case 'violet':
      return 'bg-violet-100 text-violet-600'
    default:
      return 'bg-indigo-100 text-indigo-600'
  }
}

function barColor(tono: (typeof kpis)[0]['tono']) {
  switch (tono) {
    case 'emerald':
      return 'bg-emerald-500'
    case 'amber':
      return 'bg-amber-500'
    case 'violet':
      return 'bg-violet-500'
    default:
      return 'bg-[#3148c8]'
  }
}

export function KpisProgress() {
  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="KPIs del trimestre" />
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {kpis.map((kpi) => {
          const Icon = kpi.Icon
          const cumple = kpi.valor >= kpi.meta
          const statusBg = cumple
            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
            : 'bg-amber-50 text-amber-700 ring-amber-200'
          return (
            <div
              key={kpi.titulo}
              className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-6"
            >
              <div className="flex items-start justify-between">
                <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${iconBg(kpi.tono)}`}>
                  <Icon className="h-5 w-5" />
                </div>
                <span
                  className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${statusBg}`}
                >
                  <span className="inline-flex h-1.5 w-1.5 rounded-full bg-current" />
                  {cumple ? 'Cumplido' : 'En progreso'}
                </span>
              </div>

              <div className="mt-5 flex items-baseline gap-2">
                <p className="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl tabular-nums">
                  {kpi.valor}
                  <span className="text-xl text-slate-400">%</span>
                </p>
                <span className="text-xs font-medium text-slate-400">meta {kpi.meta}%</span>
              </div>

              <p className="mt-0.5 text-sm font-semibold text-slate-700">{kpi.titulo}</p>
              <p className="mt-1 text-xs leading-relaxed text-slate-500">{kpi.descripcion}</p>

              <div className="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                  className={`dash-breakdown-bar h-full rounded-full ${barColor(kpi.tono)}`}
                  style={{ width: `${kpi.valor}%` }}
                />
              </div>

              <div className="relative mt-1 h-3">
                <div
                  className="absolute top-0 flex -translate-x-1/2 flex-col items-center"
                  style={{ left: `${kpi.meta}%` }}
                >
                  <div className="h-2 w-px bg-slate-300" />
                  <span className="text-[10px] font-medium text-slate-400">{kpi.meta}</span>
                </div>
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}
