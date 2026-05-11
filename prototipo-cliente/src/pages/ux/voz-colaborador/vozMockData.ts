import type { VoiceThread } from './vozTypes'

const img = (seed: number) => `https://picsum.photos/seed/voz${seed}/280/200`
const vidSample =
  'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4'

export const CATALOG_EMPRESAS_VOZ: { value: string; label: string }[] = [
  { value: 'demo-paco', label: 'Demo Paco' },
  { value: 'acme', label: 'Acme SA' },
  { value: 'norte', label: 'Constructora del Norte SA' },
]

export const CATALOG_UBICACIONES_VOZ: { value: string; label: string }[] = [
  { value: 'cdmx', label: 'CDMX - Oficina Central' },
  { value: 'gdl', label: 'Guadalajara - Planta' },
  { value: 'mty', label: 'Monterrey - Obra Norte' },
]

export const CATALOG_CATEGORIAS_VOZ: { value: string; label: string }[] = [
  { value: 'conflicto', label: 'Conflicto de interés' },
  { value: 'clima', label: 'Clima laboral' },
  { value: 'capacitacion', label: 'Capacitación' },
  { value: 'seguridad', label: 'Seguridad e higiene' },
]

export const OPCIONES_ESTADO_VOZ: { value: string; label: string }[] = [
  { value: 'Pendiente', label: 'Pendiente' },
  { value: 'En Proceso', label: 'En Proceso' },
  { value: 'Atendido', label: 'Atendido' },
]

export const OPCIONES_PRIORIDAD_VOZ: { value: string; label: string }[] = [
  { value: 'Sin Asignar', label: 'Sin Asignar' },
  { value: 'Baja', label: 'Baja' },
  { value: 'Media', label: 'Media' },
  { value: 'Alta', label: 'Alta' },
]

/** Radix Select no admite `value=""` en ítems; usar sentinela y mapear a `assigneeKey` vacío. */
export const VOICE_ASSIGNEE_NONE = '__unassigned__'

export const CATALOG_ASIGNADOS_VOZ: { value: string; label: string }[] = [
  { value: VOICE_ASSIGNEE_NONE, label: 'Sin asignar' },
  { value: 'carlos', label: 'Carlos Méndez Ríos' },
  { value: 'laura', label: 'Laura Fernández Ruiz' },
]

/** Hilos iniciales — ampliación del ejemplo del doc PROTOTIPO-voice-attachments. */
export const INITIAL_VOICE_THREADS: VoiceThread[] = [
  {
    id: 4521,
    status: 'En Proceso',
    priority: 'Alta',
    category: 'Capacitación',
    empresaId: 'norte',
    ubicacionKey: 'cdmx',
    sender: {
      name: 'Ana García López',
      isAnonymous: false,
      company: 'Constructora del Norte SA',
      location: 'CDMX - Oficina Central',
      department: 'Recursos Humanos',
      area: 'Compensaciones',
      position: 'Analista RH',
    },
    comment:
      'Mi líder no respeta el horario de comida y nos pide regresar antes.',
    result:
      'Gracias por reportarlo. Investigaremos con tu jefe directo.',
    date: '2026-05-08T11:30:00Z',
    attentionDate: '2026-05-09T09:15:00Z',
    attendedBy: 'Carlos Méndez Ríos',
    urgency: 7,
    assigneeKey: 'carlos',
    assignedToLabel: 'Carlos Méndez Ríos',
    attachments: [
      {
        id: 17,
        kind: 'image',
        side: 'comment',
        url: img(17),
        original_name: 'evidencia_horario.jpg',
        mime_type: 'image/jpeg',
        size_bytes: 184320,
      },
      {
        id: 18,
        kind: 'document',
        side: 'comment',
        url: 'https://www.w3.org/WAI/WCAG21/working-examples/pdf-note/note.pdf',
        original_name: 'captura_chat.pdf',
        mime_type: 'application/pdf',
        size_bytes: 542100,
      },
      {
        id: 19,
        kind: 'image',
        side: 'result',
        url: img(19),
        original_name: 'respuesta_oficial.png',
        mime_type: 'image/png',
        size_bytes: 95234,
      },
    ],
    extras: [
      {
        id: 9001,
        comment: 'Sigue pasando esta semana.',
        result: '',
        attachments: [
          {
            id: 20,
            kind: 'video',
            side: 'comment',
            url: vidSample,
            original_name: 'grabacion_corta.mp4',
            mime_type: 'video/mp4',
            size_bytes: 4521000,
          },
        ],
      },
    ],
  },
  {
    id: 17856,
    status: 'En Proceso',
    priority: 'Sin Asignar',
    category: 'Conflicto de interés',
    empresaId: 'demo-paco',
    ubicacionKey: 'gdl',
    sender: {
      name: 'Ricardo Jafif Pereyra',
      isAnonymous: false,
      company: 'Demo Paco',
      location: 'Guadalajara - Planta',
      department: 'Operaciones',
      area: 'Campo',
      position: 'Supervisor',
    },
    comment: 'resp app',
    result: 'test 3 revi',
    date: '2026-04-29T12:58:00Z',
    attentionDate: '2026-04-29T14:00:00Z',
    attendedBy: 'Demo Paco',
    assigneeKey: '',
    assignedToLabel: null,
    attachments: [
      {
        id: 301,
        kind: 'image',
        side: 'comment',
        url: img(301),
        original_name: 'foto_sitio.jpg',
        mime_type: 'image/jpeg',
        size_bytes: 210000,
      },
      {
        id: 302,
        kind: 'image',
        side: 'result',
        url: img(302),
        original_name: 'respuesta.jpg',
        mime_type: 'image/jpeg',
        size_bytes: 98000,
      },
    ],
    extras: [],
  },
  {
    id: 8891,
    status: 'Pendiente',
    priority: 'Media',
    category: 'Clima laboral',
    empresaId: 'acme',
    ubicacionKey: 'mty',
    sender: {
      name: 'Colaborador anónimo',
      isAnonymous: true,
      company: 'Acme SA',
      location: 'Monterrey - Obra Norte',
      department: '—',
      area: '—',
      position: '—',
    },
    comment: 'El clima en turno nocturno es tenso; falta comunicación del turno.',
    result: '',
    date: '2026-05-10T08:00:00Z',
    assigneeKey: '',
    assignedToLabel: null,
    attachments: [],
    extras: [],
  },
  {
    id: 3300,
    status: 'Atendido',
    priority: 'Baja',
    category: 'Seguridad e higiene',
    empresaId: 'demo-paco',
    ubicacionKey: 'cdmx',
    sender: {
      name: 'Pedro Ruiz Torres',
      isAnonymous: false,
      company: 'Demo Paco',
      location: 'CDMX - Oficina Central',
      department: 'Mantenimiento',
      area: 'Logística',
      position: 'Técnico',
    },
    comment: 'Falta material EPP en almacén B.',
    result: 'Se surtió el 07-May. Quedamos atentos.',
    date: '2026-05-05T16:20:00Z',
    attentionDate: '2026-05-07T11:00:00Z',
    attendedBy: 'Laura Fernández Ruiz',
    assigneeKey: 'laura',
    assignedToLabel: 'Laura Fernández Ruiz',
    attachments: [
      {
        id: 401,
        kind: 'document',
        side: 'comment',
        url: 'https://www.w3.org/WAI/WCAG21/working-examples/pdf-note/note.pdf',
        original_name: 'lista_epp.pdf',
        mime_type: 'application/pdf',
        size_bytes: 120400,
      },
    ],
    extras: [],
  },
]
