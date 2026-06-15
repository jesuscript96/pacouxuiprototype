import { useSyncExternalStore } from 'react'

import type { CategoriaFormValues } from './CategoriaSlideOver'
import {
  CATEGORIAS_INICIALES,
  ENCUESTAS_INICIALES,
  draftToNewRow,
  duplicarEncuesta,
  empresaNombre,
  mergeDraftIntoRow,
  nextCategoriaId,
} from './encuestasMockData'
import type {
  CategoriaEncuesta,
  EncuestaDraft,
  EncuestaRow,
  EnvioEncuestaDraft,
} from './encuestasTypes'

type EncuestasState = {
  categorias: CategoriaEncuesta[]
  encuestas: EncuestaRow[]
}

let state: EncuestasState = {
  categorias: CATEGORIAS_INICIALES,
  encuestas: ENCUESTAS_INICIALES,
}

const listeners = new Set<() => void>()

function emit(next: EncuestasState): void {
  state = next
  listeners.forEach((l) => l())
}

function subscribe(listener: () => void): () => void {
  listeners.add(listener)
  return () => listeners.delete(listener)
}

function getSnapshot(): EncuestasState {
  return state
}

export const encuestasStore = {
  getState(): EncuestasState {
    return state
  },

  getEncuesta(key: string): EncuestaRow | undefined {
    return state.encuestas.find((e) => e.key === key)
  },

  addCategoria(values: CategoriaFormValues): void {
    const nueva: CategoriaEncuesta = {
      id: nextCategoriaId(),
      nombre: values.nombre,
      empresaId: values.empresaId,
      empresaNombre: empresaNombre(values.empresaId),
      creada: new Date().toISOString().slice(0, 10),
      encuestasLigadas: 0,
    }
    emit({ ...state, categorias: [...state.categorias, nueva] })
  },

  updateCategoria(id: number, values: CategoriaFormValues): void {
    emit({
      ...state,
      categorias: state.categorias.map((c) =>
        c.id === id
          ? { ...c, nombre: values.nombre, empresaId: values.empresaId, empresaNombre: empresaNombre(values.empresaId) }
          : c,
      ),
    })
  },

  removeCategoria(id: number): void {
    emit({ ...state, categorias: state.categorias.filter((c) => c.id !== id) })
  },

  /** Crea una encuesta nueva y devuelve su key. */
  addEncuesta(draft: EncuestaDraft): string {
    const row = draftToNewRow(draft, state.categorias)
    emit({ ...state, encuestas: [...state.encuestas, row] })
    return row.key
  },

  updateEncuesta(key: string, draft: EncuestaDraft): void {
    emit({
      ...state,
      encuestas: state.encuestas.map((e) => (e.key === key ? mergeDraftIntoRow(e, draft, state.categorias) : e)),
    })
  },

  duplicateEncuesta(key: string): void {
    const row = state.encuestas.find((e) => e.key === key)
    if (!row) {
      return
    }
    emit({ ...state, encuestas: [...state.encuestas, duplicarEncuesta(row)] })
  },

  setEstadoEncuesta(key: string, estado: EncuestaRow['estado']): void {
    emit({
      ...state,
      encuestas: state.encuestas.map((e) => (e.key === key ? { ...e, estado } : e)),
    })
  },

  removeEncuesta(key: string): void {
    emit({ ...state, encuestas: state.encuestas.filter((e) => e.key !== key) })
  },

  /** Registra un envío a partir de una encuesta y el draft del modal de envío. */
  registrarEnvio(encuesta: EncuestaRow, envio: EnvioEncuestaDraft, totalDestinatarios: number): void {
    const hoy = new Date().toISOString().slice(0, 10)
    emit({
      ...state,
      encuestas: state.encuestas.map((e) =>
        e.key === encuesta.key
          ? {
              ...e,
              estado: 'activa',
              envio: {
                enviados: totalDestinatarios,
                contestados: 0,
                noContestado: totalDestinatarios,
                urgente: envio.config.urgente,
                anonima: envio.config.anonima,
                recurrente: envio.config.recurrente,
                cerrada: false,
                fechaEnvio: hoy,
                vigencia: envio.config.vigencia || '—',
                vencimiento: envio.config.vigencia || '—',
              },
            }
          : e,
      ),
    })
  },

  /** Edita los datos de envío (fechas/título) de una encuesta ya enviada. */
  updateEnvio(key: string, patch: { titulo?: string; fechaEnvio?: string; vigencia?: string; vencimiento?: string }): void {
    emit({
      ...state,
      encuestas: state.encuestas.map((e) => {
        if (e.key !== key || !e.envio) {
          return e
        }
        return {
          ...e,
          titulo: patch.titulo ?? e.titulo,
          envio: {
            ...e.envio,
            fechaEnvio: patch.fechaEnvio ?? e.envio.fechaEnvio,
            vigencia: patch.vigencia ?? e.envio.vigencia,
            vencimiento: patch.vencimiento ?? e.envio.vencimiento,
          },
        }
      }),
    })
  },

  cerrarEncuesta(key: string, cerrar: boolean): void {
    emit({
      ...state,
      encuestas: state.encuestas.map((e) =>
        e.key === key && e.envio
          ? { ...e, estado: cerrar ? 'cerrada' : 'activa', envio: { ...e.envio, cerrada: cerrar } }
          : e,
      ),
    })
  },
}

export function useEncuestasStore(): EncuestasState {
  return useSyncExternalStore(subscribe, getSnapshot, getSnapshot)
}
