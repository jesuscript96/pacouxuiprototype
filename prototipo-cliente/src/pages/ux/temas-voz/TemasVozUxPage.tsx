import { MegaphoneIcon } from '@heroicons/react/24/outline'
import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'

import { ConfirmDialog } from '@/components/ConfirmDialog'
import { FilamentListToolbar } from '@/components/ux/FilamentListToolbar'
import { MockFilamentTable } from '@/components/ux/MockFilamentTable'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { TableIconActionButtons } from '@/components/ux/TableIconActionButtons'
import { UxHero } from '@/components/ux/UxHero'
import { UX_TEMAS_VOZ } from '@/guidance/uxSections'
import { paths } from '@/navigation/config'
import { TemaVozWizardDialog } from './TemaVozWizardDialog'
import { emptyTemaVozForm, type TemaVozFormState } from './temaVozFormState'
import {
  EMPRESAS_TEMAS_VOZ,
  FILTRO_GLOBALES,
  FILTRO_TODAS,
  destinatariosDePool,
  empresaNombre,
  todosLosDestinatariosIds,
  type TemaVozRow,
} from './temasVozMockData'
import {
  loadTemasVoz,
  nextTemaVozId,
  saveTemasVoz,
} from './temasVozStorage'

type PanelMode = 'create' | 'edit' | null

const COLUMNS = [
  { key: 'nombre', header: 'Tema', className: 'whitespace-normal align-top' },
  { key: 'descripcion', header: 'Descripción', className: 'whitespace-normal align-top' },
  { key: 'exclusividad', header: 'Exclusividad', className: 'align-top' },
  { key: 'destinatarios', header: 'Destinatarios', className: 'align-top' },
  { key: 'creado', header: 'Creado', className: 'align-top' },
]

function formatFechaCorta(iso: string): string {
  try {
    const d = new Date(iso)
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
  } catch {
    return iso
  }
}

function ExclusividadBadge({ empresaId }: { empresaId: string | null }) {
  if (!empresaId) {
    return (
      <span className="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/80">
        Global
      </span>
    )
  }
  return (
    <span className="inline-flex max-w-[16rem] truncate rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/20">
      {empresaNombre(empresaId)}
    </span>
  )
}

function DestinatariosCelda({ row }: { row: TemaVozRow }) {
  const total = todosLosDestinatariosIds(row.empresaId).length
  const seleccionados = row.destinatarioIds.length
  if (total === 0) {
    return <span className="text-xs italic text-slate-400">Sin pool</span>
  }
  const esTodos = seleccionados === total
  const tono = esTodos
    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/80'
    : seleccionados === 0
      ? 'bg-rose-50 text-rose-700 ring-rose-200/80'
      : 'bg-slate-100 text-slate-700 ring-slate-200/80'
  return (
    <span
      className={`inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ${tono}`}
      title={esTodos ? 'Todos los colaboradores del segmento' : `${seleccionados} de ${total}`}
    >
      {esTodos ? 'Todos' : `${seleccionados} de ${total}`}
    </span>
  )
}

function temaRowToDisplay(row: TemaVozRow): Record<string, ReactNode> {
  return {
    nombre: (
      <div className="font-medium leading-snug text-slate-900" title={row.nombre}>
        {row.nombre}
      </div>
    ),
    descripcion: (
      <p
        className="line-clamp-2 text-sm leading-snug text-slate-600"
        title={row.descripcion}
      >
        {row.descripcion || <span className="italic text-slate-400">Sin descripción</span>}
      </p>
    ),
    exclusividad: <ExclusividadBadge empresaId={row.empresaId} />,
    destinatarios: <DestinatariosCelda row={row} />,
    creado: (
      <span className="whitespace-nowrap text-xs tabular-nums text-slate-500">
        {formatFechaCorta(row.creadoEn)}
      </span>
    ),
  }
}

