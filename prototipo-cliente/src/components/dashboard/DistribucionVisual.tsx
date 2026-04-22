import { BuildingOffice2Icon, ClockIcon, UserGroupIcon } from '@heroicons/react/24/outline'
import { SectionTitle } from './SectionTitle'

const departamentos = [
  { label: 'Operaciones', valor: 9812, porcentaje: 32 },
  { label: 'Ventas', valor: 6124, porcentaje: 20 },
  { label: 'Producción', valor: 4978, porcentaje: 16 },
  { label: 'Administración', valor: 3846, porcentaje: 13 },
  { label: 'Tecnología', valor: 3203, porcentaje: 11 },
  { label: 'Recursos Humanos', valor: 2561, porcentaje: 8 },
]

const antiguedad = [
  { label: '0 – 1 año', valor: 5234, porcentaje: 17 },
  { label: '1 – 3 años', valor: 9821, porcentaje: 32 },
  { label: '3 – 5 años', valor: 7845, porcentaje: 26 },
  { label: '5 – 10 años', valor: 5412, porcentaje: 18 },
  { label: '+10 años', valor: 2212, porcentaje: 7 },
]

const genero = [
  { label: 'Mujeres', valor: 15892, porcentaje: 52, tono: 'violet' as const },
  { label: 'Hombres', valor: 14021, porcentaje: 46, tono: 'sky' as const },
  { label: 'No binario', valor: 611, porcentaje: 2, tono: 'amber' as const },
]

function fmt(n: number) {
  return n.toLocaleString('es-MX')
}

export function DistribucionVisual() {
  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="Distribución de la plantilla" />
      <div className="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-3">
        <div className="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition hover:shadow-lg hover:shadow-slate-900/5 sm:p-6">
          <div className="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-50" />
          <div className="relative flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">Por departamento</h3>
              <p className="mt-0.5 text-xs text-slate-400">Top 6 áreas · Total 30,524</p>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100">
              <BuildingOffice2Icon className="h-[18px] w-[18px] text-indigo-600" />
            </div>
          </div>
          <div className="relative mt-5 space-y-3">
            {departamentos.map((item) => (
              <div key={item.label}>
                <div className="flex items-baseline justify-between gap-2 text-xs">
                  <span className="font-medium text-slate-700">{item.label}</span>
                  <span className="tabular-nums text-slate-400">
                    {fmt(item.valor)} <span className="text-slate-300">·</span>{' '}
                    <span className="font-semibold text-slate-600">{item.porcentaje}%</span>
                  </span>
                </div>
                <div className="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                  <div
                    className="dash-breakdown-bar h-full rounded-full bg-[#3148c8]"
                    style={{ width: `${item.porcentaje * 3}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition hover:shadow-lg hover:shadow-slate-900/5 sm:p-6">
          <div className="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-50" />
          <div className="relative flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">Por antigüedad</h3>
              <p className="mt-0.5 text-xs text-slate-400">Promedio general: 3.2 años</p>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100">
              <ClockIcon className="h-[18px] w-[18px] text-emerald-600" />
            </div>
          </div>
          <div className="relative mt-5 space-y-3">
            {antiguedad.map((item) => (
              <div key={item.label}>
                <div className="flex items-baseline justify-between gap-2 text-xs">
                  <span className="font-medium text-slate-700">{item.label}</span>
                  <span className="tabular-nums text-slate-400">
                    {fmt(item.valor)} <span className="text-slate-300">·</span>{' '}
                    <span className="font-semibold text-emerald-700">{item.porcentaje}%</span>
                  </span>
                </div>
                <div className="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                  <div
                    className="dash-breakdown-bar h-full rounded-full bg-emerald-500"
                    style={{ width: `${item.porcentaje * 3}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition hover:shadow-lg hover:shadow-slate-900/5 sm:p-6">
          <div className="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-violet-50" />
          <div className="relative flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">Por género</h3>
              <p className="mt-0.5 text-xs text-slate-400">Diversidad en la plantilla</p>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100">
              <UserGroupIcon className="h-[18px] w-[18px] text-violet-600" />
            </div>
          </div>
          <div className="relative mt-5">
            <div className="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
              {genero.map((item) => (
                <div
                  key={item.label}
                  className={`dash-breakdown-bar h-full ${
                    item.tono === 'violet'
                      ? 'bg-violet-500'
                      : item.tono === 'sky'
                        ? 'bg-sky-500'
                        : 'bg-amber-400'
                  }`}
                  style={{ width: `${item.porcentaje}%` }}
                />
              ))}
            </div>
          </div>
          <div className="relative mt-4 space-y-2.5">
            {genero.map((item) => (
              <div key={item.label} className="flex items-center justify-between gap-2 text-xs">
                <span className="flex items-center gap-2 font-medium text-slate-700">
                  <span
                    className={`inline-flex h-2.5 w-2.5 rounded-full ${
                      item.tono === 'violet'
                        ? 'bg-violet-500'
                        : item.tono === 'sky'
                          ? 'bg-sky-500'
                          : 'bg-amber-400'
                    }`}
                  />
                  {item.label}
                </span>
                <span className="tabular-nums text-slate-400">
                  {fmt(item.valor)} <span className="text-slate-300">·</span>{' '}
                  <span className="font-semibold text-slate-700">{item.porcentaje}%</span>
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
