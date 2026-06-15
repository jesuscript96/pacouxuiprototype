import {
  ArrowLeftIcon,
  ArrowRightIcon,
  ComputerDesktopIcon,
  DevicePhoneMobileIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

import type { BloqueFormulario, EncuestaDraft } from '../encuestasTypes'

type Props = {
  draft: EncuestaDraft
  onClose: () => void
}

/** Pasos navegables: cada bloque es una página; cada pregunta de sección, una página. */
type PreviewStep =
  | { kind: 'bloque'; bloque: BloqueFormulario }
  | { kind: 'pregunta'; seccionTitulo: string; bloque: BloqueFormulario; preguntaIndex: number }

export function BuilderPreview({ draft, onClose }: Props) {
  const [device, setDevice] = useState<'desktop' | 'mobile'>('desktop')
  const [index, setIndex] = useState(0)

  const steps = useMemo<PreviewStep[]>(() => {
    const list: PreviewStep[] = []
    for (const b of draft.bloques) {
      if (b.tipo === 'seccion') {
        b.preguntas.forEach((_, i) => {
          list.push({ kind: 'pregunta', seccionTitulo: b.titulo, bloque: b, preguntaIndex: i })
        })
      } else {
        list.push({ kind: 'bloque', bloque: b })
      }
    }
    return list
  }, [draft.bloques])

  const safeIndex = Math.min(index, Math.max(0, steps.length - 1))
  const step = steps[safeIndex]

  return (
    <div className="fixed inset-0 z-[90] flex flex-col bg-slate-100">
      <div className="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3">
        <div className="flex items-center gap-2 text-sm font-medium text-slate-600">
          <span className="font-semibold text-slate-900">Vista previa</span>
          <span className="text-slate-400">·</span>
          <span>
            {steps.length > 0 ? `${safeIndex + 1} de ${steps.length}` : 'Sin contenido'}
          </span>
        </div>
        <div className="flex items-center gap-1 rounded-lg border border-slate-200 p-0.5">
          <button
            type="button"
            className={cn('rounded-md p-1.5', device === 'desktop' ? 'bg-[#3148c8] text-white' : 'text-slate-500 hover:bg-slate-100')}
            aria-label="Vista escritorio"
            onClick={() => setDevice('desktop')}
          >
            <ComputerDesktopIcon className="h-4 w-4" />
          </button>
          <button
            type="button"
            className={cn('rounded-md p-1.5', device === 'mobile' ? 'bg-[#3148c8] text-white' : 'text-slate-500 hover:bg-slate-100')}
            aria-label="Vista móvil"
            onClick={() => setDevice('mobile')}
          >
            <DevicePhoneMobileIcon className="h-4 w-4" />
          </button>
        </div>
        <Button type="button" variant="ghost" className="gap-1.5" onClick={onClose}>
          <XMarkIcon className="h-4 w-4" />
          Cerrar
        </Button>
      </div>

      <div className="flex min-h-0 flex-1 items-center justify-center overflow-y-auto p-6">
        <div
          className={cn(
            'w-full rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition-all sm:p-12',
            device === 'mobile' ? 'max-w-sm' : 'max-w-2xl',
          )}
        >
          {step ? <PreviewBody step={step} /> : <p className="text-center text-slate-400">No hay secciones para previsualizar.</p>}
        </div>
      </div>

      <div className="flex shrink-0 items-center justify-between gap-3 border-t border-slate-200 bg-white px-4 py-3">
        <Button type="button" variant="outline" className="gap-1.5" disabled={safeIndex === 0} onClick={() => setIndex((i) => Math.max(0, i - 1))}>
          <ArrowLeftIcon className="h-4 w-4" />
          Anterior
        </Button>
        <Button type="button" className="gap-1.5" disabled={safeIndex >= steps.length - 1} onClick={() => setIndex((i) => Math.min(steps.length - 1, i + 1))}>
          Siguiente
          <ArrowRightIcon className="h-4 w-4" />
        </Button>
      </div>
    </div>
  )
}

function PreviewBody({ step }: { step: PreviewStep }) {
  if (step.kind === 'bloque') {
    const b = step.bloque
    if (b.tipo === 'bienvenida') {
      return (
        <div className="space-y-5 text-center">
          <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">{b.titulo || 'Bienvenido'}</h2>
          {b.descripcion ? <p className="text-slate-500">{b.descripcion}</p> : null}
          {b.mensajePersonalizado ? <p className="text-sm text-slate-400">{b.mensajePersonalizado}</p> : null}
          <span className="inline-flex rounded-lg bg-[#3148c8] px-6 py-2.5 text-sm font-semibold text-white">{b.textoBoton || 'Comenzar'}</span>
        </div>
      )
    }
    if (b.tipo === 'agradecimiento') {
      return (
        <div className="space-y-4 text-center">
          <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">{b.titulo || '¡Gracias!'}</h2>
          {b.mensaje ? <p className="text-slate-500">{b.mensaje}</p> : null}
        </div>
      )
    }
    if (b.tipo === 'nps') {
      return (
        <div className="space-y-5">
          <h2 className="text-xl font-bold text-slate-900 sm:text-2xl">{b.titulo}</h2>
          {b.subtitulo ? <p className="text-slate-500">{b.subtitulo}</p> : null}
          <div className="flex flex-wrap gap-1.5">
            {Array.from({ length: 11 }, (_, n) => (
              <span key={n} className="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:border-[#3148c8] hover:text-[#3148c8]">
                {n}
              </span>
            ))}
          </div>
          <div className="flex justify-between text-xs text-slate-400">
            <span>Nada probable</span>
            <span>Muy probable</span>
          </div>
        </div>
      )
    }
    return null
  }

  const b = step.bloque
  if (b.tipo !== 'seccion') {
    return null
  }
  const q = b.preguntas[step.preguntaIndex]
  if (!q) {
    return null
  }
  return (
    <div className="space-y-5">
      <p className="text-xs font-semibold uppercase tracking-wide text-[#3148c8]">{step.seccionTitulo}</p>
      <h2 className="text-xl font-bold text-slate-900 sm:text-2xl">
        {q.titulo || 'Pregunta'}
        {q.obligatoria ? <span className="text-red-500"> *</span> : null}
      </h2>
      {q.subtitulo ? <p className="text-slate-500">{q.subtitulo}</p> : null}
      <ul className="space-y-2">
        {q.opciones.map((o, i) => (
          <li key={o.id} className="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-slate-700 hover:border-[#3148c8] hover:bg-[#3148c8]/5">
            <span className="flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 text-xs font-semibold text-slate-500">
              {String.fromCharCode(65 + i)}
            </span>
            <span>{o.titulo || `Opción ${String.fromCharCode(65 + i)}`}</span>
          </li>
        ))}
      </ul>
    </div>
  )
}