function rowToFormState(row: TemaVozRow): TemaVozFormState {
  return {
    nombre: row.nombre,
    descripcion: row.descripcion,
    asignarEmpresa: row.empresaId !== null,
    empresaId: row.empresaId ?? '',
    destinatarioIds: new Set(row.destinatarioIds),
  }
}

function formStateToRow(form: TemaVozFormState, id: number, creadoEn: string): TemaVozRow {
  const empresaId = form.asignarEmpresa && form.empresaId ? form.empresaId : null
  return {
    id,
    nombre: form.nombre.trim(),
    descripcion: form.descripcion.trim(),
    empresaId,
    destinatarioIds: Array.from(form.destinatarioIds),
    creadoEn,
  }
}

function validateForm(form: TemaVozFormState): string | null {
  if (!form.nombre.trim()) {
    return 'El nombre del tema es obligatorio.'
  }
  if (form.asignarEmpresa && !form.empresaId) {
    return 'Selecciona la empresa exclusiva o desactiva el interruptor.'
  }
  if (form.destinatarioIds.size === 0) {
    return 'Selecciona al menos un destinatario para el tema.'
  }
  return null
}

/** Calcula el id de empresa efectivo a partir del formulario (null = global). */
function empresaEfectiva(form: TemaVozFormState): string | null {
  return form.asignarEmpresa && form.empresaId ? form.empresaId : null
}

function matchesEmpresaFilter(row: TemaVozRow, filtro: string): boolean {
  if (filtro === FILTRO_TODAS) {
    return true
  }
  if (filtro === FILTRO_GLOBALES) {
    return row.empresaId === null
  }
  return row.empresaId === null || row.empresaId === filtro
}

function matchesSearch(row: TemaVozRow, q: string): boolean {
  const t = q.trim().toLowerCase()
  if (!t) {
    return true
  }
  return (
    row.nombre.toLowerCase().includes(t) ||
    row.descripcion.toLowerCase().includes(t) ||
    empresaNombre(row.empresaId).toLowerCase().includes(t)
  )
}

