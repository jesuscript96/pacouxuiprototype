import { cn } from '@/lib/utils'

type Props = {
  id: string
  label?: string
  description?: string
  checked: boolean
  onChange: (value: boolean) => void
  disabled?: boolean
}

/** Interruptor consistente con el patrón del prototipo (color primario #3148c8). */
export function ProtoSwitch({ id, label, description, checked, onChange, disabled }: Props) {
  const compact = !label?.trim()
  return (
    <div
      className={cn(
        'flex items-center gap-4 rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3',
        compact ? 'justify-end' : 'justify-between',
      )}
    >
      {compact ? (
        <span id={`${id}-lbl`} className="sr-only">
          Alternar
        </span>
      ) : (
        <label htmlFor={id} className="min-w-0">
          <span className="block text-sm font-medium text-slate-800">{label}</span>
          {description ? <span className="mt-0.5 block text-xs text-slate-500">{description}</span> : null}
        </label>
      )}
      <button
        id={id}
        type="button"
        role="switch"
        aria-labelledby={compact ? `${id}-lbl` : undefined}
        aria-label={compact ? 'Alternar' : undefined}
        aria-checked={checked}
        disabled={disabled}
        onClick={() => onChange(!checked)}
        className={cn(
          'relative h-7 w-12 shrink-0 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
          checked ? 'bg-[#3148c8]' : 'bg-slate-300',
        )}
      >
        <span
          className={cn(
            'absolute top-0.5 h-6 w-6 rounded-full bg-white shadow transition-transform',
            checked ? 'translate-x-[1.35rem]' : 'translate-x-0.5',
          )}
        />
      </button>
    </div>
  )
}
