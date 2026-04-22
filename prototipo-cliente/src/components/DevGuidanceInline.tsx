import type { GuidanceContent } from '../guidance/types'

type Props = {
  content: GuidanceContent
  className?: string
}

/**
 * Misma información que el panel colapsable, siempre visible y compacta (debajo de un componente o en un slot).
 */
export function DevGuidanceInline({ content, className = '' }: Props) {
  return (
    <aside
      className={
        'rounded-xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50/90 to-white/90 p-3.5 text-left text-xs text-slate-700 shadow-sm ring-1 ring-indigo-100/50 sm:p-4 ' +
        className
      }
    >
      <div className="flex items-start gap-2">
        <span
          className="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-600/10 text-[10px] font-bold text-indigo-800 ring-1 ring-indigo-200/50"
          aria-hidden
        >
          Dev
        </span>
        <div className="min-w-0 flex-1 space-y-2">
          <div>
            <p className="font-semibold text-slate-900">{content.title}</p>
            <p className="mt-1 leading-relaxed text-slate-600">{content.summary}</p>
          </div>
          <div>
            <p className="text-[10px] font-semibold uppercase tracking-wide text-indigo-800">Cuándo usar</p>
            <ul className="mt-1 list-inside list-disc space-y-0.5 text-slate-600">
              {content.bulletsCuandoUsar.map((b) => (
                <li key={b}>{b}</li>
              ))}
            </ul>
          </div>
          <div>
            <p className="text-[10px] font-semibold uppercase tracking-wide text-amber-900/90">Cuándo no</p>
            <ul className="mt-1 list-inside list-disc space-y-0.5 text-slate-600">
              {content.bulletsEvitar.map((b) => (
                <li key={b}>{b}</li>
              ))}
            </ul>
          </div>
          {content.equivalenteFilament?.length ? (
            <p className="text-[11px] text-slate-500">
              <span className="font-semibold text-slate-600">Filament: </span>
              {content.equivalenteFilament.join(' · ')}
            </p>
          ) : null}
          {content.referenciaReglasCursor ? (
            <p className="text-[10px] leading-relaxed text-slate-500">{content.referenciaReglasCursor}</p>
          ) : null}
        </div>
      </div>
    </aside>
  )
}
