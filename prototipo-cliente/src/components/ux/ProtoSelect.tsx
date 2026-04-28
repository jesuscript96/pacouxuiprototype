import { CheckIcon, ChevronDownIcon } from '@heroicons/react/24/outline'
import { Select } from 'radix-ui'

import { cn } from '@/lib/utils'

/** Valor interno para “sin selección” con Radix (no usar en datos de negocio). */
export const PROTO_SELECT_NONE = '__proto_select_none__'

export type ProtoSelectOption = {
  value: string
  label: string
  disabled?: boolean
}

export type ProtoSelectProps = {
  value: string
  onValueChange: (value: string) => void
  options: ProtoSelectOption[]
  /** Etiqueta de la opción vacía cuando `allowEmpty` es true. */
  placeholder?: string
  /** Si es true, se añade opción interna vacía (`''`). Si es false, el valor debe existir en `options`. */
  allowEmpty?: boolean
  disabled?: boolean
  id?: string
  'aria-label'?: string
  className?: string
  contentClassName?: string
}

/**
 * Select estilizado (Radix) para reemplazar `<select>` nativo en prototipos UX.
 * `value` vacío muestra el placeholder y emite `''` al limpiar.
 */
export function ProtoSelect({
  value,
  onValueChange,
  options,
  placeholder = 'Selecciona…',
  allowEmpty = true,
  disabled,
  id,
  'aria-label': ariaLabel,
  className,
  contentClassName,
}: ProtoSelectProps) {
  const valid = new Set(options.map((o) => o.value))
  const coerced = valid.has(value) ? value : ''
  const useEmpty = allowEmpty
  const emptyLabel = placeholder ?? 'Selecciona…'
  const radixValue = useEmpty && coerced === '' ? PROTO_SELECT_NONE : coerced
  const withPlaceholder: ProtoSelectOption[] = useEmpty
    ? [{ value: PROTO_SELECT_NONE, label: emptyLabel }, ...options]
    : options

  const handleChange = (v: string) => {
    onValueChange(useEmpty && v === PROTO_SELECT_NONE ? '' : v)
  }

  return (
    <Select.Root value={radixValue} onValueChange={handleChange} disabled={disabled}>
      <Select.Trigger
        id={id}
        aria-label={ariaLabel}
        className={cn(
          'flex min-h-10 w-full min-w-0 items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-left text-sm leading-tight text-slate-900 shadow-sm',
          'transition-[border-color,box-shadow,color]',
          'hover:border-slate-300 data-placeholder:text-slate-400',
          'focus-visible:border-slate-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8]/18 focus-visible:ring-offset-0',
          'disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500',
          className,
        )}
      >
        <Select.Value placeholder={useEmpty ? emptyLabel : undefined} />
        <Select.Icon>
          <ChevronDownIcon className="size-4 shrink-0 text-slate-400" aria-hidden />
        </Select.Icon>
      </Select.Trigger>
      <Select.Portal>
        <Select.Content
          position="popper"
          sideOffset={5}
          className={cn(
            'z-[200] max-h-72 min-w-[var(--radix-select-trigger-width)] overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-[0_10px_38px_-12px_rgba(15,23,42,0.28),0_4px_16px_-8px_rgba(15,23,42,0.12)]',
            'data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95',
            'data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95',
            contentClassName,
          )}
        >
          <Select.Viewport className="p-1">
            {withPlaceholder.map((opt) => (
              <Select.Item
                key={opt.value}
                value={opt.value}
                disabled={opt.disabled}
                textValue={opt.label}
                className={cn(
                  'relative flex cursor-pointer select-none items-center rounded-md py-2.5 pr-10 pl-3 text-sm text-slate-700 outline-none',
                  /* Navegación teclado / hover */
                  '[&[data-highlighted]]:bg-slate-50 [&[data-highlighted]]:text-slate-900',
                  /* Opción activa: franja gris + check (referencia catálogo) */
                  '[&[data-state=checked]]:bg-slate-100 [&[data-state=checked]]:text-slate-900',
                  '[&[data-state=checked][data-highlighted]]:bg-slate-100',
                  'data-disabled:pointer-events-none data-disabled:opacity-40',
                )}
              >
                <Select.ItemText>{opt.label}</Select.ItemText>
                <Select.ItemIndicator className="absolute right-2.5 flex size-4 items-center justify-center text-slate-500">
                  <CheckIcon className="size-4" strokeWidth={2.25} />
                </Select.ItemIndicator>
              </Select.Item>
            ))}
          </Select.Viewport>
        </Select.Content>
      </Select.Portal>
    </Select.Root>
  )
}
