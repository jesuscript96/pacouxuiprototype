import {
  ArrowDownTrayIcon,
  ArrowRightIcon,
  BellAlertIcon,
  CheckCircleIcon,
  ClipboardDocumentCheckIcon,
  DocumentChartBarIcon,
  EllipsisVerticalIcon,
  EyeIcon,
  MagnifyingGlassIcon,
  PaperAirplaneIcon,
  ShieldCheckIcon,
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
import { Input } from '@/components/ui/input'
import { MockFilamentTable } from '@/components/ux/MockFilamentTable'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { UxHero } from '@/components/ux/UxHero'
import { UxTabs, type UxTab } from '@/components/ux/UxTabs'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { cn } from '@/lib/utils'
import { UX_NOM035 } from '@/guidance/uxSections'
import { paths } from '@/navigation/config'

import {
  CATALOG_EMPRESAS,
  CATALOG_UBICACIONES,
  OPCIONES_MESES,
} from '../mensajes/mensajesConstants'
import { EnviarEncuestaModal } from '../encuestas/EnviarEncuestaModal'
import { OPCIONES_FORMATO_REPORTE } from '../encuestas/encuestasMockData'
import type { EnvioEncuestaDraft } from '../encuestas/encuestasTypes'
import {
  ANIOS_OPCIONES,
  ENVIOS_NOM035,
  GUIAS_NOM035,
  GUIAS_OPCIONES,
  OPCIONES_ESTATUS_NOM,
  totalPreguntas,
} from './nom035MockData'
import type { EnvioNom035Row, EstatusNom, GuiaNom035 } from './nom035Types'

type TabId = 'guias' | 'envios'

const TABS: UxTab[] = [
  { id: 'guias', label: 'Guías', icon: ClipboardDocumentCheckIcon },
  { id: 'envios', label: 'Envíos', icon: PaperAirplaneIcon },
]

function estatusBadge(estatus: EstatusNom) {
  const map = {
    pendiente: 'bg-amber-50 text-amber-900 ring-amber-200/80',
    contestada: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    vencida: 'bg-red-50 text-red-700 ring-red-200/80',
  }[estatus]
  const label = { pendiente: 'Pendiente', contestada: 'Contestada', vencida: 'Vencida' }[estatus]
  return <span className={cn('inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1', map)}>{label}</span>
}

export function Nom035UxPage() {
  const navigate = useNavigate()
  const [tab, setTab] = useState<TabId>('guias')
  const [search, setSearch] = useState('')
  const [notice, setNotice] = useState<string | null>(null)

  const [enviarOpen, setEnviarOpen] = useState(false)
  const [enviarGuia, setEnviarGuia] = useState<GuiaNom035 | null>(null)
  const [reporteGuia, setReporteGuia] = useState<GuiaNom035 | null>(null)
  const [recordatorio, setRecordatorio] = useState<EnvioNom035Row | null>(null)
  const [recordatorioMasivo, setRecordatorioMasivo] = useState(false)

  // Filtros de envíos
  const [fEmpresa, setFEmpresa] = useState('')
  const [fUbicacion, setFUbicacion] = useState('')
  const [fGuia, setFGuia] = useState('')
  const [fEstatus, setFEstatus] = useState('')
  const [fDesde, setFDesde] = useState('')
  const [fHasta, setFHasta] = useState('')

  const q = search.trim().toLowerCase()

  const guiasFiltradas = useMemo(() => {
    if (!q) {
      return GUIAS_NOM035
    }
    return GUIAS_NOM035.filter(
      (g) => g.titulo.toLowerCase().includes(q) || g.clave.toLowerCase().includes(q) || String(g.id).includes(q),
    )
  }, [q])

  const enviosFiltrados = useMemo(() => {
    return ENVIOS_NOM035.filter((r) => {
      if (q && !(r.nombre.toLowerCase().includes(q) || String(r.id).includes(q))) {
        return false
      }
      if (fEmpresa && r.empresa !== labelOf(CATALOG_EMPRESAS, fEmpresa)) {
        return false
      }
      if (fUbicacion && r.ubicacion !== labelOf(CATALOG_UBICACIONES, fUbicacion)) {
        return false
      }
      if (fGuia && r.guia !== fGuia) {
        return false
      }
      if (fEstatus && r.estatus !== fEstatus) {
        return false
      }
      if (fDesde && r.fechaEnvio < fDesde) {
        return false
      }
      if (fHasta && r.fechaEnvio > fHasta) {
        return false
      }
      return true
    })
  }, [q, fEmpresa, fUbicacion, fGuia, fEstatus, fDesde, fHasta])

  const pendientesFiltrados = useMemo(
    () => enviosFiltrados.filter((r) => r.estatus !== 'contestada'),
    [enviosFiltrados],
  )

  const kpis = useMemo(() => {
    const contestadas = ENVIOS_NOM035.filter((r) => r.estatus === 'contestada').length
    const pendientes = ENVIOS_NOM035.filter((r) => r.estatus === 'pendiente').length
    return { guias: GUIAS_NOM035.length, envios: ENVIOS_NOM035.length, contestadas, pendientes }
  }, [])

  const enviosRows = useMemo(
    () =>
      enviosFiltrados.map((r) => ({
        id: <span className="font-mono text-xs text-slate-500">#{r.id}</span>,
        nombre: <span className="font-semibold text-slate-900">{r.nombre}</span>,
        guia: r.guia,
        empresa: r.empresa,
        ubicacion: r.ubicacion,
        fechaEnvio: r.fechaEnvio,
        vencimiento: r.vencimiento,
        cerrada: r.cerrada ? <span className="text-xs font-semibold text-slate-600">Cerrada</span> : <span className="text-xs text-emerald-700">Abierta</span>,
        estatus: estatusBadge(r.estatus),
        _key: r.key,
      })),
    [enviosFiltrados],
  )

  const handleSendDone = (_envio: EnvioEncuestaDraft, destinatarioIds: string[]) => {
    setEnviarOpen(false)
    setNotice(`Cuestionario "${enviarGuia?.clave ?? ''}" enviado a ${destinatarioIds.length} colaborador(es) (demo).`)
  }

  const irAEnviosDeGuia = (guia: GuiaNom035) => {
    setFGuia(guia.clave)
    setSearch('')
    setTab('envios')
  }

  const limpiarFiltros = () => {
    setFEmpresa('')
    setFUbicacion('')
    setFGuia('')
    setFEstatus('')
    setFDesde('')
    setFHasta('')
  }

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Encuestas · Cumplimiento"
        title="Cuestionarios NOM-035"
        description="Guías oficiales de la NOM-035-STPS, envío segmentado, reportes de riesgo y control de participación, en una sola vista."
        icon={ShieldCheckIcon}
        stat={{ label: 'Guías disponibles', value: String(kpis.guias), hint: 'Cargadas en el sistema' }}
        guidance={UX_NOM035}
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Kpi label="Guías oficiales" value={String(kpis.guias)} hint="De referencia, solo lectura" icon={ClipboardDocumentCheckIcon} />
        <Kpi label="Evaluaciones" value={String(kpis.envios)} hint="Total de envíos" icon={PaperAirplaneIcon} />
        <Kpi label="Contestadas" value={String(kpis.contestadas)} hint="Participación completada" icon={CheckCircleIcon} />
        <Kpi label="Pendientes" value={String(kpis.pendientes)} hint="Requieren recordatorio" icon={BellAlertIcon} />
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

      {tab === 'guias' ? (
        <div className="space-y-4">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="text-base font-semibold leading-tight text-slate-900">Guías de referencia</h2>
              <p className="text-xs text-muted-foreground">Selecciona una guía para ver sus evaluaciones, o usa el menú para verla, enviarla o generar su reporte.</p>
            </div>
            <div className="relative min-w-[12rem] sm:max-w-xs">
              <MagnifyingGlassIcon className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" aria-hidden />
              <Input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Buscar guía…" className="h-9 w-full pl-9 shadow-sm" aria-label="Buscar guía" />
            </div>
          </div>

          {reporteGuia ? (
            <ReportePanel guia={reporteGuia} onClose={() => setReporteGuia(null)} onGenerar={() => { setNotice(`Reporte de "${reporteGuia.clave}" generado (demo).`); setReporteGuia(null) }} />
          ) : null}

          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-2">
            {guiasFiltradas.map((g) => (
              <GuiaCard
                key={g.id}
                guia={g}
                onOpenEnvios={() => irAEnviosDeGuia(g)}
                onVer={() => navigate(paths.nom035Guia(g.id))}
                onEnviar={() => { setEnviarGuia(g); setEnviarOpen(true) }}
                onReporte={() => setReporteGuia(g)}
              />
            ))}
          </div>
        </div>
      ) : null}

      {tab === 'envios' ? (
        <div className="space-y-4">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="text-base font-semibold leading-tight text-slate-900">Destinatarios NOM-035</h2>
              <p className="text-xs text-muted-foreground">Control de participación. Envía recordatorios a quienes tienen evaluaciones pendientes.</p>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <div className="relative min-w-[12rem] sm:max-w-xs">
                <MagnifyingGlassIcon className="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" aria-hidden />
                <Input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Buscar por nombre o ID…" className="h-9 w-full pl-9 shadow-sm" aria-label="Buscar destinatario" />
              </div>
              <Button
                type="button"
                variant="outline"
                className="shrink-0 gap-1.5 font-semibold"
                disabled={pendientesFiltrados.length === 0}
                onClick={() => setRecordatorioMasivo(true)}
              >
                <BellAlertIcon className="h-4 w-4" />
                Enviar recordatorios
                {pendientesFiltrados.length > 0 ? (
                  <span className="ml-1 rounded-full bg-amber-100 px-1.5 text-xs font-semibold text-amber-900">
                    {pendientesFiltrados.length}
                  </span>
                ) : null}
              </Button>
              <Button type="button" className="shrink-0 gap-1.5 font-semibold" onClick={() => setNotice('Excel de destinatarios NOM-035 generado (demo).')}>
                <ArrowDownTrayIcon className="h-4 w-4" />
                Descargar Excel
              </Button>
            </div>
          </div>

          <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <h3 className="text-sm font-semibold text-slate-900">Filtros</h3>
              <Button type="button" variant="outline" size="sm" onClick={limpiarFiltros}>Limpiar</Button>
            </div>
            <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              <div>
                <label className={protoLabelClass} htmlFor="nom-f-emp">Empresa</label>
                <ProtoSelect id="nom-f-emp" value={fEmpresa} onValueChange={setFEmpresa} options={CATALOG_EMPRESAS} placeholder="Todas" />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="nom-f-ub">Ubicación</label>
                <ProtoSelect id="nom-f-ub" value={fUbicacion} onValueChange={setFUbicacion} options={CATALOG_UBICACIONES} placeholder="Todas" />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="nom-f-guia">Guía</label>
                <ProtoSelect id="nom-f-guia" value={fGuia} onValueChange={setFGuia} options={GUIAS_OPCIONES} placeholder="Todas" />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="nom-f-est">Estatus</label>
                <ProtoSelect id="nom-f-est" value={fEstatus} onValueChange={setFEstatus} options={OPCIONES_ESTATUS_NOM} placeholder="Cualquiera" />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="nom-f-desde">Desde</label>
                <input id="nom-f-desde" type="date" className={protoInputClass} value={fDesde} onChange={(e) => setFDesde(e.target.value)} />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="nom-f-hasta">Hasta</label>
                <input id="nom-f-hasta" type="date" className={protoInputClass} value={fHasta} onChange={(e) => setFHasta(e.target.value)} />
              </div>
            </div>
          </section>

          <MockFilamentTable
            columns={[
              { key: 'id', header: 'ID' },
              { key: 'nombre', header: 'Nombre' },
              { key: 'guia', header: 'Guía' },
              { key: 'empresa', header: 'Empresa' },
              { key: 'ubicacion', header: 'Ubicación' },
              { key: 'fechaEnvio', header: 'Fecha de envío' },
              { key: 'vencimiento', header: 'Vencimiento' },
              { key: 'cerrada', header: 'Cerrada' },
              { key: 'estatus', header: 'Estatus' },
            ]}
            rows={enviosRows}
            rowKey={(row) => String(row._key)}
            actionsColumn={{
              render: (_row, index) => {
                const r = enviosFiltrados[index]
                if (!r) {
                  return null
                }
                return <EnvioActions row={r} onRecordatorio={() => setRecordatorio(r)} />
              },
            }}
          />
        </div>
      ) : null}

      {enviarOpen ? (
        <EnviarEncuestaModal open onClose={() => setEnviarOpen(false)} titulo={enviarGuia?.titulo ?? null} onSend={handleSendDone} />
      ) : null}

      <ConfirmDialog
        open={recordatorio !== null}
        onClose={() => setRecordatorio(null)}
        title="¿Enviar recordatorio?"
        description={
          recordatorio
            ? `Se enviará una notificación push a ${recordatorio.nombre} para que conteste su cuestionario ${recordatorio.guia}.`
            : undefined
        }
        confirmLabel="Enviar recordatorio"
        danger={false}
        onConfirm={() => {
          if (recordatorio) {
            setNotice(`Recordatorio push enviado a ${recordatorio.nombre} (demo).`)
          }
        }}
      />

      <ConfirmDialog
        open={recordatorioMasivo}
        onClose={() => setRecordatorioMasivo(false)}
        title="¿Enviar recordatorios?"
        description={`Se enviará una notificación push a ${pendientesFiltrados.length} colaborador(es) con evaluación pendiente en la vista actual.`}
        confirmLabel="Enviar recordatorios"
        danger={false}
          onConfirm={() => setNotice(`Recordatorios push enviados a ${pendientesFiltrados.length} colaborador(es) (demo).`)}
      />
    </div>
  )
}

