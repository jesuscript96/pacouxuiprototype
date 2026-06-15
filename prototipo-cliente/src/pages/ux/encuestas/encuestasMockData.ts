import type { ProtoSelectOption } from '@/components/ux/ProtoSelect'

import { CATALOG_EMPRESAS, emptyAudiencia } from '../mensajes/mensajesConstants'
import type {
  BloqueFormulario,
  CategoriaEncuesta,
  EncuestaDraft,
  EncuestaRow,
  EnvioEncuestaDraft,
  OpcionRespuesta,
  PreguntaEncuesta,
} from './encuestasTypes'

export { CATALOG_EMPRESAS } from '../mensajes/mensajesConstants'

/** Catálogo de categorías para selects (derivado de CATEGORIAS_INICIALES). */
export const CATALOG_DIMENSIONES: ProtoSelectOption[] = [
  { value: 'dim_liderazgo', label: 'Liderazgo' },
  { value: 'dim_comunicacion', label: 'Comunicación' },
  { value: 'dim_reconocimiento', label: 'Reconocimiento' },
  { value: 'dim_carga', label: 'Carga de trabajo' },
  { value: 'dim_desarrollo', label: 'Desarrollo profesional' },
]

export const CATALOG_DIRIGIDO_A: ProtoSelectOption[] = [
  { value: 'dir_todos', label: 'Todos los colaboradores' },
  { value: 'dir_lideres', label: 'Líderes y gerentes' },
  { value: 'dir_operativo', label: 'Personal operativo' },
  { value: 'dir_nuevos', label: 'Ingresos recientes' },
]

export const OPCIONES_FORMATO_REPORTE: ProtoSelectOption[] = [
  { value: 'xls', label: 'Excel (XLS)' },
  { value: 'pdf', label: 'PDF' },
]

let categoriaSeq = 9

export const CATEGORIAS_INICIALES: CategoriaEncuesta[] = [
  { id: 1, nombre: 'Clima Organizacional', empresaId: 'emp_acme', empresaNombre: 'Acme SA de CV', creada: '2024-01-15', encuestasLigadas: 3 },
  { id: 2, nombre: 'Capacitación', empresaId: '', empresaNombre: 'Sin asignar', creada: '2024-01-20', encuestasLigadas: 1 },
  { id: 3, nombre: 'Evaluación de Capacitación', empresaId: 'emp_alsea', empresaNombre: 'Alsea', creada: '2024-02-02', encuestasLigadas: 0 },
  { id: 4, nombre: 'Bienestar', empresaId: 'emp_acme', empresaNombre: 'Acme SA de CV', creada: '2024-02-11', encuestasLigadas: 2 },
  { id: 5, nombre: 'Liderazgo', empresaId: '', empresaNombre: 'Sin asignar', creada: '2024-03-01', encuestasLigadas: 0 },
  { id: 6, nombre: 'Procedimiento Call Center', empresaId: 'emp_norte', empresaNombre: 'Servicios Acme Norte SA', creada: '2024-03-08', encuestasLigadas: 1 },
  { id: 7, nombre: 'Calidad', empresaId: '', empresaNombre: 'Sin asignar', creada: '2024-03-19', encuestasLigadas: 0 },
  { id: 8, nombre: 'Iniciativa', empresaId: 'emp_acme', empresaNombre: 'Acme SA de CV', creada: '2024-04-02', encuestasLigadas: 0 },
]

export function nextCategoriaId(): number {
  categoriaSeq += 1
  return categoriaSeq
}

export function categoriasComoOpciones(categorias: CategoriaEncuesta[]): ProtoSelectOption[] {
  return categorias.map((c) => ({ value: String(c.id), label: c.nombre }))
}

export function empresaNombre(empresaId: string): string {
  return CATALOG_EMPRESAS.find((e) => e.value === empresaId)?.label ?? 'Sin asignar'
}

let bloqueSeq = 0
export function nextBloqueId(prefix = 'blk'): string {
  bloqueSeq += 1
  return `${prefix}_${Date.now()}_${bloqueSeq}`
}

export function emptyOpcion(): OpcionRespuesta {
  return { id: nextBloqueId('opt'), titulo: '', valor: '0' }
}

