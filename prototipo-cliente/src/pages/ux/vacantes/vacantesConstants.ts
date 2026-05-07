import type { CampoFormulario, VacanteRow, VacanteWizardDraft } from './vacantesTypes'

export const MOCK_EMPRESAS = [
  { value: 'acme', label: 'Acme SA' },
  { value: 'demo', label: 'Empresa demo Norte' },
  { value: 'sur', label: 'Corporativo Sur' },
] as const

export const OPCIONES_JORNADA = [
  { value: 'tiempo-completo', label: 'Tiempo completo' },
  { value: 'medio-tiempo', label: 'Medio tiempo' },
  { value: 'por-proyecto', label: 'Por proyecto' },
  { value: 'temporal', label: 'Temporal' },
]

export const OPCIONES_MODALIDAD = [
  { value: 'presencial', label: 'Presencial' },
  { value: 'remoto', label: 'Remoto' },
  { value: 'hibrido', label: 'Híbrido' },
]

export const OPCIONES_CAMPO_TIPO = [
  { value: 'texto', label: 'Texto corto' },
  { value: 'correo', label: 'Correo electrónico' },
  { value: 'telefono', label: 'Teléfono' },
  { value: 'fecha', label: 'Fecha' },
  { value: 'lista', label: 'Lista desplegable' },
  { value: 'area', label: 'Área de texto' },
]

export const OPCIONES_SI_NO = [
  { value: 'si', label: 'Sí' },
  { value: 'no', label: 'No' },
]

function empresaNombre(id: string): string {
  return MOCK_EMPRESAS.find((e) => e.value === id)?.label ?? id
}

function nuevoIdCampo(): string {
  return `cf_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`
}

/** Campos iniciales tipo legacy (demo). */
export function seedCamposFormulario(): CampoFormulario[] {
  return [
    {
      id: nuevoIdCampo(),
      nombre: 'Nombres',
      tipo: 'texto',
      requerido: true,
      textoAyuda: '',
    },
    {
      id: nuevoIdCampo(),
      nombre: 'Apellido paterno',
      tipo: 'texto',
      requerido: true,
      textoAyuda: '',
    },
    {
      id: nuevoIdCampo(),
      nombre: 'Apellido materno',
      tipo: 'texto',
      requerido: true,
      textoAyuda: '',
    },
    {
      id: nuevoIdCampo(),
      nombre: 'Fecha de nacimiento',
      tipo: 'fecha',
      requerido: true,
      textoAyuda: '',
    },
    {
      id: nuevoIdCampo(),
      nombre: 'Género',
      tipo: 'lista',
      requerido: true,
      textoAyuda: '',
    },
    {
      id: nuevoIdCampo(),
      nombre: 'Lugar de nacimiento',
      tipo: 'lista',
      requerido: false,
      textoAyuda: '',
    },
    {
      id: nuevoIdCampo(),
      nombre: 'Teléfono/Celular',
      tipo: 'telefono',
      requerido: true,
      textoAyuda: '',
    },
    {
      id: nuevoIdCampo(),
      nombre: 'Correo electrónico',
      tipo: 'correo',
      requerido: true,
      textoAyuda: '',
    },
  ]
}

export function emptyVacanteDraft(): VacanteWizardDraft {
  return {
    empresaId: '',
    puesto: '',
    tipoJornada: 'tiempo-completo',
    modalidad: 'hibrido',
    mostrarSalario: false,
    salarioMin: '',
    salarioMax: '',
    visiblePortal: true,
    fechaLimite: '',
    requisitos: '',
    aptitudes: '',
    prestaciones: '',
    camposFormulario: seedCamposFormulario(),
    n: 0,
  }
}

export function rowToDraft(row: VacanteRow): VacanteWizardDraft {
  return {
    empresaId: row.empresaId,
    puesto: row.puesto,
    tipoJornada: row.tipoJornada,
    modalidad: row.modalidad,
    mostrarSalario: row.mostrarSalario,
    salarioMin: row.salarioMin,
    salarioMax: row.salarioMax,
    visiblePortal: row.visiblePortal,
    fechaLimite: row.fechaLimite,
    requisitos: row.requisitos,
    aptitudes: row.aptitudes,
    prestaciones: row.prestaciones,
    camposFormulario: row.camposFormulario.map((c) => ({ ...c })),
    n: row.n,
  }
}

