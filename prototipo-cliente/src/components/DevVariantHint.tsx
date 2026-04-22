import type { VariantHintContent } from '../guidance/types'

type Props = {
  content: VariantHintContent
  className?: string
}

/**
 * Nota fija junto a un ejemplo concreto: cuándo usar esa variante (sin depender de un bloque genérico de página).
 */
export function DevVariantHint({ content, className = '' }: Props) {
  return (
    <aside
      className={
        'rounded-lg border border-indigo-100/90 bg-indigo-50/50 px-3 py-2.5 text-left text-xs leading-snug text-slate-700 ring-1 ring-indigo-100/60 ' +
        className
      }
    >
      <p className="font-semibold text-indigo-950">{content.titulo}</p>
      <p className="mt-1 text-[11px] font-medium uppercase tracking-wide text-indigo-800/90">Elige esta variante si</p>
      <ul className="mt-1 list-inside list-disc space-y-0.5 text-slate-600">
        {content.eligeEstoSi.map((b) => (
          <li key={b}>{b}</li>
        ))}
      </ul>
      {content.mejorNoSi?.length ? (
        <>
          <p className="mt-2 text-[11px] font-medium uppercase tracking-wide text-amber-900/85">Mejor otra si</p>
          <ul className="mt-1 list-inside list-disc space-y-0.5 text-slate-600">
            {content.mejorNoSi.map((b) => (
              <li key={b}>{b}</li>
            ))}
          </ul>
        </>
      ) : null}
      {content.filament ? (
        <p className="mt-2 border-t border-indigo-100/80 pt-2 text-[11px] text-slate-500">
          <span className="font-semibold text-slate-600">Filament: </span>
          {content.filament}
        </p>
      ) : null}
    </aside>
  )
}
