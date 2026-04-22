import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  LightBulbIcon,
  MinusCircleIcon,
  ShieldExclamationIcon,
  StarIcon,
  XCircleIcon,
} from '@heroicons/react/24/outline'
import type { ComponentType, FC, ReactNode, SVGProps } from 'react'
import { StorybookShell } from '../../components/StorybookShell'

type HeroIcon = ComponentType<SVGProps<SVGSVGElement>>

function SbPanel({
  title,
  subtitle,
  children,
}: {
  title: string
  subtitle?: string
  children: ReactNode
}) {
  return (
    <div className="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
      <h3 className="text-lg font-semibold tracking-tight text-slate-900">{title}</h3>
      {subtitle ? (
        <p className="mt-1.5 text-sm leading-relaxed text-slate-500">{subtitle}</p>
      ) : null}
      <div className="mt-6">{children}</div>
    </div>
  )
}

export const EnfasisPage: FC = () => (
  <StorybookShell>
    <div className="space-y-10 sm:space-y-12">
      <SbPanel title="Colores semánticos de estado" subtitle="Badges, iconos y textos">
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {[
            ['Success', 'bg-green-100', 'text-green-800', 'border-green-200', 'Activo, completado'],
            ['Warning', 'bg-amber-100', 'text-amber-800', 'border-amber-200', 'Pendiente, en revisión'],
            ['Danger', 'bg-red-100', 'text-red-800', 'border-red-200', 'Inactivo, rechazado'],
            ['Info', 'bg-sky-100', 'text-sky-800', 'border-sky-200', 'Información general'],
            ['Primary', 'bg-indigo-100', 'text-indigo-800', 'border-indigo-200', 'Acción principal'],
            ['Gray', 'bg-slate-100', 'text-slate-700', 'border-slate-200', 'Neutral, borrador'],
          ].map(([label, bg, text, border, desc]) => (
            <div key={label as string} className={`rounded-xl border ${border} p-4`}>
              <span
                className={`${bg} ${text} inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold`}
              >
                {label}
              </span>
              <p className="mt-2 text-sm text-slate-500">{desc}</p>
            </div>
          ))}
        </div>
      </SbPanel>
      <SbPanel title="Chips de énfasis" subtitle="Equivalentes web de la app móvil">
        <div className="mt-4 flex flex-wrap gap-3">
          {[
            ['Error / alerta', '#E53935'],
            ['Advertencia', '#F9A825'],
            ['Éxito', '#2E7D32'],
            ['Info violeta', '#6A1B9A'],
            ['Info mora', '#4A148C'],
          ].map(([lab, color]) => (
            <span
              key={lab as string}
              className="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-white"
              style={{ backgroundColor: color as string }}
            >
              {lab}
            </span>
          ))}
        </div>
      </SbPanel>
      <SbPanel title="Iconos con estado" subtitle="Heroicons coloreados">
        <div className="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
          {(
            [
              [CheckCircleIcon, 'bg-green-100', 'text-green-600', 'Success'],
              [ExclamationTriangleIcon, 'bg-amber-100', 'text-amber-600', 'Warning'],
              [XCircleIcon, 'bg-red-100', 'text-red-600', 'Danger'],
              [InformationCircleIcon, 'bg-sky-100', 'text-sky-600', 'Info'],
              [StarIcon, 'bg-indigo-100', 'text-indigo-600', 'Primary'],
              [MinusCircleIcon, 'bg-slate-100', 'text-slate-500', 'Gray'],
            ] satisfies [HeroIcon, string, string, string][]
          ).map(([Icon, bg, col, lab]) => (
            <div
              key={lab}
              className="flex flex-col items-center gap-2 rounded-xl border border-slate-100 p-4"
            >
              <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${bg}`}>
                <Icon className={`h-5 w-5 ${col}`} />
              </div>
              <span className="text-xs font-medium text-slate-600">{lab}</span>
            </div>
          ))}
        </div>
      </SbPanel>
    </div>
  </StorybookShell>
)

export const DegradadosPage: FC = () => (
  <StorybookShell>
    <div className="space-y-10 sm:space-y-12">
      <SbPanel
        title="Superficies tipo vidrio"
        subtitle="Clase dash-glass-hero — fondo translúcido y borde suave"
      >
        <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div className="dash-glass-hero rounded-2xl border-l-[3px] border-l-[#3148c8] p-5 sm:p-6">
            <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Primario</p>
            <p className="mt-2 text-2xl font-extrabold text-slate-900">#3148c8</p>
          </div>
          <div className="dash-glass-hero rounded-2xl border-l-[3px] border-l-slate-600 p-5 sm:p-6">
            <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Neutro</p>
            <p className="mt-2 text-2xl font-extrabold text-slate-900">Slate</p>
          </div>
          <div className="dash-glass-hero rounded-2xl border-l-[3px] border-l-indigo-500 p-5 sm:p-6">
            <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Secundario</p>
            <p className="mt-2 text-2xl font-extrabold text-slate-900">Indigo</p>
          </div>
        </div>
      </SbPanel>
      <SbPanel title="Barras de progreso" subtitle="Color sólido de marca">
        <div className="mt-6 space-y-4">
          {[
            ['78% — Tasa de registro', 78],
            ['45% — Ejemplo medio', 45],
            ['100% — Completo', 100],
          ].map(([label, w]) => (
            <div key={label as string}>
              <p className="mb-2 text-sm font-medium text-slate-600">{label}</p>
              <div className="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                  className="dash-progress h-full rounded-full bg-[#3148c8]"
                  style={{ width: `${w}%` }}
                />
              </div>
            </div>
          ))}
        </div>
      </SbPanel>
      <SbPanel title="Profundidad sin degradado" subtitle="Halos suaves + vidrio">
        <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div className="relative flex h-40 items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-50/80 backdrop-blur-md">
            <div className="pointer-events-none absolute -right-6 -top-6 h-28 w-28 rounded-full bg-indigo-400/[0.08] blur-2xl" />
            <p className="relative text-sm font-medium text-slate-600">Halo indigo 8%</p>
          </div>
          <div className="relative flex h-40 items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 bg-white/70 backdrop-blur-xl">
            <div className="pointer-events-none absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-slate-400/[0.07] blur-2xl" />
            <p className="relative text-sm font-medium text-slate-600">Halo slate 7%</p>
          </div>
        </div>
      </SbPanel>
    </div>
  </StorybookShell>
)

export const MarcaPage: FC = () => (
  <StorybookShell>
    <div className="space-y-10 sm:space-y-12">
      <SbPanel title="Logo Paco" subtitle="Sidebar, login y documentos">
        <div className="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div className="flex flex-col items-center gap-4 rounded-2xl border border-slate-100 bg-white p-8">
            <img src="/img/logo_paco.png" alt="Logo Paco" className="h-12" />
            <p className="text-sm font-medium text-slate-700">Fondo claro</p>
          </div>
          <div className="dash-glass-hero flex flex-col items-center gap-4 rounded-2xl border-l-[3px] border-l-[#3148c8] p-8">
            <img src="/img/logo_paco.png" alt="Logo Paco" className="h-12" />
            <p className="text-sm font-medium text-slate-700">Fondo vidrio</p>
          </div>
        </div>
      </SbPanel>
      <SbPanel title="Favicon" subtitle="Ícono del navegador">
        <div className="mt-6 flex items-center gap-6">
          {[32, 48, 64].map((s) => (
            <div key={s} className="flex flex-col items-center gap-2">
              <img src="/img/favicon_paco.png" alt="" width={s} height={s} />
              <span className="text-xs text-slate-400">{s}px</span>
            </div>
          ))}
        </div>
      </SbPanel>
    </div>
  </StorybookShell>
)

export const GridsPage: FC = () => (
  <StorybookShell>
    <div className="space-y-10 sm:space-y-12">
      <SbPanel title="Grid de 2 columnas" subtitle="Formularios: grid-cols-1 sm:grid-cols-2">
        <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
          {[1, 2, 3, 4].map((i) => (
            <div
              key={i}
              className="flex h-20 items-center justify-center rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 text-sm font-medium text-indigo-400"
            >
              Campo {i}
            </div>
          ))}
        </div>
      </SbPanel>
      <SbPanel title="Grid de 7 columnas (accesos rápidos)" subtitle="xl:grid-cols-7">
        <div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7">
          {Array.from({ length: 7 }, (_, i) => (
            <div
              key={i}
              className="flex h-20 flex-col items-center justify-center gap-1 rounded-2xl border border-slate-200 bg-white text-center transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
            >
              <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100">
                <span className="text-xs font-bold text-indigo-600">{i + 1}</span>
              </div>
              <span className="text-xs font-medium text-slate-500">Link {i + 1}</span>
            </div>
          ))}
        </div>
      </SbPanel>
    </div>
  </StorybookShell>
)

export const SeccionesPage: FC = () => (
  <StorybookShell>
    <div className="space-y-10 sm:space-y-12">
      <SbPanel title="Sección básica" subtitle="Contenedor tipo fi-section">
        <div className="mt-6 overflow-hidden rounded-lg border border-[#e4e8f0] bg-white">
          <div className="border-b border-[#e4e8f0] px-5 py-3.5">
            <h4 className="text-[0.9375rem] font-bold tracking-[-0.01em] text-[#1e293b]">
              Datos personales
            </h4>
          </div>
          <div className="p-5">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <label className="mb-1.5 block text-sm font-medium text-slate-700">Nombre</label>
                <input
                  type="text"
                  defaultValue="María"
                  className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm"
                />
              </div>
              <div>
                <label className="mb-1.5 block text-sm font-medium text-slate-700">Apellido</label>
                <input
                  type="text"
                  defaultValue="García López"
                  className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm"
                />
              </div>
            </div>
          </div>
        </div>
      </SbPanel>
      <SbPanel title="Tabs secundarios" subtitle="Grupos independientes">
        <div className="mt-6">
          <div className="flex gap-1 border-b border-slate-200 px-1">
            {['General', 'Permisos', 'Historial'].map((t, i) => (
              <button
                key={t}
                type="button"
                className={
                  'rounded-t-md px-4 py-2.5 text-[0.8125rem] font-medium transition-colors ' +
                  (i === 0
                    ? 'border-b-2 border-[#3148c8] bg-white text-[#3148c8]'
                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700')
                }
              >
                {t}
              </button>
            ))}
          </div>
          <div className="rounded-b-lg border border-t-0 border-[#e4e8f0] bg-white p-5">
            <p className="text-sm text-slate-600">
              Contenido de la pestaña activa — mismo patrón que catálogos en el panel.
            </p>
          </div>
        </div>
      </SbPanel>
    </div>
  </StorybookShell>
)

export const CamposTextoPage: FC = () => (
  <StorybookShell>
    <SbPanel title="Campos de texto" subtitle="Input, textarea, máscaras">
      <div className="grid max-w-xl grid-cols-1 gap-4">
        <div>
          <label className="mb-1.5 block text-sm font-medium text-slate-700">Correo</label>
          <input
            type="email"
            placeholder="nombre@empresa.com"
            className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20"
          />
        </div>
        <div>
          <label className="mb-1.5 block text-sm font-medium text-slate-700">Notas</label>
          <textarea
            rows={3}
            className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20"
            placeholder="Texto multilínea…"
          />
        </div>
      </div>
    </SbPanel>
  </StorybookShell>
)

export const IconosPage: FC = () => (
  <StorybookShell>
    <SbPanel title="Biblioteca de iconos" subtitle="Prefijo heroicon-o- en Filament">
      <p className="text-sm text-slate-600">
        En el prototipo usamos <code className="rounded bg-slate-100 px-1">@heroicons/react/24/outline</code> como
        equivalente directo.
      </p>
      <div className="mt-6 flex flex-wrap gap-3">
        {[CheckCircleIcon, StarIcon, InformationCircleIcon].map((Ic, i) => (
          <span
            key={i}
            className="flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-white shadow-sm"
          >
            <Ic className="h-6 w-6 text-[#3148c8]" />
          </span>
        ))}
      </div>
    </SbPanel>
  </StorybookShell>
)

export const SelectsPage: FC = () => (
  <StorybookShell>
    <SbPanel title="Select y combobox" subtitle="Searchable en listas largas">
      <label className="mb-1.5 block text-sm font-medium text-slate-700">Departamento</label>
      <select className="w-full max-w-md rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20">
        <option>Operaciones</option>
        <option>Recursos Humanos</option>
        <option>Tecnología</option>
      </select>
    </SbPanel>
  </StorybookShell>
)

export const CheckboxesPage: FC = () => (
  <StorybookShell>
    <SbPanel title="Checkboxes y switches" subtitle="Booleanos nativos">
      <div className="space-y-3">
        <label className="flex items-center gap-2 text-sm text-slate-700">
          <input type="checkbox" className="rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]" defaultChecked />
          Recibir notificaciones por correo
        </label>
        <label className="flex items-center gap-2 text-sm text-slate-700">
          <input type="checkbox" className="rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]" />
          Acceso a app móvil
        </label>
      </div>
    </SbPanel>
  </StorybookShell>
)

export const DatePickersPage: FC = () => (
  <StorybookShell>
    <SbPanel title="Date pickers" subtitle="native(false) en Filament para consistencia">
      <label className="mb-1.5 block text-sm font-medium text-slate-700">Fecha de ingreso</label>
      <input
        type="date"
        className="rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20"
      />
    </SbPanel>
  </StorybookShell>
)

export const NotificacionesPage: FC = () => (
  <StorybookShell>
    <div className="space-y-10 sm:space-y-12">
      <SbPanel title="Notificaciones toast" subtitle="Cuatro niveles semánticos">
        <div className="mt-6 space-y-4">
          {(
            [
              [
                'bg-green-50',
                'border-green-200',
                'text-green-800',
                'text-green-600',
                CheckCircleIcon,
                'Cambios guardados',
                'El colaborador fue actualizado.',
              ],
              [
                'bg-amber-50',
                'border-amber-200',
                'text-amber-800',
                'text-amber-600',
                ExclamationTriangleIcon,
                'Acción requerida',
                'Faltan 2 aprobaciones.',
              ],
              [
                'bg-red-50',
                'border-red-200',
                'text-red-800',
                'text-red-600',
                XCircleIcon,
                'No se pudo enviar',
                'Revisa tu conexión.',
              ],
              [
                'bg-sky-50',
                'border-sky-200',
                'text-sky-800',
                'text-sky-600',
                InformationCircleIcon,
                'Nueva encuesta',
                'Plazo hasta el viernes.',
              ],
            ] satisfies [string, string, string, string, HeroIcon, string, string][]
          ).map(([bg, border, tc, ic, Icon, title, body], i) => (
            <div
              key={i}
              className={`flex items-start gap-3 rounded-xl border ${border} ${bg} p-4`}
            >
              <Icon className={`h-5 w-5 shrink-0 ${ic}`} />
              <div>
                <p className={`text-sm font-semibold ${tc}`}>{title}</p>
                <p className="mt-0.5 text-sm text-slate-600">{body}</p>
              </div>
            </div>
          ))}
        </div>
      </SbPanel>
      <SbPanel title="Alertas inline" subtitle="Dentro de formularios">
        <div className="mt-6 space-y-4">
          <div className="flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-3">
            <LightBulbIcon className="h-5 w-5 shrink-0 text-[#3148c8]" />
            <p className="text-sm text-[#3148c8]">
              <span className="font-semibold">Tip:</span> importación masiva desde Excel.
            </p>
          </div>
          <div className="flex items-center gap-2 rounded-lg bg-amber-50 px-4 py-3">
            <ExclamationTriangleIcon className="h-5 w-5 shrink-0 text-amber-600" />
            <p className="text-sm text-amber-800">
              <span className="font-semibold">Nota:</span> los cambios en catálogos afectan a todos.
            </p>
          </div>
          <div className="flex items-center gap-2 rounded-lg bg-red-50 px-4 py-3">
            <ShieldExclamationIcon className="h-5 w-5 shrink-0 text-red-600" />
            <p className="text-sm text-red-800">
              <span className="font-semibold">Importante:</span> acción irreversible.
            </p>
          </div>
        </div>
      </SbPanel>
    </div>
  </StorybookShell>
)

export const ModalesPageFull: FC = () => (
  <StorybookShell>
    <div className="space-y-10 sm:space-y-12">
      <SbPanel title="Modal de confirmación" subtitle="requiresConfirmation() en acciones destructivas">
        <div className="mt-6 flex justify-center">
          <div className="w-full max-w-md overflow-hidden rounded-xl border border-[#e4e8f0] bg-white shadow-xl">
            <div className="p-6 text-center">
              <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <ExclamationTriangleIcon className="h-6 w-6 text-red-600" />
              </div>
              <h4 className="mt-4 text-lg font-semibold text-slate-900">
                ¿Dar de baja a este colaborador?
              </h4>
              <p className="mt-2 text-sm text-slate-500">
                Esta acción registrará la baja. El registro se conservará en el historial.
              </p>
            </div>
            <div className="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
              <button
                type="button"
                className="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              >
                Cancelar
              </button>
              <button
                type="button"
                className="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700"
              >
                Confirmar baja
              </button>
            </div>
          </div>
        </div>
      </SbPanel>
      <SbPanel title="Slide-over" subtitle="CatalogSlideOver — edición rápida">
        <div className="mt-6 flex justify-end">
          <div className="w-full max-w-sm overflow-hidden rounded-l-xl border border-[#e4e8f0] bg-white shadow-xl">
            <div className="border-b border-slate-100 px-5 py-4">
              <h4 className="text-base font-semibold text-slate-900">Editar puesto</h4>
            </div>
            <div className="space-y-4 p-5">
              <div>
                <label className="mb-1.5 block text-sm font-medium text-slate-700">Nombre</label>
                <input
                  type="text"
                  className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                  defaultValue="Analista"
                />
              </div>
            </div>
            <div className="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
              <button type="button" className="rounded-lg border border-slate-200 px-4 py-2 text-sm">
                Cancelar
              </button>
              <button
                type="button"
                className="rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white"
              >
                Guardar
              </button>
            </div>
          </div>
        </div>
      </SbPanel>
    </div>
  </StorybookShell>
)

export const TablasPageFull: FC = () => (
  <StorybookShell>
    <SbPanel title="Tabla de datos" subtitle="Listados integrados al layout">
      <div className="overflow-x-auto rounded-xl border border-slate-200">
        <table className="sb-demo-table min-w-full text-sm">
          <thead className="bg-slate-50">
            <tr>
              <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Nombre</th>
              <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Email</th>
              <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Estado</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {[
              ['Ana López', 'ana@empresa.com', 'Activo'],
              ['Luis Pérez', 'luis@empresa.com', 'Activo'],
            ].map(([n, e, s]) => (
              <tr key={n as string}>
                <td className="px-4 py-3 font-medium">{n}</td>
                <td className="px-4 py-3 text-slate-600">{e}</td>
                <td className="px-4 py-3">
                  <span className="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/80">
                    {s}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </SbPanel>
  </StorybookShell>
)

export { TablasEstiloNotionPage } from './TablasEstiloNotionStory'
