import {
  BoltIcon,
  CalendarIcon,
  ChartBarIcon,
  ChartPieIcon,
  FaceSmileIcon,
  FlagIcon,
  SquaresPlusIcon,
  TrophyIcon,
} from '@heroicons/react/24/outline'

const kpis = [
  {
    label: 'Headcount activo',
    valor: '30,524',
    delta: '+2.4%',
    tono: 'indigo' as const,
    path: 'M0,22 L15,18 L30,20 L45,14 L60,16 L75,10 L90,12 L105,6 L120,4',
  },
  {
    label: 'Rotación anual',
    valor: '2.6%',
    delta: '-0.3 pts',
    tono: 'emerald' as const,
    path: 'M0,10 L15,14 L30,12 L45,18 L60,16 L75,20 L90,18 L105,22 L120,24',
  },
  {
    label: 'eNPS',
    valor: '72',
    delta: '+6',
    tono: 'violet' as const,
    path: 'M0,20 L15,18 L30,15 L45,16 L60,12 L75,10 L90,8 L105,10 L120,6',
  },
  {
    label: 'Adopción app',
    valor: '81%',
    delta: '+12%',
    tono: 'amber' as const,
    path: 'M0,24 L15,20 L30,22 L45,18 L60,14 L75,16 L90,10 L105,8 L120,6',
  },
]

const tonoUi: Record<
  string,
  { accent: string; icon: string; stroke: string; fillTop: string }
> = {
  indigo: {
    accent: 'border-t-[3px] border-t-[#3148c8]',
    icon: 'border-indigo-100/80 bg-indigo-50/95 text-[#3148c8]',
    stroke: '#3148c8',
    fillTop: '49,72,200',
  },
  emerald: {
    accent: 'border-t-[3px] border-t-emerald-500',
    icon: 'border-emerald-100/80 bg-emerald-50/95 text-emerald-700',
    stroke: '#059669',
    fillTop: '5,150,105',
  },
  violet: {
    accent: 'border-t-[3px] border-t-violet-500',
    icon: 'border-violet-100/80 bg-violet-50/95 text-violet-700',
    stroke: '#7c3aed',
    fillTop: '124,58,237',
  },
  amber: {
    accent: 'border-t-[3px] border-t-amber-500',
    icon: 'border-amber-100/80 bg-amber-50/95 text-amber-800',
    stroke: '#d97706',
    fillTop: '217,119,6',
  },
}

