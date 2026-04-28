import { CATALOG_RESOURCE_META, type CatalogPlainRow, type CatalogTabId } from './catalogResourceMeta'

const LABEL_DEP_GRAL: Record<string, string> = {
  '1': 'Comercial',
  '2': 'Operativo',
}

const LABEL_AREA_GRAL: Record<string, string> = {
  '1': 'Comercial',
  '2': 'Servicio',
  '3': 'Administración',
}

const LABEL_PUESTO_GRAL: Record<string, string> = {
  '1': 'Ejecutivo comercial',
  '2': 'Analista',
}

function nombrePorId(rows: CatalogPlainRow[] | undefined, id: string): string {
  if (!rows?.length || !id) {
    return ''
  }
  const row = rows.find((r) => String(r.id) === String(id))

  return row?.nombre !== undefined ? String(row.nombre) : ''
}

/** Valores de formulario (todos string; checkboxes como 'true' | 'false'). */
export type CatalogFormState = Record<string, string>

export function catalogPlainRowToFormState(row: CatalogPlainRow): CatalogFormState {
  const s: CatalogFormState = {}
  for (const [k, v] of Object.entries(row)) {
    if (k === 'id') {
      continue
    }
    if (typeof v === 'boolean') {
      s[k] = v ? 'true' : 'false'
    } else {
      s[k] = String(v)
    }
  }
  return s
}

/** Solo campos presentes en el formulario Filament (para abrir editar / ver). */
export function catalogRowToFormStateForMeta(
  tab: CatalogTabId,
  row: CatalogPlainRow,
): CatalogFormState {
  const meta = CATALOG_RESOURCE_META[tab]
  const defaults = emptyFormDefaults(tab)
  const s: CatalogFormState = { ...defaults }
  for (const f of meta.formFields) {
    const k = f.key
    if (row[k] !== undefined && row[k] !== null) {
      const v = row[k]
      s[k] = typeof v === 'boolean' ? (v ? 'true' : 'false') : String(v)
    }
  }

  return s
}

function nextNumericId(rows: CatalogPlainRow[]): string {
  const nums = rows.map((r) => parseInt(String(r.id), 10)).filter((n) => !Number.isNaN(n))
  const m = nums.length ? Math.max(...nums) : 0

  return String(m + 1)
}

/**
 * Construye fila plana desde el formulario del panel lateral.
 * Mantiene etiquetas desnormalizadas donde el listado las muestra como en Filament.
 */
export function catalogFormStateToPlainRow(
  tab: CatalogTabId,
  form: CatalogFormState,
  options: {
    id: string | null
    existingRows: CatalogPlainRow[]
    /** Filas por pestaña para etiquetas desnormalizadas (p. ej. tras alta rápida). */
    rowsByTab?: Partial<Record<CatalogTabId, CatalogPlainRow[]>>
  },
): CatalogPlainRow {
  const id = options.id ?? nextNumericId(options.existingRows)
  const rb = options.rowsByTab

  switch (tab) {
    case 'regiones':
      return { id, nombre: form.nombre?.trim() ?? '' }
    case 'departamentos': {
      const dgId = form.departamento_general_id ?? ''
      const dgLabel =
        dgId && rb?.departamentos_generales?.length
          ? nombrePorId(rb.departamentos_generales, dgId)
          : dgId
            ? (LABEL_DEP_GRAL[dgId] ?? '')
            : ''
      return {
        id,
        nombre: form.nombre?.trim() ?? '',
        empresa: form.empresa ?? 'Acme SA',
        departamento_general_id: dgId,
        departamento_general: dgLabel,
      }
    }
    case 'departamentos_generales':
      return {
        id,
        nombre: form.nombre?.trim() ?? '',
        departamentos: options.id
          ? Number(options.existingRows.find((r) => String(r.id) === id)?.departamentos ?? 0)
          : 0,
      }
    case 'areas': {
      const agId = form.area_general_id ?? ''
      const agLabel =
        agId && rb?.areas_generales?.length
          ? nombrePorId(rb.areas_generales, agId)
          : agId
            ? (LABEL_AREA_GRAL[agId] ?? '')
            : ''
      return {
        id,
        nombre: form.nombre?.trim() ?? '',
        area_general_id: agId,
        area_general: agLabel,
      }
    }
    case 'areas_generales':
      return {
        id,
        nombre: form.nombre?.trim() ?? '',
        areas: options.id
          ? Number(options.existingRows.find((r) => String(r.id) === id)?.areas ?? 0)
          : 0,
      }
    case 'puestos': {
      const pgId = form.puesto_general_id ?? ''
      const agId = form.area_general_id ?? ''
      const pgLabel =
        pgId && rb?.puestos_generales?.length
          ? nombrePorId(rb.puestos_generales, pgId)
          : pgId
            ? (LABEL_PUESTO_GRAL[pgId] ?? '')
            : ''
      const agLabel =
        agId && rb?.areas_generales?.length
          ? nombrePorId(rb.areas_generales, agId)
          : agId
            ? (LABEL_AREA_GRAL[agId] ?? '')
            : ''
      return {
        id,
        nombre: form.nombre?.trim() ?? '',
        puesto_general_id: pgId,
        puesto_general: pgLabel,
        area_general_id: agId,
        area_general: agLabel,
        ocupacion: form.ocupacion?.trim() ?? '',
      }
    }
    case 'puestos_generales':
      return { id, nombre: form.nombre?.trim() ?? '' }
    case 'centros_pago':
      return {
        id,
        nombre: form.nombre?.trim() ?? '',
        empresa: form.empresa ?? 'Acme SA',
        registro_patronal: form.registro_patronal?.trim() ?? '',
      }
    case 'ubicaciones':
      return {
        id,
        nombre: form.nombre?.trim() ?? '',
        empresa: form.empresa ?? 'Acme SA',
        cp: form.cp?.trim() ?? '',
        agendar_cita: form.agendar_cita === 'true',
      }
    default:
      return { id }
  }
}

export function emptyFormDefaults(tab: CatalogTabId): CatalogFormState {
  switch (tab) {
    case 'regiones':
      return { nombre: '' }
    case 'departamentos':
      return { nombre: '', empresa: 'Acme SA', departamento_general_id: '' }
    case 'departamentos_generales':
      return { nombre: '' }
    case 'areas':
      return { nombre: '', area_general_id: '1' }
    case 'areas_generales':
      return { nombre: '' }
    case 'puestos':
      return { nombre: '', puesto_general_id: '1', area_general_id: '1', ocupacion: '' }
    case 'puestos_generales':
      return { nombre: '' }
    case 'centros_pago':
      return { nombre: '', empresa: 'Acme SA', registro_patronal: '' }
    case 'ubicaciones':
      return { nombre: '', empresa: 'Acme SA', cp: '', agendar_cita: 'false' }
    default:
      return {}
  }
}
