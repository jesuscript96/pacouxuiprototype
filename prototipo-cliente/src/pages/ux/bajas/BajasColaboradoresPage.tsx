import { ArchiveBoxIcon, ClockIcon, UserMinusIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { CrudSlideOver } from '../../../components/CrudSlideOver'
import { DevGuidanceInline } from '../../../components/DevGuidanceInline'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { protoInputClass, protoLabelClass, protoSelectClass } from '../../../components/ux/protoFormStyles'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UxTabs, type UxTab } from '../../../components/ux/UxTabs'
import { UX_BAJAS_HISTORIAL, UX_BAJAS_PENDIENTES } from '../../../guidance/uxSections'

const tabs: UxTab[] = [
  {
    id: 'pendientes',
    label: 'Pendientes',
    icon: ClockIcon,
    description: 'Solicitudes en revisión',
  },
  {
    id: 'historial',
    label: 'Historial',
    icon: ArchiveBoxIcon,
    description: 'Bajas registradas',
  },
]

function badgeMotivo(label: string) {
  const map: Record<string, string> = {
    Renuncia: 'bg-sky-50 text-sky-900 ring-sky-200/80',
    Despido: 'bg-rose-50 text-rose-800 ring-rose-200/80',
    'Término de contrato': 'bg-emerald-50 text-emerald-900 ring-emerald-200/80',
    Abandono: 'bg-amber-50 text-amber-900 ring-amber-200/80',
  }
  const cls = map[label] ?? 'bg-slate-50 text-slate-700 ring-slate-200/80'
  return (
    <span className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${cls}`}>{label}</span>
  )
}

function badgeEstadoBaja(label: string) {
  const map: Record<string, string> = {
    Programada: 'bg-amber-50 text-amber-900 ring-amber-200/80',
    Ejecutada: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    Cancelada: 'bg-rose-50 text-rose-800 ring-rose-200/80',
  }
  const cls = map[label] ?? 'bg-slate-50 text-slate-700 ring-slate-200/80'
  return (
    <span className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${cls}`}>{label}</span>
  )
}

type RawRow = {
  key: string
  colaborador: string
  email: string
  fecha_baja: string
  motivo: string
  estado: string
  departamento: string
}

const INITIAL_PENDIENTES: RawRow[] = [
  {
    key: 'p1',
    colaborador: 'Ricardo Sánchez Pérez',
    email: 'ricardo.sanchez@ejemplo.com',
    fecha_baja: '30/04/2026',
    motivo: 'Renuncia',
    estado: 'Programada',
    departamento: 'Operaciones',
  },
  {
    key: 'p2',
    colaborador: 'Laura Méndez Ruiz',
    email: 'laura.mendez@ejemplo.com',
    fecha_baja: '15/05/2026',
    motivo: 'Abandono',
    estado: 'Programada',
    departamento: 'Finanzas',
  },
]

const INITIAL_HISTORIAL: RawRow[] = [
  {
    key: 'h1',
    colaborador: 'Héctor Ruiz López',
    email: 'hector.ruiz@ejemplo.com',
    fecha_baja: '02/03/2026',
    motivo: 'Despido',
    estado: 'Ejecutada',
    departamento: 'Logística',
  },
  {
    key: 'h2',
    colaborador: 'Patricia Núñez Soto',
    email: 'patricia.nunez@ejemplo.com',
    fecha_baja: '15/01/2026',
    motivo: 'Término de contrato',
    estado: 'Ejecutada',
    departamento: 'Ventas',
  },
]

const MOTIVOS = ['Renuncia', 'Despido', 'Término de contrato', 'Abandono'] as const
const ESTADOS = ['Programada', 'Ejecutada', 'Cancelada'] as const

type PanelMode = 'create' | 'edit' | 'view' | null

function mapRawToRow(r: RawRow) {
  return {
    _key: r.key,
    colaborador: r.colaborador,
    email: r.email,
    fecha_baja: r.fecha_baja,
    motivo: badgeMotivo(r.motivo),
    estado: badgeEstadoBaja(r.estado),
    departamento: r.departamento,
  }
}

