import { SparklesIcon } from '@heroicons/react/24/outline'

function getSaludo(): string {
  const h = new Date().getHours()
  if (h < 12) {
    return 'Buenos días'
  }
  if (h < 19) {
    return 'Buenas tardes'
  }
  return 'Buenas noches'
}

const chips: { label: string; tone: 'success' | 'warning' | 'danger' | 'info' }[] = [
  { label: '5 solicitudes por aprobar', tone: 'warning' },
  { label: '2 cartas SUA pendientes', tone: 'danger' },
  { label: '98% registro al día', tone: 'success' },
  { label: 'Nueva encuesta activa', tone: 'info' },
]

function chipClass(tone: (typeof chips)[0]['tone']) {
  switch (tone) {
    case 'success':
      return 'bg-emerald-50 text-emerald-800 ring-emerald-200/80'
    case 'warning':
      return 'bg-amber-50 text-amber-900 ring-amber-200/80'
    case 'danger':
      return 'bg-rose-50 text-rose-800 ring-rose-200/80'
    case 'info':
      return 'bg-white/70 text-slate-700 ring-slate-200/80'
    default:
      return 'bg-white/70 text-slate-700 ring-slate-200/80'
  }
}

export function BienvenidaBanner() {
  const fecha = new Intl.DateTimeFormat('es-MX', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  }).format(new Date())

  return (
    <div className="dash-showroom dash-banner dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8 lg:p-10">
      <div className="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-indigo-400/[0.06] blur-3xl" />
      <div className="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-slate-300/[0.12] blur-3xl" />

      <div className="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
        <div>
          <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
            <span className="inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/15" />
            Panel ejecutivo · {fecha}
          </div>

          <h1 className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
            {getSaludo()}, <span className="font-semibold text-slate-600">Usuario</span>
          </h1>

          <p className="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">
            Aquí tienes el resumen de <span className="font-semibold text-slate-800">Acme SA</span>. Este
            dashboard muestra lo más representativo de tu operación y del sistema de diseño del producto.
          </p>

          <div className="mt-5 flex flex-wrap gap-2">
            {chips.map((chip) => (
              <span
                key={chip.label}
                className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 backdrop-blur-sm ${chipClass(chip.tone)}`}
              >
                <span className="inline-flex h-1.5 w-1.5 rounded-full bg-current opacity-80" />
                {chip.label}
              </span>
            ))}
          </div>
        </div>

        <div className="flex flex-col items-start gap-3 rounded-2xl border border-white/60 bg-white/45 p-4 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl lg:items-end lg:p-5">
          <div className="flex items-center gap-3">
            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200/60 bg-white/60 text-[#3148c8] shadow-sm">
              <SparklesIcon className="h-6 w-6" />
            </div>
            <div>
              <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Índice de salud</p>
              <p className="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
                94<span className="text-sm font-semibold text-slate-500"> / 100</span>
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2 text-xs text-slate-600">
            <svg className="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            <span>+3 pts vs mes anterior</span>
          </div>
        </div>
      </div>
    </div>
  )
}
