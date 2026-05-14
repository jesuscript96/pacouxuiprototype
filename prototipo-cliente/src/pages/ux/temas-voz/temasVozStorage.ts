import {
  INITIAL_TEMAS_VOZ,
  todosLosDestinatariosIds,
  type TemaVozRow,
} from './temasVozMockData'

const STORAGE_KEY = 'proto-temas-voz-v1'

/**
 * Normaliza una fila que podría venir de una versión anterior del prototipo
 * (sin `destinatarioIds`). Si falta, se rellena con todos los destinatarios
 * del segmento — mantiene la regla «inicialmente todos seleccionados».
 */
function normalizeRow(row: TemaVozRow): TemaVozRow {
  if (Array.isArray(row.destinatarioIds)) {
    return row
  }
  return {
    ...row,
    destinatarioIds: todosLosDestinatariosIds(row.empresaId ?? null),
  }
}

export function loadTemasVoz(): TemaVozRow[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) {
      return [...INITIAL_TEMAS_VOZ]
    }
    const parsed = JSON.parse(raw) as TemaVozRow[]
    if (!Array.isArray(parsed) || parsed.length === 0) {
      return [...INITIAL_TEMAS_VOZ]
    }
    return parsed.map(normalizeRow)
  } catch {
    return [...INITIAL_TEMAS_VOZ]
  }
}

export function saveTemasVoz(rows: TemaVozRow[]): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(rows))
  } catch {
    /* ignore quota errors en demo */
  }
}

export function nextTemaVozId(rows: TemaVozRow[]): number {
  if (rows.length === 0) {
    return 1
  }
  return rows.reduce((max, r) => Math.max(max, r.id), 0) + 1
}
