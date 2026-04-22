import type { FC } from 'react'
import { EyeIcon, PencilSquareIcon, PlusIcon, TrashIcon, UsersIcon } from '@heroicons/react/24/outline'
import type { StorybookSlug } from '../../navigation/config'
import { DevGuidanceInline } from '../../components/DevGuidanceInline'
import { DevVariantHint } from '../../components/DevVariantHint'
import { StorybookShell } from '../../components/StorybookShell'
import { HINT_GLASS_NEUTRAL, HINT_GLASS_PRIMARY, HINT_GLASS_SECONDARY } from '../../guidance/glassSurfaceHints'
import {
  CamposTextoPage,
  CheckboxesPage,
  DatePickersPage,
  DegradadosPage,
  EnfasisPage,
  GridsPage,
  IconosPage,
  MarcaPage,
  ModalesPageFull,
  NotificacionesPage,
  SelectsPage,
  SeccionesPage,
  TablasEstiloNotionPage,
  TablasPageFull,
} from './StorybookExtended'

function Panel({
  title,
  subtitle,
  developerSlot,
  children,
}: {
  title: string
  subtitle?: string
  developerSlot?: React.ReactNode
  children: React.ReactNode
}) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
      <h3 className="text-lg font-semibold tracking-tight text-slate-900">{title}</h3>
      {subtitle ? (
        <p className="mt-1.5 text-sm leading-relaxed text-slate-500">{subtitle}</p>
      ) : null}
      {developerSlot ? <div className="mt-4">{developerSlot}</div> : null}
      <div className="mt-6">{children}</div>
    </div>
  )
}

const pacoColors = [
  { label: 'Azul marca (trazo)', token: '--primary / paco-blue', hex: '#3148c8' },
  { label: 'Azul suave (relleno)', token: 'paco-blue-soft', hex: '#cad6fb' },
  { label: 'Azul profundo', token: 'paco-blue-deep', hex: '#2436a3' },
  { label: 'Coral / acento', token: 'paco-accent', hex: '#fb4f33' },
]

const grayColors = [
  { label: 'Gris Niebla', token: 'gray-niebla', hex: '#eef1fc' },
  { label: 'Gris Humo', token: 'gray-humo', hex: '#dde3f0' },
  { label: 'Gris Pizarra', token: 'gray-pizarra', hex: '#5c6488' },
  { label: 'Carbón', token: 'gray-carbon', hex: '#1a1f2e' },
]

const emphasisColors = [
  { label: 'Rojo', token: 'emphasis-red', hex: '#E53935' },
  { label: 'Amarillo', token: 'emphasis-yellow', hex: '#F9A825' },
  { label: 'Verde', token: 'emphasis-green', hex: '#2E7D32' },
  { label: 'Violeta', token: 'emphasis-violet', hex: '#6A1B9A' },
  { label: 'Mora', token: 'emphasis-mora', hex: '#4A148C' },
]

function SwatchGrid({
  colors,
  cols = 'sm:grid-cols-4',
}: {
  colors: { label: string; token: string; hex: string }[]
  cols?: string
}) {
  return (
    <div className={`grid grid-cols-2 gap-4 ${cols}`}>
      {colors.map((c) => (
        <div key={c.hex + c.label}>
          <div
            className="sb-swatch h-20 w-full rounded-xl border border-slate-200 shadow-sm"
            style={{ backgroundColor: c.hex }}
          />
          <p className="mt-2 text-sm font-semibold text-slate-800">{c.label}</p>
          <p className="font-mono text-xs text-slate-500">{c.hex}</p>
          <p className="font-mono text-xs italic text-slate-400">{c.token}</p>
        </div>
      ))}
    </div>
  )
}

function Colores() {
  return (
    <StorybookShell>
      <div className="space-y-10 sm:space-y-12">
        <Panel title="Colores principales" subtitle="Azules de marca y acento coral">
          <SwatchGrid colors={pacoColors} />
        </Panel>
        <Panel
          title="Escala de grises"
          subtitle="Grises del design system: niebla, humo, pizarra, carbón"
        >
          <SwatchGrid colors={grayColors} />
        </Panel>
        <Panel title="Colores de énfasis" subtitle="Alertas, estados y acciones especiales">
          <SwatchGrid colors={emphasisColors} cols="sm:grid-cols-5" />
        </Panel>
        <Panel
          title="Sidebar"
          subtitle="Variables CSS alineadas con filament-sidebar-overrides"
        >
          <SwatchGrid
            colors={[
              { label: 'Sidebar fondo', token: '--sidebar-bg', hex: '#eef1f8' },
              { label: 'Sidebar borde', token: '--sidebar-border', hex: '#d8dde8' },
              { label: 'Sidebar hover', token: '--sidebar-hover-bg', hex: '#e0e6f4' },
              {
                label: 'Sidebar activo',
                token: '--sidebar-active-bg',
                hex: 'rgba(49,72,200,0.10)',
              },
              { label: 'Texto muted', token: '--sidebar-text-muted', hex: '#94a3b8' },
              { label: 'Texto base', token: '--sidebar-text', hex: '#64748b' },
              { label: 'Texto hover', token: '--sidebar-text-hover', hex: '#1e293b' },
              { label: 'Texto activo', token: '--sidebar-text-active', hex: '#3148c8' },
            ]}
          />
        </Panel>
        <Panel title="Contenido y tarjetas" subtitle="Fondos y bordes del área principal">
          <SwatchGrid
            colors={[
              { label: 'Fondo contenido', token: '--content-bg', hex: '#f7f9fc' },
              { label: 'Card fondo', token: '--card-bg', hex: '#ffffff' },
              { label: 'Card borde', token: '--card-border', hex: '#e4e8f0' },
              { label: 'Topbar fondo', token: '--topbar-bg', hex: '#ffffff' },
            ]}
          />
        </Panel>
      </div>
    </StorybookShell>
  )
}

