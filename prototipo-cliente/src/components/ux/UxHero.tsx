import type { ComponentType, ReactNode } from 'react'
import { DevGuidanceInline } from '../DevGuidanceInline'
import type { GuidanceContent } from '../../guidance/types'

type Stat = {
  label?: string
  value: string
  hint?: string
}

type Props = {
  eyebrow?: string
  title: string
  description?: string
  icon?: ComponentType<{ className?: string }>
  stat?: Stat
  children?: ReactNode
  /** Criterios de uso del módulo, pegados al héroe (superficie vidrio + contexto). */
  guidance?: GuidanceContent
}

export function UxHero({
  eyebrow,
  title,
  description,
  icon: Icon,
  stat,
  children,
  guidance,
}: Props) {
  return (
    <div className="ux-hero dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8">
      <div className="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-indigo-400/[0.06] blur-3xl" />
      <div className="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-slate-300/[0.12] blur-3xl" />

      <div className="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
        <div>
          {eyebrow ? (
            <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
              <span className="inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/15" />
              {eyebrow}
            </div>
          ) : null}

          <h1 className="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-[2rem]">
            {title}
          </h1>

          {description ? (
            <p className="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">
              {description}
            </p>
          ) : null}
        </div>

        {stat && Icon ? (
          <div className="flex items-center gap-3 rounded-2xl border border-white/60 bg-white/45 p-4 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200/70 bg-white/70 text-[#3148c8] shadow-sm">
              <Icon className="h-6 w-6" />
            </div>
            <div>
              {stat.label ? (
                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                  {stat.label}
                </p>
              ) : null}
              <p className="text-2xl font-extrabold tabular-nums text-slate-900">{stat.value}</p>
              {stat.hint ? (
                <p className="text-[11px] text-slate-600">{stat.hint}</p>
              ) : null}
            </div>
          </div>
        ) : null}
      </div>

      {guidance ? (
        <div className="relative mt-6 border-t border-slate-200/60 pt-5">
          <DevGuidanceInline content={guidance} />
        </div>
      ) : null}

      {children}
    </div>
  )
}
