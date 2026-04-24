import { CalendarDaysIcon } from '@heroicons/react/24/outline'
import type { ColumnDef } from '@tanstack/react-table'
import { useMemo } from 'react'

import { DataTable } from '@/components/data-table/data-table'

const mes = new Intl.DateTimeFormat('es-MX', { month: 'long' }).format(new Date())

const rows = [
  { colaborador: 'María Fernández López', departamento: 'Operaciones', cumple: '15 de abril' },
  { colaborador: 'Jorge Luis Pineda', departamento: 'Ventas', cumple: '22 de abril' },
  { colaborador: 'Ana Lucía Herrera', departamento: 'Tecnología', cumple: '28 de abril' },
]

type Row = (typeof rows)[number]

export function CumpleanosTable() {
  const columns = useMemo<ColumnDef<Row>[]>(
    () => [
      {
        id: 'colaborador',
        header: 'Colaborador',
        cell: ({ row }) => <span className="font-medium text-slate-800">{row.original.colaborador}</span>,
      },
      {
        id: 'departamento',
        header: 'Departamento',
        cell: ({ row }) => (
          <span className="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
            {row.original.departamento}
          </span>
        ),
      },
      {
        id: 'cumple',
        header: 'Cumpleaños',
        cell: ({ row }) => (
          <span className="inline-flex rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-100">
            {row.original.cumple}
          </span>
        ),
      },
    ],
    [],
  )

  return (
    <div className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
      <div className="border-b border-slate-100 bg-slate-50/80 px-4 py-3">
        <h3 className="flex items-center gap-2 text-sm font-semibold text-slate-800">
          <CalendarDaysIcon className="h-5 w-5 shrink-0 text-amber-600" aria-hidden />
          Cumpleaños de {mes}
        </h3>
      </div>
      <div className="overflow-x-auto">
        <DataTable
          columns={columns}
          data={rows}
          getRowId={(r) => r.colaborador}
          headerRowClassName="border-b border-slate-100 hover:bg-transparent"
          bodyRowClassName="border-b border-slate-50 transition-colors hover:bg-slate-50/50"
        />
      </div>
    </div>
  )
}
