import {
  EyeIcon,
  PencilSquareIcon,
  PlusIcon,
  TrashIcon,
} from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'
import { ConfirmDialog } from '../../components/ConfirmDialog'
import { CrudSlideOver } from '../../components/CrudSlideOver'
import { ProtoDataTable, type ProtoColumn } from '../../components/ProtoDataTable'

type Row = {
  id: number
  nombre: string
  estado: string
  actualizado: string
}

const initial: Row[] = [
  { id: 1, nombre: 'Ana López', estado: 'Activo', actualizado: '18/04/2026' },
  { id: 2, nombre: 'Luis Herrera', estado: 'Activo', actualizado: '12/04/2026' },
  { id: 3, nombre: 'María Ruiz', estado: 'Pendiente', actualizado: '10/04/2026' },
]

type Mode = 'create' | 'edit' | 'view' | null

export function MockResourcePage({
  title,
  singular,
  nameFieldLabel = 'Nombre',
}: {
  title: string
  singular: string
  nameFieldLabel?: string
}) {
  const [rows, setRows] = useState<Row[]>(initial)
  const [panelOpen, setPanelOpen] = useState(false)
  const [mode, setMode] = useState<Mode>(null)
  const [active, setActive] = useState<Row | null>(null)
  const [name, setName] = useState('')
  const [confirmOpen, setConfirmOpen] = useState(false)
  const [pendingDelete, setPendingDelete] = useState<Row | null>(null)

  const columns: ProtoColumn<Row>[] = useMemo(
    () => [
      {
        key: 'nombre',
        header: nameFieldLabel,
        render: (r) => <span className="font-medium text-slate-900">{r.nombre}</span>,
      },
      {
        key: 'estado',
        header: 'Estado',
        render: (r) => (
          <span
            className={
              r.estado === 'Activo'
                ? 'inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/80'
                : 'inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-900 ring-1 ring-amber-200/80'
            }
          >
            {r.estado}
          </span>
        ),
      },
      {
        key: 'actualizado',
        header: 'Actualizado',
        render: (r) => <span className="text-slate-600">{r.actualizado}</span>,
      },
    ],
    [nameFieldLabel],
  )

  function openCreate() {
    setMode('create')
    setActive(null)
    setName('')
    setPanelOpen(true)
  }

  function openEdit(row: Row) {
    setMode('edit')
    setActive(row)
    setName(row.nombre)
    setPanelOpen(true)
  }

  function openView(row: Row) {
    setMode('view')
    setActive(row)
    setName(row.nombre)
    setPanelOpen(true)
  }

  function save() {
    if (mode === 'create') {
      const next: Row = {
        id: Math.max(0, ...rows.map((r) => r.id)) + 1,
        nombre: name || 'Sin nombre',
        estado: 'Activo',
        actualizado: new Date().toLocaleDateString('es-MX'),
      }
      setRows((r) => [...r, next])
    } else if (mode === 'edit' && active) {
      setRows((r) =>
        r.map((x) =>
          x.id === active.id ? { ...x, nombre: name || x.nombre, actualizado: new Date().toLocaleDateString('es-MX') } : x,
        ),
      )
    }
    setPanelOpen(false)
  }

  function confirmDelete() {
    if (!pendingDelete) {
      return
    }
    setRows((r) => r.filter((x) => x.id !== pendingDelete.id))
    setPendingDelete(null)
  }

  const panelTitle =
    mode === 'create'
      ? `Nuevo ${singular.toLowerCase()}`
      : mode === 'edit'
        ? `Editar ${singular.toLowerCase()}`
        : mode === 'view'
          ? `Ver ${singular.toLowerCase()}`
          : ''

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">{title}</h1>
          <p className="mt-1 text-sm text-slate-600">
            Datos de demostración — el alta y la edición abren en un panel lateral.
          </p>
        </div>
        <button
          type="button"
          onClick={openCreate}
          className="inline-flex items-center justify-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#2a3db0] sm:inline-flex"
        >
          <PlusIcon className="h-4 w-4" />
          Nuevo {singular.toLowerCase()}
        </button>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <ProtoDataTable
          columns={columns}
          rows={rows}
          rowKey={(r) => r.id}
          actions={(row) => (
            <div className="flex justify-end gap-1">
              <button
                type="button"
                className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-[#3148c8]"
                aria-label="Ver"
                onClick={() => openView(row)}
              >
                <EyeIcon className="h-5 w-5" />
              </button>
              <button
                type="button"
                className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-indigo-700"
                aria-label="Editar"
                onClick={() => openEdit(row)}
              >
                <PencilSquareIcon className="h-5 w-5" />
              </button>
              <button
                type="button"
                className="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-600"
                aria-label="Eliminar"
                onClick={() => {
                  setPendingDelete(row)
                  setConfirmOpen(true)
                }}
              >
                <TrashIcon className="h-5 w-5" />
              </button>
            </div>
          )}
        />
      </div>

      <CrudSlideOver
        open={panelOpen}
        onClose={() => setPanelOpen(false)}
        title={panelTitle}
        footer={
          mode === 'view' ? (
            <div className="flex justify-end">
              <button
                type="button"
                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                onClick={() => setPanelOpen(false)}
              >
                Cerrar
              </button>
            </div>
          ) : (
            <div className="flex flex-wrap justify-end gap-2">
              <button
                type="button"
                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                onClick={() => setPanelOpen(false)}
              >
                Cancelar
              </button>
              <button
                type="button"
                className="rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0]"
                onClick={save}
              >
                Guardar
              </button>
            </div>
          )
        }
      >
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700" htmlFor="nombre">
              {nameFieldLabel}
            </label>
            <input
              id="nombre"
              className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20 disabled:bg-slate-50"
              value={name}
              onChange={(e) => setName(e.target.value)}
              disabled={mode === 'view'}
            />
          </div>
          {mode === 'view' && active ? (
            <p className="text-sm text-slate-500">
              Estado: <strong>{active.estado}</strong> · Actualizado{' '}
              <strong>{active.actualizado}</strong>
            </p>
          ) : null}
        </div>
      </CrudSlideOver>

      <ConfirmDialog
        open={confirmOpen}
        onClose={() => {
          setConfirmOpen(false)
          setPendingDelete(null)
        }}
        title="¿Eliminar registro?"
        description="Esta acción es solo de demostración y quita la fila de la lista en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
