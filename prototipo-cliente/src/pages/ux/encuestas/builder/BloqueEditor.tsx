import { ArrowLeftIcon, ArrowRightIcon, PencilSquareIcon, PlusIcon, TrashIcon } from '@heroicons/react/24/outline'
import type { ReactNode } from 'react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

import type {
  BloqueFormulario,
  OpcionRespuesta,
  PreguntaEncuesta,
} from '../encuestasTypes'

const titleClass =
  'w-full resize-none border-0 bg-transparent p-0 pr-6 text-2xl font-bold leading-snug text-slate-900 outline-none placeholder:text-slate-300 focus:ring-0 sm:text-3xl'
const subtitleClass =
  'w-full resize-none border-0 bg-transparent p-0 pr-6 text-base leading-relaxed text-slate-500 outline-none placeholder:text-slate-300 focus:ring-0'

/** Envoltorio que evidencia que el contenido es editable (resalte + lápiz). */
function Editable({ children, center }: { children: ReactNode; center?: boolean }) {
  return (
    <div
      className={cn(
        'group/edit relative -mx-2 cursor-text rounded-xl px-2 py-1.5 ring-1 ring-transparent transition-colors hover:bg-slate-50 hover:ring-slate-200 focus-within:bg-slate-50 focus-within:ring-[#3148c8]/30',
        center && 'text-center',
      )}
    >
      {children}
      <PencilSquareIcon className="pointer-events-none absolute right-2 top-2 h-4 w-4 text-slate-300 transition-colors group-hover/edit:text-slate-400 group-focus-within/edit:text-[#3148c8]" />
    </div>
  )
}

type Props = {
  bloque: BloqueFormulario | undefined
  pregunta: PreguntaEncuesta | undefined
  navIndex: number
  navTotal: number
  onPrev: () => void
  onNext: () => void
  onUpdateBloque: (blockId: string, patch: Partial<BloqueFormulario>) => void
  onUpdatePregunta: (blockId: string, questionId: string, patch: Partial<PreguntaEncuesta>) => void
  onAddOpcion: (blockId: string, questionId: string) => void
  onUpdateOpcion: (blockId: string, questionId: string, opcionId: string, patch: Partial<OpcionRespuesta>) => void
  onRemoveOpcion: (blockId: string, questionId: string, opcionId: string) => void
  onSelectPregunta: (blockId: string, questionId: string) => void
  onAddPregunta: (blockId: string) => void
}

function Letra({ i }: { i: number }) {
  return (
    <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-slate-300 bg-white text-xs font-semibold text-slate-500">
      {String.fromCharCode(65 + i)}
    </span>
  )
}

