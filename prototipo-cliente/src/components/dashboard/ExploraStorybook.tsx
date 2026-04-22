import {
  BellAlertIcon,
  CreditCardIcon,
  IdentificationIcon,
  SparklesIcon,
  SwatchIcon,
  TableCellsIcon,
  TagIcon,
} from '@heroicons/react/24/outline'
import { Link } from 'react-router-dom'
import { paths, type StorybookSlug } from '../../navigation/config'
import { SectionTitle } from './SectionTitle'

const enlaces: {
  slug: StorybookSlug
  label: string
  descripcion: string
  tono: 'indigo' | 'violet' | 'emerald' | 'amber' | 'sky' | 'rose' | 'slate'
  Icon: typeof CreditCardIcon
}[] = [
  {
    slug: 'tarjetas',
    label: 'Tarjetas',
    descripcion: 'Hero cards, metric cards y accesos directos',
    tono: 'indigo',
    Icon: CreditCardIcon,
  },
  {
    slug: 'degradados',
    label: 'Degradados',
    descripcion: 'Gradientes institucionales y radial',
    tono: 'violet',
    Icon: SwatchIcon,
  },
  {
    slug: 'enfasis',
    label: 'Énfasis',
    descripcion: 'Colores semánticos, badges y chips',
    tono: 'emerald',
    Icon: SparklesIcon,
  },
  {
    slug: 'notificaciones',
    label: 'Notificaciones',
    descripcion: 'Toasts, banners y mensajes',
    tono: 'amber',
    Icon: BellAlertIcon,
  },
  {
    slug: 'tablas-estilo-notion',
    label: 'Tablas Notion',
    descripcion: 'Tablas inline editables estilo Notion',
    tono: 'sky',
    Icon: TableCellsIcon,
  },
  {
    slug: 'badges',
    label: 'Badges',
    descripcion: 'Sistema de etiquetas y estados',
    tono: 'rose',
    Icon: TagIcon,
  },
  {
    slug: 'marca',
    label: 'Marca',
    descripcion: 'Logo, colores y reglas de uso',
    tono: 'slate',
    Icon: IdentificationIcon,
  },
]

const iconBg: Record<(typeof enlaces)[0]['tono'], string> = {
  indigo: 'border-indigo-200/80 bg-indigo-50/90 text-indigo-800',
  violet: 'border-violet-200/80 bg-violet-50/90 text-violet-800',
  emerald: 'border-emerald-200/80 bg-emerald-50/90 text-emerald-800',
  amber: 'border-amber-200/80 bg-amber-50/90 text-amber-900',
  sky: 'border-sky-200/80 bg-sky-50/90 text-sky-800',
  rose: 'border-rose-200/80 bg-rose-50/90 text-rose-800',
  slate: 'border-slate-200/80 bg-white/70 text-slate-700',
}

export function ExploraStorybook() {
  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="Sistema de diseño" />
      <div className="dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8 lg:p-10">
        <div className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-400/[0.07] blur-3xl" />
        <div className="pointer-events-none absolute -bottom-28 -left-16 h-80 w-80 rounded-full bg-slate-300/[0.12] blur-3xl" />

        <div className="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-start">
          <div className="max-w-2xl">
            <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-white/50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600 ring-1 ring-white/40 backdrop-blur-sm">
              <SparklesIcon className="h-3.5 w-3.5 text-[#3148c8]" />
              Showroom completo
            </span>
            <h2 className="mt-4 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
              Cada componente de este dashboard vive en el Storybook
            </h2>
            <p className="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
              Tarjetas con vidrio esmerilado, badges, tablas estilo Notion, banners y más. Cada patrón está documentado
              con ejemplos reales, variantes y reglas de uso. Abre cualquier sección para ver cómo se construye, copia el
              código y aplícalo donde lo necesites.
            </p>
          </div>

          <div className="flex flex-col items-start gap-3 rounded-2xl border border-white/60 bg-white/45 p-5 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl lg:items-end">
            <div className="flex items-center gap-3">
              <div className="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200/70 bg-white/70 text-[#3148c8] shadow-sm">
                <SwatchIcon className="h-6 w-6" />
              </div>
              <div>
                <p className="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">19</p>
                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Páginas de componentes</p>
              </div>
            </div>
            <div className="text-xs text-slate-600">
              Botones · Tarjetas · Tablas · Modales · Grids · Selects · DatePickers · Checkboxes · Iconos · Tipografía ·
              Marca · Colores · Secciones · Campos · y más
            </div>
          </div>
        </div>

        <div className="relative mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-7">
          {enlaces.map(({ slug, label, descripcion, tono, Icon }) => (
            <Link
              key={slug}
              to={paths.storybook(slug)}
              className="group flex flex-col gap-2 rounded-2xl border border-slate-200/70 bg-white/40 p-4 shadow-sm ring-1 ring-white/30 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-slate-300/80 hover:bg-white/65 hover:shadow-md"
            >
              <div
                className={`flex h-10 w-10 items-center justify-center rounded-xl border shadow-sm backdrop-blur-sm ${iconBg[tono]}`}
              >
                <Icon className="h-5 w-5" />
              </div>
              <div>
                <p className="text-sm font-semibold text-slate-900">{label}</p>
                <p className="mt-0.5 hidden text-[11px] leading-snug text-slate-600 sm:block">{descripcion}</p>
              </div>
              <div className="mt-auto flex items-center gap-1 pt-1 text-[11px] font-semibold text-slate-600">
                <span>Abrir</span>
                <svg className="h-3 w-3 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </div>
  )
}
