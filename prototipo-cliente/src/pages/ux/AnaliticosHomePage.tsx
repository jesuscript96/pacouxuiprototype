import {
  ArrowPathRoundedSquareIcon,
  BriefcaseIcon,
  ChartPieIcon,
  ClipboardDocumentCheckIcon,
  FaceSmileIcon,
  HeartIcon,
  PresentationChartLineIcon,
  SparklesIcon,
  TableCellsIcon,
} from '@heroicons/react/24/outline'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { paths } from '../../navigation/config'
import {
  DemograficosSection,
  EngagementSection,
  EncuestasSection,
  ResumenSection,
  RotacionSection,
} from './analiticos/AnaliticosSections'

const secciones = [
  {
    id: 'resumen',
    label: 'Resumen',
    descripcion: 'KPIs principales y visión ejecutiva',
    icon: '📊',
  },
  {
    id: 'rotacion',
    label: 'Rotación',
    descripcion: 'Altas, bajas y motivos',
    icon: '🔄',
  },
  {
    id: 'engagement',
    label: 'Engagement',
    descripcion: 'Actividad, adopción y uso',
    icon: '💬',
  },
  {
    id: 'demograficos',
    label: 'Demográficos',
    descripcion: 'Composición de la plantilla',
    icon: '👥',
  },
  {
    id: 'encuestas',
    label: 'Encuestas',
    descripcion: 'eNPS, clima y satisfacción',
    icon: '📋',
  },
] as const

type SeccionId = (typeof secciones)[number]['id']

/** Misma rejilla de accesos rápidos que `analiticos-home.blade.php` (informes Tableau). */
const TABLEAU_CTA = [
  {
    label: 'Rotación personal',
    segment: 'rotacion-personal',
    icon: ArrowPathRoundedSquareIcon,
  },
  { label: 'Demográficos', segment: 'demograficos', icon: ChartPieIcon },
  { label: 'eNPS', segment: 'satisfaccion-enps', icon: FaceSmileIcon },
  {
    label: 'Encuestas',
    segment: 'encuestas',
    icon: ClipboardDocumentCheckIcon,
  },
  { label: 'Reclutamiento', segment: 'reclutamiento', icon: BriefcaseIcon },
  { label: 'Reconocimientos', segment: 'reconocimientos', icon: SparklesIcon },
  { label: 'Salud mental', segment: 'salud-mental', icon: HeartIcon },
] as const

export function AnaliticosHomePage() {
  const [seccion, setSeccion] = useState<SeccionId>('resumen')

  return (
    <div className="an-showroom space-y-6 sm:space-y-8">
      <div className="an-hero dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8">
        <div className="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-indigo-400/[0.06] blur-3xl" />
        <div className="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-slate-300/[0.12] blur-3xl" />
        <div className="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
              <span className="inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/15" />
              Analíticos · Showroom de visualización
            </div>
            <h1 className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
              Una forma distinta de ver cada historia en tus datos
            </h1>
            <p className="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">
              Navega las 5 secciones para ver más de 20 patrones de visualización aplicados al negocio:
              donut, barras apiladas, heatmaps, pirámide poblacional, funnel, radar, gauge y mucho más.
              Cuando necesites el detalle navegable, abre los tableros de Tableau al final de esta vista.
            </p>
          </div>
          <div className="flex items-center gap-3 rounded-2xl border border-white/60 bg-white/45 p-4 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200/70 bg-white/70 text-[#3148c8] shadow-sm">
              <PresentationChartLineIcon className="h-6 w-6" />
            </div>
            <div>
              <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Visualizaciones
              </p>
              <p className="text-2xl font-extrabold tabular-nums text-slate-900">21</p>
              <p className="text-[11px] text-slate-600">Patrones en 5 secciones</p>
            </div>
          </div>
        </div>
      </div>

      <div className="an-tabs sticky top-0 z-20 -mx-4 sm:-mx-6 lg:-mx-8">
        <div className="mx-4 rounded-2xl border border-slate-200 bg-white/80 p-2 shadow-sm backdrop-blur-md sm:mx-6 lg:mx-8">
          <nav className="flex flex-wrap items-stretch gap-1" role="tablist">
            {secciones.map((item) => {
              const activo = seccion === item.id
              return (
                <button
                  key={item.id}
                  type="button"
                  role="tab"
                  aria-selected={activo}
                  onClick={() => setSeccion(item.id)}
                  className={
                    'group flex min-w-[8rem] flex-1 items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-all duration-200 ' +
                    (activo
                      ? 'bg-[#3148c8] text-white shadow-md ring-1 ring-[#3148c8]/25'
                      : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900')
                  }
                >
                  <span
                    className={
                      'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-base ' +
                      (activo ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-white')
                    }
                  >
                    {item.icon}
                  </span>
                  <span className="flex flex-col items-start leading-tight">
                    <span>{item.label}</span>
                    <span
                      className={
                        'text-[10.5px] font-normal normal-case tracking-normal ' +
                        (activo ? 'text-white/75' : 'text-slate-400')
                      }
                    >
                      {item.descripcion}
                    </span>
                  </span>
                </button>
              )
            })}
          </nav>
        </div>
      </div>

      {seccion === 'resumen' ? <ResumenSection /> : null}
      {seccion === 'rotacion' ? <RotacionSection /> : null}
      {seccion === 'engagement' ? <EngagementSection /> : null}
      {seccion === 'demograficos' ? <DemograficosSection /> : null}
      {seccion === 'encuestas' ? <EncuestasSection /> : null}

      <div className="an-tableau-cta relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8">
        <div className="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-indigo-50 blur-3xl" />
        <div className="relative grid grid-cols-1 gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <span className="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700 ring-1 ring-indigo-100">
              <TableCellsIcon className="h-3.5 w-3.5" />
              Datos en profundidad
            </span>
            <h3 className="mt-3 text-xl font-bold tracking-tight text-slate-900">
              ¿Necesitas analizar los datos reales?
            </h3>
            <p className="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600">
              Las visualizaciones de esta vista son un showroom con datos demo. Para los tableros navegables en vivo,
              abre cualquiera de los informes de Tableau: filtros, drill-down y descargas disponibles.
            </p>
          </div>
          <Link
            to={paths.analiticosReport('rotacion-personal')}
            className="inline-flex items-center gap-2 self-start rounded-xl bg-[#3148c8] px-5 py-3 text-sm font-semibold text-white shadow-md ring-1 ring-[#3148c8]/30 transition hover:-translate-y-0.5 hover:bg-[#2a3eb0] hover:shadow-lg"
          >
            Abrir informes Tableau
            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden>
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </Link>
        </div>

        <div className="relative mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
          {TABLEAU_CTA.map(({ label, segment, icon: Icon }) => (
            <Link
              key={segment}
              to={paths.analiticosReport(segment)}
              className="group flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-3 text-center transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md hover:shadow-indigo-500/5"
            >
              <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                <Icon className="h-4 w-4" />
              </span>
              <span className="text-[11px] font-semibold leading-tight text-slate-700">{label}</span>
            </Link>
          ))}
        </div>
      </div>
    </div>
  )
}
