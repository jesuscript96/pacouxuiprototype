import { UsersIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { CrudSlideOver } from '../../../components/CrudSlideOver'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { protoInputClass, protoLabelClass, protoSelectClass } from '../../../components/ux/protoFormStyles'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'

function estadoBadge(label: string) {
  const map: Record<string, string> = {
    Activo: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    'Baja programada': 'bg-amber-50 text-amber-900 ring-amber-200/80',
    'Dado de baja': 'bg-rose-50 text-rose-800 ring-rose-200/80',
  }
  return (
    <span
      className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${map[label] ?? 'bg-slate-50 text-slate-700 ring-slate-200'}`}
    >
      {label}
    </span>
  )
}

function boolSi(valor: boolean) {
  return <span className={valor ? 'text-emerald-700' : 'text-slate-400'}>{valor ? 'Sí' : 'No'}</span>
}

type ColabRow = {
  id: string
  estado: string
  nombre: string
  numero: string
  empresa: string
  departamento: string
  puesto: string
  ingreso: string
  cuenta: boolean
}

const INITIAL_COLAB: ColabRow[] = [
  {
    id: '1204',
    estado: 'Activo',
    nombre: 'Ana López Martínez',
    numero: 'EMP-8821',
    empresa: 'Acme SA',
    departamento: 'Tecnología',
    puesto: 'Desarrolladora',
    ingreso: '15/01/2022',
    cuenta: true,
  },
  {
    id: '1205',
    estado: 'Baja programada',
    nombre: 'Luis Herrera Ruiz',
    numero: 'EMP-7710',
    empresa: 'Acme SA',
    departamento: 'Finanzas',
    puesto: 'Analista',
    ingreso: '03/06/2019',
    cuenta: true,
  },
  {
    id: '1188',
    estado: 'Dado de baja',
    nombre: 'Patricia Núñez Soto',
    numero: 'EMP-5402',
    empresa: 'Acme SA',
    departamento: 'Ventas',
    puesto: 'Ejecutiva',
    ingreso: '10/03/2018',
    cuenta: false,
  },
]

const ESTADOS = ['Activo', 'Baja programada', 'Dado de baja'] as const

type PanelMode = 'create' | 'edit' | 'view' | null

function nextColabId(rows: ColabRow[]): string {
  const max = rows.reduce((m, r) => Math.max(m, Number.parseInt(r.id, 10) || 0), 0)
  return String(max + 1)
}

export function ColaboradoresUxPage() {
  const [search, setSearch] = useState('')
  const [rows, setRows] = useState<ColabRow[]>(() => [...INITIAL_COLAB])
  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [form, setForm] = useState({
    nombre: '',
    numero: '',
    empresa: 'Acme SA',
    departamento: '',
    puesto: '',
    ingreso: '',
    estado: 'Activo' as string,
    cuenta: true,
  })
  const [editingId, setEditingId] = useState<string | null>(null)
  const [deleteId, setDeleteId] = useState<string | null>(null)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return rows.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.nombre, r.numero, r.departamento, r.puesto, r.estado].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    })
  }, [rows, search])

  const displayRows = useMemo(
    () =>
      filtered.map((r) => ({
        id: r.id,
        estado: estadoBadge(r.estado),
        nombre: r.nombre,
        numero: r.numero,
        empresa: r.empresa,
        departamento: r.departamento,
        puesto: r.puesto,
        ingreso: r.ingreso,
        cuenta: boolSi(r.cuenta),
      })),
    [filtered],
  )

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setEditingId(null)
  }, [])

  const openCreate = useCallback(() => {
    setPanelMode('create')
    setEditingId(null)
    setForm({
      nombre: '',
      numero: '',
      empresa: 'Acme SA',
      departamento: '',
      puesto: '',
      ingreso: new Date().toLocaleDateString('es-MX'),
      estado: 'Activo',
      cuenta: true,
    })
  }, [])

  const openEdit = useCallback((r: ColabRow) => {
    setPanelMode('edit')
    setEditingId(r.id)
    setForm({
      nombre: r.nombre,
      numero: r.numero,
      empresa: r.empresa,
      departamento: r.departamento,
      puesto: r.puesto,
      ingreso: r.ingreso,
      estado: r.estado,
      cuenta: r.cuenta,
    })
  }, [])

  const openView = useCallback((r: ColabRow) => {
    setPanelMode('view')
    setEditingId(r.id)
    setForm({
      nombre: r.nombre,
      numero: r.numero,
      empresa: r.empresa,
      departamento: r.departamento,
      puesto: r.puesto,
      ingreso: r.ingreso,
      estado: r.estado,
      cuenta: r.cuenta,
    })
  }, [])

  const save = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    if (panelMode === 'create') {
      setRows((list) => {
        const id = nextColabId(list)
        return [
          ...list,
          {
            id,
            nombre: form.nombre.trim() || 'Sin nombre',
            numero: form.numero.trim() || `EMP-${id}`,
            empresa: form.empresa.trim() || 'Acme SA',
            departamento: form.departamento.trim() || '—',
            puesto: form.puesto.trim() || '—',
            ingreso: form.ingreso.trim() || new Date().toLocaleDateString('es-MX'),
            estado: form.estado,
            cuenta: form.cuenta,
          },
        ]
      })
    } else if (editingId) {
      setRows((list) =>
        list.map((r) =>
          r.id === editingId
            ? {
                ...r,
                nombre: form.nombre.trim() || r.nombre,
                numero: form.numero.trim() || r.numero,
                empresa: form.empresa.trim() || r.empresa,
                departamento: form.departamento.trim() || r.departamento,
                puesto: form.puesto.trim() || r.puesto,
                ingreso: form.ingreso.trim() || r.ingreso,
                estado: form.estado,
                cuenta: form.cuenta,
              }
            : r,
        ),
      )
    }
    closePanel()
  }, [closePanel, editingId, form, panelMode])

  const confirmDelete = useCallback(() => {
    if (!deleteId) {
      return
    }
    setRows((list) => list.filter((r) => r.id !== deleteId))
    setDeleteId(null)
  }, [deleteId])

  const readOnly = panelMode === 'view'
  const panelTitle =
    panelMode === 'create'
      ? 'Nuevo colaborador'
      : panelMode === 'edit'
        ? 'Editar colaborador'
        : panelMode === 'view'
          ? 'Ver colaborador'
          : ''

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Gestión de personal"
        title="Ficha RH de la organización"
        description="La ficha RH de tu organización. Consulta, importa y edita masivamente la información de todos tus colaboradores activos e inactivos."
        icon={UsersIcon}
      />

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Colaboradores"
          newLabel="Nuevo colaborador"
          onNew={openCreate}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por nombre, ID, puesto…"
          hint="Listado con acciones CRUD de demostración (sin backend)."
        />
        <MockFilamentTable
          columns={[
            { key: 'id', header: 'ID', className: 'text-right' },
            { key: 'estado', header: 'Estado', className: 'text-center' },
            { key: 'nombre', header: 'Nombre completo' },
            { key: 'numero', header: 'Nº empleado' },
            { key: 'empresa', header: 'Empresa' },
            { key: 'departamento', header: 'Departamento' },
            { key: 'puesto', header: 'Puesto' },
            { key: 'ingreso', header: 'Fecha ingreso' },
            { key: 'cuenta', header: 'Cuenta activa', className: 'text-center' },
          ]}
          rows={displayRows}
          rowKey={(row) => String(row.id)}
          actionsColumn={{
            render: (_row, i) => {
              const raw = filtered[i]
              if (!raw) {
                return null
              }
              return (
                <UxCrudRowActions
                  onView={() => openView(raw)}
                  onEdit={() => openEdit(raw)}
                  onDelete={() => setDeleteId(raw.id)}
                />
              )
            },
          }}
        />
      </div>

      <CrudSlideOver
        open={panelMode !== null}
        onClose={closePanel}
        title={panelTitle}
        footer={
          readOnly ? (
            <div className="flex justify-end">
              <button
                type="button"
                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                onClick={closePanel}
              >
                Cerrar
              </button>
            </div>
          ) : (
            <div className="flex flex-wrap justify-end gap-2">
              <button
                type="button"
                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                onClick={closePanel}
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
            <label className={protoLabelClass} htmlFor="colab-nombre">
              Nombre completo
            </label>
            <input
              id="colab-nombre"
              className={protoInputClass}
              value={form.nombre}
              onChange={(e) => setForm((f) => ({ ...f, nombre: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="colab-numero">
              Nº empleado
            </label>
            <input
              id="colab-numero"
              className={protoInputClass}
              value={form.numero}
              onChange={(e) => setForm((f) => ({ ...f, numero: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="colab-empresa">
              Empresa
            </label>
            <input
              id="colab-empresa"
              className={protoInputClass}
              value={form.empresa}
              onChange={(e) => setForm((f) => ({ ...f, empresa: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="colab-depto">
              Departamento
            </label>
            <input
              id="colab-depto"
              className={protoInputClass}
              value={form.departamento}
              onChange={(e) => setForm((f) => ({ ...f, departamento: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="colab-puesto">
              Puesto
            </label>
            <input
              id="colab-puesto"
              className={protoInputClass}
              value={form.puesto}
              onChange={(e) => setForm((f) => ({ ...f, puesto: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="colab-ingreso">
              Fecha de ingreso
            </label>
            <input
              id="colab-ingreso"
              className={protoInputClass}
              value={form.ingreso}
              onChange={(e) => setForm((f) => ({ ...f, ingreso: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="colab-estado">
              Estado
            </label>
            <select
              id="colab-estado"
              className={protoSelectClass}
              value={form.estado}
              onChange={(e) => setForm((f) => ({ ...f, estado: e.target.value }))}
              disabled={readOnly}
            >
              {ESTADOS.map((s) => (
                <option key={s} value={s}>
                  {s}
                </option>
              ))}
            </select>
          </div>
          <label className="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
            <input
              type="checkbox"
              className="rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30"
              checked={form.cuenta}
              onChange={(e) => setForm((f) => ({ ...f, cuenta: e.target.checked }))}
              disabled={readOnly}
            />
            Cuenta activa en el sistema
          </label>
        </div>
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteId !== null}
        onClose={() => setDeleteId(null)}
        title="¿Eliminar registro?"
        description="Demo en memoria: la fila desaparece del listado. En producción se validarían dependencias y permisos."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
