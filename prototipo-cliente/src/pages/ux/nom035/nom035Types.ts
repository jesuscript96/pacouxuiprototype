export type EscalaOpcion = {
  label: string
  valor: number
}

export type PreguntaNom = {
  id: string
  texto: string
}

export type SeccionNom = {
  titulo: string
  preguntas: PreguntaNom[]
}

export type GuiaNom035 = {
  id: number
  /** Clave corta usada en los envíos (ej. "Guía II"). */
  clave: string
  titulo: string
  descripcion: string
  dirigidaA: string
  duracionMin: number
  escala: EscalaOpcion[]
  secciones: SeccionNom[]
}

export type EstatusNom = 'pendiente' | 'contestada' | 'vencida'

export type EnvioNom035Row = {
  key: string
  id: number
  nombre: string
  guia: string
  empresa: string
  ubicacion: string
  fechaEnvio: string
  vencimiento: string
  cerrada: boolean
  estatus: EstatusNom
}
