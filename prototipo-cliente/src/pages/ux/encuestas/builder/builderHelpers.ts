import {
  emptyOpcion,
  emptyPregunta,
  nextBloqueId,
} from '../encuestasMockData'
import type {
  BloqueFormulario,
  BloqueSeccion,
  EncuestaDraft,
  OpcionRespuesta,
  PreguntaEncuesta,
} from '../encuestasTypes'

export type ActiveSelection = { blockId: string; questionId: string | null }

export function findBloque(draft: EncuestaDraft, blockId: string): BloqueFormulario | undefined {
  return draft.bloques.find((b) => b.id === blockId)
}

export function findPregunta(
  draft: EncuestaDraft,
  blockId: string,
  questionId: string,
): PreguntaEncuesta | undefined {
  const b = findBloque(draft, blockId)
  if (b?.tipo === 'seccion') {
    return b.preguntas.find((q) => q.id === questionId)
  }
  return undefined
}

function mapBloques(
  draft: EncuestaDraft,
  fn: (bloques: BloqueFormulario[]) => BloqueFormulario[],
): EncuestaDraft {
  return { ...draft, bloques: fn(draft.bloques) }
}

export function updateBloque(
  draft: EncuestaDraft,
  blockId: string,
  patch: Partial<BloqueFormulario>,
): EncuestaDraft {
  return mapBloques(draft, (bloques) =>
    bloques.map((b) => (b.id === blockId ? ({ ...b, ...patch } as BloqueFormulario) : b)),
  )
}

export function updateSeccion(
  draft: EncuestaDraft,
  blockId: string,
  updater: (s: BloqueSeccion) => BloqueSeccion,
): EncuestaDraft {
  return mapBloques(draft, (bloques) =>
    bloques.map((b) => (b.id === blockId && b.tipo === 'seccion' ? updater(b) : b)),
  )
}

export function updatePregunta(
  draft: EncuestaDraft,
  blockId: string,
  questionId: string,
  patch: Partial<PreguntaEncuesta>,
): EncuestaDraft {
  return updateSeccion(draft, blockId, (s) => ({
    ...s,
    preguntas: s.preguntas.map((q) => (q.id === questionId ? { ...q, ...patch } : q)),
  }))
}

export function addPregunta(draft: EncuestaDraft, blockId: string): { draft: EncuestaDraft; questionId: string } {
  const nueva = emptyPregunta()
  return {
    questionId: nueva.id,
    draft: updateSeccion(draft, blockId, (s) => ({ ...s, preguntas: [...s.preguntas, nueva] })),
  }
}

export function removePregunta(draft: EncuestaDraft, blockId: string, questionId: string): EncuestaDraft {
  return updateSeccion(draft, blockId, (s) => ({
    ...s,
    preguntas: s.preguntas.filter((q) => q.id !== questionId),
  }))
}

export function duplicatePregunta(draft: EncuestaDraft, blockId: string, questionId: string): EncuestaDraft {
  return updateSeccion(draft, blockId, (s) => {
    const idx = s.preguntas.findIndex((q) => q.id === questionId)
    if (idx === -1) {
      return s
    }
    const orig = s.preguntas[idx]!
    const copia: PreguntaEncuesta = {
      ...orig,
      id: nextBloqueId('q'),
      opciones: orig.opciones.map((o) => ({ ...o, id: nextBloqueId('opt') })),
    }
    const preguntas = [...s.preguntas]
    preguntas.splice(idx + 1, 0, copia)
    return { ...s, preguntas }
  })
}

export function movePregunta(draft: EncuestaDraft, blockId: string, questionId: string, dir: -1 | 1): EncuestaDraft {
  return updateSeccion(draft, blockId, (s) => {
    const idx = s.preguntas.findIndex((q) => q.id === questionId)
    const target = idx + dir
    if (idx === -1 || target < 0 || target >= s.preguntas.length) {
      return s
    }
    const preguntas = [...s.preguntas]
    const [item] = preguntas.splice(idx, 1)
    preguntas.splice(target, 0, item!)
    return { ...s, preguntas }
  })
}

export function addOpcion(draft: EncuestaDraft, blockId: string, questionId: string): EncuestaDraft {
  return updateSeccion(draft, blockId, (s) => ({
    ...s,
    preguntas: s.preguntas.map((q) =>
      q.id === questionId ? { ...q, opciones: [...q.opciones, emptyOpcion()] } : q,
    ),
  }))
}