function GuiaCard({
  guia,
  onOpenEnvios,
  onVer,
  onEnviar,
  onReporte,
}: {
  guia: GuiaNom035
  onOpenEnvios: () => void
  onVer: () => void
  onEnviar: () => void
  onReporte: () => void
}) {
  return (
    <div
      role="button"
      tabIndex={0}
      onClick={onOpenEnvios}
      onKeyDown={(e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault()
          onOpenEnvios()
        }
      }}
      className="group flex cursor-pointer flex-col rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition-all hover:border-[#3148c8]/40 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8]/30"
    >
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-center gap-2">
          <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#3148c8]/10 text-[#3148c8]">
            <ClipboardDocumentCheckIcon className="h-5 w-5" />
          </span>
          <span className="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
            {guia.clave}
          </span>
        </div>
        <div onClick={(e) => e.stopPropagation()}>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button type="button" variant="ghost" size="icon" className="h-8 w-8 text-slate-500 hover:text-slate-800" aria-label={`Acciones de ${guia.clave}`}>
                <EllipsisVerticalIcon className="h-5 w-5" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="min-w-[12rem]">
              <DropdownMenuItem className="gap-2" onClick={onVer}>
                <EyeIcon className="h-4 w-4 text-slate-500" />
                Ver guía
              </DropdownMenuItem>
              <DropdownMenuItem className="gap-2" onClick={onEnviar}>
                <PaperAirplaneIcon className="h-4 w-4 text-slate-500" />
                Enviar
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem className="gap-2" onClick={onReporte}>
                <DocumentChartBarIcon className="h-4 w-4 text-slate-500" />
                Generar reporte
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <h3 className="mt-3 text-base font-semibold text-slate-900">{guia.titulo}</h3>
      <p className="mt-1.5 line-clamp-2 text-sm text-slate-500">{guia.descripcion}</p>

      <div className="mt-3 flex flex-wrap gap-2">
        <CardBadge>{guia.dirigidaA}</CardBadge>
        <CardBadge>{totalPreguntas(guia)} preguntas</CardBadge>
        <CardBadge>{guia.duracionMin} min</CardBadge>
      </div>

      <div className="mt-4 flex items-center gap-1.5 border-t border-slate-100 pt-3 text-sm font-semibold text-[#3148c8]">
        Ver evaluaciones
        <ArrowRightIcon className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
      </div>
    </div>
  )
}