export function emptyPregunta(): PreguntaEncuesta {
  return {
    id: nextBloqueId('q'),
    titulo: '',
    subtitulo: '',
    tipo: 'opcion_multiple',
    obligatoria: true,
    opciones: [
      { id: nextBloqueId('opt'), titulo: 'Totalmente de acuerdo', valor: '4' },
      { id: nextBloqueId('opt'), titulo: 'De acuerdo', valor: '3' },
      { id: nextBloqueId('opt'), titulo: 'En desacuerdo', valor: '2' },
      { id: nextBloqueId('opt'), titulo: 'Totalmente en desacuerdo', valor: '1' },
    ],
    ponderacion: '100',
    calificacionMaxima: '4',
    dimension: '',
    dirigidoA: '',
  }
}

export function nuevoBloque(tipo: BloqueFormulario['tipo'], indice = 1): BloqueFormulario {
  switch (tipo) {
    case 'bienvenida':
      return {
        id: nextBloqueId('bienv'),
        tipo: 'bienvenida',
        titulo: 'Bienvenido a la encuesta',
        descripcion: 'Tu opinión es importante. Esta encuesta es confidencial.',
        mensajePersonalizado: '',
        textoBoton: 'Comenzar',
      }
    case 'seccion':
      return {
        id: nextBloqueId('sec'),
        tipo: 'seccion',
        titulo: `Sección ${indice}`,
        ponderacion: '0',
        preguntas: [emptyPregunta()],
      }
    case 'nps':
      return {
        id: nextBloqueId('nps'),
        tipo: 'nps',
        titulo: '¿Qué tan probable es que recomiendes a esta empresa como un buen lugar para trabajar?',
        subtitulo: 'En una escala del 0 al 10.',
      }
    case 'agradecimiento':
      return {
        id: nextBloqueId('grx'),
        tipo: 'agradecimiento',
        titulo: '¡Gracias por participar!',
        mensaje: 'Tus respuestas se registraron correctamente.',
      }
  }
}

export function emptyEncuestaDraft(): EncuestaDraft {
  return {
    config: {
      titulo: '',
      categoriaId: '',
      asignarEmpresa: false,
      empresaId: '',
      encuestaSalida: false,
      duracionMin: '10',
    },
    bloques: [nuevoBloque('bienvenida'), nuevoBloque('agradecimiento')],
  }
}

export function contarPreguntas(draft: EncuestaDraft): number {
  return draft.bloques.reduce((sum, b) => {
    if (b.tipo === 'seccion') {
      return sum + b.preguntas.length
    }
    if (b.tipo === 'nps') {
      return sum + 1
    }
    return sum
  }, 0)
}

function demoDraft(titulo: string, categoriaId: string, empresaId: string): EncuestaDraft {
  return {
    config: {
      titulo,
      categoriaId,
      asignarEmpresa: Boolean(empresaId),
      empresaId,
      encuestaSalida: false,
      duracionMin: '8',
    },
    bloques: [
      nuevoBloque('bienvenida'),
      {
        id: nextBloqueId('sec'),
        tipo: 'seccion',
        titulo: 'Ambiente de trabajo',
        ponderacion: '60',
        preguntas: [
          {
            id: nextBloqueId('q'),
            titulo: 'Me siento cómodo en mi lugar de trabajo',
            subtitulo: '',
            tipo: 'opcion_multiple',
            obligatoria: true,
            opciones: [
              { id: nextBloqueId('opt'), titulo: 'Siempre', valor: '4' },
              { id: nextBloqueId('opt'), titulo: 'Casi siempre', valor: '3' },
              { id: nextBloqueId('opt'), titulo: 'Algunas veces', valor: '2' },
              { id: nextBloqueId('opt'), titulo: 'Nunca', valor: '1' },
            ],
            ponderacion: '100',
            calificacionMaxima: '4',
            dimension: 'dim_carga',
            dirigidoA: 'dir_todos',
          },
        ],
      },
      { id: nextBloqueId('nps'), tipo: 'nps', titulo: '¿Recomendarías a esta empresa como un buen lugar para trabajar?', subtitulo: 'En una escala del 0 al 10.' },
      nuevoBloque('agradecimiento'),
    ],
  }
}

