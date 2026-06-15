import {
  ChevronDownIcon,
  ChevronUpIcon,
  DocumentDuplicateIcon,
  EllipsisHorizontalIcon,
  FlagIcon,
  HandRaisedIcon,
  PlusIcon,
  QueueListIcon,
  StarIcon,
  TrashIcon,
} from '@heroicons/react/24/outline'
import type { ComponentType } from 'react'

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { cn } from '@/lib/utils'

import type { BloqueFormulario } from '../encuestasTypes'
import { bloqueIncompleto, preguntaIncompleta, type ActiveSelection } from './builderHelpers'

const ICONO: Record<BloqueFormulario['tipo'], ComponentType<{ className?: string }>> = {
  bienvenida: HandRaisedIcon,
  seccion: QueueListIcon,
  nps: StarIcon,
  agradecimiento: FlagIcon,
}

type Props = {
  bloques: BloqueFormulario[]
  active: ActiveSelection
  onSelectBloque: (blockId: string) => void
  onSelectPregunta: (blockId: string, questionId: string) => void
  onAddPregunta: (blockId: string) => void
  onMoveBloque: (blockId: string, dir: -1 | 1) => void
  onDuplicateBloque: (blockId: string) => void
  onRemoveBloque: (blockId: string) => void
  onMovePregunta: (blockId: string, questionId: string, dir: -1 | 1) => void
  onDuplicatePregunta: (blockId: string, questionId: string) => void
  onRemovePregunta: (blockId: string, questionId: string) => void
}

