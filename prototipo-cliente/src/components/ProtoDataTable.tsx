import type { ColumnDef } from '@tanstack/react-table'
import { useMemo, type ReactNode } from 'react'

import { DataTable } from '@/components/data-table/data-table'

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
  const columnDefs = useMemo<ColumnDef<T>[]>(() => {
    const dataCols: ColumnDef<T>[] = columns.map((c) => ({
      id: c.key,
      header: c.header,
      cell: ({ row }) => c.render(row.original),
      meta: {
        cellClassName: c.className,
        headerClassName: c.className,
      },
    }))
    if (!actions) {
      return dataCols
    }
    const actionsCol: ColumnDef<T> = {
      id: '__actions',
      header: () => <span className="sr-only">Acciones</span>,
      cell: ({ row }) => actions(row.original),
      meta: {
        headerClassName: 'px-3 py-3 text-right',
        cellClassName: 'whitespace-nowrap px-3 py-3 text-right text-sm',
      },
    }
    return [...dataCols, actionsCol]
  }, [columns, actions])

  return (
    <div className="-mx-4 overflow-x-auto sm:mx-0">
      <div className="inline-block min-w-full align-middle">
        <div className="overflow-hidden rounded-md border border-slate-200 bg-white">
          <DataTable
            columns={columnDefs}
            data={rows}
            emptyMessage={emptyMessage}
            getRowId={(row) => String(rowKey(row))}
            tableClassName="min-w-full divide-y divide-slate-200"
            headerClassName="bg-slate-50/90 px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
            cellClassName="whitespace-nowrap px-3 py-3 text-sm text-slate-800"
            bodyRowClassName="border-b border-slate-100 transition-colors hover:bg-slate-50/80"
            headerRowClassName="border-b border-slate-200 hover:bg-transparent"
          />
        </div>
      </div>
    </div>
  )
}
