import { CheckIcon } from '@heroicons/react/24/outline'
import type { CSSProperties } from 'react'

import { clsx } from '@/utils/cn'

export type WizardProgressStep = {
  id: string
  label: string
  shortLabel?: string
}

type Props = {
  steps: WizardProgressStep[]
  currentIndex: number
  onStepClick: (index: number) => void
  /** Pasos ya visitados (refuerzo visual al saltar adelante). */
  visitedIndices?: Set<number>
}

/**
 * Genera `grid-template-columns`: pasos en `auto`, conectores `80px` o `1fr` según cantidad de pasos.
 * · 2–3 pasos: conectores fijos 80px · 4+ pasos: conectores flexibles (1fr)
 */
function gridTemplateColumnsForSteps(count: number): string {
  if (count <= 0) {
    return 'auto'
  }
  return Array.from({ length: count }, (_, i) =>
    i === 0
      ? 'auto'
      : count > 3
        ? '1fr auto'
        : '80px auto',
  ).join(' ')
}

/**
 * Progreso de asistente: barra superior a ancho completo + grid de pasos con trazos entre círculos.
 */
export function UxWizardProgress({
  steps,
  currentIndex,
  onStepClick,
  visitedIndices,
}: Props) {
  const total = steps.length
  const pct =
    total <= 1 ? 100 : Math.round((currentIndex / Math.max(1, total - 1)) * 100)

  const gridStyle: CSSProperties = {
    gridTemplateColumns: gridTemplateColumnsForSteps(total),
  }

  return (
    <div className="w-full space-y-4">
      <div
        className="relative h-1 overflow-hidden rounded-full bg-slate-200/90"
        role="progressbar"
        aria-valuemin={0}
        aria-valuemax={100}
        aria-valuenow={pct}
        aria-valuetext={`Paso ${currentIndex + 1} de ${total}`}
        aria-label="Progreso del asistente"
      >
        <div
          className="h-full rounded-full bg-gradient-to-r from-[#3148c8] to-[#4d62d4] transition-[width] duration-500 ease-out"
          style={{ width: `${pct}%` }}
        />
      </div>

      <ol
        className="grid w-full list-none items-center gap-0 px-0.5 sm:px-1"
        style={gridStyle}
      >
        {steps.flatMap((step, index) => {
          const isActive = index === currentIndex
          const isDone = index < currentIndex
          const touched = visitedIndices?.has(index) ?? false
          const emphasized = isDone || (touched && !isActive)

          const stepItem = (
            <li key={`step-${step.id}`} className="flex min-w-0 justify-center">
              <button
                type="button"
                onClick={() => onStepClick(index)}
                aria-current={isActive ? 'step' : undefined}
                className={clsx(
                  'group flex max-w-full flex-col items-center gap-2 rounded-xl px-1 py-1.5 outline-none transition-colors sm:px-2',
                  'focus-visible:ring-2 focus-visible:ring-[#3148c8]/35',
                  !isActive && 'hover:bg-slate-50',
                )}
              >
                <span
                  className={clsx(
                    'relative flex items-center justify-center rounded-full border-2 transition-all duration-200',
                    isActive &&
                      'size-5 border-[#3148c8] bg-white shadow-[0_0_0_4px_rgba(49,72,200,0.18)] sm:size-6',
                    !isActive &&
                      emphasized &&
                      'size-4 border-[#3148c8] bg-[#3148c8] text-white sm:size-5',
                    !isActive &&
                      !emphasized &&
                      'size-3.5 border-slate-300 bg-white sm:size-4',
                  )}
                  aria-hidden
                >
                  {isDone && !isActive ? (
                    <CheckIcon className="size-3 text-white sm:size-3.5" strokeWidth={2.5} />
                  ) : isActive ? (
                    <span className="size-2 rounded-full bg-[#3148c8] sm:size-2.5" />
                  ) : null}
                </span>
                <span
                  className={clsx(
                    'max-w-[5.5rem] text-center text-[10px] font-semibold leading-tight sm:max-w-[9rem] sm:text-xs',
                    isActive && 'text-[#3148c8]',
                    !isActive && emphasized && 'text-slate-800',
                    !isActive && !emphasized && 'text-slate-500',
                  )}
                >
                  <span className="hidden sm:inline">{step.label}</span>
                  <span className="sm:hidden">{step.shortLabel ?? step.label}</span>
                </span>
              </button>
            </li>
          )

          if (index === steps.length - 1) {
            return [stepItem]
          }

          const connItem = (
            <li
              key={`conn-${index}`}
              aria-hidden
              className="flex min-h-[4px] min-w-0 items-center self-center"
            >
              <div
                className={clsx(
                  'h-1 w-full min-w-0 rounded-full transition-colors duration-300',
                  currentIndex >= index + 1 ? 'bg-[#3148c8]' : 'bg-slate-200',
                )}
              />
            </li>
          )

          return [stepItem, connItem]
        })}
      </ol>
    </div>
  )
}
