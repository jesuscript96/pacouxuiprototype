import {
  ArrowDownTrayIcon,
  ArrowTrendingDownIcon,
  ArrowTrendingUpIcon,
  BuildingOffice2Icon,
  FunnelIcon,
  MinusSmallIcon,
} from '@heroicons/react/24/outline'
import { SectionTitle } from './SectionTitle'

const rows: {
  departamento: string
  headcount: number
  rotacion: number
  cumpleanos: number
  satisfaccion: number
  tendencia: 'sube' | 'baja' | 'estable'
}[] = [
  { departamento: 'Operaciones', headcount: 9812, rotacion: 2.4, cumpleanos: 812, satisfaccion: 88, tendencia: 'sube' },
  { departamento: 'Ventas', headcount: 6124, rotacion: 4.1, cumpleanos: 534, satisfaccion: 79, tendencia: 'baja' },
  { departamento: 'Producción', headcount: 4978, rotacion: 3.2, cumpleanos: 421, satisfaccion: 83, tendencia: 'estable' },
  { departamento: 'Administración', headcount: 3846, rotacion: 1.8, cumpleanos: 298, satisfaccion: 91, tendencia: 'sube' },
  { departamento: 'Tecnología', headcount: 3203, rotacion: 2.9, cumpleanos: 256, satisfaccion: 95, tendencia: 'sube' },
  { departamento: 'Recursos Humanos', headcount: 2561, rotacion: 1.5, cumpleanos: 126, satisfaccion: 93, tendencia: 'estable' },
]

function rotClass(r: number) {
  if (r <= 2.5) {
    return 'text-emerald-700'
  }
  if (r <= 4) {
    return 'text-amber-700'
  }
  return 'text-rose-700'
}

function satBarClass(s: number) {
  if (s >= 90) {
    return 'bg-emerald-500'
  }
  if (s >= 80) {
    return 'bg-[#3148c8]'
  }
  return 'bg-amber-500'
}

function TendenciaBadge({ t }: { t: (typeof rows)[0]['tendencia'] }) {
  if (t === 'sube') {
    return (
      <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
        <ArrowTrendingUpIcon className="h-3.5 w-3.5" />
        Sube
      </span>
    )
  }
  if (t === 'baja') {
    return (
      <span className="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">
        <ArrowTrendingDownIcon className="h-3.5 w-3.5" />
        Baja
      </span>
    )
  }
  return (
    <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
      <MinusSmallIcon className="h-3.5 w-3.5" />
      Estable
    </span>
  )
}

export function ResumenEjecutivo() {
  const mesAnio = new Intl.DateTimeFormat('es-MX', { month: 'long', year: 'numeric' }).format(new Date())
  const fechaCons = new Intl.DateTimeFormat('es-MX', { day: 'numeric', month: 'long', year: 'numeric' }).format(
    new Date(),
  )

  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="Resumen ejecutivo" />
      <div className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white/85 shadow-sm ring-1 ring-white/50 backdrop-blur-md">
        <div className="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-6 py-4">
          <div>
            <h3 className="text-sm font-semibold text-slate-800">KPIs por departamento</h3>
            <p className="mt-0.5 text-xs text-slate-400">Vista estilo tabla compacta · {mesAnio}</p>
          </div>
          <div className="flex flex-wrap items-center gap-2 text-xs">
            <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
              <FunnelIcon className="h-3.5 w-3.5" />6 departamentos
            </span>
            <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700 ring-1 ring-emerald-200">
              Rotación global 2.6%
            </span>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead>
              <tr className="border-b border-slate-100 bg-slate-50/50">
                <th className="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Departamento</th>
                <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">
                  Headcount
                </th>
                <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">
                  Rotación
                </th>
                <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">
                  Cumpleaños
                </th>
                <th className="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Satisfacción</th>
                <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">
                  Tendencia
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {rows.map((row) => (
                <tr key={row.departamento} className="transition-colors hover:bg-indigo-50/40">
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <BuildingOffice2Icon className="h-4 w-4" />
                      </div>
                      <div>
                        <p className="text-sm font-semibold text-slate-800">{row.departamento}</p>
                        <p className="text-[11px] text-slate-400">Activo · 3 centros de costo</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-right font-mono text-sm tabular-nums text-slate-700">
                    {row.headcount.toLocaleString('es-MX')}
                  </td>
                  <td
                    className={`px-6 py-4 text-right font-mono text-sm font-semibold tabular-nums ${rotClass(row.rotacion)}`}
                  >
                    {row.rotacion.toFixed(1)}%
                  </td>
                  <td className="px-6 py-4 text-right">
                    <span className="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200 tabular-nums">
                      <span>🎂</span> {row.cumpleanos.toLocaleString('es-MX')}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                        <div
                          className={`dash-breakdown-bar h-full rounded-full ${satBarClass(row.satisfaccion)}`}
                          style={{ width: `${row.satisfaccion}%` }}
                        />
                      </div>
                      <span className="text-xs font-semibold tabular-nums text-slate-700">{row.satisfaccion}%</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-right">
                    <TendenciaBadge t={row.tendencia} />
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-6 py-3 text-xs text-slate-500">
          <span>Datos consolidados al {fechaCons}</span>
          <button type="button" className="inline-flex items-center gap-1.5 font-semibold text-indigo-700 hover:text-indigo-900">
            Descargar reporte
            <ArrowDownTrayIcon className="h-3.5 w-3.5" />
          </button>
        </div>
      </div>
    </div>
  )
}
