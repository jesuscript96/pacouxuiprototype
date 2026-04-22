import type { ReactNode } from 'react'
import type { CatalogPlainRow, CatalogTabId } from './catalogResourceMeta'
import { CATALOG_TABLE_COLUMNS } from './catalogResourceMeta'

function badgeCount(n: number) {
  return (
    <span className="inline-flex min-w-[1.75rem] justify-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
      {n}
    </span>
  )
}

function boolSiNo(valor: boolean) {
  return (
    <span className={valor ? 'text-emerald-700' : 'text-slate-400'}>{valor ? 'Sí' : 'No'}</span>
  )
}

/** Convierte una fila plana en celdas listables (badges, booleanos). */
export function catalogPlainRowToDisplayCells(
  tab: CatalogTabId,
  row: CatalogPlainRow,
): Record<string, ReactNode> {
  const cols = CATALOG_TABLE_COLUMNS[tab]
  const out: Record<string, ReactNode> = {}

  for (const c of cols) {
    const v = row[c.key]
    if (c.key === 'departamentos' || c.key === 'areas') {
      out[c.key] = badgeCount(Number(v))
      continue
    }
    if (c.key === 'agendar_cita') {
      out[c.key] = boolSiNo(Boolean(v))
      continue
    }
    if (v === undefined || v === null) {
      out[c.key] = '—'
      continue
    }
    out[c.key] = String(v)
  }

  return out
}