function Tipografia() {
  const rows = [
    ['text-xs', '0.75rem', 'Etiqueta auxiliar'],
    ['text-sm', '0.875rem', 'Texto secundario y descripciones'],
    ['text-base', '1rem', 'Texto base del cuerpo'],
    ['text-lg', '1.125rem', 'Heading de sección'],
    ['text-xl', '1.25rem', 'Heading principal'],
  ]
  return (
    <StorybookShell>
      <div className="space-y-10 sm:space-y-12">
        <Panel title="Fuente del sistema" subtitle="Instrument Sans — vía Google Fonts">
          <div className="space-y-6">
            <div className="rounded-xl border border-slate-100 bg-slate-50 p-5">
              <p className="text-xs font-medium uppercase tracking-wider text-slate-400">
                Extra Bold — 3xl
              </p>
              <p className="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">
                Buenos días
              </p>
            </div>
            <div className="rounded-xl border border-slate-100 bg-slate-50 p-5">
              <p className="text-xs font-medium uppercase tracking-wider text-slate-400">
                Semibold — base
              </p>
              <p className="mt-2 text-base font-semibold text-slate-800">Accesos directos</p>
            </div>
            <div className="rounded-xl border border-slate-100 bg-slate-50 p-5">
              <p className="text-xs font-medium uppercase tracking-wider text-slate-400">
                Regular — sm
              </p>
              <p className="mt-2 text-sm text-slate-500">
                Aquí tienes el resumen de tu empresa al 21 de abril, 2026
              </p>
            </div>
          </div>
        </Panel>
        <Panel title="Escala de tamaños" subtitle="Tailwind text-* con Instrument Sans">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead>
                <tr className="border-b border-slate-100">
                  <th className="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Clase
                  </th>
                  <th className="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Tamaño
                  </th>
                  <th className="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Ejemplo
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50">
                {rows.map(([cls, size, ex]) => (
                  <tr key={cls}>
                    <td className="py-3 pr-6 font-mono text-xs text-slate-600">{cls}</td>
                    <td className="py-3 pr-6 text-slate-500">{size}</td>
                    <td className={`py-3 ${cls}`}>{ex}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Panel>
      </div>
    </StorybookShell>
  )
}

function Botones() {
  return (
    <StorybookShell>
      <div className="space-y-10 sm:space-y-12">
        <Panel title="Botones primarios" subtitle="Color primario #3148c8">
          <div className="flex flex-wrap items-center gap-4">
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-[#2a3db0] hover:shadow-md"
            >
              Guardar cambios
            </button>
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm"
            >
              <PlusIcon className="h-4 w-4" />
              Nuevo registro
            </button>
            <button
              type="button"
              disabled
              className="inline-flex cursor-not-allowed items-center gap-2 rounded-lg bg-[#3148c8]/60 px-4 py-2.5 text-sm font-semibold text-white/90 shadow-sm"
            >
              Procesando…
            </button>
          </div>
        </Panel>
        <Panel title="Secundarios y neutros" subtitle="Cancelar, borde y texto">
          <div className="flex flex-wrap items-center gap-4">
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
            >
              Cancelar
            </button>
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-lg border border-[#3148c8]/35 bg-white px-4 py-2.5 text-sm font-semibold text-[#3148c8] hover:bg-indigo-50/80"
            >
              Volver al listado
            </button>
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100"
            >
              Omitir
            </button>
          </div>
        </Panel>
        <Panel title="Botones de peligro" subtitle="Acciones destructivas">
          <div className="flex flex-wrap gap-4">
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700"
            >
              <TrashIcon className="h-4 w-4" />
              Eliminar
            </button>
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50"
            >
              <TrashIcon className="h-4 w-4" />
              Eliminar (outline)
            </button>
          </div>
        </Panel>
        <Panel title="Iconos en tabla" subtitle="Patrón de acciones">
          <div className="flex flex-wrap gap-3">
            <button
              type="button"
              className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200"
            >
              <PencilSquareIcon className="h-5 w-5" />
            </button>
            <button
              type="button"
              className="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 hover:bg-indigo-200"
            >
              <EyeIcon className="h-5 w-5" />
            </button>
            <button
              type="button"
              className="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600 hover:bg-red-200"
            >
              <TrashIcon className="h-5 w-5" />
            </button>
          </div>
        </Panel>
      </div>
    </StorybookShell>
  )
}

function Badges() {
  return (
    <StorybookShell>
      <div className="space-y-10 sm:space-y-12">
        <Panel title="Badges de estado" subtitle="Semántica de color">
          <div className="flex flex-wrap gap-2">
            <span className="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/80">
              Activo
            </span>
            <span className="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-900 ring-1 ring-amber-200/80">
              Pendiente
            </span>
            <span className="inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800 ring-1 ring-rose-200/80">
              Rechazado
            </span>
            <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/80">
              Borrador
            </span>
          </div>
        </Panel>
        <Panel title="Badges informativos" subtitle="Primary, éxito y peligro compactos">
          <div className="flex flex-wrap gap-2">
            <span className="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-[#3148c8] ring-1 ring-indigo-200/80">
              Nuevo
            </span>
            <span className="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-800 ring-1 ring-sky-200/80">
              Info
            </span>
            <span className="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800 ring-1 ring-red-200/80">
              Crítico
            </span>
            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 ring-1 ring-emerald-200/80">
              <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
              En línea
            </span>
          </div>
        </Panel>
      </div>
    </StorybookShell>
  )
}

function Tarjetas() {
  return (
    <StorybookShell>
      <div className="space-y-10 sm:space-y-12">
        <Panel
          title="Hero cards (dashboard)"
          subtitle="dash-glass-hero con acento de marca"
          developerSlot={
            <DevGuidanceInline
              content={{
                title: 'Mismo patrón que en Degradados',
                summary:
                  'Las tarjetas del dashboard reutilizan el vidrio con borde de acento. La decisión de color es de jerarquía, no de “gusto”.',
                bulletsCuandoUsar: [
                  'Primario: KPI que quieres que el ejecutivo vea primero.',
                  'Neutro: estados “cero” o secundarios que no deben gritar.',
                  'Secundario: métrica de producto/adopción entre las dos anteriores.',
                ],
                bulletsEvitar: [
                  'Copiar números de demo a producción sin revisar contraste en tema oscuro (si aplica).',
                ],
                equivalenteFilament: ['Widgets Stats o Blade con mismas utilidades Tailwind.'],
                referenciaReglasCursor: 'Ver también Storybook «Degradados» por variante.',
              }}
            />
          }
        >
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div className="flex flex-col gap-3">
              <div className="dash-hero-card dash-glass-hero group relative overflow-hidden rounded-2xl border-l-[3px] border-l-[#3148c8] p-5 text-slate-800 shadow-sm transition-all hover:-translate-y-0.5 sm:p-6">
                <div className="flex items-center justify-between">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-100/80 bg-indigo-50/90 text-[#3148c8] shadow-sm">
                    <UsersIcon className="h-5 w-5" aria-hidden />
                  </div>
                  <span className="inline-flex items-center gap-1 rounded-full border border-emerald-200/80 bg-emerald-50/90 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                    +12%
                  </span>
                </div>
                <div className="mt-4">
                  <p className="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    30,524
                  </p>
                  <p className="mt-1 text-sm font-medium text-slate-600">Total Colaboradores</p>
                </div>
              </div>
              <DevVariantHint content={HINT_GLASS_PRIMARY} />
            </div>
            <div className="flex flex-col gap-3">
              <div className="dash-hero-card dash-glass-hero rounded-2xl border-l-[3px] border-l-slate-600 bg-white/80 p-5 shadow-sm sm:p-6">
                <p className="text-3xl font-extrabold text-slate-900">0</p>
                <p className="mt-1 text-sm text-slate-600">Sin atender</p>
              </div>
              <DevVariantHint content={HINT_GLASS_NEUTRAL} />
            </div>
            <div className="flex flex-col gap-3">
              <div className="dash-hero-card dash-glass-hero rounded-2xl border-l-[3px] border-l-indigo-500 bg-white/80 p-5 shadow-sm sm:p-6">
                <p className="text-3xl font-extrabold text-slate-900">23,292</p>
                <p className="mt-1 text-sm text-slate-600">Descargas App</p>
              </div>
              <DevVariantHint content={HINT_GLASS_SECONDARY} />
            </div>
          </div>
        </Panel>
      </div>
    </StorybookShell>
  )
}

const STORYBOOK_REGISTRY: Record<StorybookSlug, FC> = {
  colores: Colores,
  tipografia: Tipografia,
  enfasis: EnfasisPage,
  degradados: DegradadosPage,
  marca: MarcaPage,
  secciones: SeccionesPage,
  grids: GridsPage,
  botones: Botones,
  badges: Badges,
  tarjetas: Tarjetas,
  tablas: TablasPageFull,
  'tablas-estilo-notion': TablasEstiloNotionPage,
  'campos-texto': CamposTextoPage,
  iconos: IconosPage,
  selects: SelectsPage,
  checkboxes: CheckboxesPage,
  'date-pickers': DatePickersPage,
  notificaciones: NotificacionesPage,
  modales: ModalesPageFull,
}

export function StorybookView({ slug }: { slug: StorybookSlug }) {
  const Cmp = STORYBOOK_REGISTRY[slug]
  return <Cmp />
}

