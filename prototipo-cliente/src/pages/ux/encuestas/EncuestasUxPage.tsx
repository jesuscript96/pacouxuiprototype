import {
  ChartBarIcon,
  ClipboardDocumentListIcon,
  DocumentDuplicateIcon,
  EllipsisVerticalIcon,
  LinkIcon,
  LockClosedIcon,
  LockOpenIcon,
  NoSymbolIcon,
  PaperAirplaneIcon,
  PencilSquareIcon,
  RectangleStackIcon,
  TableCellsIcon,
  TagIcon,
  TrashIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'
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
import { UxTabs, type UxTab } from '@/components/ux/UxTabs'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { cn } from '@/lib/utils'
import { UX_ENCUESTAS, UX_ENCUESTAS_CATEGORIAS, UX_ENCUESTAS_ENVIADAS } from '@/guidance/uxSections'
import { paths } from '@/navigation/config'

import { CATALOG_EMPRESAS, OPCIONES_SI_NO } from '../mensajes/mensajesConstants'
import { CategoriaSlideOver, type CategoriaFormValues } from './CategoriaSlideOver'
import { EditarEnvioModal, type EnvioEdit } from './EditarEnvioModal'
import { EnviarEncuestaModal } from './EnviarEncuestaModal'
import { categoriasComoOpciones, estadoEncuestaLabel } from './encuestasMockData'
import { encuestasStore, useEncuestasStore } from './encuestasStore'
import type { CategoriaEncuesta, EncuestaRow, EnvioEncuestaDraft } from './encuestasTypes'

type TabId = 'todas' | 'enviadas' | 'categorias'

const TABS: UxTab[] = [
  { id: 'todas', label: 'Todas', icon: ClipboardDocumentListIcon },
  { id: 'enviadas', label: 'Enviadas', icon: PaperAirplaneIcon },
  { id: 'categorias', label: 'Categorías', icon: TagIcon },
]

function estadoBadge(estado: EncuestaRow['estado']) {
  const classes = {
    activa: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    borrador: 'bg-amber-50 text-amber-900 ring-amber-200/80',
    cerrada: 'bg-slate-100 text-slate-700 ring-slate-200/80',
    inactiva: 'bg-slate-100 text-slate-500 ring-slate-200/80',
  }[estado]
  return (
    <span className={cn('inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1', classes)}>
      {estadoEncuestaLabel(estado)}
    </span>
  )
}

function Participacion({ row }: { row: EncuestaRow }) {
  if (!row.envio) {
    return <span className="text-xs text-slate-400">Sin enviar</span>
  }
  const { enviados, contestados, noContestado } = row.envio
  const pct = enviados > 0 ? Math.round((contestados / enviados) * 100) : 0
  return (
    <div className="min-w-[9rem]">
      <div className="flex items-center justify-between text-xs">
        <span className="font-semibold text-slate-800">
          {contestados}/{enviados}
        </span>
        <span className="text-slate-400">{pct}%</span>
      </div>
      <div className="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
        <div className="h-full rounded-full bg-[#3148c8]" style={{ width: `${pct}%` }} />
      </div>
      <div className="mt-0.5 text-[11px] text-slate-400">{noContestado} sin contestar</div>
    </div>
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
  icon: typeof ClipboardDocumentListIcon
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

export function EncuestasUxPage() {
  const navigate = useNavigate()
  const { categorias, encuestas } = useEncuestasStore()
  const [tab, setTab] = useState<TabId>('todas')
  const [search, setSearch] = useState('')
  const [notice, setNotice] = useState<string | null>(null)

  // Filtros "Todas"
  const [tCategoria, setTCategoria] = useState('')
  const [tEmpresa, setTEmpresa] = useState('')

  // Filtros "Enviadas"
  const [eEmpresa, setEEmpresa] = useState('')
  const [eUrgente, setEUrgente] = useState('')
  const [eCerrada, setECerrada] = useState('')
  const [eDesde, setEDesde] = useState('')
  const [eHasta, setEHasta] = useState('')

  const [catOpen, setCatOpen] = useState(false)
  const [catRecord, setCatRecord] = useState<CategoriaEncuesta | null>(null)
  const [enviarOpen, setEnviarOpen] = useState(false)
  const [enviarRow, setEnviarRow] = useState<EncuestaRow | null>(null)
  const [editEnvioOpen, setEditEnvioOpen] = useState(false)
  const [editEnvioRow, setEditEnvioRow] = useState<EncuestaRow | null>(null)

  const [confirm, setConfirm] = useState<
    | { kind: 'encuesta-delete'; row: EncuestaRow }
    | { kind: 'encuesta-toggle'; row: EncuestaRow }
    | { kind: 'encuesta-cerrar'; row: EncuestaRow }
    | { kind: 'categoria-delete'; row: CategoriaEncuesta }
    | null
  >(null)

  const q = search.trim().toLowerCase()
  const categoriasOptions = useMemo(() => categoriasComoOpciones(categorias), [categorias])

  const kpis = useMemo(() => {
    const enviadas = encuestas.filter((e) => e.envio)
    const totalEnviados = enviadas.reduce((s, r) => s + (r.envio?.enviados ?? 0), 0)
    const totalContestados = enviadas.reduce((s, r) => s + (r.envio?.contestados ?? 0), 0)
    const pct = totalEnviados > 0 ? Math.round((totalContestados / totalEnviados) * 100) : 0
    return {
      encuestas: encuestas.length,
      activas: encuestas.filter((e) => e.estado === 'activa').length,
      enviadas: enviadas.length,
      participacion: `${pct}%`,
    }
  }, [encuestas])

  // ---- TODAS ----
  const todasFiltradas = useMemo(() => {
    return encuestas.filter((e) => {
      if (tCategoria && e.categoriaId !== tCategoria) {
        return false
      }
      if (tEmpresa && e.empresaId !== tEmpresa) {
        return false
      }
      if (!q) {
        return true
      }
      return (
        String(e.id).includes(q) ||
        e.titulo.toLowerCase().includes(q) ||
        e.categoriaNombre.toLowerCase().includes(q) ||
        e.empresaNombre.toLowerCase().includes(q)
      )
    })
  }, [encuestas, q, tCategoria, tEmpresa])

  const todasRows = useMemo(
    () =>
      todasFiltradas.map((e) => ({
        id: <span className="font-mono text-xs text-slate-500">#{e.id}</span>,
        titulo: <span className="font-semibold text-slate-900">{e.titulo}</span>,
        categoria: e.categoriaNombre,
        empresa: e.empresaNombre,
        estado: estadoBadge(e.estado),
        participacion: <Participacion row={e} />,
        _key: e.key,
      })),
    [todasFiltradas],
  )

  // ---- ENVIADAS ----
  const enviadasFiltradas = useMemo(() => {
    return encuestas.filter((e) => {
      if (!e.envio) {
        return false
      }
      if (eEmpresa && e.empresaId !== eEmpresa) {
        return false
      }
      if (eUrgente === 'si' && !e.envio.urgente) {
        return false
      }
      if (eUrgente === 'no' && e.envio.urgente) {
        return false
      }
      if (eCerrada === 'si' && !e.envio.cerrada) {
        return false
      }
      if (eCerrada === 'no' && e.envio.cerrada) {
        return false
      }
      if (eDesde && e.envio.fechaEnvio < eDesde) {
        return false
      }
      if (eHasta && e.envio.fechaEnvio > eHasta) {
        return false
      }
      if (!q) {
        return true
      }
      return String(e.id).includes(q) || e.titulo.toLowerCase().includes(q) || e.empresaNombre.toLowerCase().includes(q)
    })
  }, [encuestas, q, eEmpresa, eUrgente, eCerrada, eDesde, eHasta])

  const enviadasRows = useMemo(
    () =>
      enviadasFiltradas.map((e) => {
        const env = e.envio!
        return {
          id: <span className="font-mono text-xs text-slate-500">#{e.id}</span>,
          titulo: <span className="font-semibold text-slate-900">{e.titulo}</span>,
          conteo: (
            <span className="font-medium tabular-nums text-[#3148c8]">
              {env.enviados} / {env.contestados} / {env.noContestado}
            </span>
          ),
          urgente: env.urgente ? (
            <span className="inline-flex rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">
              Urgente
            </span>
          ) : (
            <span className="text-xs text-slate-400">—</span>
          ),
          cerrada: env.cerrada ? (
            <span className="text-xs font-semibold text-slate-600">Cerrada</span>
          ) : (
            <span className="text-xs text-emerald-700">Sin cerrar</span>
          ),
          fechaEnvio: env.fechaEnvio,
          vigencia: (
            <span className="inline-flex rounded-md bg-[#3148c8]/10 px-2 py-0.5 text-xs font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/20">
              {env.vigencia}
            </span>
          ),
          vencimiento: <span className="text-xs text-slate-600">{env.vencimiento}</span>,
          empresa: e.empresaNombre,
          _key: e.key,
        }
      }),
    [enviadasFiltradas],
  )

  // ---- CATEGORÍAS ----
  const categoriasFiltradas = useMemo(() => {
    if (!q) {
      return categorias
    }
    return categorias.filter(
      (c) => String(c.id).includes(q) || c.nombre.toLowerCase().includes(q) || c.empresaNombre.toLowerCase().includes(q),
    )
  }, [categorias, q])

  const categoriasRows = useMemo(
    () =>
      categoriasFiltradas.map((c) => ({
        id: <span className="font-mono text-xs text-slate-500">#{c.id}</span>,
        nombre: <span className="font-semibold text-slate-900">{c.nombre}</span>,
        empresa: c.empresaNombre === 'Sin asignar' ? <span className="text-slate-400">Sin asignar</span> : c.empresaNombre,
        ligadas: <span className="tabular-nums">{c.encuestasLigadas}</span>,
        _key: String(c.id),
      })),
    [categoriasFiltradas],
  )

  const handleNew = () => {
    if (tab === 'categorias') {
      setCatRecord(null)
      setCatOpen(true)
    } else {
      navigate(paths.encuestasNueva)
    }
  }

  const handleSendDone = (envio: EnvioEncuestaDraft, destinatarioIds: string[]) => {
    if (enviarRow) {
      encuestasStore.registrarEnvio(enviarRow, envio, destinatarioIds.length)
    }
    setEnviarOpen(false)
  }

  const handleSaveCategoria = (values: CategoriaFormValues) => {
    if (catRecord) {
      encuestasStore.updateCategoria(catRecord.id, values)
    } else {
      encuestasStore.addCategoria(values)
    }
  }

  const handleSaveEnvio = (key: string, values: EnvioEdit) => {
    encuestasStore.updateEnvio(key, {
      titulo: values.titulo,
      fechaEnvio: values.fechaEnvio || '—',
      vigencia: values.vigencia || '—',
      vencimiento: values.vigencia || '—',
    })
  }

  const confirmAction = () => {
    if (!confirm) {
      return
    }
    if (confirm.kind === 'encuesta-delete') {
      encuestasStore.removeEncuesta(confirm.row.key)
    } else if (confirm.kind === 'encuesta-toggle') {
      encuestasStore.setEstadoEncuesta(confirm.row.key, confirm.row.estado === 'inactiva' ? 'activa' : 'inactiva')
    } else if (confirm.kind === 'encuesta-cerrar') {
      encuestasStore.cerrarEncuesta(confirm.row.key, confirm.row.estado !== 'cerrada')
    } else if (confirm.kind === 'categoria-delete') {
      encuestasStore.removeCategoria(confirm.row.id)
    }
    setConfirm(null)
  }

  const limpiarFiltrosEnviadas = () => {
    setEEmpresa('')
    setEUrgente('')
    setECerrada('')
    setEDesde('')
    setEHasta('')
  }

  const newLabel = tab === 'categorias' ? 'Nueva categoría' : 'Nueva encuesta'
  const heading = tab === 'enviadas' ? 'Encuestas enviadas' : tab === 'categorias' ? 'Categorías' : 'Encuestas'
  const guidanceForTab =
    tab === 'categorias' ? UX_ENCUESTAS_CATEGORIAS : tab === 'enviadas' ? UX_ENCUESTAS_ENVIADAS : UX_ENCUESTAS

  const renderEncuestaActions = (raw: EncuestaRow) => (
    <EncuestaActions
      row={raw}
      onEdit={() => navigate(paths.encuestasEditar(raw.key))}
      onSend={() => {
        setEnviarRow(raw)
        setEnviarOpen(true)
      }}
      onEditEnvio={() => {
        setEditEnvioRow(raw)
        setEditEnvioOpen(true)
      }}
      onEnlaces={() => setNotice(`Enlaces de "${raw.titulo}" copiados al portapapeles (demo).`)}
      onEnlacesExcel={() => setNotice(`Excel de enlaces de "${raw.titulo}" generado (demo).`)}
      onDuplicate={() => encuestasStore.duplicateEncuesta(raw.key)}
      onToggleCerrar={() => setConfirm({ kind: 'encuesta-cerrar', row: raw })}
      onToggleActiva={() => setConfirm({ kind: 'encuesta-toggle', row: raw })}
      onDelete={() => setConfirm({ kind: 'encuesta-delete', row: raw })}
    />
  )

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Encuestas"
        title="Centro de encuestas"
        description="Diseña, envía y monitorea encuestas de clima y satisfacción. Borradores, campañas en curso y resultados en una sola lista."
        icon={RectangleStackIcon}
        stat={{ label: 'Encuestas activas', value: String(kpis.activas), hint: 'Publicadas y en curso' }}
        guidance={guidanceForTab}
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <KpiCard label="Total de encuestas" value={String(kpis.encuestas)} hint="Borradores, activas y cerradas" icon={ClipboardDocumentListIcon} />
        <KpiCard label="Activas" value={String(kpis.activas)} hint="Publicadas y en curso" icon={RectangleStackIcon} />
        <KpiCard label="Enviadas" value={String(kpis.enviadas)} hint="Con al menos un envío" icon={PaperAirplaneIcon} />
        <KpiCard label="Participación promedio" value={kpis.participacion} hint="Contestadas vs enviadas" icon={ChartBarIcon} />
      </div>

      {notice ? (
        <div className="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
          <span>{notice}</span>
          <button type="button" className="rounded p-1 hover:bg-emerald-100" aria-label="Cerrar aviso" onClick={() => setNotice(null)}>
            <XMarkIcon className="h-4 w-4" />
          </button>
        </div>
      ) : null}

      <UxTabs tabs={TABS} active={tab} onChange={(id) => { setTab(id as TabId); setSearch('') }} />

      <div className="space-y-4">
        <FilamentListToolbar
          heading={heading}
          newLabel={newLabel}
          onNew={handleNew}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder={
            tab === 'categorias'
              ? 'Buscar por ID, nombre o empresa…'
              : tab === 'enviadas'
                ? 'Buscar por ID, título o empresa…'
                : 'Buscar por ID, título, categoría o empresa…'
          }
          hint="Prototipo con datos mock. Las acciones modifican estado local en memoria."
        />

        {tab === 'todas' ? (
          <>
            <FilterBar onClear={() => { setTCategoria(''); setTEmpresa('') }}>
              <FilterField label="Categoría" id="t-cat">
                <ProtoSelect id="t-cat" value={tCategoria} onValueChange={setTCategoria} options={categoriasOptions} placeholder="Todas" />
              </FilterField>
              <FilterField label="Empresa emisora" id="t-emp">
                <ProtoSelect id="t-emp" value={tEmpresa} onValueChange={setTEmpresa} options={CATALOG_EMPRESAS} placeholder="Todas" />
              </FilterField>
            </FilterBar>

            <MockFilamentTable
              columns={[
                { key: 'id', header: 'ID' },
                { key: 'titulo', header: 'Título' },
                { key: 'categoria', header: 'Categoría' },
                { key: 'empresa', header: 'Empresa emisora' },
                { key: 'estado', header: 'Estado' },
                { key: 'participacion', header: 'Participación' },
              ]}
              rows={todasRows}
              rowKey={(row) => String(row._key)}
              actionsColumn={{
                render: (_row, index) => {
                  const raw = todasFiltradas[index]
                  return raw ? renderEncuestaActions(raw) : null
                },
              }}
            />
          </>
        ) : null}

        {tab === 'enviadas' ? (
          <>
            <FilterBar onClear={limpiarFiltrosEnviadas}>
              <FilterField label="Empresa emisora" id="e-emp">
                <ProtoSelect id="e-emp" value={eEmpresa} onValueChange={setEEmpresa} options={CATALOG_EMPRESAS} placeholder="Todas" />
              </FilterField>
              <FilterField label="Urgente" id="e-urg">
                <ProtoSelect id="e-urg" value={eUrgente} onValueChange={setEUrgente} options={OPCIONES_SI_NO} placeholder="Cualquiera" />
              </FilterField>
              <FilterField label="Cerrada" id="e-cer">
                <ProtoSelect id="e-cer" value={eCerrada} onValueChange={setECerrada} options={OPCIONES_SI_NO} placeholder="Cualquiera" />
              </FilterField>
              <FilterField label="Envío desde" id="e-desde">
                <input id="e-desde" type="date" className={protoInputClass} value={eDesde} onChange={(ev) => setEDesde(ev.target.value)} />
              </FilterField>
              <FilterField label="Envío hasta" id="e-hasta">
                <input id="e-hasta" type="date" className={protoInputClass} value={eHasta} onChange={(ev) => setEHasta(ev.target.value)} />
              </FilterField>
            </FilterBar>

            <MockFilamentTable
              columns={[
                { key: 'id', header: 'ID' },
                { key: 'titulo', header: 'Título' },
                { key: 'conteo', header: 'Enviado / Contestado / No contestado' },
                { key: 'urgente', header: 'Urgente' },
                { key: 'cerrada', header: 'Cerrada' },
                { key: 'fechaEnvio', header: 'Fecha de envío' },
                { key: 'vigencia', header: 'Vigencia' },
                { key: 'vencimiento', header: 'Vencimiento' },
                { key: 'empresa', header: 'Empresa de emisor' },
              ]}
              rows={enviadasRows}
              rowKey={(row) => String(row._key)}
              actionsColumn={{
                render: (_row, index) => {
                  const raw = enviadasFiltradas[index]
                  return raw ? renderEncuestaActions(raw) : null
                },
              }}
            />
          </>
        ) : null}

        {tab === 'categorias' ? (
          <MockFilamentTable
            columns={[
              { key: 'id', header: 'No.' },
              { key: 'nombre', header: 'Nombre' },
              { key: 'empresa', header: 'Empresa' },
              { key: 'ligadas', header: 'Encuestas ligadas' },
            ]}
            rows={categoriasRows}
            rowKey={(row) => String(row._key)}
            actionsColumn={{
              render: (_row, index) => {
                const raw = categoriasFiltradas[index]
                if (!raw) {
                  return null
                }
                return (
                  <CategoriaActions
                    row={raw}
                    onEdit={() => {
                      setCatRecord(raw)
                      setCatOpen(true)
                    }}
                    onDelete={() => setConfirm({ kind: 'categoria-delete', row: raw })}
                  />
                )
              },
            }}
          />
        ) : null}
      </div>

      {catOpen ? (
        <CategoriaSlideOver open onClose={() => setCatOpen(false)} record={catRecord} onSave={handleSaveCategoria} />
      ) : null}

      {enviarOpen ? (
        <EnviarEncuestaModal open onClose={() => setEnviarOpen(false)} titulo={enviarRow?.titulo ?? null} onSend={handleSendDone} />
      ) : null}

      {editEnvioOpen ? (
        <EditarEnvioModal open onClose={() => setEditEnvioOpen(false)} record={editEnvioRow} onSave={handleSaveEnvio} />
      ) : null}

      <ConfirmDialog
        open={confirm !== null}
        onClose={() => setConfirm(null)}
        title={
          confirm?.kind === 'encuesta-delete'
            ? '¿Eliminar encuesta?'
            : confirm?.kind === 'encuesta-toggle'
              ? confirm.row.estado === 'inactiva'
                ? '¿Reactivar encuesta?'
                : '¿Desactivar encuesta?'
              : confirm?.kind === 'encuesta-cerrar'
                ? confirm.row.estado === 'cerrada'
                  ? '¿Reabrir encuesta?'
                  : '¿Cerrar encuesta?'
                : '¿Eliminar categoría?'
        }
        description={
          confirm?.kind === 'encuesta-delete'
            ? 'Solo demostración: se quitará la encuesta del listado en memoria.'
            : confirm?.kind === 'encuesta-toggle'
              ? 'Cambia el estado de la encuesta en el prototipo.'
              : confirm?.kind === 'encuesta-cerrar'
                ? confirm.row.estado === 'cerrada'
                  ? 'La encuesta volverá a aceptar respuestas.'
                  : 'Se dejará de aceptar respuestas para esta encuesta.'
                : 'Se eliminará la categoría del catálogo en memoria.'
        }
        confirmLabel={
          confirm?.kind === 'encuesta-toggle'
            ? confirm.row.estado === 'inactiva'
              ? 'Reactivar'
              : 'Desactivar'
            : confirm?.kind === 'encuesta-cerrar'
              ? confirm.row.estado === 'cerrada'
                ? 'Reabrir'
                : 'Cerrar'
              : 'Eliminar'
        }
        danger={confirm?.kind === 'encuesta-delete' || confirm?.kind === 'categoria-delete'}
        onConfirm={confirmAction}
      />
    </div>
  )
}

function FilterBar({ children, onClear }: { children: React.ReactNode; onClear: () => void }) {
  return (
    <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h3 className="text-sm font-semibold text-slate-900">Filtros</h3>
        <Button type="button" variant="outline" size="sm" onClick={onClear}>
          Borrar filtros
        </Button>
      </div>
      <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">{children}</div>
    </section>
  )
}

function FilterField({ label, id, children }: { label: string; id: string; children: React.ReactNode }) {
  return (
    <div>
      <label className={protoLabelClass} htmlFor={id}>
        {label}
      </label>
      {children}
    </div>
  )
}

function EncuestaActions({
  row,
  onEdit,
  onSend,
  onEditEnvio,
  onEnlaces,
  onEnlacesExcel,
  onDuplicate,
  onToggleCerrar,
  onToggleActiva,
  onDelete,
}: {
  row: EncuestaRow
  onEdit: () => void
  onSend: () => void
  onEditEnvio: () => void
  onEnlaces: () => void
  onEnlacesExcel: () => void
  onDuplicate: () => void
  onToggleCerrar: () => void
  onToggleActiva: () => void
  onDelete: () => void
}) {
  const enviada = Boolean(row.envio)
  return (
    <div className="flex justify-end">
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button type="button" variant="ghost" size="icon" className="h-8 w-8 text-slate-500 hover:text-slate-800" aria-label={`Acciones de ${row.titulo}`}>
            <EllipsisVerticalIcon className="h-5 w-5" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[14rem]">
          <DropdownMenuItem className="gap-2" onClick={onEdit}>
            <PencilSquareIcon className="h-4 w-4 text-slate-500" />
            Editar diseño
          </DropdownMenuItem>
          <DropdownMenuItem className="gap-2" onClick={onSend}>
            <PaperAirplaneIcon className="h-4 w-4 text-slate-500" />
            {enviada ? 'Reenviar' : 'Enviar'}
          </DropdownMenuItem>
          {enviada ? (
            <>
              <DropdownMenuItem className="gap-2" onClick={onEditEnvio}>
                <ChartBarIcon className="h-4 w-4 text-slate-500" />
                Editar envío
              </DropdownMenuItem>
              <DropdownMenuItem className="gap-2" onClick={onEnlaces}>
                <LinkIcon className="h-4 w-4 text-slate-500" />
                Enlaces
              </DropdownMenuItem>
              <DropdownMenuItem className="gap-2" onClick={onEnlacesExcel}>
                <TableCellsIcon className="h-4 w-4 text-slate-500" />
                Enlaces Excel
              </DropdownMenuItem>
            </>
          ) : null}
          <DropdownMenuItem className="gap-2" onClick={onDuplicate}>
            <DocumentDuplicateIcon className="h-4 w-4 text-slate-500" />
            Duplicar
          </DropdownMenuItem>
          <DropdownMenuSeparator />
          {enviada ? (
            <DropdownMenuItem className="gap-2" onClick={onToggleCerrar}>
              {row.estado === 'cerrada' ? (
                <>
                  <LockOpenIcon className="h-4 w-4 text-slate-500" />
                  Reabrir
                </>
              ) : (
                <>
                  <LockClosedIcon className="h-4 w-4 text-slate-500" />
                  Cerrar
                </>
              )}
            </DropdownMenuItem>
          ) : null}
          <DropdownMenuItem className="gap-2" onClick={onToggleActiva}>
            <NoSymbolIcon className="h-4 w-4 text-slate-500" />
            {row.estado === 'inactiva' ? 'Reactivar' : 'Desactivar'}
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

function CategoriaActions({
  row,
  onEdit,
  onDelete,
}: {
  row: CategoriaEncuesta
  onEdit: () => void
  onDelete: () => void
}) {
  const bloqueada = row.encuestasLigadas > 0
  return (
    <div className="flex justify-end">
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button type="button" variant="ghost" size="icon" className="h-8 w-8 text-slate-500 hover:text-slate-800" aria-label={`Acciones de ${row.nombre}`}>
            <EllipsisVerticalIcon className="h-5 w-5" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[13rem]">
          <DropdownMenuItem className="gap-2" onClick={onEdit}>
            <PencilSquareIcon className="h-4 w-4 text-slate-500" />
            Editar
          </DropdownMenuItem>
          <DropdownMenuSeparator />
          <DropdownMenuItem variant="destructive" className="gap-2" disabled={bloqueada} onClick={onDelete}>
            <TrashIcon className="h-4 w-4" />
            {bloqueada ? 'No se puede eliminar (en uso)' : 'Eliminar'}
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}
