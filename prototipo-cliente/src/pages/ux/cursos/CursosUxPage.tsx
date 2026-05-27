import {
  AcademicCapIcon,
  ArrowDownTrayIcon,
  ChartBarIcon,
  DocumentChartBarIcon,
  DocumentDuplicateIcon,
  EllipsisVerticalIcon,
  EyeIcon,
  NoSymbolIcon,
  PencilSquareIcon,
  PlusIcon,
  TrashIcon,
  UserGroupIcon,
} from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { FilamentListToolbar } from '@/components/ux/FilamentListToolbar'
import { MockFilamentTable } from '@/components/ux/MockFilamentTable'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { UxHero } from '@/components/ux/UxHero'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { cn } from '@/lib/utils'
import { UX_CURSOS } from '@/guidance/uxSections'
import { paths } from '@/navigation/config'
import { CursoWizardModal } from './CursoWizardModal'
import {
  CATALOG_EMPRESAS,
  CURSOS_REPORTE_HISTORICO,
  INITIAL_CURSOS_ROWS,
  cursoDraftParaFila,
  duplicarCurso,
  estadoCursoLabel,
  mergeDraftEnFila,
} from './cursosMockData'
import type { CursoReporteHistoricoRow, CursoRow, CursoWizardDraft } from './cursosTypes'

type WizardMode = 'create' | 'edit' | 'view'
type PendingAction =
  | { type: 'delete'; row: CursoRow }
  | { type: 'deactivate'; row: CursoRow }
  | null

function estadoBadge(estado: CursoRow['estado']) {
  const classes = {
    activo: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    borrador: 'bg-amber-50 text-amber-900 ring-amber-200/80',
    inactivo: 'bg-slate-100 text-slate-600 ring-slate-200/80',
  }[estado]

  return (
    <span className={cn('inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1', classes)}>
      {estadoCursoLabel(estado)}
    </span>
  )
}

function reporteEstadoBadge(estado: CursoReporteHistoricoRow['estado']) {
  const classes = {
    Aprobado: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    Pendiente: 'bg-amber-50 text-amber-900 ring-amber-200',
    'En progreso': 'bg-indigo-50 text-indigo-800 ring-indigo-200',
    'No aprobado': 'bg-red-50 text-red-700 ring-red-200',
  }[estado]

  return (
    <span className={cn('inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1', classes)}>
      {estado}
    </span>
  )
}

function KpiCard({
  label,
  value,
  hint,
  icon: Icon,
}: {
  label: string
  value: string
  hint: string
  icon: typeof AcademicCapIcon
}) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div className="flex items-start justify-between gap-3">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">{label}</p>
          <p className="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{value}</p>
          <p className="mt-1 text-xs text-slate-500">{hint}</p>
        </div>
        <div className="rounded-xl border border-indigo-100 bg-indigo-50 p-2 text-[#3148c8]">
          <Icon className="h-5 w-5" />
        </div>
      </div>
    </div>
  )
}

