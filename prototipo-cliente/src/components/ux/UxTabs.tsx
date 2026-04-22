import type { ComponentType } from 'react'

export type UxTab = {
  id: string
  label: string
  icon?: ComponentType<{ className?: string }>
  description?: string
}

type Props = {
  tabs: UxTab[]
  active: string
  onChange: (id: string) => void
}

export function UxTabs({ tabs, active, onChange }: Props) {
  return (
    <div className="ux-tabs sticky top-0 z-20 -mx-4 sm:-mx-6 lg:-mx-8">
      <div className="mx-4 rounded-2xl border border-slate-200 bg-white/80 p-2 shadow-sm backdrop-blur-md sm:mx-6 lg:mx-8">
        <nav className="flex flex-wrap items-stretch gap-1" role="tablist">
          {tabs.map((tab) => {
            const isActive = active === tab.id
            const Icon = tab.icon
            return (
              <button
                key={tab.id}
                type="button"
                role="tab"
                aria-selected={isActive}
                onClick={() => onChange(tab.id)}
                className={
                  'group flex min-w-[8rem] flex-1 items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-all duration-200 ' +
                  (isActive
                    ? 'bg-[#3148c8] text-white shadow-md ring-1 ring-[#3148c8]/25'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900')
                }
              >
                <span
                  className={
                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors ' +
                    (isActive ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-white')
                  }
                >
                  {Icon ? (
                    <Icon
                      className={
                        'h-4 w-4 ' + (isActive ? 'text-white' : 'text-slate-500')
                      }
                    />
                  ) : (
                    <span className="text-xs">▢</span>
                  )}
                </span>
                <span className="flex flex-col items-start leading-tight">
                  <span>{tab.label}</span>
                  {tab.description ? (
                    <span
                      className={
                        'text-[10.5px] font-normal normal-case tracking-normal ' +
                        (isActive ? 'text-white/75' : 'text-slate-400')
                      }
                    >
                      {tab.description}
                    </span>
                  ) : null}
                </span>
              </button>
            )
          })}
        </nav>
      </div>
    </div>
  )
}
