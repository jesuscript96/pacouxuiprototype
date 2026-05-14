/** Catálogo (mock) de temas para la Voz del colaborador. */
export type TemaVozRow = {
  id: number
  nombre: string
  descripcion: string
  /** Si es `null`, el tema aplica a todas las empresas (Global). */
  empresaId: string | null
  /**
   * IDs explícitos de colaboradores que verán este tema en la app.
   * Si está vacío significa que el usuario quitó a todos manualmente.
   * Por defecto al crear/editar se rellena con todo el pool (todos seleccionados).
   */
  destinatarioIds: string[]
  /** ISO string para ordenar y mostrar fecha de creación. */
  creadoEn: string
}

/** Persona del pool de destinatarios (vista mínima para el catálogo). */
export type DestinatarioMock = {
  id: string
  nombre: string
  empresaId: string
  puesto: string
}

export type EmpresaOpcion = {
  id: string
  nombre: string
}

/**
 * Empresas disponibles para el filtro superior y para la asignación exclusiva.
 * Reutilizamos los mismos valores que el módulo Voz del colaborador para que
 * los catálogos se sientan parte del mismo dominio.
 */
export const EMPRESAS_TEMAS_VOZ: EmpresaOpcion[] = [
  { id: 'demo-paco', nombre: 'Demo Paco' },
  { id: 'acme', nombre: 'Acme SA' },
  { id: 'norte', nombre: 'Constructora del Norte SA' },
  { id: 'ejemplo', nombre: 'Empresa Ejemplo S.A. de C.V.' },
  { id: 'prueba1', nombre: 'Prueba1' },
]

export function empresaNombre(empresaId: string | null | undefined): string {
  if (!empresaId) {
    return 'Global'
  }
  return EMPRESAS_TEMAS_VOZ.find((e) => e.id === empresaId)?.nombre ?? 'Empresa'
}

/** Lista compacta de puestos para mockear destinatarios. */
const PUESTOS_MOCK = [
  'Vendedor',
  'Líder de tienda',
  'Cajera',
  'Almacenista',
  'Supervisor',
  'Gerente de zona',
  'Auxiliar administrativo',
  'Operador',
  'Recursos humanos',
  'Encargado de turno',
]

const NOMBRES_MOCK = [
  'Ana García López',
  'Carlos Méndez Ruiz',
  'Laura Pérez Soto',
  'Diego Hernández Costa',
  'Mariana Torres Vega',
  'Roberto Álvarez Cruz',
  'Paola Jiménez Solís',
  'Héctor Ruiz López',
  'Valeria Ortiz Camacho',
  'Andrés Cantú Flores',
  'Fernanda Gil Ramos',
  'Óscar Navarro Peña',
  'Nadia Espinoza Meza',
  'Ricardo Sánchez Pérez',
  'Claudia Aparicio Díaz',
  'Jorge Luis Molina',
  'Priscila Orozco Lara',
  'Daniela Romero Vega',
  'Miguel Ángel Reyes',
  'Sofía Camacho Luna',
]

function buildMockDestinatarios(): DestinatarioMock[] {
  const list: DestinatarioMock[] = []
  EMPRESAS_TEMAS_VOZ.forEach((empresa, eIdx) => {
    const cantidad = 5 + (eIdx % 3) // 5, 6 o 7 por empresa
    for (let i = 0; i < cantidad; i++) {
      const idxNombre = (eIdx * 7 + i) % NOMBRES_MOCK.length
      const idxPuesto = (eIdx * 3 + i) % PUESTOS_MOCK.length
      list.push({
        id: `${empresa.id}-${i + 1}`,
        nombre: NOMBRES_MOCK[idxNombre]!,
        empresaId: empresa.id,
        puesto: PUESTOS_MOCK[idxPuesto]!,
      })
    }
  })
  return list
}

/** Pool completo de destinatarios disponibles para todos los temas. */
export const MOCK_DESTINATARIOS_VOZ: DestinatarioMock[] = buildMockDestinatarios()

/**
 * Devuelve los destinatarios visibles para un tema según su `empresaId`.
 * - `null` → todos (tema global).
 * - id de empresa → solo los de esa empresa.
 */
