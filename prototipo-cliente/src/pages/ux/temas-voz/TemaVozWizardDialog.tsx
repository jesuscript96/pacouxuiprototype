import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { XMarkIcon } from '@heroicons/react/24/outline'
import { useCallback, useState } from 'react'

import { Button } from '@/components/ui/button'
import { UxWizardProgress } from '@/components/ux/UxWizardProgress'
import { cn } from '@/lib/utils'

import {
  TemaVozStepSegmentacion,
  TemaVozStepTema,
} from './TemaVozFormFields'
import type { TemaVozFormState } from './temaVozFormState'
import type {
  DestinatarioMock,
  EmpresaOpcion,
} from './temasVozMockData'

const WIZARD_STEPS = [
  { id: 'tema', label: 'Tema y descripción', shortLabel: 'Tema' },
  { id: 'segmentacion', label: 'Segmentación', shortLabel: 'Segmentación' },
] as const

type Props = {
  open: boolean
  onClose: () => void
  mode: 'create' | 'edit'
  /** Paso al que abrir el wizard (0 = Tema, 1 = Segmentación). */
  initialStep?: 0 | 1
  form: TemaVozFormState
  errorMessage: string | null
  empresas: EmpresaOpcion[]
  destinatariosPool: DestinatarioMock[]
  onChange: (patch: Partial<TemaVozFormState>) => void
  onToggleDestinatario: (id: string, on: boolean) => void
  onToggleTodosDestinatarios: (on: boolean) => void
  onSave: () => void
}

/**
 * Popup centrado con asistente de 2 pasos para crear/editar un Tema de Voz.
 * El parent (TemasVozUxPage) es la fuente de verdad del `form` y se encarga
 * de la validación final al guardar. Aquí solo controlamos la navegación
 * entre pasos y validamos lo mínimo para avanzar (nombre obligatorio en paso 0).
 */
export function TemaVozWizardDialog({
  open,
  onClose,
  mode,
  initialStep = 0,
  form,
  errorMessage,
  empresas,
  destinatariosPool,
  onChange,
  onToggleDestinatario,
  onToggleTodosDestinatarios,
  onSave,
}: Props) {
  const [step, setStep] = useState<0 | 1>(initialStep)
  const [visited, setVisited] = useState<Set<number>>(
    () => new Set<number>([initialStep]),
  )
  const [stepError, setStepError] = useState<string | null>(null)
  // BL: Reset durante render para evitar `setState` en effect.
  // Patrón recomendado por React para ajustar estado cuando cambian props:
  // https://react.dev/learn/you-might-not-need-an-effect#adjusting-some-state-when-a-prop-changes
  const [lastOpen, setLastOpen] = useState<{ open: boolean; step: 0 | 1 }>({
    open,
    step: initialStep,
  })
  const acabaDeAbrir = open && !lastOpen.open
  const cambioInitialStep = open && initialStep !== lastOpen.step
  if (acabaDeAbrir || cambioInitialStep) {
    setLastOpen({ open: true, step: initialStep })
    setStep(initialStep)
    setVisited(new Set<number>([initialStep]))
    setStepError(null)
  } else if (!open && lastOpen.open) {
    setLastOpen({ open: false, step: initialStep })
  }

  const goToStep = useCallback((i: 0 | 1) => {
    setStep(i)
    setVisited((prev) => {
      const next = new Set(prev)
      next.add(i)
      return next
    })
  }, [])

  const validateStepTema = useCallback((): string | null => {
    if (!form.nombre.trim()) {
      return 'El nombre del tema es obligatorio.'
    }
    return null
  }, [form.nombre])

  const handleNext = useCallback(() => {
    if (step === 0) {
      const err = validateStepTema()
      if (err) {
        setStepError(err)
        return
      }
      setStepError(null)
      goToStep(1)
    }
  }, [step, validateStepTema, goToStep])

  const handleBack = useCallback(() => {
    setStepError(null)
    if (step > 0) {
      goToStep(0)
    }
  }, [step, goToStep])

  const handleProgressClick = useCallback(
    (i: number) => {
      if (i === 0) {
        setStepError(null)
        goToStep(0)
        return
      }
      if (i === 1) {
        const err = validateStepTema()
        if (err) {
          setStepError(err)
          return
        }
        setStepError(null)
        goToStep(1)
      }
    },
    [goToStep, validateStepTema],
  )

  const handleSave = useCallback(() => {
    const err = validateStepTema()
    if (err) {
      setStepError(err)
      goToStep(0)
      return
    }
    setStepError(null)
    onSave()
  }, [validateStepTema, onSave, goToStep])

  const titulo = mode === 'create' ? 'Nuevo tema de voz' : 'Editar tema de voz'
  const subtitulo =
    step === 0
      ? 'Define el nombre y una breve descripción del tema.'
      : 'Elige a qué empresa aplica y quiénes lo verán en la app.'

  const visibleError = stepError ?? errorMessage

  return (
    <Dialog open={open} onClose={onClose} className="relative z-[80]">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0"
      />
      <div className="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel
          transition
          className={cn(
            'flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 transition',
            'data-[closed]:scale-95 data-[closed]:opacity-0',
          )}
        >
          <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div className="min-w-0">
              <DialogTitle className="text-lg font-semibold text-slate-900">
                {titulo}
              </DialogTitle>
              <p className="mt-1 text-sm text-slate-500">{subtitulo}</p>
            </div>
            <button
              type="button"
              className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
              onClick={onClose}
              aria-label="Cerrar"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          <div className="shrink-0 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
            <UxWizardProgress
              steps={[...WIZARD_STEPS]}
              currentIndex={step}
              onStepClick={handleProgressClick}
              visitedIndices={visited}
            />
          </div>

          <div className="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6">
            {visibleError ? (
              <div
                className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900"
                role="alert"
              >
                {visibleError}
              </div>
            ) : null}

            {step === 0 ? (
              <TemaVozStepTema form={form} onChange={onChange} />
            ) : (
              <TemaVozStepSegmentacion
                form={form}
                onChange={onChange}
                onToggleDestinatario={onToggleDestinatario}
                onToggleTodosDestinatarios={onToggleTodosDestinatarios}
                empresas={empresas}
                destinatariosPool={destinatariosPool}
              />
            )}
          </div>

          <div className="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
            <Button type="button" variant="ghost" onClick={onClose}>
              Cancelar
            </Button>
            <div className="flex flex-wrap gap-2">
              {step > 0 ? (
                <Button type="button" variant="outline" onClick={handleBack}>
                  Atrás
                </Button>
              ) : null}
              {step === 0 ? (
                <Button
                  type="button"
                  className="bg-[#3148c8] hover:bg-[#263a9e]"
                  onClick={handleNext}
                >
                  Siguiente
                </Button>
              ) : (
                <Button
                  type="button"
                  className="bg-[#3148c8] hover:bg-[#263a9e]"
                  onClick={handleSave}
                >
                  Guardar
                </Button>
              )}
            </div>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}
