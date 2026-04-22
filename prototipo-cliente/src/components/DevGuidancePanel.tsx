import type { GuidanceContent } from '../guidance/types'

type Props = {
  content: GuidanceContent
  className?: string
}

/**
 * Bloque colapsable para desarrolladores: criterios de uso del patrón mostrado debajo.
 * Usar al inicio de cada sección Storybook o UX.
 */
export function DevGuidancePanel({ content, className = '' }: Props) {
  return (
    <details
      className={
        'group mb-6 rounded-xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50/90 to-white/80 p-4 text-left shadow-sm ring-1 ring-indigo-100/50 open:shadow-md sm:mb-8 sm:p-5 ' +
        className
      }
    >
      <summary className="cursor-pointer list-none text-sm font-semibold text-indigo-950 [&::-webkit-details-marker]:hidden">
        <span className="inline-flex items-center gap-2">
          <span
            className="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-indigo-600/10 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200/60"
            aria-hidden
          >
            Dev
          </span>
          Notas para desarrolladores
          <span className="text-xs font-normal text-indigo-600/80 group-open:hidden">— pulsar para expandir</span>
        </span>
      </summary>
      <div className="mt-4 space-y-4 border-t border-indigo-200/50 pt-4 text-sm text-slate-700">
        <div>
          <p className="font-semibold text-slate-900">{content.title}</p>
          <p className="mt-1.5 leading-relaxed text-slate-600">{content.summary}</p>
        </div>
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-indigo-800">Cuándo usar</p>
          <ul className="mt-2 list-inside list-disc space-y-1.5 text-slate-600">
            {content.bulletsCuandoUsar.map((b) => (
              <li key={b}>{b}</li>
            ))}
          </ul>
        </div>
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-amber-900/90">Cuándo preferir otra cosa</p>
          <ul className="mt-2 list-inside list-disc space-y-1.5 text-slate-600">
            {content.bulletsEvitar.map((b) => (
              <li key={b}>{b}</li>
            ))}
          </ul>
        </div>
        {content.equivalenteFilament?.length ? (
          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Referencia Filament</p>
            <ul className="mt-2 list-inside list-disc space-y-1 text-slate-600">
              {content.equivalenteFilament.map((b) => (
                <li key={b}>{b}</li>
              ))}
            </ul>
          </div>
        ) : null}
        {content.referenciaReglasCursor ? (
          <p className="text-xs leading-relaxed text-slate-500">{content.referenciaReglasCursor}</p>
        ) : null}
      </div>
    </details>
  )
}
