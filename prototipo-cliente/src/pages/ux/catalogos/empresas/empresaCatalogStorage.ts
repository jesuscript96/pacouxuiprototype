import { initialEmpresaCatalogRecords } from './empresaCatalogMock'
import type { EmpresaCatalogRecord } from './empresaWizardTypes'

const STORAGE_KEY = 'proto-empresas-catalog-v1'

export function loadEmpresaCatalog(): EmpresaCatalogRecord[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) {
      return initialEmpresaCatalogRecords()
    }
    const parsed = JSON.parse(raw) as EmpresaCatalogRecord[]
    if (!Array.isArray(parsed) || parsed.length === 0) {
      return initialEmpresaCatalogRecords()
    }
    return parsed
  } catch {
    return initialEmpresaCatalogRecords()
  }
}

export function saveEmpresaCatalog(rows: EmpresaCatalogRecord[]): void {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(rows))
}

export function upsertEmpresaRecord(record: EmpresaCatalogRecord): void {
  const rows = loadEmpresaCatalog()
  const idx = rows.findIndex((r) => r.id === record.id)
  if (idx === -1) {
    saveEmpresaCatalog([...rows, record])
  } else {
    const next = [...rows]
    next[idx] = record
    saveEmpresaCatalog(next)
  }
}

export function deleteEmpresaRecord(id: string): void {
  const rows = loadEmpresaCatalog().filter((r) => r.id !== id)
  saveEmpresaCatalog(rows)
}

export function getEmpresaRecordById(id: string): EmpresaCatalogRecord | undefined {
  return loadEmpresaCatalog().find((r) => r.id === id)
}

export function newEmpresaId(): string {
  return `emp-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`
}
