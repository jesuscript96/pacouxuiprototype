import { ExclamationTriangleIcon } from '@heroicons/react/24/outline'

const rows = [
  {
    colaborador: 'Luis Hernández',
    departamento: 'Operaciones',
    puesto: 'Supervisor',
    motivo: 'Renuncia',
    motivoTone: 'warning' as const,
    fecha: '30/04/2026',
  },
  {
    colaborador: 'Patricia Núñez',
    departamento: 'Ventas',
    puesto: 'Ejecutiva',
    motivo: 'Término de contrato',
    motivoTone: 'info' as const,
    fecha: '15/05/2026',
  },
]

function motivoBadge(tone: (typeof rows)[0]['motivoTone'], label: string) {
  const map = {
    warning: 'bg-amber-50 text-amber-800 ring-amber-200',
    danger: 'bg-red-50 text-red-800 ring-red-200',
    info: 'bg-sky-50 text-sky-800 ring-sky-200',
    gray: 'bg-slate-100 text-slate-600 ring-slate-200',
  }
  return (
    <span className={`inline-flex rounded-md px-2 py-0.5 text-xs font-medium ring-1 ${map[tone]}`}>{label}</span>
  )
}

export function BajasProgramadasTable() {
  return (
    <div className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
      <div className="border-b border-slate-100 bg-slate-50/80 px-4 py-3">
        <h3 className="flex items-center gap-2 text-sm font-semibold text-slate-800">
          <ExclamationTriangleIcon className="h-5 w-5 shrink-0 text-amber-600" aria-hidden />
          Bajas programadas próximas
        </h3>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-left text-sm">
          <thead>
            <tr className="border-b border-slate-100">
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Colaborador</th>
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Departamento</th>
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Puesto</th>
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Motivo</th>
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Fecha de baja</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50">
            {rows.map((r) => (
              <tr key={r.colaborador + r.fecha}>
                <td className="px-4 py-2.5 font-medium text-slate-800">{r.colaborador}</td>
                <td className="px-4 py-2.5">
                  <span className="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                    {r.departamento}
                  </span>
                </td>
                <td className="px-4 py-2.5 text-slate-600">{r.puesto}</td>
                <td className="px-4 py-2.5">{motivoBadge(r.motivoTone, r.motivo)}</td>
                <td className="px-4 py-2.5">
                  <span className="inline-flex rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">
                    {r.fecha}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