export function draftToNewRow(draft: VacanteWizardDraft, key: string, creado: string): VacanteRow {
  return {
    key,
    empresaId: draft.empresaId,
    empresaNombre: empresaNombre(draft.empresaId),
    puesto: draft.puesto.trim() || 'Vacante sin título',
    tipoJornada: draft.tipoJornada,
    modalidad: draft.modalidad,
    mostrarSalario: draft.mostrarSalario,
    salarioMin: draft.salarioMin.trim(),
    salarioMax: draft.salarioMax.trim(),
    visiblePortal: draft.visiblePortal,
    fechaLimite: draft.fechaLimite,
    requisitos: draft.requisitos.trim(),
    aptitudes: draft.aptitudes.trim(),
    prestaciones: draft.prestaciones.trim(),
    n: draft.n,
    creado,
    camposFormulario: draft.camposFormulario.map((c) => ({ ...c })),
  }
}

export function mergeDraftIntoRow(row: VacanteRow, draft: VacanteWizardDraft): VacanteRow {
  return {
    ...row,
    empresaId: draft.empresaId,
    empresaNombre: empresaNombre(draft.empresaId),
    puesto: draft.puesto.trim() || row.puesto,
    tipoJornada: draft.tipoJornada,
    modalidad: draft.modalidad,
    mostrarSalario: draft.mostrarSalario,
    salarioMin: draft.salarioMin.trim(),
    salarioMax: draft.salarioMax.trim(),
    visiblePortal: draft.visiblePortal,
    fechaLimite: draft.fechaLimite,
    requisitos: draft.requisitos.trim(),
    aptitudes: draft.aptitudes.trim(),
    prestaciones: draft.prestaciones.trim(),
    camposFormulario: draft.camposFormulario.map((c) => ({ ...c })),
  }
}

const jornadaLabel = (v: string) => OPCIONES_JORNADA.find((o) => o.value === v)?.label ?? v

export const INITIAL_VACANTES_ROWS: VacanteRow[] = [
  {
    key: 'v1',
    empresaId: 'acme',
    empresaNombre: empresaNombre('acme'),
    puesto: 'Ingeniero de datos (senior)',
    tipoJornada: 'tiempo-completo',
    modalidad: 'hibrido',
    mostrarSalario: true,
    salarioMin: '80000',
    salarioMax: '110000',
    visiblePortal: true,
    fechaLimite: '2026-06-30',
    requisitos: 'SQL avanzado, Python, 5+ años en pipelines ETL.',
    aptitudes: 'Comunicación clara, trabajo en equipo, proactividad.',
    prestaciones: 'Seguro mayor, vales, home office flexible.',
    n: 14,
    creado: '08/04/2026 10:22',
    camposFormulario: seedCamposFormulario(),
  },
  {
    key: 'v2',
    empresaId: 'demo',
    empresaNombre: empresaNombre('demo'),
    puesto: 'Ejecutivo de cuenta zona norte',
    tipoJornada: 'tiempo-completo',
    modalidad: 'presencial',
    mostrarSalario: false,
    salarioMin: '',
    salarioMax: '',
    visiblePortal: true,
    fechaLimite: '',
    requisitos: 'Experiencia en ventas B2B, disponibilidad para viajar.',
    aptitudes: 'Negociación, orientación a resultados.',
    prestaciones: 'Comisiones, auto del trabajo.',
    n: 6,
    creado: '02/04/2026 14:05',
    camposFormulario: seedCamposFormulario(),
  },
  {
    key: 'v3',
    empresaId: 'acme',
    empresaNombre: empresaNombre('acme'),
    puesto: 'Analista de nómina',
    tipoJornada: 'medio-tiempo',
    modalidad: 'remoto',
    mostrarSalario: true,
    salarioMin: '22000',
    salarioMax: '28000',
    visiblePortal: false,
    fechaLimite: '2026-05-15',
    requisitos: 'CONTPAQi o similar, cálculo de ISR y IMSS.',
    aptitudes: 'Detalle, cumplimiento de fechas.',
    prestaciones: 'Capacitación pagada, horario flexible.',
    n: 22,
    creado: '28/03/2026 09:18',
    camposFormulario: seedCamposFormulario(),
  },
]

export function labelJornadaParaTabla(value: string): string {
  return jornadaLabel(value)
}

export function nuevoIdCampoFormulario(): string {
  return nuevoIdCampo()
}