export function CursosUxPage() {
  const navigate = useNavigate()
  const [search, setSearch] = useState('')
  const [rows, setRows] = useState<CursoRow[]>(INITIAL_CURSOS_ROWS)
  const [wizardOpen, setWizardOpen] = useState(false)
  const [wizardSession, setWizardSession] = useState(0)
  const [wizardMode, setWizardMode] = useState<WizardMode>('create')
  const [wizardRecord, setWizardRecord] = useState<CursoRow | null>(null)
  const [pendingAction, setPendingAction] = useState<PendingAction>(null)
  const [reportOpen, setReportOpen] = useState(false)
  const [reportCourse, setReportCourse] = useState<CursoRow | null>(null)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return rows.filter((row) => {
      if (!q) {
        return true
      }
      return (
        String(row.id).includes(q) ||
        row.titulo.toLowerCase().includes(q) ||
        row.creadoPor.toLowerCase().includes(q) ||
        row.empresaNombre.toLowerCase().includes(q) ||
        estadoCursoLabel(row.estado).toLowerCase().includes(q)
      )
    })
  }, [rows, search])

  const totals = useMemo(
    () => ({
      cursos: rows.length,
      asignados: rows.reduce((sum, row) => sum + row.colaboradoresAsignados, 0),
      enProgreso: rows.reduce((sum, row) => sum + row.colaboradoresEnProgreso, 0),
      finalizados: rows.reduce((sum, row) => sum + row.colaboradoresFinalizados, 0),
    }),
    [rows],
  )

  const displayRows = useMemo(
    () =>
      filtered.map((row) => ({
        id: <span className="font-mono text-xs text-slate-500">#{row.id}</span>,
        titulo: <span className="font-semibold text-slate-900">{row.titulo}</span>,
        creadoPor: row.creadoPor,
        ultimaModificacion: row.ultimaModificacion,
        empresa: row.empresaNombre,
        estado: estadoBadge(row.estado),
        _key: row.key,
      })),
    [filtered],
  )

  const openCreate = useCallback(() => {
    navigate(paths.cursosNuevo)
  }, [navigate])

  const openEdit = useCallback((row: CursoRow) => {
    setWizardSession((session) => session + 1)
    setWizardMode('edit')
    setWizardRecord(row)
    setWizardOpen(true)
  }, [])

  const openView = useCallback((row: CursoRow) => {
    setWizardSession((session) => session + 1)
    setWizardMode('view')
    setWizardRecord(row)
    setWizardOpen(true)
  }, [])

  const handleSave = useCallback(
    (draft: CursoWizardDraft) => {
      const now = new Date().toLocaleDateString('es-MX')
      if (wizardMode === 'create') {
        const nextId = Math.max(0, ...rows.map((row) => row.id)) + 1
        setRows((list) => [...list, cursoDraftParaFila(draft, `curso_${Date.now()}`, nextId, now)])
      } else if (wizardMode === 'edit' && wizardRecord) {
        setRows((list) =>
          list.map((row) => (row.key === wizardRecord.key ? mergeDraftEnFila(row, draft) : row)),
        )
      }
    },
    [rows, wizardMode, wizardRecord],
  )

  const handleDuplicate = useCallback((row: CursoRow) => {
    setRows((list) => {
      const nextId = Math.max(0, ...list.map((item) => item.id)) + 1
      return [...list, duplicarCurso(row, nextId)]
    })
  }, [])

  const openReport = useCallback((row?: CursoRow) => {
    setReportCourse(row ?? null)
    setReportOpen(true)
  }, [])

  const confirmPendingAction = useCallback(() => {
    if (!pendingAction) {
      return
    }
    if (pendingAction.type === 'delete') {
      setRows((list) => list.filter((row) => row.key !== pendingAction.row.key))
    }
    if (pendingAction.type === 'deactivate') {
      setRows((list) =>
        list.map((row) =>
          row.key === pendingAction.row.key
            ? {
                ...row,
                estado: 'inactivo',
                ultimaModificacion: new Date().toLocaleDateString('es-MX'),
              }
            : row,
        ),
      )
    }
    setPendingAction(null)
  }, [pendingAction])

  const confirmTitle =
    pendingAction?.type === 'delete'
      ? '¿Eliminar curso?'
      : '¿Desactivar curso?'

  const confirmDescription =
    pendingAction?.type === 'delete'
      ? 'Solo demostración: se quitará el curso del listado en memoria.'
      : 'El curso quedará inactivo en el prototipo, sin borrar su historial.'

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Capacitación"
        title="Cursos"
        description="Administra capacitaciones, evaluaciones, audiencias y reportes de avance con datos mock del prototipo."
        icon={AcademicCapIcon}
        stat={{
          label: 'Cursos activos',
          value: String(rows.filter((row) => row.estado === 'activo').length),
          hint: 'Incluye cursos publicados',
        }}
        guidance={UX_CURSOS}
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <KpiCard
          label="Total de cursos"
          value={String(totals.cursos)}
          hint="Activos, borradores e inactivos"
          icon={AcademicCapIcon}
        />
        <KpiCard
          label="Colaboradores asignados"
          value={String(totals.asignados)}
          hint="Audiencia total mock"
          icon={UserGroupIcon}
        />
        <KpiCard
          label="En progreso"
          value={String(totals.enProgreso)}
          hint="Capacitaciones iniciadas"
          icon={ChartBarIcon}
        />
        <KpiCard
          label="Finalizados"
          value={String(totals.finalizados)}
          hint="Colaboradores que terminaron"
          icon={DocumentChartBarIcon}
        />
      </div>

      <div className="grid gap-3 sm:grid-cols-2">
        <button
          type="button"
          className="rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/40"
          onClick={() => openReport()}
        >
          <div className="flex items-start gap-3">
            <div className="rounded-xl bg-indigo-50 p-2 text-[#3148c8]">
              <DocumentChartBarIcon className="h-5 w-5" />
            </div>
            <div>
              <p className="text-sm font-semibold text-slate-900">Reporte histórico</p>
              <p className="mt-1 text-xs text-slate-500">
                Consulta por colaborador, fechas, estado y descarga Excel mock.
              </p>
            </div>
          </div>
        </button>
        <button
          type="button"
          className="rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/40"
          onClick={openCreate}
        >
          <div className="flex items-start gap-3">
            <div className="rounded-xl bg-emerald-50 p-2 text-emerald-700">
              <PlusIcon className="h-5 w-5" />
            </div>
            <div>
              <p className="text-sm font-semibold text-slate-900">Nuevo curso</p>
              <p className="mt-1 text-xs text-slate-500">
                Abre el wizard de contenido, evaluación, segmentación y resumen.
              </p>
            </div>
          </div>
        </button>
      </div>

      {reportOpen ? (
        <HistoricoReportPanel
          course={reportCourse}
          onClose={() => {
            setReportOpen(false)
            setReportCourse(null)
          }}
        />
      ) : null}

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Cursos"
          newLabel="Nuevo curso"
          onNew={openCreate}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por ID, título, creador, empresa o estado…"
          hint="CRUD demo con mock data. Las acciones solo modifican estado local del prototipo."
        />
        <MockFilamentTable
          columns={[
            { key: 'id', header: 'ID' },
            { key: 'titulo', header: 'Título' },
            { key: 'creadoPor', header: 'Creado por' },
            { key: 'ultimaModificacion', header: 'Última modificación' },
            { key: 'empresa', header: 'Empresa' },
            { key: 'estado', header: 'Estado' },
          ]}
          rows={displayRows}
          rowKey={(row) => String(row._key)}
          actionsColumn={{
            render: (_row, index) => {
              const raw = filtered[index]
              if (!raw) {
                return null
              }
              return (
                <CursoRowActions
                  row={raw}
                  onView={() => openView(raw)}
                  onEdit={() => openEdit(raw)}
                  onDuplicate={() => handleDuplicate(raw)}
                  onReport={() => openReport(raw)}
                  onDeactivate={() => setPendingAction({ type: 'deactivate', row: raw })}
                  onDelete={() => setPendingAction({ type: 'delete', row: raw })}
                />
              )
            },
          }}
        />
      </div>

      <CursoWizardModal
        key={wizardSession}
        open={wizardOpen}
        onClose={() => setWizardOpen(false)}
        mode={wizardMode}
        record={wizardRecord}
        onSave={handleSave}
      />

      <ConfirmDialog
        open={pendingAction !== null}
        onClose={() => setPendingAction(null)}
        title={confirmTitle}
        description={confirmDescription}
        confirmLabel={pendingAction?.type === 'delete' ? 'Eliminar' : 'Desactivar'}
        onConfirm={confirmPendingAction}
      />
    </div>
  )
}