export function ResumenSection() {
  return (
    <div className="an-section space-y-6 sm:space-y-8">
      <div className="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
        <div>
          <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">
            Resumen ejecutivo
          </p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            Vista de 30 segundos
          </h2>
        </div>
        <div className="flex items-center gap-2 text-xs">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
            <CalendarIcon className="h-3.5 w-3.5" />
            Últimos 30 días
          </span>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-200">
            <span className="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500" />
            Datos demo
          </span>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {kpis.map((kpi, i) => {
          const ui = tonoUi[kpi.tono]
          const isNeg = kpi.delta.startsWith('-')
          return (
            <div
              key={kpi.label}
              className={
                'an-kpi group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/72 p-5 text-slate-800 shadow-md ring-1 ring-white/40 backdrop-blur-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg sm:p-6 ' +
                ui.accent
              }
            >
              <div className="relative">
                <div className="flex items-center justify-between">
                  <div
                    className={
                      'flex h-10 w-10 items-center justify-center rounded-xl border shadow-sm ' + ui.icon
                    }
                  >
                    <span className="text-lg">●</span>
                  </div>
                  <span
                    className={
                      'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 backdrop-blur-sm ' +
                      (isNeg
                        ? 'bg-slate-800/90 text-white ring-slate-700/50'
                        : 'bg-white/70 text-slate-700 ring-slate-200/80')
                    }
                  >
                    <span className={isNeg ? 'rotate-180' : ''}>↑</span>
                    {kpi.delta}
                  </span>
                </div>
                <p className="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                  {kpi.valor}
                </p>
                <p className="mt-1 text-sm font-medium text-slate-600">{kpi.label}</p>
                <svg viewBox="0 0 120 28" className="an-sparkline mt-3 h-8 w-full" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id={`kpiSpark-${i}`} x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0%" stopColor={`rgb(${ui.fillTop})`} stopOpacity="0.22" />
                      <stop offset="100%" stopColor={`rgb(${ui.fillTop})`} stopOpacity="0" />
                    </linearGradient>
                  </defs>
                  <path d={`${kpi.path} L120,28 L0,28 Z`} fill={`url(#kpiSpark-${i})`} />
                  <path
                    d={kpi.path}
                    fill="none"
                    stroke={ui.stroke}
                    strokeOpacity="0.55"
                    strokeWidth="1.8"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                  />
                </svg>
              </div>
            </div>
          )
        })}
      </div>

      <div className="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
          <div className="flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">Objetivos del trimestre</h3>
              <p className="mt-0.5 text-xs text-slate-400">3 OKRs · cierre 30 jun</p>
            </div>
            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
              On track
            </span>
          </div>
          <div className="mt-5 grid grid-cols-3 gap-3">
            {[
              ['OKR 1', 82, '#3148c8'],
              ['OKR 2', 64, '#10b981'],
              ['OKR 3', 45, '#f59e0b'],
            ].map(([label, val, color]) => {
              const radius = 32
              const circ = 2 * Math.PI * radius
              const offset = circ - ((val as number) / 100) * circ
              return (
                <div key={label as string} className="flex flex-col items-center">
                  <div className="relative h-20 w-20">
                    <svg viewBox="0 0 80 80" className="h-20 w-20 -rotate-90">
                      <circle cx="40" cy="40" r={radius} stroke="#e2e8f0" strokeWidth="6" fill="none" />
                      <circle
                        cx="40"
                        cy="40"
                        r={radius}
                        stroke={color as string}
                        strokeWidth="6"
                        fill="none"
                        strokeDasharray={circ}
                        strokeDashoffset={offset}
                        strokeLinecap="round"
                        className="an-ring"
                      />
                    </svg>
                    <div className="absolute inset-0 flex items-center justify-center">
                      <span className="text-lg font-bold tabular-nums text-slate-800">{val}%</span>
                    </div>
                  </div>
                  <p className="mt-2 text-xs font-semibold text-slate-600">{label}</p>
                </div>
              )
            })}
          </div>
          <div className="mt-5 space-y-2 text-xs text-slate-500">
            <p className="flex items-center gap-2">
              <span className="inline-flex h-2 w-2 rounded-full bg-[#3148c8]" />
              Reducir rotación voluntaria a 2.5%
            </p>
            <p className="flex items-center gap-2">
              <span className="inline-flex h-2 w-2 rounded-full bg-emerald-500" />
              Onboarding digital del 100% de nuevos ingresos
            </p>
            <p className="flex items-center gap-2">
              <span className="inline-flex h-2 w-2 rounded-full bg-amber-500" />
              80% de firmas SUA electrónicas
            </p>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-2">
          <div className="flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">Pulso de la operación</h3>
              <p className="mt-0.5 text-xs text-slate-400">6 métricas · últimas 2 semanas</p>
            </div>
            <BoltIcon className="h-5 w-5 text-amber-500" />
          </div>
          <div className="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
            {[
              ['Solicitudes', '142', '+18', '#10b981', 'M0,20 L15,18 L30,14 L45,16 L60,10 L75,8 L90,6 L105,4 L120,2'],
              ['Bajas', '3', '-2', '#f43f5e', 'M0,6 L15,10 L30,8 L45,12 L60,14 L75,18 L90,16 L105,20 L120,22'],
              ['Cumpleaños', '89', '+4', '#f59e0b', 'M0,16 L15,14 L30,18 L45,16 L60,12 L75,14 L90,10 L105,12 L120,8'],
              ['Altas', '28', '+6', '#3148c8', 'M0,18 L15,16 L30,14 L45,10 L60,12 L75,8 L90,10 L105,6 L120,4'],
              ['Docs firmados', '214', '+32', '#0ea5e9', 'M0,22 L15,20 L30,18 L45,14 L60,10 L75,12 L90,6 L105,4 L120,2'],
              ['Alertas', '7', '+1', '#8b5cf6', 'M0,12 L15,14 L30,10 L45,14 L60,8 L75,12 L90,6 L105,10 L120,4'],
            ].map(([label, val, delta, stroke, path]) => {
              const neg = String(delta).startsWith('-')
              return (
                <div
                  key={label as string}
                  className="rounded-xl border border-slate-100 bg-slate-50/40 p-3"
                >
                  <div className="flex items-center justify-between text-[11px]">
                    <span className="font-medium text-slate-500">{label}</span>
                    <span
                      className={
                        'font-semibold tabular-nums ' + (neg ? 'text-rose-600' : 'text-emerald-600')
                      }
                    >
                      {delta}
                    </span>
                  </div>
                  <p className="mt-1 text-xl font-bold tabular-nums text-slate-900">{val}</p>
                  <svg viewBox="0 0 120 28" className="an-sparkline mt-1 h-6 w-full" preserveAspectRatio="none">
                    <path
                      d={path as string}
                      fill="none"
                      stroke={stroke as string}
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                </div>
              )
            })}
          </div>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div className="flex items-start justify-between">
          <div>
            <h3 className="text-sm font-semibold text-slate-800">Hitos del trimestre</h3>
            <p className="mt-0.5 text-xs text-slate-400">Milestones y puntos clave</p>
          </div>
          <FlagIcon className="h-5 w-5 text-indigo-500" />
        </div>
        <ol className="relative mt-5 space-y-4 border-l-2 border-slate-100 pl-4">
          {[
            ['15 abr', 'Lanzamiento encuesta clima Q2', true, 'emerald'],
            ['22 abr', 'Cierre ciclo SUA abril', true, 'emerald'],
            ['02 may', 'Revisión OKR trimestral', false, 'indigo'],
            ['15 may', 'Auditoría NOM-035', false, 'amber'],
            ['30 jun', 'Cierre trimestre', false, 'rose'],
          ].map(([fecha, titulo, done, tono]) => {
            const dot =
              tono === 'emerald'
                ? 'bg-emerald-500'
                : tono === 'amber'
                  ? 'bg-amber-500'
                  : tono === 'rose'
                    ? 'bg-rose-500'
                    : 'bg-indigo-500'
            return (
              <li key={titulo as string} className="an-timeline-item relative">
                <span
                  className={`absolute -left-[1.35rem] top-1 flex h-3 w-3 items-center justify-center rounded-full ring-4 ring-white ${dot}`}
                />
                <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                  {fecha}
                </p>
                <p
                  className={
                    'text-sm font-semibold ' +
                    (done ? 'text-slate-400 line-through' : 'text-slate-800')
                  }
                >
                  {titulo}
                </p>
              </li>
            )
          })}
        </ol>
      </div>

      <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div className="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-6 py-4">
          <div>
            <h3 className="text-sm font-semibold text-slate-800">
              Ranking de departamentos · Índice compuesto
            </h3>
            <p className="mt-0.5 text-xs text-slate-400">
              Headcount, satisfacción, adopción y retención combinados
            </p>
          </div>
          <span className="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
            <TrophyIcon className="h-3.5 w-3.5 text-amber-500" />
            Top 5
          </span>
        </div>
        <div className="divide-y divide-slate-50">
          {[
            ['#1', 'Tecnología', 96, '#3148c8'],
            ['#2', 'Recursos Humanos', 91, '#10b981'],
            ['#3', 'Administración', 88, '#8b5cf6'],
            ['#4', 'Operaciones', 82, '#0ea5e9'],
            ['#5', 'Ventas', 76, '#f59e0b'],
          ].map(([pos, dep, score, color]) => (
            <div key={dep as string} className="flex items-center gap-4 px-6 py-3">
              <span className="inline-flex h-8 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-xs font-bold text-slate-500">
                {pos}
              </span>
              <div className="min-w-0 flex-1">
                <div className="flex items-baseline justify-between">
                  <span className="text-sm font-semibold text-slate-800">{dep}</span>
                  <span className="text-sm font-bold tabular-nums text-slate-700">{score}</span>
                </div>
                <div className="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                  <div
                    className="an-bar-grow h-full rounded-full"
                    style={{ width: `${score}%`, backgroundColor: color as string }}
                  />
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}

const motivos = [
  ['Renuncia', 42, '#3148c8'],
  ['Despido', 23, '#ef4444'],
  ['Término contrato', 18, '#f59e0b'],
  ['Abandono', 10, '#8b5cf6'],
  ['Jubilación', 5, '#0ea5e9'],
  ['Otros', 2, '#94a3b8'],
] as const

export function RotacionSection() {
  const radius = 56
  const circ = 2 * Math.PI * radius
  let acc = 0
  const arcs = motivos.map((m) => {
    const dash = (m[1] / 100) * circ
    const gap = circ - dash
    const offset = circ - (acc / 100) * circ
    acc += m[1]
    return { label: m[0], pct: m[1], color: m[2], dash, gap, offset }
  })
  return (
    <div className="an-section space-y-6">
      <div className="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
        <div>
          <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">
            Rotación
          </p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            Altas, bajas y motivos
          </h2>
        </div>
        <div className="flex items-center gap-2 text-xs">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
            Últimos 12 meses
          </span>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 font-semibold text-rose-700 ring-1 ring-rose-200">
            Rotación 2.6%
          </span>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
          <div className="flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">Motivos de baja</h3>
              <p className="mt-0.5 text-xs text-slate-400">Distribución · 812 bajas YTD</p>
            </div>
            <ChartPieIcon className="h-5 w-5 text-indigo-500" />
          </div>
          <div className="mt-5 flex flex-col items-center gap-6 sm:flex-row sm:items-start">
            <div className="relative">
              <svg viewBox="0 0 160 160" className="h-40 w-40 -rotate-90">
                {arcs.map((a) => (
                  <circle
                    key={a.label}
                    cx="80"
                    cy="80"
                    r={radius}
                    fill="none"
                    stroke={a.color}
                    strokeWidth="18"
                    strokeDasharray={`${a.dash} ${a.gap}`}
                    strokeDashoffset={a.offset}
                    className="an-donut-seg"
                  />
                ))}
              </svg>
              <div className="absolute inset-0 flex flex-col items-center justify-center">
                <p className="text-3xl font-extrabold tabular-nums text-slate-900">812</p>
                <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                  Bajas YTD
                </p>
              </div>
            </div>
            <div className="flex-1 space-y-2">
              {motivos.map((m) => (
                <div key={m[0]} className="flex items-center justify-between text-xs">
                  <span className="flex items-center gap-2 text-slate-700">
                    <span
                      className="inline-flex h-2.5 w-2.5 rounded-full"
                      style={{ background: m[2] }}
                    />
                    <span className="font-medium">{m[0]}</span>
                  </span>
                  <span className="tabular-nums text-slate-500">{m[1]}%</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
          <div className="flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">Tasa de rotación mensual</h3>
              <p className="mt-0.5 text-xs text-slate-400">Meta 2.5% · promedio 2.6%</p>
            </div>
            <ChartBarIcon className="h-5 w-5 text-indigo-500" />
          </div>
          <div className="mt-6 flex h-44 items-end gap-1.5 border-b border-slate-100">
            {[
              ['Ene', 2.8],
              ['Feb', 3.1],
              ['Mar', 2.4],
              ['Abr', 2.7],
              ['May', 3.2],
              ['Jun', 2.9],
              ['Jul', 2.1],
              ['Ago', 2.3],
              ['Sep', 2.6],
              ['Oct', 2.4],
              ['Nov', 2.2],
              ['Dic', 1.9],
            ].map(([mes, valor]) => {
              const max = 4
              const pct = ((valor as number) / max) * 100
              const sobreMeta = (valor as number) > 2.5
              return (
                <div key={mes as string} className="flex flex-1 flex-col items-center gap-1">
                  <div className="relative w-full flex-1 rounded-t-md bg-slate-100">
                    <div
                      className={
                        'absolute bottom-0 left-0 right-0 rounded-t-md transition-all ' +
                        (sobreMeta ? 'bg-rose-400' : 'bg-[#3148c8]')
                      }
                      style={{ height: `${pct}%` }}
                    />
                  </div>
                  <span className="text-[10px] font-medium text-slate-500">{mes}</span>
                </div>
              )
            })}
          </div>
        </div>
      </div>
    </div>
  )
}

export function EngagementSection() {
  const dau = [
    42, 48, 51, 46, 55, 60, 58, 62, 65, 68, 72, 70, 74, 78, 75, 80, 82, 79, 84, 88, 85, 90, 92, 89, 94, 96, 98,
    95, 100, 104,
  ]
  const max = 120
  const w = 800
  const h = 220
  const stepX = w / (dau.length - 1)
  const pathLine = dau
    .map((v, i) => {
      const x = i * stepX
      const y = h - (v / max) * h
      return `${i === 0 ? 'M' : 'L'}${x},${y}`
    })
    .join(' ')
  const pathArea = `${pathLine} L${w},${h} L0,${h} Z`

  return (
    <div className="an-section space-y-6">
      <div className="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
        <div>
          <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">
            Engagement
          </p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            Actividad, adopción y uso
          </h2>
        </div>
        <div className="flex items-center gap-2 text-xs">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-200">
            <span className="inline-flex h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500" />
            En vivo
          </span>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
            DAU · WAU · MAU
          </span>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h3 className="text-sm font-semibold text-slate-800">Usuarios activos diarios · últimos 30 días</h3>
            <p className="mt-0.5 text-xs text-slate-400">DAU + promedio móvil de 7 días</p>
          </div>
          <div className="flex items-center gap-4 text-xs">
            <span className="flex items-center gap-1.5 text-slate-600">
              <span className="inline-flex h-2.5 w-6 rounded-sm bg-[#3148c8]/80" /> DAU
            </span>
            <span className="flex items-center gap-1.5 text-slate-600">
              <span className="inline-flex h-0.5 w-6 bg-fuchsia-500" /> Promedio 7d
            </span>
          </div>
        </div>
        <div className="mt-5">
          <svg viewBox={`0 0 ${w} ${h}`} className="h-56 w-full" preserveAspectRatio="none">
            <defs>
              <linearGradient id="eng-area" x1="0" x2="0" y1="0" y2="1">
                <stop offset="0%" stopColor="#3148c8" stopOpacity="0.45" />
                <stop offset="100%" stopColor="#3148c8" stopOpacity="0.02" />
              </linearGradient>
            </defs>
            {[1, 2, 3, 4].map((i) => (
              <line
                key={i}
                x1="0"
                x2={w}
                y1={(h * i) / 5}
                y2={(h * i) / 5}
                stroke="#e2e8f0"
                strokeDasharray="3 4"
                strokeWidth="1"
              />
            ))}
            <path d={pathArea} fill="url(#eng-area)" className="an-area-draw" />
            <path
              d={pathLine}
              fill="none"
              stroke="#3148c8"
              strokeWidth="2.5"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
        </div>
      </div>
    </div>
  )
}

export function DemograficosSection() {
  return (
    <div className="an-section space-y-6">
      <div className="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
        <div>
          <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">
            Demográficos
          </p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            Composición de la plantilla
          </h2>
        </div>
        <div className="flex items-center gap-2 text-xs">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
            30,524 colaboradores
          </span>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 font-semibold text-indigo-700 ring-1 ring-indigo-200">
            18 ubicaciones
          </span>
        </div>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div className="flex items-start justify-between">
          <div>
            <h3 className="text-sm font-semibold text-slate-800">Treemap · Headcount por departamento</h3>
            <p className="mt-0.5 text-xs text-slate-400">Tamaño proporcional al volumen de personas</p>
          </div>
          <SquaresPlusIcon className="h-5 w-5 text-indigo-500" />
        </div>

        <div
          className="mt-5 grid grid-cols-12 gap-2"
          style={{ gridAutoRows: 'minmax(60px, auto)' }}
        >
          <div className="an-treemap-tile col-span-6 row-span-3 flex flex-col justify-between rounded-xl border border-indigo-200/80 bg-indigo-50/90 p-4 text-indigo-950 shadow-sm backdrop-blur-sm">
            <span className="text-[11px] font-semibold uppercase tracking-wider text-indigo-700/90">
              Operaciones
            </span>
            <div>
              <p className="text-4xl font-extrabold tabular-nums text-indigo-950">12,400</p>
              <p className="text-xs text-indigo-800/80">40.6% del total</p>
            </div>
          </div>
          <div className="an-treemap-tile col-span-4 row-span-2 flex flex-col justify-between rounded-xl border border-emerald-200/80 bg-emerald-50/90 p-4 text-emerald-950 shadow-sm backdrop-blur-sm">
            <span className="text-[11px] font-semibold uppercase tracking-wider text-emerald-800/90">
              Producción
            </span>
            <div>
              <p className="text-3xl font-bold tabular-nums">9,800</p>
              <p className="text-xs text-emerald-900/75">32.1%</p>
            </div>
          </div>
          <div className="an-treemap-tile col-span-2 row-span-2 flex flex-col justify-between rounded-xl border border-amber-200/80 bg-amber-50/90 p-3 text-amber-950 shadow-sm backdrop-blur-sm">
            <span className="text-[10px] font-semibold uppercase tracking-wider text-amber-900/90">
              Ventas
            </span>
            <div>
              <p className="text-xl font-bold tabular-nums">4,200</p>
              <p className="text-[10px] text-amber-900/75">13.8%</p>
            </div>
          </div>
          <div className="an-treemap-tile col-span-3 row-span-1 flex items-end justify-between rounded-xl border border-violet-200/80 bg-violet-50/90 p-3 text-violet-950 shadow-sm backdrop-blur-sm">
            <span className="text-[10px] font-semibold uppercase tracking-wider text-violet-800/90">
              Logística
            </span>
            <span className="text-lg font-bold tabular-nums">1,600</span>
          </div>
          <div className="an-treemap-tile col-span-3 row-span-1 flex items-end justify-between rounded-xl border border-sky-200/80 bg-sky-50/90 p-3 text-sky-950 shadow-sm backdrop-blur-sm">
            <span className="text-[10px] font-semibold uppercase tracking-wider text-sky-800/90">
              Tecnología
            </span>
            <span className="text-lg font-bold tabular-nums">850</span>
          </div>
          <div className="an-treemap-tile col-span-2 row-span-1 flex items-end justify-between rounded-xl border border-rose-200/80 bg-rose-50/90 p-2 text-rose-950 shadow-sm backdrop-blur-sm">
            <span className="text-[10px] font-semibold uppercase text-rose-800/90">Admin</span>
            <span className="text-sm font-bold tabular-nums">540</span>
          </div>
          <div className="an-treemap-tile col-span-2 row-span-1 flex items-end justify-between rounded-xl border border-teal-200/80 bg-teal-50/90 p-2 text-teal-950 shadow-sm backdrop-blur-sm">
            <span className="text-[10px] font-semibold uppercase text-teal-800/90">RH</span>
            <span className="text-sm font-bold tabular-nums">320</span>
          </div>
          <div className="an-treemap-tile col-span-2 row-span-1 flex items-end justify-between rounded-xl border border-fuchsia-200/80 bg-fuchsia-50/90 p-2 text-fuchsia-950 shadow-sm backdrop-blur-sm">
            <span className="text-[10px] font-semibold uppercase text-fuchsia-800/90">Finanzas</span>
            <span className="text-sm font-bold tabular-nums">210</span>
          </div>
        </div>
      </div>
    </div>
  )
}

export function EncuestasSection() {
  const nps = 72
  const min = -100
  const max = 100
  const pct = (nps - min) / (max - min)
  const angle = pct * 180
  const radius = 90
  const cx = 110
  const cy = 110
  const rad = ((180 - angle) * Math.PI) / 180
  const endX = cx + radius * Math.cos(rad)
  const endY = cy - radius * Math.sin(rad)
  const largeArc = angle > 180 ? 1 : 0
  const needleLen = 78
  const needleX = cx + needleLen * Math.cos(rad)
  const needleY = cy - needleLen * Math.sin(rad)

  return (
    <div className="an-section space-y-6">
      <div className="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
        <div>
          <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">
            Encuestas
          </p>
          <h2 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            eNPS, clima y satisfacción
          </h2>
        </div>
        <div className="flex items-center gap-2 text-xs">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-200">
            eNPS 72 · Excelente
          </span>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
            12,842 respuestas
          </span>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-5 lg:grid-cols-5">
        <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-2">
          <div className="flex items-start justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-800">eNPS de la compañía</h3>
              <p className="mt-0.5 text-xs text-slate-400">Employee Net Promoter Score</p>
            </div>
            <FaceSmileIcon className="h-5 w-5 text-emerald-500" />
          </div>
          <div className="mt-3 flex flex-col items-center">
            <svg viewBox="0 0 220 130" className="h-44 w-full max-w-xs">
              <defs>
                <linearGradient id="gauge-grad" x1="0" x2="1" y1="0" y2="0">
                  <stop offset="0%" stopColor="#ef4444" />
                  <stop offset="30%" stopColor="#f59e0b" />
                  <stop offset="60%" stopColor="#eab308" />
                  <stop offset="100%" stopColor="#10b981" />
                </linearGradient>
              </defs>
              <path
                d="M 20 110 A 90 90 0 0 1 200 110"
                fill="none"
                stroke="#e2e8f0"
                strokeWidth="16"
                strokeLinecap="round"
              />
              <path
                d={`M 20 110 A 90 90 0 ${largeArc} 1 ${endX} ${endY}`}
                fill="none"
                stroke="url(#gauge-grad)"
                strokeWidth="16"
                strokeLinecap="round"
                className="an-gauge-fill"
              />
              <line
                x1={cx}
                y1={cy}
                x2={needleX}
                y2={needleY}
                stroke="#1e293b"
                strokeWidth="3"
                strokeLinecap="round"
                className="an-needle"
              />
              <circle cx={cx} cy={cy} r="6" fill="#1e293b" />
              <text x="20" y="126" fontSize="9" fill="#64748b" textAnchor="middle">
                -100
              </text>
              <text x="110" y="20" fontSize="9" fill="#64748b" textAnchor="middle">
                0
              </text>
              <text x="200" y="126" fontSize="9" fill="#64748b" textAnchor="middle">
                +100
              </text>
            </svg>
            <p className="mt-1 text-5xl font-extrabold tabular-nums text-emerald-600">{nps}</p>
            <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
              Rango Excelente (&gt; 50)
            </p>
          </div>
          <div className="mt-5 grid grid-cols-3 gap-2 text-center text-xs">
            <div className="rounded-lg bg-emerald-50 px-2 py-2">
              <p className="text-lg font-bold text-emerald-700 tabular-nums">81%</p>
              <p className="text-[10px] font-semibold uppercase tracking-wider text-emerald-600">
                Promotores
              </p>
            </div>
            <div className="rounded-lg bg-amber-50 px-2 py-2">
              <p className="text-lg font-bold text-amber-700 tabular-nums">14%</p>
              <p className="text-[10px] font-semibold uppercase tracking-wider text-amber-600">
                Pasivos
              </p>
            </div>
            <div className="rounded-lg bg-rose-50 px-2 py-2">
              <p className="text-lg font-bold text-rose-700 tabular-nums">5%</p>
              <p className="text-[10px] font-semibold uppercase tracking-wider text-rose-600">
                Detractores
              </p>
            </div>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-3">
          <h3 className="text-sm font-semibold text-slate-800">Clima organizacional · última medición</h3>
          <p className="mt-0.5 text-xs text-slate-400">Promedio por dimensión (1–5)</p>
          <div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
            {[
              ['Liderazgo', 4.2],
              ['Comunicación', 4.0],
              ['Reconocimiento', 3.8],
              ['Desarrollo', 4.1],
              ['Balance', 3.9],
              ['Beneficios', 4.4],
            ].map(([d, v]) => (
              <div key={d as string} className="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                <p className="text-[11px] font-medium text-slate-500">{d}</p>
                <p className="mt-1 text-2xl font-bold tabular-nums text-slate-900">{v}</p>
                <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200">
                  <div
                    className="h-full rounded-full bg-[#3148c8]"
                    style={{ width: `${((v as number) / 5) * 100}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
