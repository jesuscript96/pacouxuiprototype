import { TrophyIcon } from '@heroicons/react/24/outline'

const mes = new Intl.DateTimeFormat('es-MX', { month: 'long' }).format(new Date())

const rows = [
  {
    colaborador: 'Ricardo Soto Vega',
    departamento: 'Producción',
    fechaIngreso: '12 de abril 2019',
    antiguedad: '7 años',
  },
  {
    colaborador: 'Laura Méndez Ruiz',
    departamento: 'Administración',
    fechaIngreso: '03 de abril 2018',
    antiguedad: '8 años',
  },
]

export function AniversariosTable() {
  return (
    <div className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
      <div className="border-b border-slate-100 bg-slate-50/80 px-4 py-3">
        <h3 className="flex items-center gap-2 text-sm font-semibold text-slate-800">
          <TrophyIcon className="h-5 w-5 shrink-0 text-violet-600" aria-hidden />
          Aniversarios de {mes}
        </h3>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-left text-sm">
          <thead>
            <tr className="border-b border-slate-100">
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Colaborador</th>
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Departamento</th>
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Fecha ingreso</th>
              <th className="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Antigüedad</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50">
            {rows.map((r) => (
              <tr key={r.colaborador}>
                <td className="px-4 py-2.5 font-medium text-slate-800">{r.colaborador}</td>
                <td className="px-4 py-2.5">
                  <span className="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                    {r.departamento}
                  </span>
                </td>
                <td className="px-4 py-2.5 text-slate-600">{r.fechaIngreso}</td>
                <td className="px-4 py-2.5">
                  <span className="inline-flex rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-800 ring-1 ring-emerald-100">
                    {r.antiguedad}
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