export function TemasVozUxPage() {
  const [rows, setRows] = useState<TemaVozRow[]>(() => loadTemasVoz())
  const [empresaFiltro, setEmpresaFiltro] = useState<string>(FILTRO_TODAS)
  const [search, setSearch] = useState('')
  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [panelInitialStep, setPanelInitialStep] = useState<0 | 1>(0)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState<TemaVozFormState>(emptyTemaVozForm)
  const [formError, setFormError] = useState<string | null>(null)
  const [deleteId, setDeleteId] = useState<number | null>(null)
  const [toast, setToast] = useState<string | null>(null)

  useEffect(() => {
    saveTemasVoz(rows)
  }, [rows])

  useEffect(() => {
    if (!toast) {
      return undefined
    }
    const t = window.setTimeout(() => setToast(null), 3200)
    return () => window.clearTimeout(t)
  }, [toast])

  const empresaFiltroOptions = useMemo(() => {
    return [
      { value: FILTRO_TODAS, label: 'Todas las empresas' },
      { value: FILTRO_GLOBALES, label: 'Solo temas globales' },
      ...EMPRESAS_TEMAS_VOZ.map((e) => ({ value: e.id, label: e.nombre })),
    ]
  }, [])

  const filtered = useMemo(() => {
    return rows
      .filter((r) => matchesEmpresaFilter(r, empresaFiltro))
      .filter((r) => matchesSearch(r, search))
      .sort((a, b) => b.id - a.id)
  }, [rows, empresaFiltro, search])

  const displayRows = useMemo(() => filtered.map((r) => temaRowToDisplay(r)), [filtered])

  const totalesPorTipo = useMemo(() => {
    const globales = rows.filter((r) => r.empresaId === null).length
    const exclusivos = rows.length - globales
    return { globales, exclusivos }
  }, [rows])

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setEditingId(null)
    setForm(emptyTemaVozForm())
    setFormError(null)
    setPanelInitialStep(0)
  }, [])

  const openCreate = useCallback(
    (initialStep: 0 | 1 = 0) => {
      setPanelMode('create')
      setEditingId(null)
      const empresaPreseleccionada =
        empresaFiltro !== FILTRO_TODAS &&
        empresaFiltro !== FILTRO_GLOBALES &&
        EMPRESAS_TEMAS_VOZ.some((e) => e.id === empresaFiltro)
      const empresaInicial = empresaPreseleccionada ? empresaFiltro : ''
      const empresaIdEfectiva = empresaPreseleccionada ? empresaFiltro : null
      setForm({
        ...emptyTemaVozForm(),
        asignarEmpresa: empresaPreseleccionada,
        empresaId: empresaInicial,
        destinatarioIds: new Set(todosLosDestinatariosIds(empresaIdEfectiva)),
      })
      setFormError(null)
      setPanelInitialStep(initialStep)
    },
    [empresaFiltro],
  )

  const openEdit = useCallback((row: TemaVozRow, initialStep: 0 | 1 = 0) => {
    setPanelMode('edit')
    setEditingId(row.id)
    setForm(rowToFormState(row))
    setFormError(null)
    setPanelInitialStep(initialStep)
  }, [])

  /**
   * Patch del formulario que además mantiene la coherencia de destinatarios cuando cambia
   * la "empresa efectiva" (toggle o select de empresa).
   * Por defecto: al cambiar el segmento, se vuelven a marcar TODOS los destinatarios
   * del nuevo pool para alinearse con la regla "inicialmente todos seleccionados".
   */
  const patchForm = useCallback((patch: Partial<TemaVozFormState>) => {
    setForm((prev) => {
      const next: TemaVozFormState = { ...prev, ...patch }
      const empresaAntes = empresaEfectiva(prev)
      const empresaDespues = empresaEfectiva(next)
      if (empresaAntes !== empresaDespues) {
        next.destinatarioIds = new Set(todosLosDestinatariosIds(empresaDespues))
      }
      return next
    })
    setFormError(null)
  }, [])

  const toggleDestinatario = useCallback((id: string, on: boolean) => {
    setForm((prev) => {
      const next = new Set(prev.destinatarioIds)
      if (on) {
        next.add(id)
      } else {
        next.delete(id)
      }
      return { ...prev, destinatarioIds: next }
    })
    setFormError(null)
  }, [])

  const toggleTodosDestinatarios = useCallback((on: boolean) => {
    setForm((prev) => {
      const pool = todosLosDestinatariosIds(empresaEfectiva(prev))
      return {
        ...prev,
        destinatarioIds: on ? new Set(pool) : new Set<string>(),
      }
    })
    setFormError(null)
  }, [])

  const destinatariosPool = useMemo(
    () => destinatariosDePool(empresaEfectiva(form)),
    [form],
  )

  const savePanel = useCallback(() => {
    const error = validateForm(form)
    if (error) {
      setFormError(error)
      return
    }
    if (panelMode === 'create') {
      setRows((prev) => {
        const id = nextTemaVozId(prev)
        const created = formStateToRow(form, id, new Date().toISOString())
        return [...prev, created]
      })
      setToast('Tema creado.')
    } else if (panelMode === 'edit' && editingId !== null) {
      setRows((prev) =>
        prev.map((r) => {
          if (r.id !== editingId) {
            return r
          }
          return formStateToRow(form, r.id, r.creadoEn)
        }),
      )
      setToast('Tema actualizado.')
    }
    closePanel()
  }, [closePanel, editingId, form, panelMode])

  const confirmDelete = useCallback(() => {
    if (deleteId === null) {
      return
    }
    setRows((prev) => prev.filter((r) => r.id !== deleteId))
    setDeleteId(null)
    setToast('Tema eliminado.')
  }, [deleteId])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Comunicación"
        title="Temas de Voz"
        description="Catálogo de temas que el colaborador puede elegir al enviar un comentario de voz. Define temas globales o exclusivos por empresa."
        icon={MegaphoneIcon}
        stat={{
          label: 'En catálogo (demo)',
          value: String(rows.length),
          hint: `${totalesPorTipo.globales} globales · ${totalesPorTipo.exclusivos} exclusivos`,
        }}
        guidance={UX_TEMAS_VOZ}
      />

      <nav className="text-sm text-slate-600" aria-label="Migas de pan">
        <Link to={paths.inicio} className="font-medium text-[#3148c8] hover:underline">
          PACO
        </Link>
        <span className="mx-2 text-slate-400">/</span>
        <span className="text-slate-800">Temas de Voz</span>
      </nav>

      {toast ? (
        <div
          role="status"
          className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900"
        >
          {toast}
        </div>
      ) : null}

      <section
        aria-label="Filtros de Temas de Voz"
        className="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5"
      >
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div className="min-w-0">
            <h2 className="text-base font-semibold leading-tight text-slate-900">
              Filtrar por empresa
            </h2>
            <p className="mt-1 text-xs text-slate-500">
              Al elegir una empresa verás sus temas exclusivos junto con los temas globales que aplican
              a todas.
            </p>
          </div>
          <div className="w-full sm:max-w-xs">
            <label
              htmlFor="filtro-empresa-temas"
              className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500"
            >
              Empresa
            </label>
            <ProtoSelect
              id="filtro-empresa-temas"
              value={empresaFiltro}
              onValueChange={setEmpresaFiltro}
              options={empresaFiltroOptions}
              allowEmpty={false}
              aria-label="Filtrar temas por empresa"
            />
          </div>
        </div>
      </section>

      <div className="an-section space-y-4 rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
        <FilamentListToolbar
          heading="Listado de temas"
          newLabel="Crear tema"
          onNew={() => openCreate(0)}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por nombre, descripción o empresa"
          hint="Crear o editar abre un asistente de dos pasos. «Segmentar» abre directo el paso de segmentación."
        />

        {filtered.length === 0 ? (
          <p className="rounded-lg border border-dashed border-slate-200 bg-slate-50/80 px-4 py-8 text-center text-sm text-slate-600">
            {rows.length === 0
              ? 'No hay temas en la demo. Usa «Crear tema» para añadir el primero.'
              : 'Ningún tema coincide con el filtro o la búsqueda actuales.'}
          </p>
        ) : (
          <MockFilamentTable
            columns={COLUMNS}
            rows={displayRows}
            rowKey={(_row, i) => String(filtered[i]?.id ?? i)}
            actionsColumn={{
              header: '',
              render: (_row, i) => {
                const rec = filtered[i]
                if (!rec) {
                  return null
                }
                return (
                  <TableIconActionButtons
                    actions={[
                      {
                        id: `edit-${rec.id}`,
                        tone: 'edit',
                        label: 'Editar',
                        onClick: () => openEdit(rec, 0),
                      },
                      {
                        id: `seg-${rec.id}`,
                        tone: 'segment',
                        label: 'Segmentar',
                        hint: 'Abre el paso de segmentación',
                        onClick: () => openEdit(rec, 1),
                      },
                      {
                        id: `del-${rec.id}`,
                        tone: 'delete',
                        label: 'Eliminar',
                        onClick: () => setDeleteId(rec.id),
                      },
                    ]}
                  />
                )
              },
            }}
          />
        )}
      </div>

      <TemaVozWizardDialog
        open={panelMode !== null}
        onClose={closePanel}
        mode={panelMode === 'edit' ? 'edit' : 'create'}
        initialStep={panelInitialStep}
        form={form}
        errorMessage={formError}
        empresas={EMPRESAS_TEMAS_VOZ}
        destinatariosPool={destinatariosPool}
        onChange={patchForm}
        onToggleDestinatario={toggleDestinatario}
        onToggleTodosDestinatarios={toggleTodosDestinatarios}
        onSave={savePanel}
      />

      <ConfirmDialog
        open={deleteId !== null}
        onClose={() => setDeleteId(null)}
        title="¿Eliminar tema de voz?"
        description="En producción se validaría si hay comentarios asociados antes de borrarlo. Aquí solo se elimina de la demo local."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
