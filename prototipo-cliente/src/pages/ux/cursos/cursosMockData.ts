import type { ProtoSelectOption } from '@/components/ux/ProtoSelect'
import {
  CATALOG_EMPRESAS,
  CATALOG_DEPARTAMENTOS,
  CATALOG_REGIONES,
  CATALOG_PUESTOS,
  CATALOG_UBICACIONES,
  emptyAudiencia,
} from '../mensajes/mensajesConstants'
import type {
  CursoEstado,
  CursoModulo,
  CursoReporteHistoricoRow,
  CursoRow,
  CursoWizardDraft,
} from './cursosTypes'

export {
  CATALOG_EMPRESAS,
  CATALOG_DEPARTAMENTOS,
  CATALOG_REGIONES,
  CATALOG_PUESTOS,
  CATALOG_UBICACIONES,
}

export const OPCIONES_ESTADO_CURSO: ProtoSelectOption[] = [
  { value: 'activo', label: 'Activo' },
  { value: 'borrador', label: 'Borrador' },
  { value: 'inactivo', label: 'Inactivo' },
]

export const OPCIONES_TIPO_CURSO: ProtoSelectOption[] = [
  { value: 'obligatorio', label: 'Obligatorio' },
  { value: 'opcional', label: 'Opcional' },
]

export const OPCIONES_MODALIDAD_STPS: ProtoSelectOption[] = [
  { value: 'linea', label: 'En línea' },
  { value: 'mixta', label: 'Mixta' },
  { value: 'presencial', label: 'Presencial' },
]

export const OPCIONES_AGENTE_CAPACITADOR: ProtoSelectOption[] = [
  { value: 'interno', label: 'Interno' },
  { value: 'externo', label: 'Externo' },
  { value: 'otro', label: 'Otro' },
]

export function estadoCursoLabel(estado: CursoEstado): string {
  switch (estado) {
    case 'activo':
      return 'Activo'
    case 'borrador':
      return 'Borrador'
    case 'inactivo':
      return 'Inactivo'
  }
}

function cursoModuloDemo(): CursoModulo {
  return {
    id: 'mod_1',
    titulo: 'Módulo 1 · Fundamentos',
    lecciones: [
      {
        id: 'lec_1',
        titulo: 'Lección 1 · Contexto',
        actividadPractica:
          'Describe una situación real donde aplicarías este aprendizaje.',
        cuestionarioHabilitado: false,
        temas: [
          {
            id: 'tema_1',
            titulo: 'Introducción visual',
            descripcion:
              'Tema introductorio con varias slides para explicar el contexto.',
            slides: [
              {
                id: 'slide_1',
                titulo: 'Slide 1 · Bienvenida',
                descripcion: 'Video corto de contexto para iniciar el curso.',
                recursos: ['video'],
              },
              {
                id: 'slide_2',
                titulo: 'Slide 2 · Material de apoyo',
                descripcion: 'Imagen, archivo descargable y enlace externo.',
                recursos: ['imagen', 'archivo', 'url'],
              },
            ],
            recursos: ['video', 'imagen', 'url'],
          },
        ],
        preguntas: [
          {
            id: 'preg_1',
            texto: '¿Cuál es el objetivo principal del curso?',
            tipo: 'unica',
            opciones: ['Actualizar procesos', 'Cubrir asistencia', 'Enviar avisos'],
            respuestaCorrecta: 'Actualizar procesos',
            explicacion:
              'La capacitación busca asegurar comprensión operativa, no solo asistencia.',
          },
        ],
      },
    ],
  }
}

export function emptyCursoWizardDraft(): CursoWizardDraft {
  return {
    informacion: {
      titulo: '',
      empresaId: '',
      descripcion: '',
      duracionHoras: '2',
      imagenPortada: '',
    },
    contenido: {
      modulos: [cursoModuloDemo()],
    },
    evaluacion: {
      porcentajeMinimo: '80',
      intentosMaximos: '3',
      limiteTiempoMinutos: '30',
      mostrarCorrectas: true,
    },
    configuracion: {
      tipo: 'obligatorio',
      secuencial: true,
      antiguedadMeses: '0',
      plazoDias: '30',
      encuestaSatisfaccion: true,
    },
    segmentacion: emptyAudiencia(),
    notificacion: {
      enviarPush: true,
      mensajePush: 'Tienes una nueva capacitación asignada.',
    },
    certificados: {
      otorgarCertificado: true,
      otorgarReconocimiento: false,
    },
    stps: {
      activo: false,
      modalidad: 'linea',
      claveCurso: '',
      objetivo: 'Incremento a la productividad',
      areaTematica: 'Actividades culturales y artísticas',
      agenteTipo: 'interno',
      agenteNombre: '',
      agenteRfc: '',
    },
  }
}

export function cursoDraftParaFila(
  draft: CursoWizardDraft,
  key: string,
  id: number,
  ultimaModificacion: string,
): CursoRow {
  const empresa = CATALOG_EMPRESAS.find((e) => e.value === draft.informacion.empresaId)

  return {
    key,
    id,
    titulo: draft.informacion.titulo || 'Curso sin título',
    creadoPor: 'María RH',
    ultimaModificacion,
    empresaId: draft.informacion.empresaId,
    empresaNombre: empresa?.label ?? 'Empresa sin definir',
    estado: 'borrador',
    colaboradoresAsignados: 0,
    colaboradoresEnProgreso: 0,
    colaboradoresFinalizados: 0,
    wizardSnapshot: draft,
  }
}

