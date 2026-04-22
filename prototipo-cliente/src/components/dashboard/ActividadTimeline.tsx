import {
  ClipboardDocumentListIcon,
  DocumentCheckIcon,
  FolderOpenIcon,
  MegaphoneIcon,
  TrophyIcon,
  UserMinusIcon,
  UserPlusIcon,
} from '@heroicons/react/24/outline'
import type { ComponentType } from 'react'
import { SectionTitle } from './SectionTitle'

const eventos: {
  tipo: 'success' | 'info' | 'warning' | 'primary' | 'danger'
  titulo: string
  descripcion: string
  cuando: string
  Icon: ComponentType<{ className?: string }>
}[] = [
  {
    tipo: 'success',
    titulo: '3 nuevos ingresos',
    descripcion: 'Ana López, Carlos Ruiz y María Tamez se integraron al equipo de Operaciones.',
    cuando: 'Hace 12 minutos',
    Icon: UserPlusIcon,
  },
  {
    tipo: 'info',
    titulo: '42 cartas SUA firmadas',
    descripcion: 'Progreso del batch mensual: 64% completado.',
    cuando: 'Hace 1 hora',
    Icon: DocumentCheckIcon,
  },
  {
    tipo: 'warning',
    titulo: '5 solicitudes esperan aprobación',
    descripcion: 'Vacaciones, permisos y cambios de datos personales.',
    cuando: 'Hace 2 horas',
    Icon: ClipboardDocumentListIcon,
  },
  {
    tipo: 'primary',
    titulo: 'Encuesta de clima laboral publicada',
    descripcion: '1,423 respuestas recibidas en las primeras 3 horas.',
    cuando: 'Hace 5 horas',
    Icon: MegaphoneIcon,
  },
  {
    tipo: 'danger',
    titulo: '1 baja programada',
    descripcion: 'Luis Hernández · Último día: 30 de abril.',
    cuando: 'Ayer · 18:42',
    Icon: UserMinusIcon,
  },
  {
    tipo: 'info',
    titulo: '4 documentos corporativos publicados',
    descripcion: 'Política de home office, manual de bienvenida y 2 más.',
    cuando: 'Ayer · 15:10',
    Icon: FolderOpenIcon,
  },
  {
    tipo: 'success',
    titulo: '12 aniversarios celebrados hoy',
    descripcion: 'Se enviaron reconocimientos automáticos a todos.',
    cuando: 'Hoy · 09:00',
    Icon: TrophyIcon,
  },
]

function iconBg(tipo: (typeof eventos)[0]['tipo']) {
  switch (tipo) {
    case 'success':
      return 'bg-emerald-50 text-emerald-600'
    case 'warning':
      return 'bg-amber-50 text-amber-600'
    case 'danger':
      return 'bg-rose-50 text-rose-600'
    case 'info':
      return 'bg-sky-50 text-sky-600'
    default:
      return 'bg-indigo-50 text-indigo-600'
  }
}

function dotRing(tipo: (typeof eventos)[0]['tipo']) {
  switch (tipo) {
    case 'success':
      return 'bg-emerald-500 ring-emerald-100'
    case 'warning':
      return 'bg-amber-500 ring-amber-100'
    case 'danger':
      return 'bg-rose-500 ring-rose-100'
    case 'info':
      return 'bg-sky-500 ring-sky-100'
    default:
      return 'bg-indigo-500 ring-indigo-100'
  }
}

export function ActividadTimeline() {
  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="Actividad reciente" />
      <div className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white/85 shadow-sm ring-1 ring-white/50 backdrop-blur-md">
        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
          <div>
            <h3 className="text-sm font-semibold text-slate-800">Últimos eventos del sistema</h3>
            <p className="mt-0.5 text-xs text-slate-400">Feed en vivo · 7 eventos mostrados</p>
          </div>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
            <span className="relative flex h-2 w-2">
              <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
              <span className="relative inline-flex h-2 w-2 rounded-full bg-emerald-500" />
            </span>
            En vivo
          </span>
        </div>

        <div className="relative px-6 py-6">
          <div className="absolute bottom-6 left-[2.75rem] top-6 w-px bg-slate-200/90" />
          <ul className="relative space-y-5">
            {eventos.map((evento) => {
              const Icon = evento.Icon
              return (
                <li key={evento.titulo + evento.cuando} className="dash-timeline-item flex gap-4">
                  <div className="relative shrink-0">
                    <div className="flex h-9 w-9 items-center justify-center rounded-full bg-white ring-4 ring-white">
                      <div className={`flex h-9 w-9 items-center justify-center rounded-full ${iconBg(evento.tipo)}`}>
                        <Icon className="h-4 w-4" />
                      </div>
                    </div>
                    <span
                      className={`absolute -right-1 -top-1 inline-flex h-2.5 w-2.5 rounded-full ring-2 ${dotRing(evento.tipo)}`}
                    />
                  </div>
                  <div className="min-w-0 flex-1 pb-1">
                    <div className="flex flex-wrap items-baseline justify-between gap-2">
                      <p className="text-sm font-semibold text-slate-800">{evento.titulo}</p>
                      <span className="text-xs text-slate-400">{evento.cuando}</span>
                    </div>
                    <p className="mt-0.5 text-sm leading-relaxed text-slate-500">{evento.descripcion}</p>
                  </div>
                </li>
              )
            })}
          </ul>
        </div>

        <div className="border-t border-slate-100 bg-slate-50/60 px-6 py-3">
          <button
            type="button"
            className="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 hover:text-indigo-900"
          >
            Ver historial completo
            <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  )
}
