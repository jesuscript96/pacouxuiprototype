import { EllipsisVerticalIcon } from '@heroicons/react/24/outline'
import type { ReactNode } from 'react'

export type ProtoColumn<T> = {
  key: string
  header: string
  className?: string
  render: (row: T) => ReactNode
}

type Props<T> = {
  columns: ProtoColumn<T>[]
  rows: T[]
  rowKey: (row: T) => string | number
  actions?: (row: T) => ReactNode
  emptyMessage?: string
}

export function ProtoDataTable<T>({
  columns,
  rows,
  rowKey,
  actions,
  emptyMessage = 'No hay registros para mostrar.',
}: Props<T>) {
  return (
    <div className="-mx-4 overflow-x-auto sm:mx-0">
      <div className="inline-block min-w-full align-middle">
        <table className="min-w-full divide-y divide-slate-200 border border-slate-200 bg-white">
          <thead className="bg-slate-50/90">
            <tr>
              {columns.map((c) => (
                <th
                  key={c.key}
                  scope="col"
                  className={
                    'px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 ' +
                    (c.className ?? '')
                  }
                >
                  {c.header}
                </th>
              ))}
              {actions ? (
                <th
                  scope="col"
                  className="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600"
                >
                  <span className="sr-only sm:not-sr-only">Acciones</span>
                  <EllipsisVerticalIcon
                    className="ml-auto h-5 w-5 text-slate-400 sm:hidden"
                    aria-hidden
                  />
                </th>
              ) : null}
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {rows.length === 0 ? (
              <tr>
                <td
                  colSpan={columns.length + (actions ? 1 : 0)}
                  className="px-4 py-8 text-center text-sm text-slate-500"
                >
                  {emptyMessage}
                </td>
              </tr>
            ) : (
              rows.map((row) => (
                <tr
                  key={rowKey(row)}
                  className="hover:bg-slate-50/80"
                >
                  {columns.map((c) => (
                    <td
                      key={c.key}
                      className={
                        'whitespace-nowrap px-3 py-3 text-sm text-slate-800 ' +
                        (c.className ?? '')
                      }
                    >
                      {c.render(row)}
                    </td>
                  ))}
                  {actions ? (
                    <td className="whitespace-nowrap px-3 py-3 text-right text-sm">
                      {actions(row)}
                    </td>
                  ) : null}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
