import type { ColumnDef } from '@tanstack/react-table'
import { useMemo, type ReactNode } from 'react'

import { DataTable } from '@/components/data-table/data-table'

type Column = { key: string; header: string; className?: string }

/** Tabla estilo listado Filament — TanStack Table + componentes shadcn/ui. */
export function MockFilamentTable({
  columns,
  rows,
  actionsColumn,
  rowKey,
}: {
  columns: Column[]
  rows: Record<string, ReactNode>[]
  actionsColumn?: {
    header?: string
    className?: string
    render: (row: Record<string, ReactNode>, index: number) => ReactNode
  }
  rowKey?: (row: Record<string, ReactNode>, index: number) => string | number
}) {
  const showActions = Boolean(actionsColumn)

  const columnDefs = useMemo<ColumnDef<Record<string, ReactNode>>[]>(() => {
    const dataCols: ColumnDef<Record<string, ReactNode>>[] = columns.map((c) => ({
      id: c.key,
      header: c.header,
      cell: ({ row }) => row.original[c.key],
      meta: {
        headerClassName: c.className,
        cellClassName: c.className,
      },
    }))
    if (!showActions || !actionsColumn) {
      return dataCols
    }
    const ac = actionsColumn
    const actionsCol: ColumnDef<Record<string, ReactNode>> = {
      id: '__actions',
      header:
        ac.header === undefined
          ? () => <span className="sr-only">Acciones</span>
          : ac.header,
      cell: ({ row }) => ac.render(row.original, row.index),
      meta: {
        headerClassName: cnHeaderActions(ac.className),
        cellClassName: cnCellActions(ac.className),
      },
    }
    return [...dataCols, actionsCol]
  }, [columns, showActions, actionsColumn])

  return (
    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <DataTable
        columns={columnDefs}
        data={rows}
        getRowId={(row, index) => String(rowKey ? rowKey(row, index) : index)}
        tableClassName="min-w-full divide-y divide-slate-200 text-sm"
        headerClassName="bg-slate-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
        cellClassName="whitespace-nowrap px-4 py-3 text-slate-800"
        bodyRowClassName="transition-colors hover:bg-slate-50/80"
        headerRowClassName="hover:bg-transparent"
      />
    </div>
  )
}

function cnHeaderActions(extra?: string): string {
  return (
    'w-px whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 ' +
    (extra ?? '')
  )
}

function cnCellActions(extra?: string): string {
  return 'whitespace-nowrap px-4 py-3 text-right text-slate-800 ' + (extra ?? '')
}