function CardBadge({ children }: { children: React.ReactNode }) {
  return (
    <span className="rounded-md bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
      {children}
    </span>
  )
}

function EnvioActions({ row, onRecordatorio }: { row: EnvioNom035Row; onRecordatorio: () => void }) {
  const puedeRecordar = row.estatus !== 'contestada'
  return (
    <div className="flex justify-end">
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button type="button" variant="ghost" size="icon" className="h-8 w-8 text-slate-500 hover:text-slate-800" aria-label={`Acciones de ${row.nombre}`}>
            <EllipsisVerticalIcon className="h-5 w-5" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[13rem]">
          <DropdownMenuItem className="gap-2" disabled={!puedeRecordar} onClick={onRecordatorio}>
            <BellAlertIcon className="h-4 w-4 text-slate-500" />
            {puedeRecordar ? 'Enviar recordatorio' : 'Ya contestada'}
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}

function Kpi({
  label,
  value,
  hint,
  icon: Icon,
}: {
  label: string
  value: string
  hint: string
  icon: typeof ClipboardDocumentCheckIcon
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

function ReportePanel({
  guia,
  onClose,
  onGenerar,
}: {
  guia: GuiaNom035
  onClose: () => void
  onGenerar: () => void
}) {
  const [empresa, setEmpresa] = useState('')
  const [ubicacion, setUbicacion] = useState('')
  const [anio, setAnio] = useState('2024')
  const [mes, setMes] = useState('')
  const [formato, setFormato] = useState('xls')

  return (
    <section className="rounded-2xl border border-[#3148c8]/30 bg-[#3148c8]/5 p-4 shadow-sm sm:p-5">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3 className="text-base font-semibold text-slate-900">Reporte del cuestionario</h3>
          <p className="mt-1 text-sm text-slate-500">{guia.titulo}</p>
        </div>
        <Button type="button" variant="ghost" onClick={onClose}>Cerrar</Button>
      </div>
      <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div>
          <label className={protoLabelClass} htmlFor="rep-emp">Empresa <span className="text-red-600">*</span></label>
          <ProtoSelect id="rep-emp" value={empresa} onValueChange={setEmpresa} options={CATALOG_EMPRESAS} placeholder="Selecciona" />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="rep-ub">Ubicación <span className="text-red-600">*</span></label>
          <ProtoSelect id="rep-ub" value={ubicacion} onValueChange={setUbicacion} options={CATALOG_UBICACIONES} placeholder="Selecciona" />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="rep-anio">Año <span className="text-red-600">*</span></label>
          <ProtoSelect id="rep-anio" value={anio} onValueChange={setAnio} options={ANIOS_OPCIONES} allowEmpty={false} />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="rep-mes">Mes <span className="text-red-600">*</span></label>
          <ProtoSelect id="rep-mes" value={mes} onValueChange={setMes} options={OPCIONES_MESES} placeholder="Selecciona" />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="rep-fmt">Formato</label>
          <ProtoSelect id="rep-fmt" value={formato} onValueChange={setFormato} options={OPCIONES_FORMATO_REPORTE} allowEmpty={false} />
        </div>
      </div>
      <div className="mt-4 flex justify-end">
        <Button type="button" className="gap-1.5" onClick={onGenerar}>
          <DocumentChartBarIcon className="h-4 w-4" />
          Generar reporte
        </Button>
      </div>
    </section>
  )
}

function labelOf(options: { value: string; label: string }[], value: string): string {
  return options.find((o) => o.value === value)?.label ?? value
}
