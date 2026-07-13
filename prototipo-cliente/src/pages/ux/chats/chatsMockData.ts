import type { ChatConversacion, ChatParticipante } from './chatsTypes'

const img = (seed: number) => `https://picsum.photos/seed/chat${seed}/280/200`
const vidSample =
  'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4'
const pdfSample = 'https://www.w3.org/WAI/WCAG21/working-examples/pdf-note/note.pdf'

export const CATALOG_EMPRESAS_CHAT: { value: string; label: string }[] = [
  { value: 'demo-paco', label: 'Demo Paco' },
  { value: 'acme', label: 'Acme SA' },
  { value: 'norte', label: 'Constructora del Norte SA' },
]

const ricardo: ChatParticipante = { id: 'p-ricardo', nombre: 'Ricardo Jafif Pereyra' }
const andrea: ChatParticipante = { id: 'p-andrea', nombre: 'Andrea Paola Orozco Lara' }
const claudia: ChatParticipante = { id: 'p-claudia', nombre: 'Claudia Denise Aparicio Díaz' }
const pedro: ChatParticipante = { id: 'p-pedro', nombre: 'Pedro Ruiz Torres' }
const laura: ChatParticipante = { id: 'p-laura', nombre: 'Laura Fernández Ruiz' }
const hector: ChatParticipante = { id: 'p-hector', nombre: 'Héctor Ruiz López' }
const mariana: ChatParticipante = { id: 'p-mariana', nombre: 'Mariana Torres Vega' }

/** Colaboradores disponibles para iniciar un chat nuevo (demo). */
export const CATALOG_PERSONAS_CHAT: ChatParticipante[] = [
  ricardo,
  andrea,
  claudia,
  pedro,
  laura,
  hector,
  mariana,
]

