/** Tipos de campo del formulario postulación (paso 2 del wizard). */
export type VacanteCampoTipo =
  | 'texto'
  | 'correo'
  | 'telefono'
  | 'fecha'
  | 'lista'
  | 'area'

export type CampoFormulario = {
  id: string
  nombre: string
  tipo: VacanteCampoTipo
  requerido: boolean
  textoAyuda: string
}

/** Fila del listado + payload guardado del wizard. */
export type VacanteRow = {
  key: string
  empresaId: string
  empresaNombre: string
  puesto: string
  tipoJornada: string
  modalidad: string
  mostrarSalario: boolean
  salarioMin: string
  salarioMax: string
  visiblePortal: boolean
  /** yyyy-mm-dd o cadena vacía */
  fechaLimite: string
  requisitos: string
  aptitudes: string
  prestaciones: string
  n: number
  creado: string
  camposFormulario: CampoFormulario[]
}

/** Estado editable dentro del modal (sin metadatos de fila hasta guardar). */
export type VacanteWizardDraft = {
  empresaId: string
  puesto: string
  tipoJornada: string
  modalidad: string
  mostrarSalario: boolean
  salarioMin: string
  salarioMax: string
  visiblePortal: boolean
  fechaLimite: string
  requisitos: string
  aptitudes: string
  prestaciones: string
  camposFormulario: CampoFormulario[]
  n: number
}
