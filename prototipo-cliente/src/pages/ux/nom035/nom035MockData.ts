import type { ProtoSelectOption } from '@/components/ux/ProtoSelect'

import type { EnvioNom035Row, EscalaOpcion, GuiaNom035 } from './nom035Types'

const ESCALA_FRECUENCIA: EscalaOpcion[] = [
  { label: 'Siempre', valor: 4 },
  { label: 'Casi siempre', valor: 3 },
  { label: 'Algunas veces', valor: 2 },
  { label: 'Casi nunca', valor: 1 },
  { label: 'Nunca', valor: 0 },
]

const ESCALA_SI_NO: EscalaOpcion[] = [
  { label: 'Sí', valor: 1 },
  { label: 'No', valor: 0 },
]

let qSeq = 0
function q(texto: string) {
  qSeq += 1
  return { id: `nq_${qSeq}`, texto }
}

export const GUIAS_NOM035: GuiaNom035[] = [
  {
    id: 1,
    clave: 'Guía I',
    titulo: 'Guía de referencia I · Acontecimientos traumáticos severos',
    descripcion: 'Cuestionario para identificar a las y los trabajadores que experimentaron un acontecimiento traumático severo durante o con motivo del trabajo.',
    dirigidaA: 'Todos los centros de trabajo',
    duracionMin: 5,
    escala: ESCALA_SI_NO,
    secciones: [
      {
        titulo: 'Acontecimientos traumáticos severos',
        preguntas: [
          q('¿Ha presenciado o sufrido alguna vez, durante o con motivo del trabajo un acontecimiento como: accidente que tuvo consecuencias graves, asaltos, actos violentos derivados de la acción criminal?'),
          q('¿Ha presenciado o sufrido secuestros, amenazas o cualquier otro que ponga en riesgo su vida o salud, y/o la de otras personas?'),
          q('¿Sigue recordando o soñando el acontecimiento con frecuencia?'),
          q('¿Recuerda con frecuencia el acontecimiento de forma involuntaria?'),
          q('¿Tiene dificultad para dejar de pensar en el acontecimiento?'),
          q('¿Se ha esforzado por evitar todo lo que le recuerde el acontecimiento?'),
          q('¿Ha tenido dificultad para dormir desde que ocurrió el acontecimiento?'),
          q('¿Se ha sentido distante o lejano de los demás desde el acontecimiento?'),
        ],
      },
    ],
  },
  {
    id: 2,
    clave: 'Guía II',
    titulo: 'Guía de referencia II · Centros de trabajo de 16 a 50 trabajadores',
    descripcion: 'Identificación y análisis de los factores de riesgo psicosocial en centros de trabajo con entre 16 y 50 personas.',
    dirigidaA: 'Centros de 16 a 50 trabajadores',
    duracionMin: 15,
    escala: ESCALA_FRECUENCIA,
    secciones: [
      {
        titulo: 'Condiciones en el ambiente de trabajo',
        preguntas: [
          q('El espacio donde trabajo me permite realizar mis actividades de manera segura e higiénica.'),
          q('Mi trabajo me exige hacer mucho esfuerzo físico.'),
          q('Me preocupa sufrir un accidente en mi trabajo.'),
          q('Considero que las actividades que realizo son peligrosas.'),
        ],
      },
      {
        titulo: 'Carga de trabajo',
        preguntas: [
          q('Por la cantidad de trabajo que tengo debo quedarme tiempo adicional a mi turno.'),
          q('Por la cantidad de trabajo que tengo debo trabajar sin parar.'),
          q('Mi trabajo me exige atender muchos asuntos al mismo tiempo.'),
          q('Mi trabajo requiere que memorice mucha información.'),
          q('En mi trabajo debo brindar servicio a clientes o usuarios.'),
        ],
      },
      {
        titulo: 'Falta de control sobre el trabajo',
        preguntas: [
          q('Mi trabajo permite que desarrolle nuevas habilidades.'),
          q('En mi trabajo puedo aportar nuevas ideas para mejorar las actividades.'),
          q('Puedo decidir cuánto trabajo realizo durante la jornada laboral.'),
          q('Puedo decidir la velocidad a la que realizo mis actividades.'),
        ],
      },
      {
        titulo: 'Liderazgo y relaciones en el trabajo',
        preguntas: [
          q('Mi jefe ayuda a organizar mejor el trabajo.'),
          q('La información que me da mi jefe sobre mi trabajo es clara.'),
          q('Mi jefe tiene en cuenta mis puntos de vista y opiniones.'),
          q('Puedo confiar en mis compañeros de trabajo.'),
        ],
      },
    ],
  },
  {
    id: 3,
    clave: 'Guía III',
    titulo: 'Guía de referencia III · Centros de trabajo de más de 50 trabajadores',
    descripcion: 'Identificación, análisis de factores de riesgo psicosocial y evaluación del entorno organizacional en centros de más de 50 personas.',
    dirigidaA: 'Centros de más de 50 trabajadores',
    duracionMin: 20,
    escala: ESCALA_FRECUENCIA,
    secciones: [
      {
        titulo: 'Carga de trabajo',
        preguntas: [
          q('Mi trabajo me exige realizar mucho esfuerzo mental.'),
          q('Mi trabajo me exige atender asuntos administrativos imprevistos.'),
          q('Trabajo horas extras más de tres veces a la semana.'),
        ],
      },
      {
        titulo: 'Reconocimiento del desempeño',
        preguntas: [
          q('Recibo capacitación útil para hacer mi trabajo.'),
          q('Recibo reconocimiento o premios por el trabajo bien hecho.'),
          q('El reconocimiento que recibo es proporcional al esfuerzo que realizo.'),
          q('Considero que mi trabajo es estable.'),
        ],
      },
      {
        titulo: 'Insuficiente sentido de pertenencia e inestabilidad',
        preguntas: [
          q('Los cambios que se presentan en mi trabajo dificultan mi labor.'),
          q('Me siento comprometido con mi trabajo.'),
          q('Los problemas de mi trabajo me generan estrés.'),
        ],
      },
      {
        titulo: 'Entorno organizacional',
        preguntas: [
          q('En mi trabajo puedo expresar mis opiniones sin temor a represalias.'),
          q('Existen acciones para prevenir la violencia laboral.'),
          q('Recibo trato justo en mi trabajo.'),
          q('Atienden las quejas o sugerencias que presento.'),
        ],
      },
    ],
  },
  {
    id: 4,
    clave: 'Guía V',
    titulo: 'Guía de referencia V · Entorno organizacional favorable',
    descripcion: 'Evaluación del entorno organizacional favorable y de las prácticas de prevención en el centro de trabajo.',
    dirigidaA: 'Evaluación del entorno organizacional',
    duracionMin: 12,
    escala: ESCALA_FRECUENCIA,
    secciones: [
      {
        titulo: 'Sentido de pertenencia',
        preguntas: [
          q('Me siento orgulloso de trabajar para esta empresa.'),
          q('Las condiciones de trabajo permiten mi desarrollo profesional.'),
          q('Recomendaría a otras personas trabajar en esta empresa.'),
        ],
      },
      {
        titulo: 'Formación y comunicación',
        preguntas: [
          q('Conozco claramente las responsabilidades de mi puesto.'),
          q('La empresa me informa con claridad sobre lo que se espera de mí.'),
          q('Recibo información oportuna sobre los cambios en la organización.'),
        ],
      },
      {
        titulo: 'Prevención de la violencia laboral',
        preguntas: [
          q('Conozco los mecanismos para reportar actos de violencia laboral.'),
          q('La empresa actúa ante situaciones de hostigamiento o acoso.'),
        ],
      },
    ],
  },
]

