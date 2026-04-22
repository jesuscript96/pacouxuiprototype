import { ArrowUpTrayIcon, DocumentTextIcon } from '@heroicons/react/24/outline'
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
import { UX_CARTAS_CARGAR, UX_CARTAS_HERO, UX_CARTAS_VER } from '../../../guidance/uxSections'

const tabs: UxTab[] = [
  {
    id: 'ver',
    label: 'Ver cartas',
    icon: DocumentTextIcon,
    description: 'Consultar emitidas',
  },
  {
    id: 'cargar',
    label: 'Cargar registros',
    icon: ArrowUpTrayIcon,
    description: 'Batch de nuevas cartas',
  },
]

function estadoBadge(label: string, tone: 'success' | 'warning' | 'gray') {
  const map = {
    success: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    warning: 'bg-amber-50 text-amber-900 ring-amber-200/80',
    gray: 'bg-slate-50 text-slate-700 ring-slate-200/80',
  } as const
  return (
    <span className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${map[tone]}`}>
      {label}
    </span>
  )
}

function colaboradorCell(nombre: string, numero: string) {
  return (
    <div>
      <div className="font-medium text-slate-900">{nombre}</div>
      <div className="text-xs text-slate-500">Nº {numero}</div>
    </div>
  )
}

type CartaVerRaw = {
  key: string
  nombre: string
  numero: string
  bimestre: string
  razon: string
  total: string
  estadoLabel: string
  estadoTone: 'success' | 'warning' | 'gray'
}

const INITIAL_CARTAS: CartaVerRaw[] = [
  {
    key: 'cs1',
    nombre: 'Ricardo Sánchez Pérez',
    numero: '10482',
    bimestre: '2026-1',
    razon: 'Acme SA de CV',
    total: '$ 12,450.00',
    estadoLabel: 'Firmada',
    estadoTone: 'success',
  },
  {
    key: 'cs2',
    nombre: 'Laura Méndez Ruiz',
    numero: '9821',
    bimestre: '2026-1',
    razon: 'Acme SA de CV',
    total: '$ 8,920.50',
    estadoLabel: 'Vista',
    estadoTone: 'warning',
  },
  {
    key: 'cs3',
    nombre: 'Héctor Ruiz López',
    numero: '7710',
    bimestre: '2026-1',
    razon: 'Servicios Acme Norte SA',
    total: '$ 15,200.00',
    estadoLabel: 'Pendiente',
    estadoTone: 'gray',
  },
]

const ESTADO_OPTS: { label: string; tone: 'success' | 'warning' | 'gray' }[] = [
  { label: 'Firmada', tone: 'success' },
  { label: 'Vista', tone: 'warning' },
  { label: 'Pendiente', tone: 'gray' },
]

type PanelMode = 'create' | 'edit' | 'view' | null

export function CartasSuaPage() {
  const [active, setActive] = useState('ver')
  const [search, setSearch] = useState('')
  const [cartas, setCartas] = useState<CartaVerRaw[]>(() => [...INITIAL_CARTAS])

  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [form, setForm] = useState({
    nombre: '',
    numero: '',
    bimestre: '',
    razon: '',
    total: '',
    estadoLabel: 'Pendiente',
    estadoTone: 'gray' as 'success' | 'warning' | 'gray',
  })
  const [editingKey, setEditingKey] = useState<string | null>(null)
  const [deleteKey, setDeleteKey] = useState<string | null>(null)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return cartas.filter((r) => {
      if (!q) {
        return true
      }
      return [r.nombre, r.numero, r.bimestre, r.razon, r.total, r.estadoLabel].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    })
  }, [cartas, search])

  const verRows = useMemo(
    () =>
      filtered.map((r) => ({
        _key: r.key,
        colaborador: colaboradorCell(r.nombre, r.numero),
        bimestre: r.bimestre,
        razon: r.razon,
        total: r.total,
        estado: estadoBadge(r.estadoLabel, r.estadoTone),
      })),
    [filtered],
  )

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setEditingKey(null)
  }, [])

  const openCreate = useCallback(() => {
    setPanelMode('create')
    setEditingKey(null)
    setForm({
      nombre: '',
      numero: '',
      bimestre: '2026-1',
      razon: 'Acme SA de CV',
      total: '$ 0.00',
      estadoLabel: 'Pendiente',
      estadoTone: 'gray',
    })
  }, [])

  const openEdit = useCallback((r: CartaVerRaw) => {
    setPanelMode('edit')
    setEditingKey(r.key)
    setForm({
      nombre: r.nombre,
      numero: r.numero,
      bimestre: r.bimestre,
      razon: r.razon,
      total: r.total,
      estadoLabel: r.estadoLabel,
      estadoTone: r.estadoTone,
    })
  }, [])

  const openView = useCallback((r: CartaVerRaw) => {
    setPanelMode('view')
    setEditingKey(r.key)
    setForm({
      nombre: r.nombre,
      numero: r.numero,
      bimestre: r.bimestre,
      razon: r.razon,
      total: r.total,
      estadoLabel: r.estadoLabel,
      estadoTone: r.estadoTone,
    })
  }, [])

  const save = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    const toneFromLabel = ESTADO_OPTS.find((o) => o.label === form.estadoLabel)?.tone ?? 'gray'
    const row: CartaVerRaw = {
      key: editingKey ?? `cs${Date.now()}`,
      nombre: form.nombre.trim() || 'Sin nombre',
      numero: form.numero.trim() || '—',
      bimestre: form.bimestre.trim() || '—',
      razon: form.razon.trim() || '—',
      total: form.total.trim() || '$ 0.00',
      estadoLabel: form.estadoLabel,
      estadoTone: toneFromLabel,
    }
    if (panelMode === 'create') {
      setCartas((list) => [...list, row])
    } else if (editingKey) {
      setCartas((list) => list.map((x) => (x.key === editingKey ? row : x)))
    }
    closePanel()
  }, [closePanel, editingKey, form, panelMode])

  const confirmDelete = useCallback(() => {
    if (!deleteKey) {
      return
    }
    setCartas((list) => list.filter((x) => x.key !== deleteKey))
    setDeleteKey(null)
  }, [deleteKey])

  const readOnly = panelMode === 'view'
  const panelTitle =
    panelMode === 'create'
      ? 'Nueva carta (demo)'
      : panelMode === 'edit'
        ? 'Editar carta SUA'
        : panelMode === 'view'
          ? 'Ver carta SUA'
          : ''

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Nómina · IMSS · SUA"
        title="Cartas del ciclo de nómina"
        description="Genera y administra las cartas SUA de tus colaboradores. Carga los registros en lote, consulta las emitidas y monitorea firmas electrónicas."
        icon={DocumentTextIcon}
        guidance={UX_CARTAS_HERO}
      />

      <UxTabs
        tabs={tabs}
        active={active}
        onChange={(id) => {
          setActive(id)
          setSearch('')
        }}
      />

      {active === 'ver' ? (
        <div className="space-y-4">
          <DevGuidanceInline content={UX_CARTAS_VER} />
          <FilamentListToolbar
            heading="Cartas emitidas"
            newLabel="Nueva carta (demo)"
            onNew={openCreate}
            searchValue={search}
            onSearchChange={setSearch}
            searchPlaceholder="Buscar colaborador, bimestre o razón social…"
            hint="Listado con acciones CRUD de demostración (sin backend)."
          />
          <MockFilamentTable
            columns={[
              { key: 'colaborador', header: 'Colaborador' },
              { key: 'bimestre', header: 'Bimestre' },
              { key: 'razon', header: 'Razón social' },
              { key: 'total', header: 'Total', className: 'text-right' },
              { key: 'estado', header: 'Estado', className: 'text-center' },
            ]}
            rows={verRows}
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
      ) : (
        <div className="space-y-4">
          <DevGuidanceInline content={UX_CARTAS_CARGAR} />
          <FilamentListToolbar
            heading="Carga masiva"
            newLabel="Nueva importación"
            onNew={() => {}}
            searchValue={search}
            onSearchChange={setSearch}
            searchPlaceholder="Buscar lote (demo)…"
            hint="La búsqueda es solo visual en esta pestaña; el archivo se simula con el botón inferior."
          />
          <div className="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center">
            <p className="text-sm text-slate-600">
              Zona de carga por lotes — en el panel Laravel se usa un formulario con validación y seguimiento de
              importación.
            </p>
            <button
              type="button"
              className="mt-4 inline-flex items-center justify-center rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#2a3db0]"
            >
              Seleccionar archivo (demo)
            </button>
          </div>
        </div>
      )}

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
            <label className={protoLabelClass} htmlFor="cs-nombre">
              Colaborador
            </label>
            <input
              id="cs-nombre"
              className={protoInputClass}
              value={form.nombre}
              onChange={(e) => setForm((f) => ({ ...f, nombre: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="cs-numero">
              Nº empleado
            </label>
            <input
              id="cs-numero"
              className={protoInputClass}
              value={form.numero}
              onChange={(e) => setForm((f) => ({ ...f, numero: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="cs-bim">
              Bimestre
            </label>
            <input
              id="cs-bim"
              className={protoInputClass}
              value={form.bimestre}
              onChange={(e) => setForm((f) => ({ ...f, bimestre: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="cs-razon">
              Razón social
            </label>
            <input
              id="cs-razon"
              className={protoInputClass}
              value={form.razon}
              onChange={(e) => setForm((f) => ({ ...f, razon: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="cs-total">
              Total
            </label>
            <input
              id="cs-total"
              className={protoInputClass}
              value={form.total}
              onChange={(e) => setForm((f) => ({ ...f, total: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="cs-estado">
              Estado
            </label>
            <select
              id="cs-estado"
              className={protoSelectClass}
              value={form.estadoLabel}
              onChange={(e) => {
                const label = e.target.value
                const tone = ESTADO_OPTS.find((o) => o.label === label)?.tone ?? 'gray'
                setForm((f) => ({ ...f, estadoLabel: label, estadoTone: tone }))
              }}
              disabled={readOnly}
            >
              {ESTADO_OPTS.map((o) => (
                <option key={o.label} value={o.label}>
                  {o.label}
                </option>
              ))}
            </select>
          </div>
        </div>
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteKey !== null}
        onClose={() => setDeleteKey(null)}
        title="¿Eliminar carta?"
        description="Solo demostración: la fila se elimina del listado en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
