import { BriefcaseIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { CrudSlideOver } from '../../../components/CrudSlideOver'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { protoInputClass, protoLabelClass } from '../../../components/ux/protoFormStyles'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UX_VACANTES } from '../../../guidance/uxSections'

function badgeCandidatos(n: number) {
  return (
    <span className="inline-flex min-w-[1.75rem] justify-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-800 ring-1 ring-indigo-200/80">
      {n}
    </span>
  )
}

type VacRow = {
  key: string
  puesto: string
  n: number
  creado: string
}

const INITIAL: VacRow[] = [
  {
    key: 'v1',
    puesto: 'Ingeniero de datos (senior)',
    n: 14,
    creado: '08/04/2026 10:22',
  },
  {
    key: 'v2',
    puesto: 'Ejecutivo de cuenta zona norte',
    n: 6,
    creado: '02/04/2026 14:05',
  },
  {
    key: 'v3',
    puesto: 'Analista de nómina',
    n: 22,
    creado: '28/03/2026 09:18',
  },
]

type PanelMode = 'create' | 'edit' | 'view' | null

export function VacantesUxPage() {
  const [search, setSearch] = useState('')
  const [rows, setRows] = useState<VacRow[]>(() => [...INITIAL])
  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [form, setForm] = useState({ puesto: '', n: 0 })
  const [editingKey, setEditingKey] = useState<string | null>(null)
  const [deleteKey, setDeleteKey] = useState<string | null>(null)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return rows.filter((r) => {
      if (!q) {
        return true
      }
      return r.puesto.toLowerCase().includes(q) || r.creado.toLowerCase().includes(q)
    })
  }, [rows, search])

  const displayRows = useMemo(
    () =>
      filtered.map((r) => ({
        puesto: r.puesto,
        candidatos: badgeCandidatos(r.n),
        creado: r.creado,
        _key: r.key,
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
    setForm({ puesto: '', n: 0 })
  }, [])

  const openEdit = useCallback((r: VacRow) => {
    setPanelMode('edit')
    setEditingKey(r.key)
    setForm({ puesto: r.puesto, n: r.n })
  }, [])

  const openView = useCallback((r: VacRow) => {
    setPanelMode('view')
    setEditingKey(r.key)
    setForm({ puesto: r.puesto, n: r.n })
  }, [])

  const save = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    const now = new Date()
    const creado = now.toLocaleString('es-MX', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
    if (panelMode === 'create') {
      const key = `v${Date.now()}`
      setRows((list) => [
        ...list,
        {
          key,
          puesto: form.puesto.trim() || 'Vacante sin título',
          n: Number.isFinite(form.n) ? Math.max(0, Math.floor(form.n)) : 0,
          creado,
        },
      ])
    } else if (editingKey) {
      setRows((list) =>
        list.map((r) =>
          r.key === editingKey
            ? {
                ...r,
                puesto: form.puesto.trim() || r.puesto,
                n: Number.isFinite(form.n) ? Math.max(0, Math.floor(form.n)) : r.n,
              }
            : r,
        ),
      )
    }
    closePanel()
  }, [closePanel, editingKey, form, panelMode])

  const confirmDelete = useCallback(() => {
    if (!deleteKey) {
      return
    }
    setRows((list) => list.filter((r) => r.key !== deleteKey))
    setDeleteKey(null)
  }, [deleteKey])

  const readOnly = panelMode === 'view'
  const panelTitle =
    panelMode === 'create'
      ? 'Nueva vacante'
      : panelMode === 'edit'
        ? 'Editar vacante'
        : panelMode === 'view'
          ? 'Ver vacante'
          : ''

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Reclutamiento y selección"
        title="Pipeline de reclutamiento"
        description="Publica, gestiona y cierra vacantes. Monitorea el pipeline de candidatos y conecta a tus reclutadores con los jefes directos."
        icon={BriefcaseIcon}
        guidance={UX_VACANTES}
      />

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Vacantes"
          newLabel="Nueva vacante"
          onNew={openCreate}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar vacante o fecha…"
          hint="Listado con acciones CRUD de demostración (sin backend)."
        />
        <MockFilamentTable
          columns={[
            { key: 'puesto', header: 'Puesto' },
            { key: 'candidatos', header: 'Candidatos', className: 'text-center' },
            { key: 'creado', header: 'Fecha de creación' },
          ]}
          rows={displayRows}
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
            <label className={protoLabelClass} htmlFor="vac-puesto">
              Puesto
            </label>
            <input
              id="vac-puesto"
              className={protoInputClass}
              value={form.puesto}
              onChange={(e) => setForm((f) => ({ ...f, puesto: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="vac-n">
              Candidatos en pipeline
            </label>
            <input
              id="vac-n"
              type="number"
              min={0}
              className={protoInputClass}
              value={Number.isFinite(form.n) ? form.n : 0}
              onChange={(e) => setForm((f) => ({ ...f, n: Number.parseInt(e.target.value, 10) || 0 }))}
              disabled={readOnly}
            />
          </div>
          {readOnly && editingKey ? (
            <p className="text-sm text-slate-500">
              La fecha de creación se genera al publicar la vacante y no se edita en este prototipo.
            </p>
          ) : null}
        </div>
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteKey !== null}
        onClose={() => setDeleteKey(null)}
        title="¿Eliminar vacante?"
        description="Solo demostración: se quita la fila del listado en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
