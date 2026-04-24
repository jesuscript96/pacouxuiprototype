import {
  flexRender,
  getCoreRowModel,
  useReactTable,
  type ColumnDef,
} from '@tanstack/react-table'

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { cn } from '@/lib/utils'

export type DataTableProps<TData> = {
  columns: ColumnDef<TData, unknown>[]
  data: TData[]
  emptyMessage?: string
  getRowId?: (originalRow: TData, index: number) => string
  /** Clases del elemento `<table>` interno */
  tableClassName?: string
  /** Clases por defecto en cada `<th>` (se fusionan con `column.columnDef.meta?.headerClassName`) */
  headerClassName?: string
  /** Clases por defecto en cada `<td>` */
  cellClassName?: string
  /** Clases en `<tr>` del cuerpo */
  bodyRowClassName?: string
  /** Clases en `<tr>` del encabezado */
  headerRowClassName?: string
}

type ColumnMetaStyle = {
  headerClassName?: string
  cellClassName?: string
}

function metaClasses(meta: unknown): ColumnMetaStyle {
  if (meta && typeof meta === 'object' && !Array.isArray(meta)) {
    return meta as ColumnMetaStyle
  }
  return {}
}

export function DataTable<TData>({
  columns,
  data,
  emptyMessage = 'No hay registros para mostrar.',
  getRowId,
  tableClassName,
  headerClassName = 'px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-400',
  cellClassName = 'px-4 py-2.5 align-middle text-sm',
  bodyRowClassName = 'border-b border-slate-50',
  headerRowClassName = 'border-b border-slate-100 hover:bg-transparent',
}: DataTableProps<TData>) {
  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    ...(getRowId ? { getRowId } : {}),
  })

  const colCount = table.getAllColumns().length

  return (
    <Table className={tableClassName}>
      <TableHeader>
        {table.getHeaderGroups().map((hg) => (
          <TableRow key={hg.id} className={headerRowClassName}>
            {hg.headers.map((header) => {
              const m = metaClasses(header.column.columnDef.meta)
              return (
                <TableHead key={header.id} className={cn(headerClassName, m.headerClassName)}>
                  {header.isPlaceholder
                    ? null
                    : flexRender(header.column.columnDef.header, header.getContext())}
                </TableHead>
              )
            })}
          </TableRow>
        ))}
      </TableHeader>
      <TableBody>
        {table.getRowModel().rows.length === 0 ? (
          <TableRow className="hover:bg-transparent">
            <TableCell colSpan={colCount} className="h-24 text-center text-muted-foreground">
              {emptyMessage}
            </TableCell>
          </TableRow>
        ) : (
          table.getRowModel().rows.map((row) => (
            <TableRow key={row.id} className={bodyRowClassName}>
              {row.getVisibleCells().map((cell) => {
                const m = metaClasses(cell.column.columnDef.meta)
                return (
                  <TableCell key={cell.id} className={cn(cellClassName, m.cellClassName)}>
                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                  </TableCell>
                )
              })}
            </TableRow>
          ))
        )}
      </TableBody>
    </Table>
  )
}