/** Conversaciones demo: individuales y grupos juntos, con adjuntos y reacciones. */
export const INITIAL_CHATS: ChatConversacion[] = [
  {
    id: 9101,
    tipo: 'individual',
    participantes: [ricardo, andrea],
    perspectivaId: ricardo.id,
    empresaId: 'demo-paco',
    archivado: false,
    noLeidos: 2,
    enLinea: true,
    mensajes: [
      {
        id: 'm-9101-1',
        autorId: andrea.id,
        texto: 'Hola Ricardo, ¿me compartes la carátula de operación?',
        at: '2026-07-01T18:55:00Z',
        attachments: [],
        reacciones: [],
      },
      {
        id: 'm-9101-2',
        autorId: ricardo.id,
        texto: 'docu',
        at: '2026-07-01T19:02:00Z',
        attachments: [
          {
            id: 511,
            kind: 'document',
            side: 'comment',
            url: pdfSample,
            original_name: '03 IEBO CARÁTULA DE OPERACIÓN.docx',
            mime_type:
              'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            size_bytes: 1677722,
          },
        ],
        reacciones: [{ etiqueta: 'Me divierte', conteo: 1 }],
      },
      {
        id: 'm-9101-3',
        autorId: ricardo.id,
        texto: 'video',
        at: '2026-07-01T19:03:00Z',
        attachments: [
          {
            id: 512,
            kind: 'video',
            side: 'comment',
            url: vidSample,
            original_name: 'recorrido_planta.mp4',
            mime_type: 'video/mp4',
            size_bytes: 4521000,
          },
        ],
        reacciones: [
          { etiqueta: 'Me encanta', conteo: 1 },
          { etiqueta: 'Me entristece', conteo: 1 },
        ],
      },
      {
        id: 'm-9101-4',
        autorId: andrea.id,
        texto: 'retaas',
        at: '2026-07-02T16:46:00Z',
        attachments: [],
        reacciones: [{ etiqueta: 'Me asombra', conteo: 1 }],
      },
      {
        id: 'm-9101-5',
        autorId: andrea.id,
        texto: 'respondifo',
        at: '2026-07-02T17:02:00Z',
        attachments: [],
        reacciones: [],
      },
      {
        id: 'm-9101-6',
        autorId: andrea.id,
        texto: 'message',
        at: '2026-07-02T17:05:00Z',
        attachments: [],
        reacciones: [
          { etiqueta: 'Me gusta', conteo: 1 },
          { etiqueta: 'Me enoja', conteo: 1 },
        ],
      },
      {
        id: 'm-9101-7',
        autorId: ricardo.id,
        texto: 'Te mando también la foto del pizarrón con los pendientes.',
        at: '2026-07-02T17:06:00Z',
        attachments: [
          {
            id: 513,
            kind: 'image',
            side: 'comment',
            url: img(513),
            original_name: 'pizarron_pendientes.jpg',
            mime_type: 'image/jpeg',
            size_bytes: 214000,
          },
        ],
        reacciones: [{ etiqueta: 'Me divierte', conteo: 1 }],
      },
      {
        id: 'm-9101-8',
        autorId: andrea.id,
        texto: 'esdrerr',
        at: '2026-07-02T17:07:00Z',
        attachments: [],
        reacciones: [{ etiqueta: 'Me encanta', conteo: 1 }],
      },
    ],
  },
  {
    id: 9102,
    tipo: 'individual',
    participantes: [claudia, pedro],
    perspectivaId: claudia.id,
    empresaId: 'acme',
    archivado: false,
    noLeidos: 0,
    mensajes: [
      {
        id: 'm-9102-1',
        autorId: pedro.id,
        texto: '¿Ya quedó el alta del nuevo ingreso?',
        at: '2026-05-22T15:10:00Z',
        attachments: [],
        reacciones: [],
      },
      {
        id: 'm-9102-2',
        autorId: claudia.id,
        texto: 'Se tiene que volver a cargar el chat para verlo reflejado.',
        at: '2026-05-22T15:26:00Z',
        attachments: [],
        reacciones: [{ etiqueta: 'Me gusta', conteo: 1 }],
      },
    ],
  },
  {
    id: 9103,
    tipo: 'grupo',
    nombreGrupo: 'Equipo Nómina Norte',
    participantes: [ricardo, laura, hector, mariana],
    perspectivaId: laura.id,
    empresaId: 'norte',
    archivado: false,
    noLeidos: 5,
    mensajes: [
      {
        id: 'm-9103-1',
        autorId: hector.id,
        texto: 'Buenos días, ¿alguien tiene el layout de incidencias de junio?',
        at: '2026-07-02T14:00:00Z',
        attachments: [],
        reacciones: [],
      },
      {
        id: 'm-9103-2',
        autorId: laura.id,
        texto: 'Aquí va, revisa la pestaña de tiempo extra.',
        at: '2026-07-02T14:12:00Z',
        attachments: [
          {
            id: 521,
            kind: 'document',
            side: 'comment',
            url: pdfSample,
            original_name: 'incidencias_junio.xlsx',
            mime_type:
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            size_bytes: 88400,
          },
        ],
        reacciones: [{ etiqueta: 'Me gusta', conteo: 3 }],
      },
      {
        id: 'm-9103-3',
        autorId: mariana.id,
        texto: 'Foto del checador que reportaron en obra:',
        at: '2026-07-02T14:30:00Z',
        attachments: [
          {
            id: 522,
            kind: 'image',
            side: 'comment',
            url: img(522),
            original_name: 'checador_obra.jpg',
            mime_type: 'image/jpeg',
            size_bytes: 190300,
          },
        ],
        reacciones: [{ etiqueta: 'Me asombra', conteo: 2 }],
      },
      {
        id: 'm-9103-4',
        autorId: ricardo.id,
        texto: 'Lo reviso con mantenimiento y les aviso.',
        at: '2026-07-02T15:05:00Z',
        attachments: [],
        reacciones: [],
      },
    ],
  },
  {
    id: 9104,
    tipo: 'individual',
    participantes: [ricardo, mariana],
    perspectivaId: ricardo.id,
    empresaId: 'demo-paco',
    archivado: false,
    noLeidos: 1,
    mensajes: [
      {
        id: 'm-9104-1',
        autorId: mariana.id,
        texto: 'Audio',
        at: '2026-07-02T17:36:00Z',
        attachments: [
          {
            id: 531,
            kind: 'video',
            side: 'comment',
            url: vidSample,
            original_name: 'nota_voz.mp4',
            mime_type: 'video/mp4',
            size_bytes: 1204000,
          },
        ],
        reacciones: [],
      },
    ],
  },
  {
    id: 9105,
    tipo: 'grupo',
    nombreGrupo: 'Brigada Seguridad CDMX',
    participantes: [pedro, hector, claudia],
    perspectivaId: pedro.id,
    empresaId: 'acme',
    archivado: true,
    noLeidos: 0,
    mensajes: [
      {
        id: 'm-9105-1',
        autorId: hector.id,
        texto: 'Cerramos el simulacro sin incidentes. Gracias a todos.',
        at: '2026-04-18T19:20:00Z',
        attachments: [
          {
            id: 541,
            kind: 'image',
            side: 'comment',
            url: img(541),
            original_name: 'evidencia_simulacro.png',
            mime_type: 'image/png',
            size_bytes: 152800,
          },
        ],
        reacciones: [{ etiqueta: 'Me encanta', conteo: 2 }],
      },
    ],
  },
  {
    id: 9106,
    tipo: 'individual',
    participantes: [laura, hector],
    perspectivaId: laura.id,
    empresaId: 'norte',
    archivado: true,
    noLeidos: 0,
    mensajes: [
      {
        id: 'm-9106-1',
        autorId: laura.id,
        texto: 'Este canal ya no se usa; seguimos en el grupo de nómina.',
        at: '2026-03-30T12:00:00Z',
        attachments: [],
        reacciones: [],
      },
    ],
  },
]
