import type { ReactNode } from 'react'

type Column = { key: string; header: string; className?: string }

/** Tabla estilo listado Filament — mock para reemplazar Livewire en prototipo. */
export function MockFilamentTable({
  columns,
  rows,
  actionsColumn,
  rowKey,
}: {
  columns: Column[]
  rows: Record<string, ReactNode>[]
  /** Columna final con Ver / Editar / Eliminar como en Filament. */
  actionsColumn?: {
    header?: string
    className?: string
    render: (row: Record<string, ReactNode>, index: number) => ReactNode
  }
  rowKey?: (row: Record<string, ReactNode>, index: number) => string | number
}) {
  const showActions = Boolean(actionsColumn)

  return (
    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div className="-mx-4 overflow-x-auto sm:mx-0">
        <table className="min-w-full divide-y divide-slate-200 text-sm">
          <thead className="bg-slate-50">
            <tr>
              {columns.map((c) => (
                <th
                  key={c.key}
                  scope="col"
                  className={
                    'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 ' +
                    (c.className ?? '')
                  }
                >
                  {c.header}
                </th>
              ))}
              {showActions ? (
                <th
                  scope="col"
                  className={
                    'w-px whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 ' +
                    (actionsColumn?.className ?? '')
                  }
                >
                  {actionsColumn?.header ?? 'Acciones'}
                </th>
              ) : null}
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {rows.map((row, i) => (
              <tr
                key={rowKey ? rowKey(row, i) : i}
                className="hover:bg-slate-50/80"
              >
                {columns.map((c) => (
                  <td key={c.key} className="whitespace-nowrap px-4 py-3 text-slate-800">
                    {row[c.key]}
                  </td>
                ))}
                {showActions ? (
                  <td className="whitespace-nowrap px-4 py-3 text-right text-slate-800">
                    {actionsColumn!.render(row, i)}
                  </td>
                ) : null}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