export function mergeDraftEnFila(row: CursoRow, draft: CursoWizardDraft): CursoRow {
  const empresa = CATALOG_EMPRESAS.find((e) => e.value === draft.informacion.empresaId)

  return {
    ...row,
    titulo: draft.informacion.titulo || row.titulo,
    empresaId: draft.informacion.empresaId,
    empresaNombre: empresa?.label ?? row.empresaNombre,
    ultimaModificacion: new Date().toLocaleDateString('es-MX'),
    wizardSnapshot: draft,
  }
}

export function duplicarCurso(row: CursoRow, nextId: number): CursoRow {
  const draft = {
    ...row.wizardSnapshot,
    informacion: {
      ...row.wizardSnapshot.informacion,
      titulo: `${row.titulo} (copia)`,
    },
  }

  return {
    ...row,
    key: `curso_${Date.now()}`,
    id: nextId,
    titulo: draft.informacion.titulo,
    estado: 'borrador',
    creadoPor: 'María RH',
    ultimaModificacion: new Date().toLocaleDateString('es-MX'),
    colaboradoresAsignados: 0,
    colaboradoresEnProgreso: 0,
    colaboradoresFinalizados: 0,
    wizardSnapshot: draft,
  }
}

export const INITIAL_CURSOS_ROWS: CursoRow[] = [
  {
    key: 'curso_101',
    id: 101,
    titulo: 'Inducción corporativa 2026',
    creadoPor: 'María RH',
    ultimaModificacion: '12/05/2026',
    empresaId: 'emp_acme',
    empresaNombre: 'Acme SA de CV',
    estado: 'activo',
    colaboradoresAsignados: 320,
    colaboradoresEnProgreso: 126,
    colaboradoresFinalizados: 168,
    wizardSnapshot: {
      ...emptyCursoWizardDraft(),
      informacion: {
        ...emptyCursoWizardDraft().informacion,
        titulo: 'Inducción corporativa 2026',
        empresaId: 'emp_acme',
        descripcion:
          'Curso base para nuevos ingresos con políticas, cultura y procesos clave.',
        duracionHoras: '4',
      },
    },
  },
  {
    key: 'curso_102',
    id: 102,
    titulo: 'Seguridad operativa en planta',
    creadoPor: 'Carlos Operaciones',
    ultimaModificacion: '08/05/2026',
    empresaId: 'emp_norte',
    empresaNombre: 'Servicios Acme Norte SA',
    estado: 'activo',
    colaboradoresAsignados: 210,
    colaboradoresEnProgreso: 74,
    colaboradoresFinalizados: 92,
    wizardSnapshot: {
      ...emptyCursoWizardDraft(),
      informacion: {
        ...emptyCursoWizardDraft().informacion,
        titulo: 'Seguridad operativa en planta',
        empresaId: 'emp_norte',
        descripcion:
          'Capacitación obligatoria para operación segura y prevención de riesgos.',
        duracionHoras: '6',
      },
      stps: {
        ...emptyCursoWizardDraft().stps,
        activo: true,
        modalidad: 'mixta',
        claveCurso: 'SEG-OP-2026',
        agenteNombre: 'Capacitación Interna Acme',
        agenteRfc: 'CIA010101ABC',
      },
    },
  },
  {
    key: 'curso_103',
    id: 103,
    titulo: 'Liderazgo para mandos medios',
    creadoPor: 'Ana Talento',
    ultimaModificacion: '29/04/2026',
    empresaId: 'emp_alsea',
    empresaNombre: 'Alsea',
    estado: 'borrador',
    colaboradoresAsignados: 84,
    colaboradoresEnProgreso: 19,
    colaboradoresFinalizados: 0,
    wizardSnapshot: {
      ...emptyCursoWizardDraft(),
      informacion: {
        ...emptyCursoWizardDraft().informacion,
        titulo: 'Liderazgo para mandos medios',
        empresaId: 'emp_alsea',
        descripcion:
          'Programa para responsables de equipo con módulos de feedback y seguimiento.',
        duracionHoras: '8',
      },
    },
  },
]

export const CURSOS_REPORTE_HISTORICO: CursoReporteHistoricoRow[] = [
  {
    id: 'hist_1',
    colaborador: 'Claudia Denise Aparicio Díaz',
    empresa: 'Acme SA de CV',
    capacitacion: 'Inducción corporativa 2026',
    estado: 'Aprobado',
    fechaInicio: '01/05/2026',
    fechaFin: '03/05/2026',
    dias: 2,
    progreso: '100%',
    evaluaciones: '92/100',
  },
  {
    id: 'hist_2',
    colaborador: 'Ricardo Sánchez Pérez',
    empresa: 'Servicios Acme Norte SA',
    capacitacion: 'Seguridad operativa en planta',
    estado: 'En progreso',
    fechaInicio: '07/05/2026',
    fechaFin: 'Pendiente',
    dias: 5,
    progreso: '64%',
    evaluaciones: 'Pendiente',
  },
  {
    id: 'hist_3',
    colaborador: 'Laura Méndez Ruiz',
    empresa: 'Alsea',
    capacitacion: 'Liderazgo para mandos medios',
    estado: 'Pendiente',
    fechaInicio: 'Pendiente',
    fechaFin: 'Pendiente',
    dias: 0,
    progreso: '0%',
    evaluaciones: 'Sin iniciar',
  },
  {
    id: 'hist_4',
    colaborador: 'Héctor Ruiz López',
    empresa: 'Acme SA de CV',
    capacitacion: 'Inducción corporativa 2026',
    estado: 'No aprobado',
    fechaInicio: '21/04/2026',
    fechaFin: '24/04/2026',
    dias: 3,
    progreso: '100%',
    evaluaciones: '58/100',
  },
]