export function BloqueList({
  bloques,
  active,
  onSelectBloque,
  onSelectPregunta,
  onAddPregunta,
  onMoveBloque,
  onDuplicateBloque,
  onRemoveBloque,
  onMovePregunta,
  onDuplicatePregunta,
  onRemovePregunta,
}: Props) {
  const seccionNumeros = new Map<string, number>()
  let contador = 0
  for (const b of bloques) {
    if (b.tipo === 'seccion') {
      contador += 1
      seccionNumeros.set(b.id, contador)
    }
  }

  return (
    <div className="flex h-full flex-col">
      <div className="flex shrink-0 items-center justify-between border-b border-slate-200 px-4 py-3">
        <h2 className="text-xs font-semibold uppercase tracking-wide text-slate-500">Secciones</h2>
        <span className="text-xs text-slate-400">{bloques.length}</span>
      </div>

      <ol className="min-h-0 flex-1 space-y-1 overflow-y-auto p-3">
        {bloques.map((b) => {
          const Icon = ICONO[b.tipo]
          const isActive = active.blockId === b.id && active.questionId === null
          const incompleto = bloqueIncompleto(b)
          const movable = b.tipo === 'seccion' || b.tipo === 'nps'
          const numero = b.tipo === 'seccion' ? String(seccionNumeros.get(b.id) ?? '') : ''

          return (
            <li key={b.id}>
              <div
                className={cn(
                  'group flex items-center gap-2 rounded-lg border px-2.5 py-2 transition-colors',
                  isActive
                    ? 'border-[#3148c8]/30 bg-[#3148c8]/10'
                    : 'border-transparent hover:bg-slate-50',
                )}
              >
                <button
                  type="button"
                  className="flex min-w-0 flex-1 items-center gap-2 text-left"
                  onClick={() => onSelectBloque(b.id)}
                >
                  <span
                    className={cn(
                      'flex h-7 w-7 shrink-0 items-center justify-center rounded-md',
                      isActive ? 'bg-[#3148c8] text-white' : 'bg-slate-100 text-slate-500',
                    )}
                  >
                    <Icon className="h-4 w-4" />
                  </span>
                  <span className="min-w-0">
                    <span className={cn('block truncate text-sm font-medium', isActive ? 'text-[#3148c8]' : 'text-slate-800')}>
                      {numero ? `${numero}. ` : ''}
                      {b.titulo || tipoLabel(b.tipo)}
                    </span>
                    <span className="block text-[11px] text-slate-400">{tipoLabel(b.tipo)}</span>
                  </span>
                  {incompleto ? (
                    <span className="ml-auto h-2 w-2 shrink-0 rounded-full bg-amber-400" title="Sección incompleta" />
                  ) : null}
                </button>

                {movable ? (
                  <div className="flex shrink-0 items-center opacity-0 transition-opacity group-hover:opacity-100">
                    <button
                      type="button"
                      className="rounded p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-700"
                      aria-label="Subir sección"
                      onClick={() => onMoveBloque(b.id, -1)}
                    >
                      <ChevronUpIcon className="h-4 w-4" />
                    </button>
                    <button
                      type="button"
                      className="rounded p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-700"
                      aria-label="Bajar sección"
                      onClick={() => onMoveBloque(b.id, 1)}
                    >
                      <ChevronDownIcon className="h-4 w-4" />
                    </button>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <button
                          type="button"
                          className="rounded p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-700"
                          aria-label="Más acciones de la sección"
                        >
                          <EllipsisHorizontalIcon className="h-4 w-4" />
                        </button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" className="min-w-[11rem]">
                        <DropdownMenuItem className="gap-2" onClick={() => onDuplicateBloque(b.id)}>
                          <DocumentDuplicateIcon className="h-4 w-4 text-slate-500" />
                          Duplicar
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem variant="destructive" className="gap-2" onClick={() => onRemoveBloque(b.id)}>
                          <TrashIcon className="h-4 w-4" />
                          Eliminar
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                ) : null}
              </div>

              {b.tipo === 'seccion' ? (
                <div className="ml-4 mt-1 space-y-1 border-l border-slate-200 pl-3">
                  {b.preguntas.map((q, qi) => {
                    const qActive = active.blockId === b.id && active.questionId === q.id
                    return (
                      <div
                        key={q.id}
                        className={cn(
                          'group flex items-center gap-2 rounded-md px-2 py-1.5 transition-colors',
                          qActive ? 'bg-[#3148c8]/10' : 'hover:bg-slate-50',
                        )}
                      >
                        <button
                          type="button"
                          className="flex min-w-0 flex-1 items-center gap-2 text-left"
                          onClick={() => onSelectPregunta(b.id, q.id)}
                        >
                          <span className={cn('shrink-0 text-[11px] font-semibold tabular-nums', qActive ? 'text-[#3148c8]' : 'text-slate-400')}>
                            {numero}.{qi + 1}
                          </span>
                          <span className={cn('truncate text-sm', qActive ? 'text-[#3148c8]' : 'text-slate-700')}>
                            {q.titulo || 'Pregunta sin título'}
                          </span>
                          {preguntaIncompleta(q) ? (
                            <span className="ml-auto h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400" title="Pregunta incompleta" />
                          ) : null}
                        </button>
                        <div className="flex shrink-0 items-center opacity-0 transition-opacity group-hover:opacity-100">
                          <button type="button" className="rounded p-0.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700" aria-label="Subir pregunta" onClick={() => onMovePregunta(b.id, q.id, -1)}>
                            <ChevronUpIcon className="h-3.5 w-3.5" />
                          </button>
                          <button type="button" className="rounded p-0.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700" aria-label="Bajar pregunta" onClick={() => onMovePregunta(b.id, q.id, 1)}>
                            <ChevronDownIcon className="h-3.5 w-3.5" />
                          </button>
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <button type="button" className="rounded p-0.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700" aria-label="Más acciones de la pregunta">
                                <EllipsisHorizontalIcon className="h-3.5 w-3.5" />
                              </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="min-w-[11rem]">
                              <DropdownMenuItem className="gap-2" onClick={() => onDuplicatePregunta(b.id, q.id)}>
                                <DocumentDuplicateIcon className="h-4 w-4 text-slate-500" />
                                Duplicar
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem variant="destructive" className="gap-2" onClick={() => onRemovePregunta(b.id, q.id)}>
                                <TrashIcon className="h-4 w-4" />
                                Eliminar
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </div>
                      </div>
                    )
                  })}
                  <button
                    type="button"
                    className="flex w-full items-center gap-1.5 rounded-md px-2 py-1.5 text-left text-xs font-semibold text-[#3148c8] hover:bg-[#3148c8]/5"
                    onClick={() => onAddPregunta(b.id)}
                  >
                    <PlusIcon className="h-3.5 w-3.5" />
                    Agregar pregunta
                  </button>
                </div>
              ) : null}
            </li>
          )
        })}
      </ol>
    </div>
  )
}

function tipoLabel(tipo: BloqueFormulario['tipo']): string {
  return {
    bienvenida: 'Bienvenida',
    seccion: 'Sección',
    nps: 'Pregunta NPS',
    agradecimiento: 'Agradecimiento',
  }[tipo]
}