export function destinatariosDePool(empresaId: string | null): DestinatarioMock[] {
  if (!empresaId) {
    return MOCK_DESTINATARIOS_VOZ
  }
  return MOCK_DESTINATARIOS_VOZ.filter((d) => d.empresaId === empresaId)
}

/** IDs de todos los destinatarios del pool calculado a partir del estado del formulario. */
export function todosLosDestinatariosIds(empresaId: string | null): string[] {
  return destinatariosDePool(empresaId).map((d) => d.id)
}

type TemaSeed = Omit<TemaVozRow, 'destinatarioIds'>

const SEED_TEMAS: TemaSeed[] = [
  {
    id: 1,
    nombre: 'Tema Voz Ejemplo',
    descripcion: 'Tema genérico de prueba para temas globales de voz del colaborador.',
    empresaId: null,
    creadoEn: '2026-05-04T10:00:00.000Z',
  },
  {
    id: 2,
    nombre: 'PRUEBA',
    descripcion: 'Tema de prueba aplicado únicamente a Empresa Ejemplo S.A. de C.V.',
    empresaId: 'ejemplo',
    creadoEn: '2026-05-12T08:15:00.000Z',
  },
  {
    id: 3,
    nombre: 'PRUEBA2',
    descripcion: 'Tema exclusivo asociado a Prueba1 para validar exclusividad por empresa.',
    empresaId: 'prueba1',
    creadoEn: '2026-05-12T09:30:00.000Z',
  },
  {
    id: 4,
    nombre: 'Servicios Generales',
    descripcion:
      'Si detectas necesidades relacionadas con limpieza, reposición de suministros (jabón, papel higiénico, garrafones de agua) o el orden general en áreas comunes, por favor avísanos para atenderlo a la brevedad posible.',
    empresaId: null,
    creadoEn: '2026-05-13T11:00:00.000Z',
  },
  {
    id: 5,
    nombre: 'Sugerencias de mejora',
    descripcion: 'Ideas y propuestas para mejorar procesos, herramientas o el clima laboral.',
    empresaId: null,
    creadoEn: '2026-05-13T11:10:00.000Z',
  },
  {
    id: 6,
    nombre: 'Reconocimientos',
    descripcion: 'Felicitaciones a compañeros o líderes que hayan tenido un desempeño destacado.',
    empresaId: null,
    creadoEn: '2026-05-13T11:20:00.000Z',
  },
  {
    id: 7,
    nombre: 'Capacitación',
    descripcion: 'Solicitudes de cursos, materiales o apoyo formativo para el equipo.',
    empresaId: null,
    creadoEn: '2026-05-13T11:30:00.000Z',
  },
  {
    id: 8,
    nombre: 'Cultura y comunicación interna',
    descripcion: 'Temas relacionados con el ambiente, valores y formas de colaborar.',
    empresaId: 'ejemplo',
    creadoEn: '2026-05-13T11:40:00.000Z',
  },
  {
    id: 9,
    nombre: 'Conflictos laborales',
    descripcion: 'Situaciones interpersonales o de equipo que requieran atención formal.',
    empresaId: null,
    creadoEn: '2026-05-13T11:50:00.000Z',
  },
  {
    id: 10,
    nombre: 'Seguridad e higiene',
    descripcion:
      'Reporta condiciones inseguras, falta de equipo de protección o riesgos en las instalaciones.',
    empresaId: null,
    creadoEn: '2026-05-13T12:00:00.000Z',
  },
]

/** Semillas iniciales en memoria; persistencia en localStorage en `temasVozStorage`. */
export const INITIAL_TEMAS_VOZ: TemaVozRow[] = SEED_TEMAS.map((seed) => ({
  ...seed,
  destinatarioIds: todosLosDestinatariosIds(seed.empresaId),
}))

/** Opción especial para el filtro: «Todas las empresas». */
export const FILTRO_TODAS = '__todas__'
/** Opción especial para el filtro: «Solo globales». */
export const FILTRO_GLOBALES = '__globales__'