export function BloqueEditor({
  bloque,
  pregunta,
  navIndex,
  navTotal,
  onPrev,
  onNext,
  onUpdateBloque,
  onUpdatePregunta,
  onAddOpcion,
  onUpdateOpcion,
  onRemoveOpcion,
  onSelectPregunta,
  onAddPregunta,
}: Props) {
  if (!bloque) {
    return (
      <div className="flex h-full items-center justify-center p-8 text-center text-sm text-slate-400">
        Selecciona una sección a la izquierda para editarla.
      </div>
    )
  }

  return (
    <div className="mx-auto flex min-h-full w-full max-w-2xl flex-col justify-center px-6 py-10">
      <div className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <div className="mb-5 flex items-center justify-center gap-1.5 text-xs font-medium text-slate-400">
          <PencilSquareIcon className="h-3.5 w-3.5" />
          Toca cualquier texto para editarlo
        </div>
        {bloque.tipo === 'bienvenida' ? (
          <div className="space-y-3 text-center">
            <p className="text-xs font-semibold uppercase tracking-wide text-[#3148c8]">Pantalla de bienvenida</p>
            <Editable center>
              <textarea
                rows={2}
                className={cn(titleClass, 'text-center')}
                value={bloque.titulo}
                onChange={(e) => onUpdateBloque(bloque.id, { titulo: e.target.value })}
                placeholder="Título de bienvenida"
              />
            </Editable>
            <Editable center>
              <textarea
                rows={2}
                className={cn(subtitleClass, 'text-center')}
                value={bloque.descripcion}
                onChange={(e) => onUpdateBloque(bloque.id, { descripcion: e.target.value })}
                placeholder="Descripción / instrucciones de llenado"
              />
            </Editable>
            <Editable center>
              <textarea
                rows={2}
                className={cn(subtitleClass, 'text-center text-sm')}
                value={bloque.mensajePersonalizado}
                onChange={(e) => onUpdateBloque(bloque.id, { mensajePersonalizado: e.target.value })}
                placeholder="Mensaje personalizado (opcional)"
              />
            </Editable>
            <div className="pt-2">
              <span className="inline-flex items-center rounded-lg bg-[#3148c8] px-5 py-2.5 text-sm font-semibold text-white">
                {bloque.textoBoton || 'Comenzar'}
              </span>
            </div>
          </div>
        ) : null}

        {bloque.tipo === 'agradecimiento' ? (
          <div className="space-y-3 text-center">
            <p className="text-xs font-semibold uppercase tracking-wide text-emerald-600">Pantalla de agradecimiento</p>
            <Editable center>
              <textarea
                rows={2}
                className={cn(titleClass, 'text-center')}
                value={bloque.titulo}
                onChange={(e) => onUpdateBloque(bloque.id, { titulo: e.target.value })}
                placeholder="Título de agradecimiento"
              />
            </Editable>
            <Editable center>
              <textarea
                rows={2}
                className={cn(subtitleClass, 'text-center')}
                value={bloque.mensaje}
                onChange={(e) => onUpdateBloque(bloque.id, { mensaje: e.target.value })}
                placeholder="Mensaje de cierre"
              />
            </Editable>
          </div>
        ) : null}

        {bloque.tipo === 'nps' ? (
          <div className="space-y-5">
            <p className="text-xs font-semibold uppercase tracking-wide text-[#3148c8]">Pregunta NPS / Satisfacción</p>
            <Editable>
              <textarea
                rows={2}
                className={titleClass}
                value={bloque.titulo}
                onChange={(e) => onUpdateBloque(bloque.id, { titulo: e.target.value })}
                placeholder="¿Qué tan probable es que recomiendes…?"
              />
            </Editable>
            <Editable>
              <textarea
                rows={1}
                className={subtitleClass}
                value={bloque.subtitulo}
                onChange={(e) => onUpdateBloque(bloque.id, { subtitulo: e.target.value })}
                placeholder="Subtítulo (opcional)"
              />
            </Editable>
            <div className="flex flex-wrap gap-1.5 pt-2">
              {Array.from({ length: 11 }, (_, n) => (
                <span
                  key={n}
                  className="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-sm font-semibold text-slate-500"
                >
                  {n}
                </span>
              ))}
            </div>
            <div className="flex justify-between text-xs text-slate-400">
              <span>Nada probable</span>
              <span>Muy probable</span>
            </div>
          </div>
        ) : null}

        {bloque.tipo === 'seccion' && !pregunta ? (
          <div className="space-y-5">
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Sección</p>
            <Editable>
              <textarea
                rows={1}
                className={titleClass}
                value={bloque.titulo}
                onChange={(e) => onUpdateBloque(bloque.id, { titulo: e.target.value })}
                placeholder="Título de la sección"
              />
            </Editable>
            <p className="text-sm text-slate-500">
              Ponderación de la sección: <strong className="text-slate-700">{bloque.ponderacion || '0'}%</strong>. Ajusta los detalles en el panel derecho.
            </p>
            <div className="space-y-2">
              {bloque.preguntas.map((q, i) => (
                <button
                  key={q.id}
                  type="button"
                  className="flex w-full items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-left transition-colors hover:border-[#3148c8]/40 hover:bg-[#3148c8]/5"
                  onClick={() => onSelectPregunta(bloque.id, q.id)}
                >
                  <span className="text-sm font-semibold tabular-nums text-slate-400">{i + 1}</span>
                  <span className="truncate text-sm text-slate-700">{q.titulo || 'Pregunta sin título'}</span>
                </button>
              ))}
              <Button type="button" variant="outline" className="gap-1.5" onClick={() => onAddPregunta(bloque.id)}>
                <PlusIcon className="h-4 w-4" />
                Agregar pregunta
              </Button>
            </div>
          </div>
        ) : null}

        {bloque.tipo === 'seccion' && pregunta ? (
          <div className="space-y-5">
            <p className="text-xs font-semibold uppercase tracking-wide text-[#3148c8]">Pregunta de opción múltiple</p>
            <Editable>
              <textarea
                rows={2}
                className={titleClass}
                value={pregunta.titulo}
                onChange={(e) => onUpdatePregunta(bloque.id, pregunta.id, { titulo: e.target.value })}
                placeholder="Escribe la pregunta…"
              />
            </Editable>
            <Editable>
              <textarea
                rows={1}
                className={subtitleClass}
                value={pregunta.subtitulo}
                onChange={(e) => onUpdatePregunta(bloque.id, pregunta.id, { subtitulo: e.target.value })}
                placeholder="Subtítulo (opcional)"
              />
            </Editable>
            <ul className="space-y-2 pt-1">
              {pregunta.opciones.map((o, i) => (
                <li
                  key={o.id}
                  className="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 focus-within:border-[#3148c8]/40"
                >
                  <Letra i={i} />
                  <input
                    className="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-800 outline-none placeholder:text-slate-300 focus:ring-0"
                    value={o.titulo}
                    onChange={(e) => onUpdateOpcion(bloque.id, pregunta.id, o.id, { titulo: e.target.value })}
                    placeholder={`Opción ${String.fromCharCode(65 + i)}`}
                  />
                  <div className="flex shrink-0 items-center gap-1">
                    <span className="text-[11px] uppercase tracking-wide text-slate-400">Valor</span>
                    <input
                      type="number"
                      className="w-14 rounded-md border border-slate-200 px-2 py-1 text-sm text-slate-700 outline-none focus:border-[#3148c8]/40 focus:ring-0"
                      value={o.valor}
                      onChange={(e) => onUpdateOpcion(bloque.id, pregunta.id, o.id, { valor: e.target.value })}
                    />
                  </div>
                  <button
                    type="button"
                    className="rounded p-1 text-slate-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-30"
                    aria-label="Quitar opción"
                    disabled={pregunta.opciones.length <= 2}
                    onClick={() => onRemoveOpcion(bloque.id, pregunta.id, o.id)}
                  >
                    <TrashIcon className="h-4 w-4" />
                  </button>
                </li>
              ))}
            </ul>
            <Button type="button" variant="outline" className="gap-1.5" onClick={() => onAddOpcion(bloque.id, pregunta.id)}>
              <PlusIcon className="h-4 w-4" />
              Agregar opción
            </Button>
          </div>
        ) : null}
      </div>

      <div className="mt-5 flex items-center justify-between gap-3">
        <Button type="button" variant="outline" className="gap-1.5" disabled={navIndex <= 0} onClick={onPrev}>
          <ArrowLeftIcon className="h-4 w-4" />
          Anterior
        </Button>
        <span className="text-xs text-slate-400">
          {navTotal > 0 && navIndex >= 0 ? `${navIndex + 1} de ${navTotal}` : '—'}
        </span>
        <Button type="button" variant="outline" className="gap-1.5" disabled={navIndex < 0 || navIndex >= navTotal - 1} onClick={onNext}>
          Siguiente
          <ArrowRightIcon className="h-4 w-4" />
        </Button>
      </div>

      <p className="mt-4 text-center text-xs text-slate-400">
        Vista previa de cómo verá la sección quien responde · edita directamente sobre el contenido
      </p>
    </div>
  )
}
