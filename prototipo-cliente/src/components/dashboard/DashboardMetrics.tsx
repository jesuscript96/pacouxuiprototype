import {
  ArrowPathIcon,
  CalendarDaysIcon,
  ChartBarIcon,
  ChatBubbleLeftRightIcon,
  ClockIcon,
  StarIcon,
  TrophyIcon,
  UserMinusIcon,
} from '@heroicons/react/24/outline'
import { SectionTitle } from './SectionTitle'

export function DashboardMetrics() {
  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="Métricas rápidas" />
      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 sm:gap-4">
        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-amber-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 sm:h-10 sm:w-10">
              <CalendarDaysIcon className="h-5 w-5 text-amber-700 sm:h-6 sm:w-6" aria-hidden />
            </div>
            <p className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">2,447</p>
            <p className="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">
              Cumpleaños del Mes
            </p>
          </div>
        </div>

        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-violet-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 sm:h-10 sm:w-10">
              <TrophyIcon className="h-5 w-5 text-violet-700 sm:h-6 sm:w-6" aria-hidden />
            </div>
            <p className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">2,309</p>
            <p className="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">
              Aniversarios del Mes
            </p>
          </div>
        </div>

        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-emerald-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 sm:h-10 sm:w-10">
              <ArrowPathIcon className="h-5 w-5 text-emerald-600" />
            </div>
            <p className="mt-3 text-2xl font-extrabold tracking-tight text-emerald-600 sm:text-3xl">0.0%</p>
            <p className="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">
              Índice de Rotación
            </p>
          </div>
        </div>

        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-blue-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 sm:h-10 sm:w-10">
              <ChartBarIcon className="h-5 w-5 text-blue-600" />
            </div>
            <div className="mt-3 flex items-end gap-2">
              <p className="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">78%</p>
            </div>
            <div className="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
              <div className="dash-progress h-full rounded-full bg-[#3148c8]" style={{ width: '78%' }} />
            </div>
            <p className="mt-1.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">
              Tasa de Registro
            </p>
          </div>
        </div>

        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-indigo-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 sm:h-10 sm:w-10">
              <ClockIcon className="h-5 w-5 text-indigo-600" />
            </div>
            <p className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
              3.2 <span className="text-base font-semibold text-slate-400">años</span>
            </p>
            <p className="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">
              Antigüedad Promedio
            </p>
            <div className="mt-3 space-y-1.5">
              <div className="flex items-center gap-2 text-xs text-slate-500">
                <div className="h-1 flex-1 overflow-hidden rounded-full bg-slate-100">
                  <div className="h-full rounded-full bg-indigo-400" style={{ width: '50%' }} />
                </div>
                <span className="w-16 text-right tabular-nums">1-5 años</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-slate-500">
                <div className="h-1 flex-1 overflow-hidden rounded-full bg-slate-100">
                  <div className="h-full rounded-full bg-indigo-300" style={{ width: '18%' }} />
                </div>
                <span className="w-16 text-right tabular-nums">5-10 años</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-slate-500">
                <div className="h-1 flex-1 overflow-hidden rounded-full bg-slate-100">
                  <div className="h-full rounded-full bg-indigo-200" style={{ width: '13%' }} />
                </div>
                <span className="w-16 text-right tabular-nums">+10 años</span>
              </div>
            </div>
          </div>
        </div>

        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-amber-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-amber-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 sm:h-10 sm:w-10">
              <StarIcon className="h-5 w-5 text-amber-600" />
            </div>
            <p className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">1</p>
            <p className="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Reconocimientos</p>
          </div>
        </div>

        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-sky-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-sky-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 sm:h-10 sm:w-10">
              <ChatBubbleLeftRightIcon className="h-5 w-5 text-sky-600" />
            </div>
            <p className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">0</p>
            <p className="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Comentarios</p>
          </div>
        </div>

        <div className="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-rose-200/80 hover:shadow-lg hover:shadow-slate-900/5 sm:p-5">
          <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-rose-50 transition-transform duration-500 group-hover:scale-150" />
          <div className="relative">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 sm:h-10 sm:w-10">
              <UserMinusIcon className="h-5 w-5 text-rose-600" />
            </div>
            <p className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">3</p>
            <p className="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">
              Bajas Programadas
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
