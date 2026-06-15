import type { AudienciaCriterios } from '../mensajes/mensajesTypes'

export type CursoEstado = 'activo' | 'borrador' | 'inactivo'

export type CursoContenidoTipo =
  | 'video'
  | 'imagen'
  | 'archivo'
  | 'youtube'
  | 'audio'
  | 'url'

export type CursoPreguntaTipo = 'unica' | 'multiple'

export type CursoArchivoAdjunto = {
  id: string
  nombre: string
  tipo: string
  tamano: number
  url: string
  archivo?: File
}

export type CursoSlide = {
  id: string
  titulo: string
  descripcion: string
  recursos: CursoContenidoTipo[]
  adjuntos: CursoArchivoAdjunto[]
  urlExterna: string
  youtubeUrl: string
}

export type CursoTema = {
  id: string
  titulo: string
  descripcion: string
  slides: CursoSlide[]
  /** Compatibilidad con el primer prototipo del popup. En página nueva se usa `slides`. */
  recursos: CursoContenidoTipo[]
}

export type CursoPregunta = {
  id: string
  texto: string
  tipo: CursoPreguntaTipo
  opciones: string[]
  respuestaCorrecta: string
  explicacion: string
}

export type CursoLeccion = {
  id: string
  titulo: string
  actividadPractica: string
  cuestionarioHabilitado: boolean
  temas: CursoTema[]
  preguntas: CursoPregunta[]
}

export type CursoModulo = {
  id: string
  titulo: string
  lecciones: CursoLeccion[]
}

export type CursoWizardDraft = {
  informacion: {
    titulo: string
    empresaId: string
    descripcion: string
    duracionHoras: string
    imagenPortada: string
  }
  contenido: {
    modulos: CursoModulo[]
  }
  evaluacion: {
    porcentajeMinimo: string
    intentosMaximos: string
    limiteTiempoMinutos: string
    mostrarCorrectas: boolean
  }
  configuracion: {
    tipo: 'obligatorio' | 'opcional'
    secuencial: boolean
    antiguedadMeses: string
    plazoDias: string
    encuestaSatisfaccion: boolean
  }
  segmentacion: AudienciaCriterios
  notificacion: {
    enviarPush: boolean
    mensajePush: string
  }
  certificados: {
    otorgarCertificado: boolean
    otorgarReconocimiento: boolean
  }
  stps: {
    activo: boolean
    modalidad: 'linea' | 'mixta' | 'presencial'
    claveCurso: string
    objetivo: string
    areaTematica: string
    agenteTipo: 'interno' | 'externo' | 'otro'
    agenteNombre: string
    agenteRfc: string
  }
}

export type CursoRow = {
  key: string
  id: number
  titulo: string
  creadoPor: string
  ultimaModificacion: string
  empresaId: string
  empresaNombre: string
  estado: CursoEstado
  colaboradoresAsignados: number
  colaboradoresEnProgreso: number
  colaboradoresFinalizados: number
  wizardSnapshot: CursoWizardDraft
}

export type CursoReporteHistoricoRow = {
  id: string
  colaborador: string
  empresa: string
  capacitacion: string
  estado: 'Aprobado' | 'Pendiente' | 'En progreso' | 'No aprobado'
  fechaInicio: string
  fechaFin: string
  dias: number
  progreso: string
  evaluaciones: string
}
