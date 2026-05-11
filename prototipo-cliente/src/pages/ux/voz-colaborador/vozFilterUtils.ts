import type { VoiceThread } from './vozTypes'

export type VoiceListFilters = {
  empresaId: string
  ubicacionKey: string
  status: string
  priority: string
  categoryKey: string
  fechaDesde: string
  fechaHasta: string
}

export function emptyVoiceFilters(): VoiceListFilters {
  return {
    empresaId: '',
    ubicacionKey: '',
    status: '',
    priority: '',
    categoryKey: '',
    fechaDesde: '',
    fechaHasta: '',
  }
}

function parseDay(iso: string): number {
  const t = Date.parse(iso)
  return Number.isFinite(t) ? t : 0
}

/** Fecha `yyyy-mm-dd` del input type=date comparada al día UTC del mensaje. */
function threadDay(thread: VoiceThread): number {
  return parseDay(thread.date)
}

function dayFromInput(yyyyMmDd: string): number | null {
  if (!yyyyMmDd.trim()) {
    return null
  }
  const t = Date.parse(`${yyyyMmDd}T12:00:00Z`)
  return Number.isFinite(t) ? t : null
}

export function filterVoiceThreads(
  threads: VoiceThread[],
  filters: VoiceListFilters,
  search: string,
): VoiceThread[] {
  const q = search.trim().toLowerCase()
  const desde = dayFromInput(filters.fechaDesde)
  const hasta = dayFromInput(filters.fechaHasta)

  return threads.filter((t) => {
    if (filters.empresaId && t.empresaId !== filters.empresaId) {
      return false
    }
    if (filters.ubicacionKey && t.ubicacionKey !== filters.ubicacionKey) {
      return false
    }
    if (filters.status && t.status !== filters.status) {
      return false
    }
    if (filters.priority && t.priority !== filters.priority) {
      return false
    }
    if (filters.categoryKey) {
      const slug = CATALOG_SLUG_FROM_LABEL[t.category]
      if (slug !== filters.categoryKey) {
        return false
      }
    }
    const day = threadDay(t)
    if (desde !== null && day < desde) {
      return false
    }
    if (hasta !== null && day > hasta) {
      return false
    }
    if (!q) {
      return true
    }
    const haystack = [
      String(t.id),
      t.category,
      t.sender.name,
      t.sender.company,
      t.comment,
      t.result,
      ...t.extras.flatMap((e) => [e.comment, e.result]),
    ]
      .join(' ')
      .toLowerCase()
    return haystack.includes(q)
  })
}

/** Mapa categoría label → value del catálogo (mock estable). */
export const CATALOG_SLUG_FROM_LABEL: Record<string, string> = {
  'Conflicto de interés': 'conflicto',
  'Clima laboral': 'clima',
  Capacitación: 'capacitacion',
  'Seguridad e higiene': 'seguridad',
}