export const ENCUESTAS_INICIALES: EncuestaRow[] = [
  {
    key: 'enc_1',
    id: 101,
    titulo: 'Encuesta de Clima 2024',
    categoriaId: '1',
    categoriaNombre: 'Clima Organizacional',
    empresaId: 'emp_acme',
    empresaNombre: 'Acme SA de CV',
    estado: 'activa',
    preguntasCount: 2,
    draft: demoDraft('Encuesta de Clima 2024', '1', 'emp_acme'),
    envio: {
      enviados: 248,
      contestados: 190,
      noContestado: 58,
      urgente: false,
      anonima: true,
      recurrente: false,
      cerrada: false,
      fechaEnvio: '2024-04-01',
      vigencia: '2024-04-15',
      vencimiento: '2024-04-15',
    },
  },
  {
    key: 'enc_2',
    id: 102,
    titulo: 'Satisfacción post-capacitación',
    categoriaId: '2',
    categoriaNombre: 'Capacitación',
    empresaId: '',
    empresaNombre: 'Sin asignar',
    estado: 'cerrada',
    preguntasCount: 2,
    draft: demoDraft('Satisfacción post-capacitación', '2', ''),
    envio: {
      enviados: 60,
      contestados: 60,
      noContestado: 0,
      urgente: false,
      anonima: false,
      recurrente: true,
      cerrada: true,
      fechaEnvio: '2024-03-05',
      vigencia: '2024-03-19',
      vencimiento: '2024-03-19',
    },
  },
  {
    key: 'enc_3',
    id: 103,
    titulo: 'Bienestar emocional Q2',
    categoriaId: '4',
    categoriaNombre: 'Bienestar',
    empresaId: 'emp_acme',
    empresaNombre: 'Acme SA de CV',
    estado: 'activa',
    preguntasCount: 2,
    draft: demoDraft('Bienestar emocional Q2', '4', 'emp_acme'),
    envio: {
      enviados: 120,
      contestados: 44,
      noContestado: 76,
      urgente: true,
      anonima: true,
      recurrente: false,
      cerrada: false,
      fechaEnvio: '2024-04-10',
      vigencia: '2024-04-24',
      vencimiento: '2024-04-24',
    },
  },
  {
    key: 'enc_4',
    id: 104,
    titulo: 'Onboarding nuevos ingresos',
    categoriaId: '2',
    categoriaNombre: 'Capacitación',
    empresaId: '',
    empresaNombre: 'Sin asignar',
    estado: 'borrador',
    preguntasCount: 2,
    draft: demoDraft('Onboarding nuevos ingresos', '2', ''),
  },
]

let encuestaSeq = 103

export function draftToNewRow(draft: EncuestaDraft, categorias: CategoriaEncuesta[]): EncuestaRow {
  encuestaSeq += 1
  const cat = categorias.find((c) => String(c.id) === draft.config.categoriaId)
  const empId = draft.config.asignarEmpresa ? draft.config.empresaId : ''
  return {
    key: `enc_${Date.now()}`,
    id: encuestaSeq,
    titulo: draft.config.titulo.trim() || 'Encuesta sin título',
    categoriaId: draft.config.categoriaId,
    categoriaNombre: cat?.nombre ?? 'Sin categoría',
    empresaId: empId,
    empresaNombre: empresaNombre(empId),
    estado: 'borrador',
    preguntasCount: contarPreguntas(draft),
    draft,
  }
}

export function mergeDraftIntoRow(row: EncuestaRow, draft: EncuestaDraft, categorias: CategoriaEncuesta[]): EncuestaRow {
  const cat = categorias.find((c) => String(c.id) === draft.config.categoriaId)
  const empId = draft.config.asignarEmpresa ? draft.config.empresaId : ''
  return {
    ...row,
    titulo: draft.config.titulo.trim() || 'Encuesta sin título',
    categoriaId: draft.config.categoriaId,
    categoriaNombre: cat?.nombre ?? 'Sin categoría',
    empresaId: empId,
    empresaNombre: empresaNombre(empId),
    preguntasCount: contarPreguntas(draft),
    draft,
  }
}

export function duplicarEncuesta(row: EncuestaRow): EncuestaRow {
  encuestaSeq += 1
  return {
    ...row,
    key: `enc_${Date.now()}`,
    id: encuestaSeq,
    titulo: `${row.titulo} (copia)`,
    estado: 'borrador',
    draft: { ...row.draft, config: { ...row.draft.config, titulo: `${row.titulo} (copia)` } },
  }
}

export function emptyEnvioDraft(): EnvioEncuestaDraft {
  return {
    config: { recurrente: false, anonima: true, urgente: false, vigencia: '' },
    audiencia: emptyAudiencia(),
  }
}

export function estadoEncuestaLabel(estado: EncuestaRow['estado']): string {
  return { activa: 'Activa', borrador: 'Borrador', inactiva: 'Inactiva', cerrada: 'Cerrada' }[estado]
}

export const OPCIONES_ESTADO_ENCUESTA: ProtoSelectOption[] = [
  { value: 'borrador', label: 'Borrador' },
  { value: 'activa', label: 'Activa' },
  { value: 'cerrada', label: 'Cerrada' },
  { value: 'inactiva', label: 'Inactiva' },
]