export function BajasColaboradoresPage() {
  const [active, setActive] = useState('pendientes')
  const [search, setSearch] = useState('')
  const [pendientes, setPendientes] = useState<RawRow[]>(() => [...INITIAL_PENDIENTES])
  const [historial, setHistorial] = useState<RawRow[]>(() => [...INITIAL_HISTORIAL])

  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [form, setForm] = useState({
    colaborador: '',
    email: '',
    fecha_baja: '',
    motivo: 'Renuncia',
    estado: 'Programada',
    departamento: '',
  })
  const [editingKey, setEditingKey] = useState<string | null>(null)
  const [deleteKey, setDeleteKey] = useState<string | null>(null)

  const source = active === 'pendientes' ? pendientes : historial
  const setSource = active === 'pendientes' ? setPendientes : setHistorial

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return source.filter((r) => {
      if (!q) {
        return true
      }
      return [r.colaborador, r.email, r.departamento, r.motivo, r.estado].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    })
  }, [source, search])

  const rows = useMemo(() => filtered.map(mapRawToRow), [filtered])

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setEditingKey(null)
  }, [])

  const openCreate = useCallback(() => {
    setPanelMode('create')
    setEditingKey(null)
    setForm({
      colaborador: '',
      email: '',
      fecha_baja: new Date().toLocaleDateString('es-MX'),
      motivo: 'Renuncia',
      estado: active === 'pendientes' ? 'Programada' : 'Ejecutada',
      departamento: '',
    })
  }, [active])

  const openEdit = useCallback((r: RawRow) => {
    setPanelMode('edit')
    setEditingKey(r.key)
    setForm({
      colaborador: r.colaborador,
      email: r.email,
      fecha_baja: r.fecha_baja,
      motivo: r.motivo,
      estado: r.estado,
      departamento: r.departamento,
    })
  }, [])

  const openView = useCallback((r: RawRow) => {
    setPanelMode('view')
    setEditingKey(r.key)
    setForm({
      colaborador: r.colaborador,
      email: r.email,
      fecha_baja: r.fecha_baja,
      motivo: r.motivo,
      estado: r.estado,
      departamento: r.departamento,
    })
  }, [])

  const save = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    const rowData: RawRow = {
      key: editingKey ?? `b${Date.now()}`,
      colaborador: form.colaborador.trim() || 'Sin nombre',
      email: form.email.trim() || 'sin-email@ejemplo.com',
      fecha_baja: form.fecha_baja.trim() || '—',
      motivo: form.motivo,
      estado: form.estado,
      departamento: form.departamento.trim() || '—',
    }
    if (panelMode === 'create') {
      setSource((list) => [...list, rowData])
    } else if (editingKey) {
      setSource((list) => list.map((x) => (x.key === editingKey ? rowData : x)))
    }
    closePanel()
  }, [closePanel, editingKey, form, panelMode, setSource])

  const confirmDelete = useCallback(() => {
    if (!deleteKey) {
      return
    }
    setSource((list) => list.filter((x) => x.key !== deleteKey))
    setDeleteKey(null)
  }, [deleteKey, setSource])

  const readOnly = panelMode === 'view'
  const panelTitle =
    panelMode === 'create'
      ? active === 'pendientes'
        ? 'Nueva solicitud de baja'
        : 'Registrar baja (demo)'
      : panelMode === 'edit'
        ? 'Editar registro de baja'
        : panelMode === 'view'
          ? 'Ver registro de baja'
          : ''

  const toolbar =
    active === 'pendientes' ? (
      <FilamentListToolbar
        heading="Solicitudes pendientes"
        newLabel="Nueva solicitud de baja"
        onNew={openCreate}
        searchValue={search}
        onSearchChange={setSearch}
        searchPlaceholder="Buscar colaborador o departamento…"
        hint="Listado con acciones CRUD de demostración (sin backend)."
      />
    ) : (
      <FilamentListToolbar
        heading="Historial de bajas"
        newLabel="Registrar baja (demo)"
        onNew={openCreate}
        searchValue={search}
        onSearchChange={setSearch}
        searchPlaceholder="Buscar en historial…"
        hint="Listado con acciones CRUD de demostración (sin backend)."
      />
    )

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Gestión de personal"
        title="Bajas de colaboradores"
        description="Seguimiento de solicitudes de baja: el colaborador no se elimina del sistema; se registra el evento y el historial queda disponible para auditoría."
        icon={UserMinusIcon}
        stat={{
          label: 'Pendientes',
          value: String(pendientes.length),
          hint: 'Mock — sin backend',
        }}
      />

      <UxTabs
        tabs={tabs}
        active={active}
        onChange={(id) => {
          setActive(id)
          setSearch('')
        }}
      />

      <DevGuidanceInline content={active === 'pendientes' ? UX_BAJAS_PENDIENTES : UX_BAJAS_HISTORIAL} />

      <div className="space-y-4">
        {toolbar}
        <MockFilamentTable
          columns={[
            { key: 'colaborador', header: 'Colaborador' },
            { key: 'email', header: 'Email' },
            { key: 'fecha_baja', header: 'Fecha de baja' },
            { key: 'motivo', header: 'Motivo', className: 'text-center' },
            { key: 'estado', header: 'Estado', className: 'text-center' },
            {
              key: 'departamento',
              header: 'Departamento al momento de la baja',
            },
          ]}
          rows={rows}
          rowKey={(row) => String(row._key)}
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
                  onDelete={() => setDeleteKey(raw.key)}
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
            <label className={protoLabelClass} htmlFor="baja-colab">
              Colaborador
            </label>
            <input
              id="baja-colab"
              className={protoInputClass}
              value={form.colaborador}
              onChange={(e) => setForm((f) => ({ ...f, colaborador: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="baja-email">
              Email
            </label>
            <input
              id="baja-email"
              type="email"
              className={protoInputClass}
              value={form.email}
              onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="baja-fecha">
              Fecha de baja
            </label>
            <input
              id="baja-fecha"
              className={protoInputClass}
              value={form.fecha_baja}
              onChange={(e) => setForm((f) => ({ ...f, fecha_baja: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="baja-motivo">
              Motivo
            </label>
            <select
              id="baja-motivo"
              className={protoSelectClass}
              value={form.motivo}
              onChange={(e) => setForm((f) => ({ ...f, motivo: e.target.value }))}
              disabled={readOnly}
            >
              {MOTIVOS.map((m) => (
                <option key={m} value={m}>
                  {m}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="baja-estado">
              Estado
            </label>
            <select
              id="baja-estado"
              className={protoSelectClass}
              value={form.estado}
              onChange={(e) => setForm((f) => ({ ...f, estado: e.target.value }))}
              disabled={readOnly}
            >
              {ESTADOS.map((m) => (
                <option key={m} value={m}>
                  {m}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="baja-depto">
              Departamento
            </label>
            <input
              id="baja-depto"
              className={protoInputClass}
              value={form.departamento}
              onChange={(e) => setForm((f) => ({ ...f, departamento: e.target.value }))}
              disabled={readOnly}
            />
          </div>
        </div>
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteKey !== null}
        onClose={() => setDeleteKey(null)}
        title="¿Eliminar registro?"
        description="Solo demostración: la fila se elimina de la lista en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