export function updateOpcion(
  draft: EncuestaDraft,
  blockId: string,
  questionId: string,
  opcionId: string,
  patch: Partial<OpcionRespuesta>,
): EncuestaDraft {
  return updateSeccion(draft, blockId, (s) => ({
    ...s,
    preguntas: s.preguntas.map((q) =>
      q.id === questionId
        ? { ...q, opciones: q.opciones.map((o) => (o.id === opcionId ? { ...o, ...patch } : o)) }
        : q,
    ),
  }))
}

export function removeOpcion(draft: EncuestaDraft, blockId: string, questionId: string, opcionId: string): EncuestaDraft {
  return updateSeccion(draft, blockId, (s) => ({
    ...s,
    preguntas: s.preguntas.map((q) =>
      q.id === questionId ? { ...q, opciones: q.opciones.filter((o) => o.id !== opcionId) } : q,
    ),
  }))
}

/** Inserta un bloque nuevo justo antes del bloque de agradecimiento (si existe). */
export function insertBloque(draft: EncuestaDraft, nuevo: BloqueFormulario): EncuestaDraft {
  const idxGracias = draft.bloques.findIndex((b) => b.tipo === 'agradecimiento')
  const bloques = [...draft.bloques]
  if (idxGracias === -1) {
    bloques.push(nuevo)
  } else {
    bloques.splice(idxGracias, 0, nuevo)
  }
  return { ...draft, bloques }
}

export function removeBloque(draft: EncuestaDraft, blockId: string): EncuestaDraft {
  return mapBloques(draft, (bloques) => bloques.filter((b) => b.id !== blockId))
}

export function duplicateBloque(draft: EncuestaDraft, blockId: string): EncuestaDraft {
  return mapBloques(draft, (bloques) => {
    const idx = bloques.findIndex((b) => b.id === blockId)
    if (idx === -1) {
      return bloques
    }
    const orig = bloques[idx]!
    let copia: BloqueFormulario
    if (orig.tipo === 'seccion') {
      copia = {
        ...orig,
        id: nextBloqueId('sec'),
        preguntas: orig.preguntas.map((q) => ({
          ...q,
          id: nextBloqueId('q'),
          opciones: q.opciones.map((o) => ({ ...o, id: nextBloqueId('opt') })),
        })),
      }
    } else {
      copia = { ...orig, id: nextBloqueId(orig.tipo) }
    }
    const next = [...bloques]
    next.splice(idx + 1, 0, copia)
    return next
  })
}

/** Mueve un bloque, sin sacar bienvenida del inicio ni agradecimiento del final. */
export function moveBloque(draft: EncuestaDraft, blockId: string, dir: -1 | 1): EncuestaDraft {
  return mapBloques(draft, (bloques) => {
    const idx = bloques.findIndex((b) => b.id === blockId)
    const target = idx + dir
    if (idx === -1 || target < 0 || target >= bloques.length) {
      return bloques
    }
    const moving = bloques[idx]!
    const swap = bloques[target]!
    if (moving.tipo === 'bienvenida' || moving.tipo === 'agradecimiento') {
      return bloques
    }
    if (swap.tipo === 'bienvenida' || swap.tipo === 'agradecimiento') {
      return bloques
    }
    const next = [...bloques]
    next[idx] = swap
    next[target] = moving
    return next
  })
}

export function contarSecciones(draft: EncuestaDraft): number {
  return draft.bloques.filter((b) => b.tipo === 'seccion').length
}

/**
 * Secuencia lineal para navegar el formulario desde el panel central:
 * cada bloque es un paso; las secciones añaden un paso por pregunta.
 */
export function buildNavSequence(draft: EncuestaDraft): ActiveSelection[] {
  const seq: ActiveSelection[] = []
  for (const b of draft.bloques) {
    seq.push({ blockId: b.id, questionId: null })
    if (b.tipo === 'seccion') {
      for (const p of b.preguntas) {
        seq.push({ blockId: b.id, questionId: p.id })
      }
    }
  }
  return seq
}

/** Valida un bloque para el indicador de estado (punto ámbar). */
export function bloqueIncompleto(b: BloqueFormulario): boolean {
  switch (b.tipo) {
    case 'bienvenida':
      return !b.titulo.trim()
    case 'agradecimiento':
      return !b.titulo.trim()
    case 'nps':
      return !b.titulo.trim()
    case 'seccion':
      return !b.titulo.trim() || b.preguntas.length === 0 || b.preguntas.some(preguntaIncompleta)
  }
}

export function preguntaIncompleta(q: PreguntaEncuesta): boolean {
  return !q.titulo.trim() || q.opciones.length < 2 || q.opciones.some((o) => !o.titulo.trim())
}
