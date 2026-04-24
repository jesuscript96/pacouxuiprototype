import { ExclamationTriangleIcon } from '@heroicons/react/24/outline'
import type { ColumnDef } from '@tanstack/react-table'
import { useMemo } from 'react'

import { DataTable } from '@/components/data-table/data-table'

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

type Row = (typeof rows)[number]

function motivoBadge(tone: Row['motivoTone'], label: string) {
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
  const columns = useMemo<ColumnDef<Row>[]>(
    () => [
      {
        id: 'colaborador',
        header: 'Colaborador',
        accessorKey: 'colaborador',
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
      { id: 'puesto', header: 'Puesto', accessorKey: 'puesto', cell: ({ row }) => <span className="text-slate-600">{row.original.puesto}</span> },
      {
        id: 'motivo',
        header: 'Motivo',
        cell: ({ row }) => motivoBadge(row.original.motivoTone, row.original.motivo),
      },
      {
        id: 'fecha',
        header: 'Fecha de baja',
        cell: ({ row }) => (
          <span className="inline-flex rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">
            {row.original.fecha}
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
          <ExclamationTriangleIcon className="h-5 w-5 shrink-0 text-amber-600" aria-hidden />
          Bajas programadas próximas
        </h3>
      </div>
      <div className="overflow-x-auto">
        <DataTable
          columns={columns}
          data={rows}
          getRowId={(r) => r.colaborador + r.fecha}
          headerRowClassName="border-b border-slate-100 hover:bg-transparent"
          bodyRowClassName="border-b border-slate-50 transition-colors hover:bg-slate-50/50"
        />
      </div>
    </div>
  )
}
