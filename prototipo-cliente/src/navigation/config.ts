export type NavGroupId = 'inicio' | 'storybook' | 'ux'

export type StorybookSlug =
  | 'colores'
  | 'tipografia'
  | 'enfasis'
  | 'degradados'
  | 'marca'
  | 'secciones'
  | 'grids'
  | 'botones'
  | 'badges'
  | 'tarjetas'
  | 'tablas'
  | 'tablas-estilo-notion'
  | 'campos-texto'
  | 'iconos'
  | 'selects'
  | 'checkboxes'
  | 'date-pickers'
  | 'notificaciones'
  | 'modales'
  | 'wizard-progreso'

export type StorybookEntry = {
  slug: StorybookSlug
  label: string
  sort: number
}

export const STORYBOOK_PAGES: StorybookEntry[] = [
  { slug: 'colores', label: 'Colores', sort: 1 },
  { slug: 'tipografia', label: 'Tipografía', sort: 2 },
  { slug: 'enfasis', label: 'Énfasis', sort: 3 },
  { slug: 'degradados', label: 'Degradados', sort: 4 },
  { slug: 'marca', label: 'Marca', sort: 5 },
  { slug: 'secciones', label: 'Secciones', sort: 6 },
  { slug: 'grids', label: 'Grids', sort: 7 },
  { slug: 'botones', label: 'Botones', sort: 8 },
  { slug: 'badges', label: 'Badges', sort: 9 },
  { slug: 'tarjetas', label: 'Tarjetas', sort: 10 },
  { slug: 'tablas', label: 'Tablas', sort: 11 },
  { slug: 'tablas-estilo-notion', label: 'Tablas estilo Notion', sort: 12 },
  { slug: 'campos-texto', label: 'Campos de texto', sort: 13 },
  { slug: 'iconos', label: 'Iconos', sort: 14 },
  { slug: 'selects', label: 'Selects', sort: 15 },
  { slug: 'checkboxes', label: 'Checkboxes', sort: 16 },
  { slug: 'date-pickers', label: 'Date pickers', sort: 17 },
  { slug: 'notificaciones', label: 'Notificaciones', sort: 18 },
  { slug: 'modales', label: 'Modales', sort: 19 },
  { slug: 'wizard-progreso', label: 'Progreso wizard', sort: 20 },
]

export function isStorybookSlug(s: string): s is StorybookSlug {
  return STORYBOOK_PAGES.some((p) => p.slug === s)
}

export type UxNavItem = {
  path: string
  label: string
  parent?: string
}

/** Rutas del prototipo (sin tenant). */
export const paths = {
  inicio: '/inicio',
  storybook: (slug: StorybookSlug) => `/storybook/${slug}`,
  analiticos: '/ux/analiticos',
  analiticosReport: (segment: string) => `/ux/analiticos/${segment}`,
  solicitudes: '/ux/solicitudes',
  catalogos: '/ux/catalogos',
  catalogosEmpresas: '/ux/catalogos/empresas',
  catalogosEmpresaNueva: '/ux/catalogos/empresas/nueva',
  catalogosEmpresaEditar: (id: string) => `/ux/catalogos/empresas/${encodeURIComponent(id)}/editar`,
  documentos: '/ux/documentos-corporativos',
  cartasSua: '/ux/cartas-sua',
  colaboradores: '/ux/colaboradores',
  vacantes: '/ux/vacantes',
  roles: '/ux/roles',
  /** Query `role` con id del rol mock (ver `INITIAL_MOCK_ROLES`). */
  rolesEditar: (roleId: string) =>
    `/ux/roles/edit?role=${encodeURIComponent(roleId)}`,
  permisos: '/ux/permisos',
  bajas: '/ux/bajas-colaboradores',
} as const

export const UX_PARENT_LABELS = {
  analiticos: 'Analíticos',
  solicitudes: 'Solicitudes',
  catalogosColab: 'Catálogos Colaboradores',
  catalogoEmpresas: 'Catálogo de empresas',
  gestionPersonal: 'Gestión de personal',
  reclutamiento: 'Reclutamiento',
  cartasSua: 'Cartas SUA',
  documentos: 'Documentos Corporativos',
  configuracion: 'Configuración',
} as const

export const TABLEAU_SEGMENTS: { segment: string; label: string }[] = [
  { segment: 'voz-colaborador', label: 'Voz del colaborador' },
  { segment: 'transacciones', label: 'Transacciones' },
  { segment: 'satisfaccion-enps', label: 'Satisfacción eNPS' },
  { segment: 'salud-mental', label: 'Salud mental' },
  { segment: 'rotacion-personal', label: 'Rotación de personal' },
  { segment: 'resultados-nom-035', label: 'Resultados NOM-035' },
  { segment: 'reconocimientos', label: 'Reconocimientos' },
  { segment: 'reclutamiento', label: 'Reclutamiento' },
  { segment: 'mensajes', label: 'Mensajes' },
  { segment: 'encuestas', label: 'Encuestas' },
  { segment: 'encuestas-plan-accion', label: 'Encuestas plan de acción' },
  { segment: 'descuentos', label: 'Descuentos' },
  { segment: 'demograficos', label: 'Demográficos' },
]