function CursoRowActions({
  row,
  onView,
  onEdit,
  onDuplicate,
  onReport,
  onDeactivate,
  onDelete,
}: {
  row: CursoRow
  onView: () => void
  onEdit: () => void
  onDuplicate: () => void
  onReport: () => void
  onDeactivate: () => void
  onDelete: () => void
}) {
  return (
    <div className="flex justify-end">
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button
            type="button"
            variant="ghost"
            size="icon"
            className="h-8 w-8 text-slate-500 hover:text-slate-800"
            aria-label={`Acciones del curso ${row.titulo}`}
          >
            <EllipsisVerticalIcon className="h-5 w-5" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[14rem]">
          <DropdownMenuItem className="gap-2" onClick={onView}>
            <EyeIcon className="h-4 w-4 text-slate-500" />
            Ver
          </DropdownMenuItem>
          <DropdownMenuItem className="gap-2" onClick={onEdit}>
            <PencilSquareIcon className="h-4 w-4 text-slate-500" />
            Editar
          </DropdownMenuItem>
          <DropdownMenuItem className="gap-2" onClick={onReport}>
            <DocumentChartBarIcon className="h-4 w-4 text-slate-500" />
            Reportes
          </DropdownMenuItem>
          <DropdownMenuItem className="gap-2" onClick={onDuplicate}>
            <DocumentDuplicateIcon className="h-4 w-4 text-slate-500" />
            Duplicar
          </DropdownMenuItem>
          <DropdownMenuSeparator />
          <DropdownMenuItem className="gap-2" onClick={onDeactivate}>
            <NoSymbolIcon className="h-4 w-4 text-slate-500" />
            Desactivar
          </DropdownMenuItem>
          <DropdownMenuItem variant="destructive" className="gap-2" onClick={onDelete}>
            <TrashIcon className="h-4 w-4" />
            Eliminar
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}

function HistoricoReportPanel({
  course,
  onClose,
}: {
  course: CursoRow | null
  onClose: () => void
}) {
  const [colaborador, setColaborador] = useState('')
  const [empresaId, setEmpresaId] = useState(course?.empresaId ?? '')
  const [estado, setEstado] = useState('')
  const [fechaDesde, setFechaDesde] = useState('')
  const [fechaHasta, setFechaHasta] = useState('')

  const filteredReport = useMemo(() => {
    const q = colaborador.trim().toLowerCase()
    const empresaLabel = CATALOG_EMPRESAS.find((empresa) => empresa.value === empresaId)?.label

    return CURSOS_REPORTE_HISTORICO.filter((row) => {
      const byCourse = course ? row.capacitacion === course.titulo : true
      const byCollaborator = q ? row.colaborador.toLowerCase().includes(q) : true
      const byEmpresa = empresaLabel ? row.empresa === empresaLabel : true
      const byEstado = estado ? row.estado === estado : true

      return byCourse && byCollaborator && byEmpresa && byEstado
    })
  }, [colaborador, course, empresaId, estado])

  return (
    <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="text-base font-semibold text-slate-900">Reporte histórico</h2>
          <p className="mt-1 text-sm text-slate-500">
            {course
              ? `Vista filtrada para “${course.titulo}”.`
              : 'Consulta mock con filtros clásicos y tabla descargable.'}
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button type="button" variant="outline" className="gap-1.5">
            <ArrowDownTrayIcon className="h-4 w-4" />
            Descargar Excel
          </Button>
          <Button type="button" variant="ghost" onClick={onClose}>
            Cerrar
          </Button>
        </div>
      </div>

      <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div>
          <label className={protoLabelClass} htmlFor="curso-rep-colaborador">
            Colaborador
          </label>
          <input
            id="curso-rep-colaborador"
            className={protoInputClass}
            value={colaborador}
            onChange={(e) => setColaborador(e.target.value)}
            placeholder="Buscar colaborador…"
          />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="curso-rep-empresa">
            Empresa
          </label>
          <ProtoSelect
            id="curso-rep-empresa"
            value={empresaId}
            onValueChange={setEmpresaId}
            options={CATALOG_EMPRESAS}
            placeholder="Todas"
            allowEmpty
          />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="curso-rep-estado">
            Estado
          </label>
          <ProtoSelect
            id="curso-rep-estado"
            value={estado}
            onValueChange={setEstado}
            options={[
              { value: 'Aprobado', label: 'Aprobado' },
              { value: 'Pendiente', label: 'Pendiente' },
              { value: 'En progreso', label: 'En progreso' },
              { value: 'No aprobado', label: 'No aprobado' },
            ]}
            placeholder="Todos"
            allowEmpty
          />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="curso-rep-desde">
            Fecha desde
          </label>
          <input
            id="curso-rep-desde"
            type="date"
            className={protoInputClass}
            value={fechaDesde}
            onChange={(e) => setFechaDesde(e.target.value)}
          />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="curso-rep-hasta">
            Fecha hasta
          </label>
          <input
            id="curso-rep-hasta"
            type="date"
            className={protoInputClass}
            value={fechaHasta}
            onChange={(e) => setFechaHasta(e.target.value)}
          />
        </div>
      </div>

      <div className="mt-4 overflow-hidden rounded-xl border border-slate-200">
        <table className="min-w-full divide-y divide-slate-200 text-sm">
          <thead className="bg-slate-50">
            <tr>
              {[
                'Colaborador',
                'Empresa',
                'Capacitación',
                'Estado',
                'Inicio',
                'Fin',
                'Días',
                'Progreso',
                'Evaluaciones',
              ].map((header) => (
                <th
                  key={header}
                  className="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                >
                  {header}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 bg-white">
            {filteredReport.map((row) => (
              <tr key={row.id} className="hover:bg-slate-50/80">
                <td className="whitespace-nowrap px-4 py-3 font-medium text-slate-900">
                  {row.colaborador}
                </td>
                <td className="whitespace-nowrap px-4 py-3 text-slate-700">{row.empresa}</td>
                <td className="whitespace-nowrap px-4 py-3 text-slate-700">
                  {row.capacitacion}
                </td>
                <td className="whitespace-nowrap px-4 py-3">{reporteEstadoBadge(row.estado)}</td>
                <td className="whitespace-nowrap px-4 py-3 text-slate-700">{row.fechaInicio}</td>
                <td className="whitespace-nowrap px-4 py-3 text-slate-700">{row.fechaFin}</td>
                <td className="whitespace-nowrap px-4 py-3 text-slate-700">{row.dias}</td>
                <td className="whitespace-nowrap px-4 py-3 font-semibold text-slate-800">
                  {row.progreso}
                </td>
                <td className="whitespace-nowrap px-4 py-3 text-slate-700">
                  {row.evaluaciones}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {filteredReport.length === 0 ? (
          <div className="bg-white px-4 py-8 text-center text-sm text-slate-500">
            No hay registros para los filtros seleccionados.
          </div>
        ) : null}
      </div>
    </section>
  )
}
