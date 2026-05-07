import { BriefcaseIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UX_VACANTES } from '../../../guidance/uxSections'
import { VacanteWizardModal } from './VacanteWizardModal'
import {
  draftToNewRow,
  INITIAL_VACANTES_ROWS,
  labelJornadaParaTabla,
  mergeDraftIntoRow,
} from './vacantesConstants'
import type { VacanteRow, VacanteWizardDraft } from './vacantesTypes'

function badgeCandidatos(n: number) {
  return (
    <span className="inline-flex min-w-[1.75rem] justify-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-800 ring-1 ring-indigo-200/80">
      {n}
    </span>
  )
}

function badgeVisible(visible: boolean) {
  return (
    <span
      className={
        visible
          ? 'inline-flex rounded-md bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-800 ring-1 ring-green-200/80'
          : 'inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-200/80'
      }
    >
      {visible ? 'Sí' : 'No'}
    </span>
  )
}

type WizardMode = 'create' | 'edit' | 'view'

export function VacantesUxPage() {
  const [search, setSearch] = useState('')
  const [rows, setRows] = useState<VacanteRow[]>(() =>
    INITIAL_VACANTES_ROWS.map((r) => ({
      ...r,
      camposFormulario: r.camposFormulario.map((c) => ({ ...c })),
    })),
  )
  const [wizardOpen, setWizardOpen] = useState(false)
  const [wizardSession, setWizardSession] = useState(0)
  const [wizardMode, setWizardMode] = useState<WizardMode>('create')
  const [wizardRecord, setWizardRecord] = useState<VacanteRow | null>(null)
  const [deleteKey, setDeleteKey] = useState<string | null>(null)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return rows.filter((r) => {
      if (!q) {
        return true
      }
      const jornada = labelJornadaParaTabla(r.tipoJornada).toLowerCase()
      return (
        r.puesto.toLowerCase().includes(q) ||
        r.creado.toLowerCase().includes(q) ||
        r.empresaNombre.toLowerCase().includes(q) ||
        jornada.includes(q)
      )
    })
  }, [rows, search])

  const displayRows = useMemo(
    () =>
      filtered.map((r) => ({
        puesto: r.puesto,
        empresa: r.empresaNombre,
        jornada: labelJornadaParaTabla(r.tipoJornada),
        candidatos: badgeCandidatos(r.n),
        visible: badgeVisible(r.visiblePortal),
        creado: r.creado,
        _key: r.key,
      })),
    [filtered],
  )

  const closeWizard = useCallback(() => {
    setWizardOpen(false)
    setWizardRecord(null)
  }, [])

  const openCreate = useCallback(() => {
    setWizardSession((s) => s + 1)
    setWizardMode('create')
    setWizardRecord(null)
    setWizardOpen(true)
  }, [])

  const openEdit = useCallback((r: VacanteRow) => {
    setWizardSession((s) => s + 1)
    setWizardMode('edit')
    setWizardRecord(r)
    setWizardOpen(true)
  }, [])

  const openView = useCallback((r: VacanteRow) => {
    setWizardSession((s) => s + 1)
    setWizardMode('view')
    setWizardRecord(r)
    setWizardOpen(true)
  }, [])

  const handleSave = useCallback(
    (draft: VacanteWizardDraft) => {
      const now = new Date()
      const creado = now.toLocaleString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
      if (wizardMode === 'create') {
        const key = `v${Date.now()}`
        setRows((list) => [...list, draftToNewRow(draft, key, creado)])
      } else if (wizardMode === 'edit' && wizardRecord) {
        setRows((list) =>
          list.map((r) =>
            r.key === wizardRecord.key ? mergeDraftIntoRow(r, draft) : r,
          ),
        )
      }
    },
    [wizardMode, wizardRecord],
  )

  const confirmDelete = useCallback(() => {
    if (!deleteKey) {
      return
    }
    setRows((list) => list.filter((r) => r.key !== deleteKey))
    setDeleteKey(null)
  }, [deleteKey])

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
          searchPlaceholder="Buscar por puesto, empresa o jornada…"
          hint="Listado demo con wizard en modal (dos pasos). Sin backend."
        />
        <MockFilamentTable
          columns={[
            { key: 'puesto', header: 'Puesto' },
            { key: 'empresa', header: 'Empresa' },
            { key: 'jornada', header: 'Jornada' },
            { key: 'candidatos', header: 'Candidatos', className: 'text-center' },
            { key: 'visible', header: 'Visible', className: 'text-center' },
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

      <VacanteWizardModal
        key={wizardSession}
        open={wizardOpen}
        onClose={closeWizard}
        mode={wizardMode}
        record={wizardRecord}
        onSave={handleSave}
      />

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