export const ENVIOS_NOM035: EnvioNom035Row[] = [
  { key: 'n1', id: 9001, nombre: 'Claudia Denise Aparicio Díaz', guia: 'Guía II', empresa: 'Acme SA de CV', ubicacion: 'Corporativo', fechaEnvio: '2024-04-01', vencimiento: '2024-04-15', cerrada: false, estatus: 'contestada' },
  { key: 'n2', id: 9002, nombre: 'Ricardo Sánchez Pérez', guia: 'Guía II', empresa: 'Acme SA de CV', ubicacion: 'Corporativo', fechaEnvio: '2024-04-01', vencimiento: '2024-04-15', cerrada: false, estatus: 'pendiente' },
  { key: 'n3', id: 9003, nombre: 'Laura Méndez Ruiz', guia: 'Guía III', empresa: 'Alsea', ubicacion: 'CEDIS Norte', fechaEnvio: '2024-03-10', vencimiento: '2024-03-24', cerrada: true, estatus: 'vencida' },
  { key: 'n4', id: 9004, nombre: 'Héctor Ruiz López', guia: 'Guía I', empresa: 'Servicios Acme Norte SA', ubicacion: 'Planta Monterrey', fechaEnvio: '2024-04-05', vencimiento: '2024-04-19', cerrada: false, estatus: 'pendiente' },
  { key: 'n5', id: 9005, nombre: 'Mariana Torres Vega', guia: 'Guía II', empresa: 'Acme SA de CV', ubicacion: 'Tiendas zona centro', fechaEnvio: '2024-04-01', vencimiento: '2024-04-15', cerrada: false, estatus: 'contestada' },
]

export const GUIAS_OPCIONES: ProtoSelectOption[] = [
  { value: 'Guía I', label: 'Guía I' },
  { value: 'Guía II', label: 'Guía II' },
  { value: 'Guía III', label: 'Guía III' },
  { value: 'Guía V', label: 'Guía V' },
]

export const OPCIONES_ESTATUS_NOM: ProtoSelectOption[] = [
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'contestada', label: 'Contestada' },
  { value: 'vencida', label: 'Vencida' },
]

export const ANIOS_OPCIONES: ProtoSelectOption[] = [
  { value: '2024', label: '2024' },
  { value: '2023', label: '2023' },
  { value: '2022', label: '2022' },
]

export function totalPreguntas(guia: GuiaNom035): number {
  return guia.secciones.reduce((s, sec) => s + sec.preguntas.length, 0)
}
