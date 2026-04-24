import { TrophyIcon } from '@heroicons/react/24/outline'
import type { ColumnDef } from '@tanstack/react-table'
import { useMemo } from 'react'

import { DataTable } from '@/components/data-table/data-table'

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

type Row = (typeof rows)[number]

export function AniversariosTable() {
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
        id: 'fechaIngreso',
        header: 'Fecha ingreso',
        cell: ({ row }) => <span className="text-slate-600">{row.original.fechaIngreso}</span>,
      },
      {
        id: 'antiguedad',
        header: 'Antigüedad',
        cell: ({ row }) => (
          <span className="inline-flex rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-800 ring-1 ring-emerald-100">
            {row.original.antiguedad}
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
          <TrophyIcon className="h-5 w-5 shrink-0 text-violet-600" aria-hidden />
          Aniversarios de {mes}
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
