import type { AudienciaCriterios } from '../mensajes/mensajesTypes'

export type EncuestaEstado = 'activa' | 'borrador' | 'inactiva' | 'cerrada'

/** Información de la campaña de envío asociada a una encuesta enviada. */
export type EncuestaEnvioInfo = {
  enviados: number
  contestados: number
  noContestado: number
  urgente: boolean
  anonima: boolean
  recurrente: boolean
  cerrada: boolean
  fechaEnvio: string
  vigencia: string
  vencimiento: string
}

export type PreguntaTipo = 'opcion_multiple'

/** Opción de respuesta de una pregunta de opción múltiple (con valor numérico ponderable). */
export type OpcionRespuesta = {
  id: string
  titulo: string
  /** Valor numérico como string para edición cómoda en inputs. */
  valor: string
}

export type PreguntaEncuesta = {
  id: string
  titulo: string
  subtitulo: string
  tipo: PreguntaTipo
  obligatoria: boolean
  opciones: OpcionRespuesta[]
  ponderacion: string
  calificacionMaxima: string
  dimension: string
  dirigidoA: string
}

/** Bloques del constructor (estilo Typeform). Discriminados por `tipo`. */
export type BloqueBienvenida = {
  id: string
  tipo: 'bienvenida'
  titulo: string
  descripcion: string
  mensajePersonalizado: string
  textoBoton: string
}

export type BloqueSeccion = {
  id: string
  tipo: 'seccion'
  titulo: string
  ponderacion: string
  preguntas: PreguntaEncuesta[]
}

export type BloqueNps = {
  id: string
  tipo: 'nps'
  titulo: string
  subtitulo: string
}

export type BloqueAgradecimiento = {
  id: string
  tipo: 'agradecimiento'
  titulo: string
  mensaje: string
}

export type BloqueFormulario =
  | BloqueBienvenida
  | BloqueSeccion
  | BloqueNps
  | BloqueAgradecimiento

export type BloqueTipo = BloqueFormulario['tipo']

/** Ajustes globales de la encuesta (modal de Ajustes en el constructor). */
export type EncuestaConfig = {
  titulo: string
  categoriaId: string
  asignarEmpresa: boolean
  empresaId: string
  encuestaSalida: boolean
  duracionMin: string
}

/** Estado completo del constructor. */
export type EncuestaDraft = {
  config: EncuestaConfig
  bloques: BloqueFormulario[]
}

/** Fila del listado principal de encuestas (incluye envío si ya fue enviada). */
export type EncuestaRow = {
  key: string
  id: number
  titulo: string
  categoriaId: string
  categoriaNombre: string
  empresaId: string
  empresaNombre: string
  estado: EncuestaEstado
  preguntasCount: number
  draft: EncuestaDraft
  /** Presente solo si la encuesta ya tuvo al menos un envío. */
  envio?: EncuestaEnvioInfo
}

/** Categoría de encuesta (catálogo). */
export type CategoriaEncuesta = {
  id: number
  nombre: string
  empresaId: string
  empresaNombre: string
  creada: string
  encuestasLigadas: number
}

/** Configuración de un envío (lectura/edición en el modal de envío). */
export type EnvioConfig = {
  recurrente: boolean
  anonima: boolean
  urgente: boolean
  vigencia: string
}

export type EnvioEncuestaDraft = {
  config: EnvioConfig
  audiencia: AudienciaCriterios
}
